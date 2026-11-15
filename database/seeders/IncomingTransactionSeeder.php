<?php

namespace Database\Seeders;

use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class IncomingTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['DO-2025/10/891', 'PT Indomarco Adi Prima',       'SKU-MKN-02', '2025-10-24', 'lunas', null,         80,  3200, 118000, 2950,  9440000],
            ['DO-2025/10/878', 'CV Sumber Berkah Sembako',     'SKU-SMB-01', '2025-10-23', 'tempo', '2025-11-05', 100, 600,  204000, 34000, 20400000],
            ['DO-2025/10/862', 'PT Mayora Indah Tbk',          'SKU-BVR-03', '2025-10-22', 'lunas', null,         80,  1920, 64800,  2700,  5184000],
            ['DO-2025/10/840', 'UD Jaya Makmur Pangan',        'SKU-BVR-04', '2025-10-21', 'tempo', '2025-11-20', 30,  3600, 150000, 1250,  4500000],
            ['DO-2025/10/815', 'PT Unilever Distribution JKT', 'SKU-HSE-05', '2025-10-20', 'lunas', null,         20,  240,  134400, 11200, 2688000],
            ['DO-2025/10/810', 'CV Sumber Berkah Sembako',     'SKU-SMB-07', '2025-10-19', 'lunas', null,         30,  720,  396000, 16500, 11880000],
        ];

        foreach ($data as [$noFaktur, $supplierName, $sku, $recvAt, $status, $dueDate, $boxQty, $totalPcs, $priceBox, $pricePcs, $subtotal]) {
            $supplier = Supplier::where('name', $supplierName)->first();
            $product  = Product::where('sku', $sku)->first();
            if (! $supplier || ! $product) continue;

            IncomingTransaction::updateOrCreate(
                ['no_faktur' => $noFaktur],
                [
                    'supplier_id'    => $supplier->id,
                    'product_id'     => $product->id,
                    'received_at'    => $recvAt,
                    'payment_status' => $status,
                    'due_date'       => $dueDate,
                    'box_qty'        => $boxQty,
                    'total_pcs'      => $totalPcs,
                    'price_per_box'  => $priceBox,
                    'price_per_pcs'  => $pricePcs,
                    'subtotal'       => $subtotal,
                ]
            );
        }
    }
}