<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivablePayment extends Model
{
    protected $fillable = [
        'outgoing_transaction_id', 'customer_id', 'paid_at', 'amount',
        'payment_method', 'account', 'reference_no', 'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(OutgoingTransaction::class, 'outgoing_transaction_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function booted(): void
    {
        static::created(fn (self $p) => $p->syncTransactionPaidAmount());
        static::updated(fn (self $p) => $p->syncTransactionPaidAmount());
        static::deleted(fn (self $p) => $p->syncTransactionPaidAmount());
    }

    private function syncTransactionPaidAmount(): void
    {
        if (! $this->outgoing_transaction_id) return;

        $trx = OutgoingTransaction::find($this->outgoing_transaction_id);
        if (! $trx) return;

        $totalPaid = (float) $trx->payments()->sum('amount');
        $trx->paid_amount = $totalPaid;

        if ($totalPaid >= (float) $trx->subtotal - 0.01) {
            $trx->payment_status = 'lunas';
        } else {
            $trx->payment_status = 'tempo';
        }

        $trx->saveQuietly(); // hindari re-fire 'updated' event berantai
    }
}