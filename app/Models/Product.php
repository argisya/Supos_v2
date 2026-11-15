<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
    'category_id',
    'sku',
    'barcode',
    'name',
    'box_pcs_ratio',
    'min_stock_box',
    'stock_pcs',
    'is_active',
];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(IncomingTransaction::class);
    }

    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(OutgoingTransaction::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeByBoxSize(Builder $q, string $size): Builder
    {
        return match ($size) {
            'compact' => $q->where('box_pcs_ratio', '<=', 12),
            'medium' => $q->whereBetween('box_pcs_ratio', [13, 39]),
            'large' => $q->where('box_pcs_ratio', '>=', 40),
            default => $q,
        };
    }
}
