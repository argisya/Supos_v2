<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['sku' => 'SKU-SMB-01', 'barcode' => '8992753210014', 'name' => 'Minyak Goreng Sania 2L Pouch',        'variant' => 'Refill 2 Liter - Karton Segel',      'category' => 'sembako', 'ratio' => 6],
            ['sku' => 'SKU-MKN-02', 'barcode' => '8998866200233', 'name' => 'Indomie Goreng Original 85g',          'variant' => 'Bungkus Plastik 85 gram',            'category' => 'makanan', 'ratio' => 40],
            ['sku' => 'SKU-BVR-03', 'barcode' => '8993175532091', 'name' => 'Teh Pucuk Harum 350ml',               'variant' => 'Botol Pet 350ml Segar',              'category' => 'minuman', 'ratio' => 24],
            ['sku' => 'SKU-BVR-04', 'barcode' => '8991001429811', 'name' => 'Kopi Kapal Api Spesial Mix 24g',      'variant' => 'Dus Master isi 10 Renceng @12 Sachet','category' => 'kopi',    'ratio' => 120],
            ['sku' => 'SKU-HSE-05', 'barcode' => '8999999012351', 'name' => 'Sabun Cuci Piring Sunlight 650ml',    'variant' => 'Pouch Lime 650ml Extra Higienis',    'category' => 'rumah',   'ratio' => 12],
            ['sku' => 'SKU-SNK-06', 'barcode' => '8992741982301', 'name' => 'Biskuit Roma Kelapa 300g',            'variant' => 'Pack Keluarga 300 gram Gurih',       'category' => 'makanan', 'ratio' => 30],
            ['sku' => 'SKU-SMB-07', 'barcode' => '8992348123985', 'name' => 'Gula Pasir Gulaku Kuning 1kg',        'variant' => 'Tebu Alami Kemasan Bantal 1 Kg',     'category' => 'sembako', 'ratio' => 24],
            ['sku' => 'SKU-MKN-08', 'barcode' => '8998838382012', 'name' => 'Mie Sedaap Goreng 90g',               'variant' => 'Bungkus Kriuk Bawang Renyah',        'category' => 'makanan', 'ratio' => 40],
        ];

        foreach ($products as $p) {
            $cat = Category::where('slug', $p['category'])->first();
            if (! $cat) continue;

            Product::updateOrCreate(
                ['sku' => $p['sku']],
                [
                    'barcode'       => $p['barcode'],
                    'name'          => $p['name'],
                    'variant'       => $p['variant'],
                    'category_id'   => $cat->id,
                    'box_pcs_ratio' => $p['ratio'],
                    'status'        => 'active',
                ]
            );
        }
    }
}