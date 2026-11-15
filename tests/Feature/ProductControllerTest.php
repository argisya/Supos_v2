<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_returns_stock_and_transaction_history(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category, 'SKU-SMB-01', 12);
        $supplier = Supplier::create(['name' => 'Supplier Test']);
        $customer = Customer::create(['name' => 'Customer Test']);

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
        OutgoingTransaction::create([
            'no_invoice' => 'OUT-TEST-01',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'sold_at' => '2026-11-15 10:00:00',
            'payment_status' => 'lunas',
            'box_qty' => 1,
            'total_pcs' => 12,
            'hpp_per_box' => 120000,
            'sell_per_box' => 150000,
            'hpp_per_pcs' => 10000,
            'sell_per_pcs' => 12500,
            'subtotal' => 150000,
            'total_hpp' => 120000,
            'profit' => 30000,
        ]);

        $response = $this->getJson(route('products.show', $product));

        $response->assertOk()
            ->assertJsonPath('has_transactions', true)
            ->assertJsonPath('stock_preview', '1 Box, 0 Pcs')
            ->assertJsonPath('last_modal_box', 'Rp 120.000')
            ->assertJsonCount(2, 'recent_transactions');
        $types = collect($response->json('recent_transactions'))->pluck('type')->all();
        sort($types);
        $this->assertSame(['Barang Keluar', 'Barang Masuk'], $types);
    }

    public function test_product_export_respects_status_and_box_size_filters(): void
    {
        $category = $this->createCategory();
        $this->createProduct($category, 'SKU-SMB-01', 6, 'active');
        $this->createProduct($category, 'SKU-SMB-02', 24, 'active');
        $this->createProduct($category, 'SKU-SMB-03', 6, 'archived');

        $response = $this->get(route('products.export', [
            'status' => 'active',
            'box_size' => 'compact',
        ]));
        $csv = $response->streamedContent();

        $response->assertOk()->assertStreamed();
        $this->assertStringContainsString('SKU-SMB-01', $csv);
        $this->assertStringNotContainsString('SKU-SMB-02', $csv);
        $this->assertStringNotContainsString('SKU-SMB-03', $csv);
    }

    public function test_sku_generator_uses_the_next_existing_sequence(): void
    {
        $category = $this->createCategory();
        $this->createProduct($category, 'SKU-SMB-01');
        $this->createProduct($category, 'SKU-SMB-12');

        $response = $this->getJson(route('products.generate-sku', [
            'category_slug' => 'sembako',
        ]));

        $response->assertOk()->assertJsonPath('sku', 'SKU-SMB-13');
    }

    public function test_product_store_rejects_a_duplicate_barcode(): void
    {
        $category = $this->createCategory();
        $this->createProduct($category, 'SKU-SMB-01', 12, 'active', '8991234567890');

        $response = $this->from(route('products.index'))->post(route('products.store'), [
            'name' => 'Produk Duplikat',
            'category_id' => $category->id,
            'sku' => 'SKU-SMB-02',
            'barcode' => '8991234567890',
            'box_pcs_ratio' => 12,
        ]);

        $response->assertInvalid([
            'barcode' => 'Nomor Barcode ini sudah digunakan oleh produk lain.',
        ]);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_product_update_saves_master_fields_and_conversion_ratio(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category, 'SKU-SMB-01', 12, 'active', '8991234567890');

        $response = $this->patch(route('products.update', $product), [
            'name' => 'Nama Produk Diperbarui',
            'variant' => 'Kemasan Baru',
            'category_id' => $category->id,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'box_pcs_ratio' => 24,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nama Produk Diperbarui',
            'variant' => 'Kemasan Baru',
            'box_pcs_ratio' => 24,
        ]);
    }

    public function test_product_update_rejects_ratio_changes_after_incoming_transactions(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category, 'SKU-SMB-01', 12);
        $this->createIncomingTransaction($product);

        $response = $this->from(route('products.index'))
            ->patch(route('products.update', $product), [
                'name' => $product->name,
                'variant' => null,
                'category_id' => $category->id,
                'sku' => $product->sku,
                'barcode' => null,
                'box_pcs_ratio' => 24,
            ]);

        $response->assertInvalid([
            'box_pcs_ratio' => 'Rasio tidak dapat diubah setelah produk memiliki transaksi. Buat SKU baru untuk jenis kemasan yang berbeda.',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'box_pcs_ratio' => 12,
        ]);
    }

    public function test_product_update_rejects_ratio_changes_after_outgoing_transactions(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category, 'SKU-SMB-01', 12);
        $product->forceFill(['stock_pcs' => 12])->save();
        $customer = Customer::create(['name' => 'Customer Test']);
        OutgoingTransaction::create([
            'no_invoice' => 'OUT-TEST-01',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'sold_at' => '2026-11-15 10:00:00',
            'payment_status' => 'lunas',
            'box_qty' => 1,
            'total_pcs' => 12,
            'hpp_per_box' => 120000,
            'sell_per_box' => 150000,
            'hpp_per_pcs' => 10000,
            'sell_per_pcs' => 12500,
            'subtotal' => 150000,
            'total_hpp' => 120000,
            'profit' => 30000,
        ]);

        $response = $this->from(route('products.index'))
            ->patch(route('products.update', $product), [
                'name' => $product->name,
                'variant' => null,
                'category_id' => $category->id,
                'sku' => $product->sku,
                'barcode' => null,
                'box_pcs_ratio' => 24,
            ]);

        $response->assertInvalid(['box_pcs_ratio']);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'box_pcs_ratio' => 12,
        ]);
    }

    public function test_product_update_allows_other_fields_after_transaction_without_changing_ratio(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category, 'SKU-SMB-01', 12);
        $this->createIncomingTransaction($product);

        $response = $this->patch(route('products.update', $product), [
            'name' => 'Nama Produk Diperbarui',
            'variant' => 'Kemasan Baru',
            'category_id' => $category->id,
            'sku' => $product->sku,
            'barcode' => null,
            'box_pcs_ratio' => 12,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nama Produk Diperbarui',
            'box_pcs_ratio' => 12,
        ]);
    }

    public function test_product_import_creates_products_and_categories_from_csv(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            "\xEF\xBB\xBFSKU,Barcode,Nama Produk,Varian,Kategori,Isi Per Box\n"
                ."SKU-CSV-01,8991234567001,\"Produk, Impor\",500g,Snack & Biskuit,24\n",
        );

        $response = $this->from(route('products.index'))
            ->post(route('products.import'), ['file' => $file]);

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Import selesai: 1 produk baru ditambahkan.');
        $this->assertDatabaseHas('categories', [
            'name' => 'Snack & Biskuit',
            'slug' => 'snack-biskuit',
        ]);
        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-CSV-01',
            'barcode' => '8991234567001',
            'name' => 'Produk, Impor',
            'box_pcs_ratio' => 24,
        ]);
    }

    public function test_product_import_skips_duplicate_skus_and_barcodes(): void
    {
        $category = $this->createCategory();
        $this->createProduct($category, 'SKU-SMB-01');
        $this->createProduct($category, 'SKU-SMB-02', 12, 'active', '8991234567890');
        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            "SKU,Barcode,Nama Produk,Varian,Kategori,Isi Per Box\n"
                ."SKU-SMB-01,8991234567001,Duplikat SKU,,Sembako,6\n"
                ."SKU-NEW,8991234567890,Duplikat Barcode,,Sembako,6\n",
        );

        $response = $this->from(route('products.index'))
            ->post(route('products.import'), ['file' => $file]);

        $response->assertRedirect(route('products.index'))
            ->assertSessionHas('success', 'Import selesai: 0 produk baru ditambahkan (2 duplikat dilewati).');
        $this->assertDatabaseCount('products', 2);
    }

    public function test_product_import_rejects_blank_categories_and_rolls_back_all_rows(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            "SKU,Barcode,Nama Produk,Varian,Kategori,Isi Per Box\n"
                ."SKU-CSV-01,,Produk Valid,,Kategori Baru,12\n"
                ."SKU-CSV-02,,Produk Tanpa Kategori,, ,6\n",
        );

        $response = $this->from(route('products.index'))
            ->post(route('products.import'), ['file' => $file]);

        $response->assertInvalid([
            'file' => 'Baris 3: SKU, nama produk, kategori, dan isi per box wajib diisi dengan benar.',
        ]);
        $this->assertDatabaseMissing('products', ['sku' => 'SKU-CSV-01']);
        $this->assertDatabaseMissing('categories', ['name' => 'Kategori Baru']);
    }

    public function test_product_import_rejects_an_invalid_csv_header(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            "Product,Code\nSKU-CSV-01,Produk\n",
        );

        $response = $this->from(route('products.index'))
            ->post(route('products.import'), ['file' => $file]);

        $response->assertInvalid([
            'file' => 'Header CSV harus berurutan: SKU, Barcode, Nama Produk, Varian, Kategori, Isi Per Box.',
        ]);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_import_rejects_non_csv_files(): void
    {
        $file = UploadedFile::fake()->create('products.pdf', 1, 'application/pdf');

        $response = $this->from(route('products.index'))
            ->post(route('products.import'), ['file' => $file]);

        $response->assertInvalid(['file']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_archived_filter_only_counts_and_displays_archived_products(): void
    {
        $category = $this->createCategory();
        $this->createProduct($category, 'SKU-SMB-01', 12, 'active');
        $this->createProduct($category, 'SKU-SMB-02', 12, 'archived');

        $response = $this->get(route('products.index', ['status' => 'archived']));

        $response->assertOk()
            ->assertViewHas('catalogTotal', 1)
            ->assertViewHas('categories', fn ($categories): bool => $categories->first()->products_count === 1)
            ->assertSee('SKU-SMB-02')
            ->assertDontSee('SKU-SMB-01');
    }

    public function test_archive_and_restore_move_products_between_statuses(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category, 'SKU-SMB-01');

        $this->patch(route('products.archive', $product))
            ->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'archived',
        ]);

        $this->patch(route('products.restore', $product))
            ->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'active',
        ]);
    }

    public function test_category_store_creates_a_slug_for_the_new_category(): void
    {
        $response = $this->post(route('categories.store'), [
            'name' => 'Snack & Biskuit',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Snack & Biskuit',
            'slug' => 'snack-biskuit',
        ]);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'Sembako',
            'slug' => 'sembako',
        ]);
    }

    private function createProduct(
        Category $category,
        string $sku,
        int $ratio = 12,
        string $status = 'active',
        ?string $barcode = null
    ): Product {
        return Product::create([
            'sku' => $sku,
            'barcode' => $barcode,
            'name' => "Produk {$sku}",
            'category_id' => $category->id,
            'box_pcs_ratio' => $ratio,
            'status' => $status,
        ]);
    }

    private function createIncomingTransaction(Product $product): IncomingTransaction
    {
        $supplier = Supplier::create(['name' => 'Supplier Test']);

        return IncomingTransaction::create([
            'no_faktur' => 'IN-TEST-01',
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
            'received_at' => '2026-11-14',
            'payment_status' => 'lunas',
            'box_qty' => 1,
            'total_pcs' => $product->box_pcs_ratio,
            'price_per_box' => 120000,
            'price_per_pcs' => 10000,
            'subtotal' => 120000,
        ]);
    }
}
