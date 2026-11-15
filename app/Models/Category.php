<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'color_key'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Mapping warna → class Tailwind */
    public function badgeClass(): string
    {
        return match ($this->color_key) {
            'primary'   => 'bg-primary/10 text-primary',
            'secondary' => 'bg-secondary-container/20 text-secondary',
            'tertiary'  => 'bg-tertiary-container/20 text-tertiary',
            default     => 'bg-surface-container-highest text-on-surface',
        };
    }
}