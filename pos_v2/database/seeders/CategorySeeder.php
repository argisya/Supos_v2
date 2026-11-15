<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Sembako & Bumbu',  'slug' => 'sembako',  'color_key' => 'tertiary'],
            ['name' => 'Makanan Instan',   'slug' => 'makanan',  'color_key' => 'secondary'],
            ['name' => 'Minuman Kemasan',  'slug' => 'minuman',  'color_key' => 'tertiary'],
            ['name' => 'Kopi & Renceng',   'slug' => 'kopi',     'color_key' => 'primary'],
            ['name' => 'Kebutuhan Rumah',  'slug' => 'rumah',    'color_key' => 'neutral'],
        ];
        foreach ($rows as $row) {
            Category::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }
}