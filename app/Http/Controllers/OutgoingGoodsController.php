<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutgoingGoodsController extends Controller
{
    public function index(Request $request)
    {
        $today = OutgoingTransaction::whereDate('sold_at', today());

        $kpi = [
            'today_sales'  => (int) (clone $today)->sum('subtotal'),
            'today_count'  => (clone $today)->count(),
            'today_box'    => (int) (clone $today)->sum('box_qty'),
            'today_profit' => (int) (clone $today)->sum('profit'),
            'today_hpp'    => (int) (clone $today)->sum('total_hpp'),
            'today_pcs'    => (int) (clone $today)->sum('total_pcs'),

            'lunas_total'  => (int) (clone $today)->where('payment_status', 'lunas')->sum('subtotal'),
            'lunas_count'  => (clone $today)->where('payment_status', 'lunas')->count(),

            'tempo_total'  => (int) OutgoingTransaction::where('payment_status', 'tempo')->sum('subtotal'),
            'tempo_count'  => OutgoingTransaction::where('payment_status', 'tempo')->distinct('customer_id')->count('customer_id'),
            'tempo_rows'   => OutgoingTransaction::where('payment_status', 'tempo')->count(),

            'total_count'  => OutgoingTransaction::count(),
            'lunas_all'    => OutgoingTransaction::where('payment_status', 'lunas')->count(),
            'tempo_all'    => OutgoingTransaction::where('payment_status', 'tempo')->count(),
        ];

        $customers = Customer::orderBy('name')->get();

        // Produk yang masih ada stok + last HPP
        $products = Product::active()
            ->addSelect(['last_hpp' => IncomingTransaction::select('price_per_box')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->limit(1),
            ])
            ->where('stock_pcs', '>', 0)
            ->orderBy('name')
            ->get();

        // Tabel riwayat
        $query = OutgoingTransaction::with(['customer', 'product']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('no_invoice', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('product',  fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $status = strtoupper($request->input('status', 'SEMUA'));
        if (in_array($status, ['LUNAS', 'TEMPO'], true)) {
            $query->where('payment_status', strtolower($status));
        } else {
            $status = 'SEMUA';
        }

        $transactions = $query
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        // Total laba dari halaman saat ini (untuk footer)
        $batchProfit = (int) $transactions->getCollection()->sum('profit');

        // Suggested invoice number
        $suggestedInvoice = $this->generateInvoiceNo();

        return view('outgoing-goods.index', compact(
            'kpi', 'customers', 'products', 'transactions',
            'status', 'batchProfit', 'suggestedInvoice'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'product_id'     => 'required|exists:products,id',
            'payment_status' => 'required|in:lunas,tempo',
            'due_date'       => 'nullable|required_if:payment_status,tempo|date|after_or_equal:today',
            'box_qty'        => 'required|integer|min:1',
            'sell_per_box'   => 'required|numeric|min:1',
        ], [
            'due_date.required_if' => 'Tanggal jatuh tempo wajib diisi untuk pembayaran tempo.',
        ]);

        $product  = Product::lockForUpdate()->findOrFail($data['product_id']);
        $totalPcs = $data['box_qty'] * $product->box_pcs_ratio;

        if ($product->stock_pcs < $totalPcs) {
            $maxBox = (int) floor($product->stock_pcs / $product->box_pcs_ratio);
            return back()->withErrors([
                'box_qty' => "Stok tidak cukup. Tersedia {$product->stock_pcs} Pcs (~{$maxBox} Box).",
            ])->withInput();
        }

        // Snapshot HPP dari incoming terakhir — server yang menentukan (tidak trust form)
        $lastHpp = (float) (IncomingTransaction::where('product_id', $product->id)
            ->latest('received_at')->value('price_per_box') ?? 0);

        $subtotal = (int) $data['box_qty'] * (int) $data['sell_per_box'];
        $totalHpp = (int) $data['box_qty'] * (int) $lastHpp;
        $profit   = $subtotal - $totalHpp;

        $noInvoice = $this->generateInvoiceNo();

        DB::transaction(function () use ($data, $product, $totalPcs, $lastHpp, $subtotal, $totalHpp, $profit, $noInvoice) {
            OutgoingTransaction::create([
                'no_invoice'     => $noInvoice,
                'customer_id'    => $data['customer_id'],
                'product_id'     => $product->id,
                'sold_at'        => now(),
                'payment_status' => $data['payment_status'],
                'due_date'       => $data['payment_status'] === 'tempo' ? $data['due_date'] : null,
                'box_qty'        => $data['box_qty'],
                'total_pcs'      => $totalPcs,
                'hpp_per_box'    => $lastHpp,
                'sell_per_box'   => $data['sell_per_box'],
                'hpp_per_pcs'    => round($lastHpp / $product->box_pcs_ratio, 2),
                'sell_per_pcs'   => round($data['sell_per_box'] / $product->box_pcs_ratio, 2),
                'subtotal'       => $subtotal,
                'total_hpp'      => $totalHpp,
                'profit'         => $profit,
            ]);
            // Note: stock_pcs decrement ditangani model events
        });

        return redirect()
            ->route('outgoing-goods.index')
            ->with('success', "Faktur {$noInvoice} tersimpan. Stok -{$totalPcs} Pcs, laba " . number_format($profit, 0, ',', '.') . ".");
    }

    public function rollback(OutgoingTransaction $transaction)
    {
        $no = $transaction->no_invoice;
        $transaction->delete(); // model events auto-increment stock_pcs

        return redirect()
            ->route('outgoing-goods.index')
            ->with('success', "Transaksi {$no} di-rollback. Stok dikembalikan.");
    }

    private function generateInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('Y/m') . '/';
        $last = OutgoingTransaction::where('no_invoice', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('no_invoice');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}