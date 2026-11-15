<?php

namespace App\Http\Controllers;

use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomingGoodsController extends Controller
{
    public function index(Request $request)
    {
        // ============ KPI (bulan berjalan + global tempo) ============
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $month = IncomingTransaction::whereBetween('received_at', [$start, $end]);

        $kpi = [
            'month_total' => (int) (clone $month)->sum('subtotal'),
            'month_box' => (int) (clone $month)->sum('box_qty'),
            'month_pcs' => (int) (clone $month)->sum('total_pcs'),
            'lunas_total' => (int) (clone $month)->where('payment_status', 'lunas')->sum('subtotal'),
            'lunas_count' => (clone $month)->where('payment_status', 'lunas')->count(),
            'tempo_total' => (int) IncomingTransaction::where('payment_status', 'tempo')->sum('subtotal'),
            'tempo_count' => IncomingTransaction::where('payment_status', 'tempo')->count(),
            'tempo_soon' => IncomingTransaction::where('payment_status', 'tempo')
                ->whereBetween('due_date', [now(), now()->addDays(7)])->count(),
            'sku_updated' => (clone $month)->distinct('product_id')->count('product_id'),
            'sku_names' => (clone $month)->with('product')->latest('id')->limit(3)->get()
                ->pluck('product.name')->filter()->implode(', '),
            // Global counts untuk tab filter
            'total_count' => IncomingTransaction::count(),
            'lunas_all' => IncomingTransaction::where('payment_status', 'lunas')->count(),
            'tempo_all' => IncomingTransaction::where('payment_status', 'tempo')->count(),
        ];

        // ============ Suppliers ============
        $suppliers = Supplier::orderBy('name')->get();

        // ============ Products + last_price (harga faktur terakhir per produk) ============
        $products = Product::active()
            ->addSelect(['last_price' => IncomingTransaction::select('price_per_box')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->limit(1),
            ])
            ->orderBy('name')
            ->get();

        // ============ Tabel riwayat (search + filter status) ============
        $query = IncomingTransaction::with(['supplier', 'product']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('no_faktur', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $status = strtoupper($request->input('status', 'SEMUA'));
        if (in_array($status, ['LUNAS', 'TEMPO'], true)) {
            $query->where('payment_status', strtolower($status));
        } else {
            $status = 'SEMUA';
        }

        $transactions = $query
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        return view('incoming-goods.index', compact(
            'kpi', 'suppliers', 'products', 'transactions', 'status'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'no_faktur' => 'required|string|max:100|unique:incoming_transactions,no_faktur',
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'received_at' => 'required|date',
            'payment_status' => 'required|in:lunas,tempo',
            'due_date' => 'nullable|required_if:payment_status,tempo|date|after:received_at',
            'box_qty' => 'required|integer|min:1',
            'price_per_box' => 'required|numeric|min:1',
        ], [
            'no_faktur.unique' => 'Nomor faktur sudah terdaftar. Gunakan nomor lain.',
            'due_date.required_if' => 'Tanggal jatuh tempo wajib diisi untuk pembayaran tempo.',
        ]);

        $totalPcs = DB::transaction(function () use ($data): int {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);
            $totalPcs = $data['box_qty'] * $product->box_pcs_ratio;
            $modalPcs = (int) round(($data['box_qty'] * $data['price_per_box']) / max($totalPcs, 1));
            $subtotal = (int) $data['box_qty'] * (int) $data['price_per_box'];

            IncomingTransaction::create([
                'no_faktur' => $data['no_faktur'],
                'supplier_id' => $data['supplier_id'],
                'product_id' => $data['product_id'],
                'received_at' => $data['received_at'],
                'payment_status' => $data['payment_status'],
                'due_date' => $data['payment_status'] === 'tempo' ? $data['due_date'] : null,
                'box_qty' => $data['box_qty'],
                'total_pcs' => $totalPcs,
                'price_per_box' => $data['price_per_box'],
                'price_per_pcs' => $modalPcs,
                'subtotal' => $subtotal,
            ]);

            return $totalPcs;
        });

        return redirect()
            ->route('incoming-goods.index')
            ->with('success', "Faktur {$data['no_faktur']} tersimpan. Stok bertambah {$totalPcs} Pcs.");
    }

    public function rollback(IncomingTransaction $transaction)
    {
        // Cek apakah incoming ini sudah "dipakai" oleh outgoing
        $product = $transaction->product;
        if ($product->stock_pcs < $transaction->total_pcs) {
            return back()->withErrors([
                'error' => "Tidak bisa rollback: stok {$product->name} ({$product->stock_pcs} Pcs) ".
                        "lebih kecil dari total incoming ({$transaction->total_pcs} Pcs). ".
                        'Sebagian stok sudah terjual lewat Barang Keluar.',
            ]);
        }

        $no = $transaction->no_faktur;
        $transaction->delete();

        return redirect()
            ->route('incoming-goods.index')
            ->with('success', "Transaksi {$no} di-rollback. Stok dikurangi.");
    }

    public function payNow(IncomingTransaction $transaction)
    {
        $transaction->update(['payment_status' => 'lunas', 'due_date' => null]);

        return redirect()
            ->route('incoming-goods.index')
            ->with('success', "Faktur {$transaction->no_faktur} telah dilunasi.");
    }
}
