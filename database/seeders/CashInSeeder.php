<?php

namespace Database\Seeders;

use App\Models\CashIn;
use App\Models\OutgoingTransaction;
use App\Models\ReceivablePayment;
use Illuminate\Database\Seeder;

class CashInSeeder extends Seeder
{
    public function run(): void
    {
        // ========== MANUAL entries (demo) ==========
        $manuals = [
            ['refund_supplier',    'bca',       550_000,   0,  'PT Indomarco Adi Prima',  'Refund retur 3 dus minyak bocor'],
            ['modal_pemilik',      'bca',     15_000_000, 1,  'Pemilik',                 'Setoran modal tambahan operasional'],
            ['penjualan_palet',    'kas_tunai', 450_000,   2,  'Pengepul Palet',          'Penjualan 30 kardus bekas'],
            ['pendapatan_non_ops', 'mandiri',   125_000,   3,  'Bank Mandiri',            'Bunga tabungan giro'],
            ['deposit_kembali',    'kas_tunai', 800_000,   4,  'CV Sumber Berkah',        'Pengembalian titipan deposit'],
        ];

        foreach ($manuals as [$cat, $acc, $amount, $daysAgo, $from, $note]) {
            $code = CashIn::generateCode(false);
            CashIn::updateOrCreate(
                ['notes' => $note],
                [
                    'code'         => $code,
                    'category'     => $cat,
                    'account'      => $acc,
                    'amount'       => $amount,
                    'received_at'  => now()->subDays($daysAgo),
                    'reference_no' => null,
                    'source_type'  => null,
                    'source_id'    => null,
                ]
            );
        }

        // ========== BACKFILL: pastikan semua outgoing lunas sudah punya cash_in ==========
        OutgoingTransaction::where('payment_status', 'lunas')
            ->whereDoesntHave('payments') // belum lunas via cicilan
            ->get()
            ->each(function ($trx) {
                if (CashIn::where('source_type', OutgoingTransaction::class)->where('source_id', $trx->id)->exists()) return;
                CashIn::create([
                    'code'         => CashIn::generateCode(true),
                    'category'     => 'penjualan_tunai',
                    'account'      => $trx->payment_account ?? 'kas_tunai',
                    'amount'       => $trx->subtotal,
                    'received_at'  => $trx->sold_at,
                    'source_type'  => OutgoingTransaction::class,
                    'source_id'    => $trx->id,
                    'reference_no' => $trx->no_invoice,
                    'notes'        => "Penjualan {$trx->box_qty} Box {$trx->product->name}",
                ]);
            });

        // ========== BACKFILL: pastikan semua receivable_payment sudah punya cash_in ==========
        ReceivablePayment::all()->each(function ($p) {
            if (CashIn::where('source_type', ReceivablePayment::class)->where('source_id', $p->id)->exists()) return;
            $account = match (true) {
                str_contains($p->account ?? '', 'BCA')     => 'bca',
                str_contains($p->account ?? '', 'Mandiri') => 'mandiri',
                default                                    => 'kas_tunai',
            };
            CashIn::create([
                'code'         => CashIn::generateCode(true),
                'category'     => 'pelunasan_piutang',
                'account'      => $account,
                'amount'       => $p->amount,
                'received_at'  => $p->paid_at,
                'source_type'  => ReceivablePayment::class,
                'source_id'    => $p->id,
                'reference_no' => $p->transaction?->no_invoice,
                'notes'        => "Pelunasan piutang {$p->customer?->name}",
            ]);
        });
    }
}