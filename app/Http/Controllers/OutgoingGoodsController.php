<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

            'tempo_total'  => (int) OutgoingTransaction::where('payment_status', 'tempo')
                ->selectRaw('COALESCE(SUM(subtotal - paid_amount), 0) as total')
                ->value('total'),
            'tempo_count'  => OutgoingTransaction::where('payment_status', 'tempo')->distinct('customer_id')->count('customer_id'),
            'tempo_rows'   => OutgoingTransaction::where('payment_status', 'tempo')->count(),

            'total_count'  => OutgoingTransaction::count(),
            'lunas_all'    => OutgoingTransaction::where('payment_status', 'lunas')->count(),
            'tempo_all'    => OutgoingTransaction::where('payment_status', 'tempo')->count(),
        ];

        $customers = Customer::orderBy('name')->get();

        $products = Product::active()
            ->addSelect([
                'last_hpp' => IncomingTransaction::select('price_per_box')
                    ->whereColumn('product_id', 'products.id')
                    ->orderByDesc('received_at')
                    ->orderByDesc('id')
                    ->limit(1),
            ])
            ->where('stock_pcs', '>', 0)
            ->orderBy('name')
            ->get();

        $query = OutgoingTransaction::with(['customer', 'product']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('no_invoice', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
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
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $batchProfit = (int) $transactions->getCollection()->sum('profit');
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

        $result = DB::transaction(function () use ($data): array {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $ratio = max(1, (int) $product->box_pcs_ratio);
            $totalPcs = (int) $data['box_qty'] * $ratio;

            if ($product->stock_pcs < $totalPcs) {
                $maxBox = intdiv((int) $product->stock_pcs, $ratio);
                throw ValidationException::withMessages([
                    'box_qty' => "Stok tidak cukup. Tersedia {$product->stock_pcs} Pcs (~{$maxBox} Box).",
                ]);
            }

            $lastIncoming = IncomingTransaction::where('product_id', $product->id)
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->first();

            if (! $lastIncoming || (float) $lastIncoming->price_per_box <= 0) {
                throw ValidationException::withMessages([
                    'product_id' => 'Produk belum memiliki riwayat HPP di Barang Masuk. Input Barang Masuk terlebih dahulu.',
                ]);
            }

            $noInvoice = $this->generateInvoiceNo(true);
            $lastHpp   = (float) $lastIncoming->price_per_box;
            $sellBox   = (float) $data['sell_per_box'];
            $boxQty    = (int) $data['box_qty'];

            $subtotal   = $boxQty * $sellBox;
            $totalHpp   = $boxQty * $lastHpp;
            $profit     = $subtotal - $totalHpp;
            $isLunas    = $data['payment_status'] === 'lunas';

            OutgoingTransaction::create([
                'no_invoice'     => $noInvoice,
                'customer_id'    => $data['customer_id'],
                'product_id'     => $product->id,
                'sold_at'        => now(),
                'payment_status' => $data['payment_status'],
                'due_date'       => $isLunas ? null : $data['due_date'],
                'box_qty'        => $boxQty,
                'total_pcs'      => $totalPcs,
                'hpp_per_box'    => $lastHpp,
                'sell_per_box'   => $sellBox,
                'hpp_per_pcs'    => round($lastHpp / $ratio, 2),
                'sell_per_pcs'   => round($sellBox / $ratio, 2),
                'subtotal'       => $subtotal,
                'total_hpp'      => $totalHpp,
                'profit'         => $profit,
                'paid_amount'    => $isLunas ? $subtotal : 0,
            ]);

            return [
                'no_invoice' => $noInvoice,
                'total_pcs'  => $totalPcs,
                'profit'     => $profit,
            ];
        });

        return redirect()
            ->route('outgoing-goods.index')
            ->with('success', "Faktur {$result['no_invoice']} tersimpan. Stok -{$result['total_pcs']} Pcs, laba Rp " . number_format($result['profit'], 0, ',', '.') . '.');
    }

    public function rollback(OutgoingTransaction $transaction)
    {
        if ($transaction->payments()->exists()) {
            return back()->withErrors([
                'error' => "Tidak bisa rollback {$transaction->no_invoice}: transaksi sudah memiliki riwayat pembayaran piutang.",
            ]);
        }

        $no = $transaction->no_invoice;

        DB::transaction(function () use ($transaction) {
            $transaction->delete();
        });

        return redirect()
            ->route('outgoing-goods.index')
            ->with('success', "Transaksi {$no} berhasil di-rollback. Stok fisik telah dikembalikan.");
    }

    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120|unique:customers,name',
            'type'    => 'nullable|string|max:60',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama pelanggan/toko mitra sudah terdaftar.',
        ]);

        $customer = Customer::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Pelanggan mitra berhasil ditambahkan.',
                'customer' => $customer,
            ]);
        }

        return back()->with('success', "Pelanggan {$customer->name} berhasil ditambahkan.");
    }

    public function receipt(OutgoingTransaction $transaction)
    {
        $transaction->load(['customer', 'product']);

        return view('outgoing-goods.receipt', compact('transaction'));
    }

    private function generateInvoiceNo(bool $lock = false): string
    {
        $prefix = 'INV-' . now()->format('Y/m') . '/';
        $query  = OutgoingTransaction::where('no_invoice', 'like', $prefix . '%');

        if ($lock) {
            $query->lockForUpdate();
        }

        $last = $query->orderByDesc('id')->value('no_invoice');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}