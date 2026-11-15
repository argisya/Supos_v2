<?php

namespace Database\Seeders;

use App\Models\OutgoingTransaction;
use App\Models\ReceivablePayment;
use Illuminate\Database\Seeder;

class ReceivablePaymentSeeder extends Seeder
{
    public function run(): void
    {
        // [no_invoice, amount, days_ago_paid, method, account]
        $rows = [
            ['INV-2025/10/742', 3_000_000, 2, 'Transfer Bank', 'BCA'],
            ['INV-2025/10/720', 2_460_000, 3, 'Transfer Bank', 'Mandiri'],
        ];

        foreach ($rows as [$noInv, $amount, $daysAgo, $method, $account]) {
            $trx = OutgoingTransaction::where('no_invoice', $noInv)->first();
            if (! $trx) continue;

            ReceivablePayment::updateOrCreate(
                ['outgoing_transaction_id' => $trx->id, 'amount' => $amount],
                [
                    'customer_id'    => $trx->customer_id,
                    'paid_at'        => now()->subDays($daysAgo),
                    'payment_method' => $method,
                    'account'        => $account,
                    'reference_no'   => 'REF-' . rand(100000, 999999),
                    'notes'          => 'Cicilan pembayaran via ' . $method,
                ]
            );
        }
    }
}