<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class CashIn extends Model
{
    /** Master account terdaftar */
    public const ACCOUNTS = [
        'bca' => ['label' => 'BCA Bisnis', 'short' => 'BCA', 'icon' => 'account_balance', 'color' => 'primary'],
        'mandiri' => ['label' => 'Mandiri Giro', 'short' => 'MDR', 'icon' => 'account_balance', 'color' => 'secondary'],
        'kas_tunai' => ['label' => 'Kas Laci Toko', 'short' => 'Tunai', 'icon' => 'point_of_sale', 'color' => 'tertiary'],
    ];

    /** Kategori lengkap (auto + manual) */
    public const CATEGORIES = [
        'penjualan_tunai' => ['label' => 'Penjualan Tunai Lunas', 'color' => 'primary', 'auto' => true],
        'pelunasan_piutang' => ['label' => 'Pelunasan Piutang', 'color' => 'secondary', 'auto' => true],
        'refund_supplier' => ['label' => 'Refund Retur Supplier', 'color' => 'tertiary', 'auto' => false],
        'modal_pemilik' => ['label' => 'Setoran Modal Pemilik', 'color' => 'primary', 'auto' => false],
        'penjualan_palet' => ['label' => 'Penjualan Palet / Bekas', 'color' => 'tertiary', 'auto' => false],
        'pendapatan_non_ops' => ['label' => 'Pendapatan Non-Operasional', 'color' => 'secondary', 'auto' => false],
        'deposit_kembali' => ['label' => 'Pengembalian Deposit', 'color' => 'primary', 'auto' => false],
    ];

    protected $fillable = [
        'code',
        'category',
        'account',
        'amount',
        'received_at',
        'source_type',
        'source_id',
        'reference_no',
        'notes',
        'attachment_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Selalu tampilkan dalam zona waktu Asia/Jakarta (WIB) dan simpan sesuai timezone aplikasi
     * tanpa perlu mengubah APP_TIMEZONE default.
     */
    protected function receivedAt(): Attribute
    {
        $appTz = config('app.timezone', 'UTC');

        return Attribute::make(
            get: fn($value) => $value
            ? Carbon::parse($value, $appTz)->setTimezone('Asia/Jakarta')
            : null,
            set: fn($value) => $value
            ? ($value instanceof \DateTimeInterface
                ? Carbon::instance($value)->setTimezone($appTz)->format('Y-m-d H:i:s')
                : Carbon::parse($value, 'Asia/Jakarta')->setTimezone($appTz)->format('Y-m-d H:i:s'))
            : null,
        );
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

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

    /** Generate kode: KM-YYYY/MM/NNN (auto) | KM-NON-NNN (manual) */
    public static function generateCode(bool $auto = true, bool $lock = false): string
    {
        $prefix = $auto ? ('KM-' . now('Asia/Jakarta')->format('Y/m') . '/') : 'KM-NON-';
        $query = self::where('code', 'like', $prefix . '%')->orderByDesc('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        $last = $query->value('code');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}