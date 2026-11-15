<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'type', 'credit_limit', 'pic_name', 'phone'];

    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(OutgoingTransaction::class);
    }

    public function getUsedCreditAttribute(): float
    {
        return (float) $this->outgoingTransactions()
            ->where('payment_status', 'tempo')
            ->sum(\DB::raw('GREATEST(0, subtotal - paid_amount)'));
    }

    public function getAvailableCreditAttribute(): float
    {
        return max(0, (float) $this->credit_limit - $this->used_credit);
    }

    public function getWhatsappNumberAttribute(): ?string
    {
        if (! $this->phone) return null;
        $digits = preg_replace('/[^0-9]/', '', $this->phone);
        return '62' . ltrim($digits, '0');
    }
}