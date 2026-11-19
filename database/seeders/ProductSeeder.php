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
            // Kategori: sembako (SKU-SMB-01, SKU-SMB-02)
            ['name' => 'Minyak Goreng Sania 2L Pouch', 'variant' => 'Refill 2 Liter - Karton Segel', 'category' => 'sembako', 'ratio' => 6],
            ['name' => 'Gula Pasir Gulaku Kuning 1kg', 'variant' => 'Tebu Alami Kemasan Bantal 1 Kg', 'category' => 'sembako', 'ratio' => 24],

            // Kategori: makanan (SKU-MKN-01, SKU-MKN-02)
            ['name' => 'Indomie Goreng Original 85g', 'variant' => 'Bungkus Plastik 85 gram', 'category' => 'makanan', 'ratio' => 40],
            ['name' => 'Mie Sedaap Goreng 90g', 'variant' => 'Bungkus Kriuk Bawang Renyah', 'category' => 'makanan', 'ratio' => 40],

            // Kategori: snack (SKU-SNK-01)
            ['name' => 'Biskuit Roma Kelapa 300g', 'variant' => 'Pack Keluarga 300 gram Gurih', 'category' => 'snack', 'ratio' => 30],

            // Kategori: minuman & kopi (SKU-BVR-01, SKU-BVR-02)
            ['name' => 'Teh Pucuk Harum 350ml', 'variant' => 'Botol Pet 350ml Segar', 'category' => 'minuman', 'ratio' => 24],
            ['name' => 'Kopi Kapal Api Spesial Mix 24g', 'variant' => 'Dus Master isi 10 Renceng @12 Sachet', 'category' => 'kopi', 'ratio' => 120],

            // Kategori: rumah (SKU-HSE-01)
            ['name' => 'Sabun Cuci Piring Sunlight 650ml', 'variant' => 'Pouch Lime 650ml Extra Higienis', 'category' => 'rumah', 'ratio' => 12],
        ];

        $prefixMap = [
            'sembako' => 'SMB',
            'makanan' => 'MKN',
            'snack' => 'SNK',
            'minuman' => 'BVR',
            'kopi' => 'BVR',
            'rumah' => 'HSE',
        ];

        $counters = [];

        foreach ($products as $p) {
            $cat = Category::where('slug', $p['category'])->first();
            if (!$cat) {
                continue;
            }

            $prefix = $prefixMap[$p['category']] ?? 'GEN';
            $counters[$prefix] = ($counters[$prefix] ?? 0) + 1;
            $sku = sprintf('SKU-%s-%02d', $prefix, $counters[$prefix]);

            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $p['name'],
                    'variant' => $p['variant'],
                    'category_id' => $cat->id,
                    'box_pcs_ratio' => $p['ratio'],
                    'status' => 'active',
                ]
            );
        }
    }
}