<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutgoingTransaction extends Model
{
    protected $fillable = [
        'no_invoice', 'customer_id', 'product_id',
        'sold_at', 'payment_status', 'due_date',
        'box_qty', 'total_pcs',
        'hpp_per_box', 'sell_per_box', 'hpp_per_pcs', 'sell_per_pcs',
        'subtotal', 'total_hpp', 'profit',
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
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }

    protected static function booted(): void
    {
        // Stok KELUAR saat transaksi dibuat
        static::created(function (self $trx) {
            $trx->product->decrement('stock_pcs', $trx->total_pcs);
        });

        // Stok KEMBALI saat transaksi dihapus (rollback)
        static::deleted(function (self $trx) {
            $trx->product->increment('stock_pcs', $trx->total_pcs);
        });
    }
}