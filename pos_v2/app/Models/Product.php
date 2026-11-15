<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'sku', 'barcode', 'name', 'variant',
        'category_id', 'box_pcs_ratio', 'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeByBoxSize(Builder $q, string $size): Builder
    {
        return match ($size) {
            'compact' => $q->where('box_pcs_ratio', '<=', 12),
            'medium'  => $q->whereBetween('box_pcs_ratio', [13, 39]),
            'large'   => $q->where('box_pcs_ratio', '>=', 40),
            default   => $q,
        };
    }
}