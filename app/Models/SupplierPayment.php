<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = [
        'incoming_transaction_id', 'supplier_id', 'paid_at', 'amount',
        'payment_method', 'account', 'reference_no', 'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(IncomingTransaction::class, 'incoming_transaction_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    protected static function booted(): void
    {
        static::created(function (self $p) {
            $p->syncTransactionPaidAmount();

            $trx = $p->transaction;
            if (! $trx) return;

            $account = match (true) {
                str_contains($p->account ?? '', 'BCA')     => 'bca',
                str_contains($p->account ?? '', 'Mandiri') => 'mandiri',
                default                                    => 'kas_tunai',
            };

            CashOut::create([
                'code'         => CashOut::generateCode(true),
                'category'     => 'pelunasan_hutang_supplier',
                'account'      => $account,
                'amount'       => $p->amount,
                'paid_at'      => $p->paid_at ?? now(),
                'source_type'  => self::class,
                'source_id'    => $p->id,
                'reference_no' => $trx->no_faktur,
                'notes'        => "Pelunasan hutang {$trx->supplier?->name}",
            ]);
        });

        static::updated(fn (self $p) => $p->syncTransactionPaidAmount());

        static::deleted(function (self $p) {
            $p->syncTransactionPaidAmount();
            CashOut::where('source_type', self::class)
                ->where('source_id', $p->id)
                ->delete();
        });
    }

    public function syncTransactionPaidAmount(): void
    {
        if (! $this->incoming_transaction_id) return;
        $trx = IncomingTransaction::find($this->incoming_transaction_id);
        if (! $trx) return;

        $totalPaid = (float) $trx->payments()->sum('amount');
        $trx->paid_amount = $totalPaid;

        if ($totalPaid >= (float) $trx->subtotal - 0.01) {
            $trx->payment_status = 'lunas';
            $trx->due_date = null;
        } else {
            $trx->payment_status = 'tempo';
        }

        $trx->saveQuietly();
    }
}