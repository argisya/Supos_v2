<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OutgoingTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // [no_invoice, customer, sku, hours_ago, status, due_in_days, box_qty, sell_per_box]
        $data = [
            ['INV-2025/10/772', 'Toko Sumber Rejeki',       'SKU-MKN-02', 1,   'lunas', null, 5,  126000],
            ['INV-2025/10/771', 'Toko Sumber Rejeki',       'SKU-MKN-02', 24,  'lunas', null, 20, 126000],
            ['INV-2025/10/770', 'Warung Madura Berkah',     'SKU-SMB-01', 48,  'tempo', 14,   15, 218000],
            ['INV-2025/10/769', 'Toko Sembako Jaya',        'SKU-BVR-03', 72,  'lunas', null, 30, 71000],
            ['INV-2025/10/768', 'Kios Ibu Ani',             'SKU-BVR-04', 96,  'tempo', 3,    10, 165000],
            ['INV-2025/10/742', 'Toko Barokah Jaya',        'SKU-MKN-02', 432, 'tempo', -4,   40, 126000],
            ['INV-2025/10/735', 'Toko Sembako Rezeki',      'SKU-SMB-07', 264, 'tempo', 0,    20, 404000],
            ['INV-2025/10/720', 'Minimarket Sumber Rejeki', 'SKU-BVR-03', 144, 'tempo', 9,    30, 71000],
        ];

        foreach ($data as [$noInvoice, $custName, $sku, $hoursAgo, $status, $dueInDays, $boxQty, $sellBox]) {
            $customer = Customer::where('name', $custName)->first();
            $product  = Product::where('sku', $sku)->first();
            if (! $customer || ! $product) continue;

            $lastHpp = (float) (IncomingTransaction::where('product_id', $product->id)
                ->latest('received_at')->value('price_per_box') ?? 0);

            $totalPcs = $boxQty * $product->box_pcs_ratio;
            $subtotal = $boxQty * $sellBox;
            $totalHpp = $boxQty * $lastHpp;
            $profit   = $subtotal - $totalHpp;

            OutgoingTransaction::updateOrCreate(
                ['no_invoice' => $noInvoice],
                [
                    'customer_id'    => $customer->id,
                    'product_id'     => $product->id,
                    'sold_at'        => now()->subHours($hoursAgo),
                    'payment_status' => $status,
                    'due_date'       => $status === 'tempo' ? now()->addDays($dueInDays)->toDateString() : null,
                    'box_qty'        => $boxQty,
                    'total_pcs'      => $totalPcs,
                    'hpp_per_box'    => $lastHpp,
                    'sell_per_box'   => $sellBox,
                    'hpp_per_pcs'    => round($lastHpp / $product->box_pcs_ratio, 2),
                    'sell_per_pcs'   => round($sellBox / $product->box_pcs_ratio, 2),
                    'subtotal'       => $subtotal,
                    'total_hpp'      => $totalHpp,
                    'profit'         => $profit,
                ]
            );
        }
    }
}