<?php

namespace Database\Seeders;

use App\Models\CashOut;
use App\Models\IncomingTransaction;
use App\Models\SupplierPayment;
use Illuminate\Database\Seeder;

class CashOutSeeder extends Seeder
{
    public function run(): void
    {
        // ========== MANUAL entries ==========
        $manuals = [
            ['biaya_listrik',     'mandiri',   3_500_000, 0, 'Tagihan listrik & cold storage'],
            ['logistik_armada',   'bca',       2_200_000, 1, 'Sewa armada pick-up L300'],
            ['bensin_distribusi', 'kas_tunai',   850_000, 2, 'BBM & tol distribusi'],
            ['gaji_staff',        'bca',       5_000_000, 3, 'Gaji staff gudang periode ini'],
            ['biaya_operasional', 'kas_tunai', 1_200_000, 4, 'ATK, konsumsi, kebersihan'],
            ['lain_lain',         'mandiri',     500_000, 5, 'Donasi & iuran lingkungan'],
        ];

        foreach ($manuals as [$cat, $acc, $amount, $daysAgo, $note]) {
            $code = CashOut::generateCode(false);
            CashOut::updateOrCreate(
                ['notes' => $note],
                [
                    'code'         => $code,
                    'category'     => $cat,
                    'account'      => $acc,
                    'amount'       => $amount,
                    'paid_at'      => now()->subDays($daysAgo),
                    'source_type'  => null,
                    'source_id'    => null,
                ]
            );
        }

        // ========== BACKFILL: Lunas incoming → CashOut ==========
        IncomingTransaction::where('payment_status', 'lunas')
            ->whereDoesntHave('payments')
            ->get()
            ->each(function ($trx) {
                if (CashOut::where('source_type', IncomingTransaction::class)->where('source_id', $trx->id)->exists()) return;
                CashOut::create([
                    'code'         => CashOut::generateCode(true),
                    'category'     => 'kulakan_lunas',
                    'account'      => 'kas_tunai',
                    'amount'       => $trx->subtotal,
                    'paid_at'      => $trx->received_at,
                    'source_type'  => IncomingTransaction::class,
                    'source_id'    => $trx->id,
                    'reference_no' => $trx->no_faktur,
                    'notes'        => "Kulakan lunas {$trx->box_qty} Box {$trx->product->name}",
                ]);
            });

        // ========== BACKFILL: Supplier payments → CashOut ==========
        SupplierPayment::all()->each(function ($p) {
            if (CashOut::where('source_type', SupplierPayment::class)->where('source_id', $p->id)->exists()) return;
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
                'paid_at'      => $p->paid_at,
                'source_type'  => SupplierPayment::class,
                'source_id'    => $p->id,
                'reference_no' => $p->transaction?->no_faktur,
                'notes'        => "Pelunasan hutang {$p->supplier?->name}",
            ]);
        });
    }
}