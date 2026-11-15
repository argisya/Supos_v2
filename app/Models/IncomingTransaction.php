<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class IncomingTransaction extends Model
{
    protected $fillable = [
        'no_faktur', 'supplier_id', 'product_id',
        'received_at', 'payment_status', 'due_date',
        'box_qty', 'total_pcs',
        'price_per_box', 'price_per_pcs', 'subtotal',
    ];

    protected $casts = [
        'received_at'   => 'date',
        'due_date'      => 'date',
        'box_qty'       => 'integer',
        'total_pcs'     => 'integer',
        'price_per_box' => 'decimal:2',
        'price_per_pcs' => 'decimal:2',
        'subtotal'      => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (self $trx) {
            $trx->product()->increment('stock_pcs', $trx->total_pcs);
        });

        static::deleting(function (self $trx) {
            $product = Product::query()->lockForUpdate()->findOrFail($trx->product_id);

            if ($product->stock_pcs < $trx->total_pcs) {
                throw new RuntimeException(
                    "Tidak bisa rollback: stok {$product->name} ({$product->stock_pcs} Pcs) " .
                    "lebih kecil dari total incoming ({$trx->total_pcs} Pcs). Sebagian stok sudah terjual lewat Barang Keluar."
                );
            }

            $product->decrement('stock_pcs', $trx->total_pcs);
        });
    }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }
}