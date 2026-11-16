<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutgoingTransaction extends Model
{
    protected $fillable = [
        'no_invoice', 'customer_id', 'product_id',
        'sold_at', 'payment_status', 'due_date',
        'box_qty', 'total_pcs',
        'hpp_per_box', 'sell_per_box', 'hpp_per_pcs', 'sell_per_pcs',
        'subtotal', 'total_hpp', 'profit',
        'paid_amount', 'payment_account',
    ];

    protected $casts = [
        'sold_at'      => 'datetime',
        'due_date'     => 'date',
        'box_qty'      => 'integer',
        'total_pcs'    => 'integer',
        'hpp_per_box'  => 'decimal:2',
        'sell_per_box' => 'decimal:2',
        'hpp_per_pcs'  => 'decimal:2',
        'sell_per_pcs' => 'decimal:2',
        'subtotal'     => 'decimal:2',
        'total_hpp'    => 'decimal:2',
        'profit'       => 'decimal:2',
        'paid_amount'  => 'decimal:2',
    ];

    // ============ RELATIONSHIPS ============

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ReceivablePayment::class, 'outgoing_transaction_id')->latest('paid_at');
    }

    // ============ ACCESSORS ============

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->subtotal - (float) $this->paid_amount);
    }

    public function getPaidPctAttribute(): float
    {
        return (float) $this->subtotal > 0
            ? min(100, round(((float) $this->paid_amount / (float) $this->subtotal) * 100, 1))
            : 0;
    }

    public function getDaysToDueAttribute(): int
    {
        if (! $this->due_date) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($this->due_date->copy()->startOfDay(), false);
    }

    public function getAgingStatusAttribute(): string
    {
        if ($this->payment_status === 'lunas' || $this->remaining <= 0) {
            return 'lunas';
        }
        if (! $this->due_date) {
            return 'normal';
        }

        $days = $this->days_to_due;
        if ($days < 0)  return 'overdue';
        if ($days <= 3) return 'due_soon';
        if ($days <= 7) return 'warning';
        return 'normal';
    }

    // ============ MODEL EVENTS ============

    protected static function booted(): void
    {
        static::creating(function (self $trx) {
            $product = $trx->product ?? Product::find($trx->product_id);
            if (! $product) {
                return;
            }

            $newStock = $product->stock_pcs - $trx->total_pcs;
            if ($newStock < 0) {
                throw new \RuntimeException(
                    "Stok tidak mencukupi untuk {$trx->no_invoice} ({$product->name}). " .
                    "Dibutuhkan {$trx->total_pcs} Pcs, tersedia {$product->stock_pcs} Pcs."
                );
            }
        });

        static::created(function (self $trx) {
            // 1. Potong stok
            $trx->product()->decrement('stock_pcs', $trx->total_pcs);

            // 2. Auto-journal Kas Masuk jika penjualan langsung lunas
            if ($trx->payment_status === 'lunas') {
                \App\Models\CashIn::create([
                    'code'         => \App\Models\CashIn::generateCode(true),
                    'category'     => 'penjualan_tunai',
                    'account'      => $trx->payment_account ?? 'kas_tunai',
                    'amount'       => $trx->subtotal,
                    'received_at'  => $trx->sold_at ?? now(),
                    'source_type'  => self::class,
                    'source_id'    => $trx->id,
                    'reference_no' => $trx->no_invoice,
                    'notes'        => "Penjualan {$trx->box_qty} Box " . ($trx->product?->name ?? ''),
                ]);
            }
        });

        static::deleting(function (self $trx) {
            if ($trx->payments()->exists()) {
                throw new \RuntimeException(
                    "Transaksi {$trx->no_invoice} sudah memiliki riwayat cicilan piutang. Batalkan cicilan di Buku Tagihan terlebih dahulu."
                );
            }
        });

        static::deleted(function (self $trx) {
            // 1. Kembalikan stok
            $trx->product()->increment('stock_pcs', $trx->total_pcs);

            // 2. Hapus jurnal Kas Masuk terkait penjualan tunai
            \App\Models\CashIn::where('source_type', self::class)
                ->where('source_id', $trx->id)
                ->delete();
        });
    }
}