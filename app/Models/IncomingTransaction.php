<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomingTransaction extends Model
{
    protected $fillable = [
        'no_faktur', 'supplier_id', 'product_id',
        'received_at', 'payment_status', 'due_date',
        'box_qty', 'total_pcs',
        'price_per_box', 'price_per_pcs', 'subtotal',
        'paid_amount',
    ];

    protected $casts = [
        'received_at'   => 'date',
        'due_date'      => 'date',
        'box_qty'       => 'integer',
        'total_pcs'     => 'integer',
        'price_per_box' => 'decimal:2',
        'price_per_pcs' => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'paid_amount'   => 'decimal:2',
    ];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->subtotal - (float) $this->paid_amount);
    }

    public function getPaidPctAttribute(): float
    {
        return $this->subtotal > 0 ? round(($this->paid_amount / $this->subtotal) * 100, 1) : 0;
    }

    public function getDaysToDueAttribute(): int
    {
        if (! $this->due_date) {
            return 0;
        }
        return (int) now()->startOfDay()->diffInDays($this->due_date, false);
    }

    protected static function booted(): void
    {
        static::creating(function (self $trx) {
            if ($trx->payment_status === 'lunas') {
                $trx->paid_amount = $trx->subtotal;
                $trx->due_date = null;
            }
        });

        static::created(function (self $trx) {
            $trx->product()->increment('stock_pcs', $trx->total_pcs);

            if ($trx->payment_status === 'lunas') {
                CashOut::create([
                    'code'         => CashOut::generateCode(true, true),
                    'category'     => 'kulakan_lunas',
                    'account'      => 'kas_tunai',
                    'amount'       => $trx->subtotal,
                    'paid_at'      => $trx->received_at ?? now(),
                    'source_type'  => self::class,
                    'source_id'    => $trx->id,
                    'reference_no' => $trx->no_faktur,
                    'notes'        => "Kulakan lunas {$trx->box_qty} Box {$trx->product?->name}",
                ]);
            }
        });

        static::updated(function (self $trx) {
            // Jika dilunasi langsung via tombol payNow() di Barang Masuk
            if ($trx->wasChanged('payment_status') && $trx->payment_status === 'lunas') {
                $sisa = max(0, (float) $trx->subtotal - (float) $trx->paid_amount);
                $alreadyLogged = CashOut::where('source_type', self::class)
                    ->where('source_id', $trx->id)
                    ->exists();

                if ($sisa > 0 && ! $alreadyLogged) {
                    CashOut::create([
                        'code'         => CashOut::generateCode(true, true),
                        'category'     => 'pelunasan_hutang_supplier',
                        'account'      => 'kas_tunai',
                        'amount'       => $sisa,
                        'paid_at'      => now(),
                        'source_type'  => self::class,
                        'source_id'    => $trx->id,
                        'reference_no' => $trx->no_faktur,
                        'notes'        => "Pelunasan langsung faktur {$trx->no_faktur} ({$trx->supplier?->name})",
                    ]);

                    $trx->paid_amount = $trx->subtotal;
                    $trx->saveQuietly();
                }
            }
        });

        static::deleted(function (self $trx) {
            $product = $trx->product;
            if ($product) {
                $newStock = max(0, $product->stock_pcs - $trx->total_pcs);
                $product->update(['stock_pcs' => $newStock]);
            }

            CashOut::where('source_type', self::class)
                ->where('source_id', $trx->id)
                ->delete();
        });
    }
}