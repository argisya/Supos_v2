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

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }
}