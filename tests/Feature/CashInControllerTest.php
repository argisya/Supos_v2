<?php

namespace Tests\Feature;

use App\Models\CashIn;
use App\Models\Category;
use App\Models\Customer;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashInControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_in_page_renders_modal_dialogs_and_summary_labels(): void
    {
        $response = $this->get(route('cash-ins.index'));

        $response->assertOk()
            ->assertSee('createCashInModal')
            ->assertSee('cashInDetailModal')
            ->assertSee('cashInReceiptModal')
            ->assertSee('deleteCashInModal')
            ->assertSee('Net Mutasi Tercatat')
            ->assertSee('Total Cocok');
    }

    public function test_manual_cash_in_is_saved_with_reference_and_redirects(): void
    {
        $response = $this->post(route('cash-ins.store'), [
            'category' => 'modal_pemilik',
            'account' => 'bca',
            'amount' => 250000,
            'received_at' => '2026-11-17T10:30',
            'reference_no' => 'SLIP-123',
            'notes' => 'Setoran modal awal',
        ]);

        $response->assertRedirect(route('cash-ins.index'))
            ->assertSessionHas('success', 'Penerimaan kas Rp 250.000 berhasil dicatat ke BCA Bisnis.');

        $this->assertDatabaseHas('cash_ins', [
            'category' => 'modal_pemilik',
            'account' => 'bca',
            'amount' => 250000,
            'reference_no' => 'SLIP-123',
            'notes' => 'Setoran modal awal',
            'source_id' => null,
        ]);
    }

    public function test_manual_cash_in_requires_a_category_account_amount_and_audit_note(): void
    {
        $response = $this->from(route('cash-ins.index'))
            ->post(route('cash-ins.store'), []);

        $response->assertRedirect(route('cash-ins.index'))
            ->assertSessionHasErrors([
                'category' => 'Pilih kategori penerimaan kas.',
                'account',
                'amount',
                'notes' => 'Keterangan wajib diisi untuk audit trail.',
            ]);

        $this->assertDatabaseCount('cash_ins', 0);
    }

    public function test_automatic_cash_in_cannot_be_deleted_from_the_cash_in_page(): void
    {
        $cashIn = CashIn::create([
            'code' => 'KM-2026/11/001',
            'category' => 'penjualan_tunai',
            'account' => 'kas_tunai',
            'amount' => 100000,
            'received_at' => now(),
            'source_type' => OutgoingTransaction::class,
            'source_id' => 999,
            'reference_no' => 'INV-001',
            'notes' => 'Penjualan otomatis',
        ]);

        $response = $this->from(route('cash-ins.index'))
            ->delete(route('cash-ins.destroy', $cashIn));

        $response->assertRedirect(route('cash-ins.index'))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('cash_ins', [
            'id' => $cashIn->id,
            'code' => 'KM-2026/11/001',
        ]);
    }

    public function test_manual_cash_in_can_be_deleted(): void
    {
        $cashIn = CashIn::create([
            'code' => 'KM-NON-001',
            'category' => 'modal_pemilik',
            'account' => 'bca',
            'amount' => 100000,
            'received_at' => now(),
            'source_type' => null,
            'source_id' => null,
            'notes' => 'Setoran manual',
        ]);

        $response = $this->delete(route('cash-ins.destroy', $cashIn));

        $response->assertRedirect(route('cash-ins.index'))
            ->assertSessionHas('success', 'Entri kas masuk manual KM-NON-001 berhasil dihapus.');

        $this->assertDatabaseMissing('cash_ins', [
            'id' => $cashIn->id,
        ]);
    }

    public function test_cash_in_date_filter_rejects_an_end_date_before_the_start_date(): void
    {
        $response = $this->get(route('cash-ins.index', [
            'date_from' => '2026-11-17',
            'date_to' => '2026-11-16',
        ]));

        $response->assertRedirect()
            ->assertSessionHasErrors([
                'date_to' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
            ]);
    }

    public function test_cash_in_search_finds_automatic_entries_by_customer_name(): void
    {
        $category = Category::create([
            'name' => 'Sembako',
            'slug' => 'sembako',
        ]);
        $product = Product::create([
            'sku' => 'SKU-SEARCH-01',
            'name' => 'Minyak Goreng',
            'category_id' => $category->id,
            'status' => 'active',
        ]);
        Product::query()->whereKey($product->id)->update(['stock_pcs' => 10]);
        $customer = Customer::create(['name' => 'Toko Sumber Rezeki']);
        $transaction = OutgoingTransaction::create([
            'no_invoice' => 'INV-SEARCH-01',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'sold_at' => now(),
            'payment_status' => 'tempo',
            'due_date' => now()->addDays(7)->toDateString(),
            'box_qty' => 1,
            'total_pcs' => 1,
            'hpp_per_box' => 100000,
            'sell_per_box' => 120000,
            'hpp_per_pcs' => 100000,
            'sell_per_pcs' => 120000,
            'subtotal' => 120000,
            'total_hpp' => 100000,
            'profit' => 20000,
        ]);
        CashIn::create([
            'code' => 'KM-2026/11/002',
            'category' => 'penjualan_tunai',
            'account' => 'kas_tunai',
            'amount' => 120000,
            'received_at' => now(),
            'source_type' => OutgoingTransaction::class,
            'source_id' => $transaction->id,
            'reference_no' => $transaction->no_invoice,
            'notes' => 'Penjualan otomatis',
        ]);

        $response = $this->get(route('cash-ins.index', ['search' => 'Toko Sumber Rezeki']));

        $response->assertOk()
            ->assertSee('KM-2026/11/002')
            ->assertSee('Toko Sumber Rezeki');
    }
}
