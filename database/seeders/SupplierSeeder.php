<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'PT Indomarco Adi Prima',      'initials' => 'PI', 'type' => 'Distributor Resmi'],
            ['name' => 'CV Sumber Berkah Sembako',    'initials' => 'SB', 'type' => 'Grosir Agen Minyak'],
            ['name' => 'PT Mayora Indah Tbk',         'initials' => 'MY', 'type' => 'Factory Direct'],
            ['name' => 'PT Unilever Distribution JKT','initials' => 'UN', 'type' => 'Distributor Resmi'],
            ['name' => 'UD Jaya Makmur Pangan',       'initials' => 'JP', 'type' => 'Agen Lokal'],
        ];

        foreach ($rows as $row) {
            Supplier::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}