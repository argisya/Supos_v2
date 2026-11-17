<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutgoingGoodsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_outgoing_transaction_uses_product_ratio_and_decrements_stock(): void
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
        IncomingTransaction::create([
            'no_faktur' => 'IN-TEST-01',
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
            'received_at' => '2026-11-14',
            'payment_status' => 'lunas',
            'box_qty' => 2,
            'total_pcs' => 24,
            'price_per_box' => 120000,
            'price_per_pcs' => 10000,
            'subtotal' => 240000,
        ]);
        $customer = Customer::create(['name' => 'Customer Test']);

        $response = $this->post(route('outgoing-goods.store'), [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'payment_status' => 'lunas',
            'payment_account' => 'bca',
            'box_qty' => 1,
            'sell_per_box' => 150000,
        ]);

        $response->assertRedirect(route('outgoing-goods.index'));
        $this->assertDatabaseHas('outgoing_transactions', [
            'product_id' => $product->id,
            'box_qty' => 1,
            'total_pcs' => 12,
            'hpp_per_box' => 120000,
            'sell_per_box' => 150000,
            'profit' => 30000,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_pcs' => 12,
        ]);
        $this->assertDatabaseHas('cash_ins', [
            'category' => 'penjualan_tunai',
            'account' => 'bca',
            'amount' => 150000,
        ]);
    }

    public function test_outgoing_transaction_rejects_quantity_above_locked_stock(): void
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
        Product::query()->whereKey($product->id)->update(['stock_pcs' => 6]);
        $customer = Customer::create(['name' => 'Customer Test']);

        $response = $this->from(route('outgoing-goods.index'))
            ->post(route('outgoing-goods.store'), [
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'payment_status' => 'lunas',
                'box_qty' => 1,
                'sell_per_box' => 150000,
            ]);

        $response->assertInvalid([
            'box_qty' => 'Stok tidak cukup. Tersedia 6 Pcs (~0 Box).',
        ]);
        $this->assertDatabaseCount('outgoing_transactions', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_pcs' => 6,
        ]);
    }
}
