<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Toko Sumber Rejeki',       'type' => 'Mitra Grosir Platinum', 'credit_limit' => 25_000_000, 'pic_name' => 'Pak Hendra',  'phone' => '0812-1111-2222'],
            ['name' => 'Warung Madura Berkah',     'type' => 'Warung Kelontong',      'credit_limit' => 10_000_000, 'pic_name' => 'Cak Romli',   'phone' => '0812-9988-7711'],
            ['name' => 'Grosir Rejeki Barokah',    'type' => 'Distributor Cabang',    'credit_limit' => 50_000_000, 'pic_name' => 'Pak Rudi',    'phone' => '0812-5555-6666'],
            ['name' => 'Toko Sembako Jaya',        'type' => 'Mitra Pasar Induk',     'credit_limit' => 15_000_000, 'pic_name' => 'Koh Aceng',   'phone' => '0878-1122-8899'],
            ['name' => 'Kios Ibu Ani',             'type' => 'Warung Kelontong',      'credit_limit' => 5_000_000,  'pic_name' => 'Ibu Ani',     'phone' => '0857-3321-4455'],
            ['name' => 'Toko Barokah Jaya',        'type' => 'Grosir Semi-Modern',    'credit_limit' => 15_000_000, 'pic_name' => 'H. Sudirman', 'phone' => '0811-2233-4455'],
            ['name' => 'Minimarket Sumber Rejeki', 'type' => 'Minimarket',            'credit_limit' => 20_000_000, 'pic_name' => 'Pak Gunawan', 'phone' => '0813-7766-5544'],
            ['name' => 'Toko Sembako Rezeki',      'type' => 'Mitra Reguler',         'credit_limit' => 12_000_000, 'pic_name' => 'Koh Aceng',   'phone' => '0878-1122-8899'],
            ['name' => 'Pelanggan Umum / Walk-in', 'type' => 'Non-Member (Tunai)',    'credit_limit' => 0,          'pic_name' => null,          'phone' => null],
        ];

        foreach ($rows as $row) {
            Customer::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}