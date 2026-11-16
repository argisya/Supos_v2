<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivablePayment extends Model
{
    protected $fillable = [
        'outgoing_transaction_id',
        'customer_id',
        'paid_at',
        'amount',
        'payment_method',
        'account',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    // ============ RELATIONSHIPS ============

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(OutgoingTransaction::class, 'outgoing_transaction_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // ============ EVENTS ============

    protected static function booted(): void
    {
        // Sync paid_amount ke transaksi induk
        static::created(function (self $p) {
            $p->syncTransactionPaidAmount();
        });
        static::updated(function (self $p) {
            $p->syncTransactionPaidAmount();
        });
        static::deleted(function (self $p) {
            $p->syncTransactionPaidAmount();
        });

        // Auto-journal Kas Masuk
        static::created(function (self $p) {
            $trx = $p->transaction;
            if (! $trx) {
                return;
            }

            $account = match (true) {
                str_contains($p->account ?? '', 'BCA')     => 'bca',
                str_contains($p->account ?? '', 'Mandiri') => 'mandiri',
                default                                    => 'kas_tunai',
            };

            \App\Models\CashIn::create([
                'code'         => \App\Models\CashIn::generateCode(true),
                'category'     => 'pelunasan_piutang',
                'account'      => $account,
                'amount'       => $p->amount,
                'received_at'  => $p->paid_at ?? now(),
                'source_type'  => self::class,
                'source_id'    => $p->id,
                'reference_no' => $trx->no_invoice,
                'notes'        => "Pelunasan piutang {$trx->customer?->name}",
            ]);
        });

        static::deleted(function (self $p) {
            \App\Models\CashIn::where('source_type', self::class)
                ->where('source_id', $p->id)
                ->delete();
        });
    }

    // ============ HELPER ============

    public function syncTransactionPaidAmount(): void
    {
        if (! $this->outgoing_transaction_id) {
            return;
        }

        $trx = OutgoingTransaction::find($this->outgoing_transaction_id);
        if (! $trx) {
            return;
        }

        $totalPaid = (float) $trx->payments()->sum('amount');
        $trx->paid_amount = $totalPaid;

        if ($totalPaid >= (float) $trx->subtotal - 0.01) {
            $trx->payment_status = 'lunas';
        } else {
            $trx->payment_status = 'tempo';
        }

        $trx->saveQuietly();
    }
}