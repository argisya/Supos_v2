<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Toko Berkah Mandiri',     'type' => 'Mitra Grosir Platinum', 'credit_limit' => 25_000_000],
            ['name' => 'Warung Bu Joko',          'type' => 'Retail / Semi-Grosir',  'credit_limit' => 5_000_000],
            ['name' => 'Grosir Rejeki Barokah',   'type' => 'Distributor Cabang',    'credit_limit' => 50_000_000],
            ['name' => 'Toko Sembako Jaya',       'type' => 'Mitra Pasar Induk',     'credit_limit' => 15_000_000],
            ['name' => 'Pelanggan Umum / Walk-in','type' => 'Non-Member (Tunai)',    'credit_limit' => 0],
        ];

        foreach ($rows as $row) {
            Customer::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}