<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;  // ← INI YANG HILANG

class OutgoingTransaction extends Model
{
    protected $fillable = [
        'no_invoice', 'customer_id', 'product_id',
        'sold_at', 'payment_status', 'due_date',
        'box_qty', 'total_pcs',
        'hpp_per_box', 'sell_per_box', 'hpp_per_pcs', 'sell_per_pcs',
        'subtotal', 'total_hpp', 'profit', 'paid_amount',  // ← pastikan paid_amount ada
    ];

    protected $casts = [
        'sold_at'       => 'datetime',
        'due_date'      => 'date',
        'box_qty'       => 'integer',
        'total_pcs'     => 'integer',
        'hpp_per_box'   => 'decimal:2',
        'sell_per_box'  => 'decimal:2',
        'hpp_per_pcs'   => 'decimal:2',
        'sell_per_pcs'  => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'total_hpp'     => 'decimal:2',
        'profit'        => 'decimal:2',
        'paid_amount'   => 'decimal:2',  // ← tambahkan
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }

    public function payments(): HasMany  // ← sekarang compile OK
    {
        return $this->hasMany(ReceivablePayment::class);
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
        if (! $this->due_date) return 0;
        return (int) now()->startOfDay()->diffInDays($this->due_date, false);
    }

    public function getAgingStatusAttribute(): string
    {
        if ($this->payment_status === 'lunas' || $this->remaining <= 0) return 'lunas';
        if (! $this->due_date) return 'normal';

        $days = $this->days_to_due;
        if ($days < 0)  return 'overdue';
        if ($days <= 3) return 'due_soon';
        if ($days <= 7) return 'warning';
        return 'normal';
    }

    protected static function booted(): void
    {
        static::creating(function (self $trx) {
            $product = $trx->product;
            if (! $product) return;

            $newStock = $product->stock_pcs - $trx->total_pcs;
            if ($newStock < 0) {
                throw new \RuntimeException(
                    "Stok tidak mencukupi untuk {$trx->no_invoice} ({$product->name}). " .
                    "Dibutuhkan {$trx->total_pcs} Pcs, tersedia {$product->stock_pcs} Pcs."
                );
            }
        });

        static::created(function (self $trx) {
            $trx->product()->decrement('stock_pcs', $trx->total_pcs);
        });

        static::deleted(function (self $trx) {
            $trx->product()->increment('stock_pcs', $trx->total_pcs);
        });
    }
}