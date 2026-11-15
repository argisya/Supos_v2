<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingTransaction extends Model
{
    protected $fillable = [
        'no_faktur', 'supplier_id', 'product_id',
        'received_at', 'payment_status', 'due_date',
        'box_qty', 'total_pcs',
        'price_per_box', 'price_per_pcs', 'subtotal',
    ];

    protected $casts = [
        'received_at'    => 'date',
        'due_date'       => 'date',
        'box_qty'        => 'integer',
        'total_pcs'      => 'integer',
        'price_per_box'  => 'decimal:2',
        'price_per_pcs'  => 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (self $trx) {
            // increment selalu positif, aman
            $trx->product()->increment('stock_pcs', $trx->total_pcs);
        });

        static::deleted(function (self $trx) {
            // Guard: cegah stok negatif saat rollback incoming
            // (bisa terjadi jika stok incoming ini sudah habis terjual via outgoing)
            $product  = $trx->product;
            $newStock = max(0, $product->stock_pcs - $trx->total_pcs);
            $product->update(['stock_pcs' => $newStock]);
        });
    }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }
}