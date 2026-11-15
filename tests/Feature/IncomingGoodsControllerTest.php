<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomingGoodsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_incoming_transaction_uses_product_ratio_and_updates_stock(): void
    {
        $category = Category::create([
            'name' => 'Sembako',
            'slug' => 'sembako',
        ]);
        $product = Product::create([
            'sku' => 'SKU-SMB-01',
            'name' => 'Produk Sembako',
            'category_id' => $category->id,
            'box_pcs_ratio' => 12,
            'status' => 'active',
        ]);
        $supplier = Supplier::create(['name' => 'Supplier Test']);

        $response = $this->post(route('incoming-goods.store'), [
            'no_faktur' => 'IN-TEST-01',
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
            'received_at' => '2026-11-15',
            'payment_status' => 'lunas',
            'box_qty' => 2,
            'price_per_box' => 120000,
        ]);

        $response->assertRedirect(route('incoming-goods.index'))
            ->assertSessionHas('success', 'Faktur IN-TEST-01 tersimpan. Stok bertambah 24 Pcs.');
        $this->assertDatabaseHas('incoming_transactions', [
            'no_faktur' => 'IN-TEST-01',
            'product_id' => $product->id,
            'box_qty' => 2,
            'total_pcs' => 24,
            'price_per_pcs' => 10000,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_pcs' => 24,
        ]);
    }
}
