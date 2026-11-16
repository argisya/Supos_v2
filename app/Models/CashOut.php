<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashOut extends Model
{
    public const ACCOUNTS = CashIn::ACCOUNTS; // reuse: bca | mandiri | kas_tunai

    public const CATEGORIES = [
        'kulakan_lunas'             => ['label' => 'Kulakan Tunai (COD)',      'color' => 'primary',   'auto' => true],
        'pelunasan_hutang_supplier' => ['label' => 'Pelunasan Hutang Supplier','color' => 'primary',   'auto' => true],
        'biaya_operasional'         => ['label' => 'Biaya Operasional',         'color' => 'tertiary',  'auto' => false],
        'biaya_listrik'             => ['label' => 'Listrik & Utilitas',        'color' => 'tertiary',  'auto' => false],
        'gaji_staff'                => ['label' => 'Gaji & Kasbon Staff',       'color' => 'secondary', 'auto' => false],
        'logistik_armada'           => ['label' => 'Logistik & Armada',         'color' => 'tertiary',  'auto' => false],
        'bensin_distribusi'         => ['label' => 'Bensin & Tol Distribusi',   'color' => 'tertiary',  'auto' => false],
        'lain_lain'                 => ['label' => 'Pengeluaran Lain',          'color' => 'neutral',   'auto' => false],
    ];

    protected $fillable = [
        'code', 'category', 'account', 'amount', 'paid_at',
        'source_type', 'source_id', 'reference_no', 'notes', 'attachment_path',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function source(): MorphTo { return $this->morphTo(); }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category]['label'] ?? ucfirst($this->category);
    }

    public function getAccountLabelAttribute(): string
    {
        return self::ACCOUNTS[$this->account]['label'] ?? ucfirst($this->account);
    }

    public function getIsAutoAttribute(): bool
    {
        return (bool) ($this->source_id ?? false);
    }

    public static function generateCode(bool $auto = true): string
    {
        if ($auto) {
            $prefix = 'KK-' . now()->format('Y/m') . '/';
            $last = self::where('code', 'like', $prefix . '%')->orderByDesc('id')->value('code');
            $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
            return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
        }
        $prefix = 'KK-NON-';
        $last = self::where('code', 'like', $prefix . '%')->orderByDesc('id')->value('code');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}