<?php

namespace App\Http\Controllers;

use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IncomingGoodsController extends Controller
{
    public function index(Request $request)
    {
        $nowWib = now('Asia/Jakarta');
        $start  = $nowWib->copy()->startOfMonth()->toDateString();
        $end    = $nowWib->copy()->endOfMonth()->toDateString();
        $month  = IncomingTransaction::whereBetween('received_at', [$start, $end]);

        $kpi = [
            'month_total' => (int) (clone $month)->sum('subtotal'),
            'month_box'   => (int) (clone $month)->sum('box_qty'),
            'month_pcs'   => (int) (clone $month)->sum('total_pcs'),
            'lunas_total' => (int) (clone $month)->where('payment_status', 'lunas')->sum('subtotal'),
            'lunas_count' => (clone $month)->where('payment_status', 'lunas')->count(),
            'tempo_total' => (int) IncomingTransaction::where('payment_status', 'tempo')->sum('subtotal'),
            'tempo_count' => IncomingTransaction::where('payment_status', 'tempo')->count(),
            'tempo_soon'  => IncomingTransaction::where('payment_status', 'tempo')
                ->whereDate('due_date', '<=', $nowWib->copy()->addDays(7)->toDateString())->count(),
            'sku_updated' => (clone $month)->distinct('product_id')->count('product_id'),
            'sku_names'   => (clone $month)->with('product')->latest('id')->limit(3)->get()
                ->pluck('product.name')->filter()->unique()->implode(', '),
            'total_count' => IncomingTransaction::count(),
            'lunas_all'   => IncomingTransaction::where('payment_status', 'lunas')->count(),
            'tempo_all'   => IncomingTransaction::where('payment_status', 'tempo')->count(),
        ];

        $suppliers = Supplier::orderBy('name')->get();

        $products = Product::active()
            ->addSelect([
                'last_price' => IncomingTransaction::select('price_per_box')
                    ->whereColumn('product_id', 'products.id')
                    ->orderByDesc('received_at')
                    ->orderByDesc('id')
                    ->limit(1),
            ])
            ->orderBy('name')
            ->get();

        $query = $this->buildFilteredQuery($request);

        $status = strtoupper($request->input('status', 'SEMUA'));
        if (! in_array($status, ['LUNAS', 'TEMPO'], true)) {
            $status = 'SEMUA';
        }

        $transactions = $query
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $selectedProductId = $request->integer('product_id') ?: null;

        return view('incoming-goods.index', compact(
            'kpi', 'suppliers', 'products', 'transactions', 'status', 'selectedProductId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'no_faktur'      => 'required|string|max:100|unique:incoming_transactions,no_faktur',
            'supplier_id'    => 'required|exists:suppliers,id',
            'product_id'     => 'required|exists:products,id',
            'received_at'    => 'required|date',
            'payment_status' => 'required|in:lunas,tempo',
            'due_date'       => 'nullable|required_if:payment_status,tempo|date|after_or_equal:received_at',
            'box_qty'        => 'required|integer|min:1',
            'price_per_box'  => 'required|integer|min:1',
        ], [
            'no_faktur.unique'        => 'Nomor faktur sudah terdaftar. Gunakan nomor lain.',
            'due_date.required_if'    => 'Tanggal jatuh tempo wajib diisi untuk pembayaran tempo.',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal masuk gudang.',
            'price_per_box.min'       => 'Harga modal per box minimal Rp 1.',
        ]);

        $noFaktur = strtoupper(trim($data['no_faktur']));

        $totalPcs = DB::transaction(function () use ($data, $noFaktur): int {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $totalPcs = $data['box_qty'] * $product->box_pcs_ratio;
            $modalPcs = (int) round(($data['box_qty'] * $data['price_per_box']) / max($totalPcs, 1));
            $subtotal = (int) round($data['box_qty'] * $data['price_per_box']);

            IncomingTransaction::create([
                'no_faktur'      => $noFaktur,
                'supplier_id'    => $data['supplier_id'],
                'product_id'     => $data['product_id'],
                'received_at'    => $data['received_at'],
                'payment_status' => $data['payment_status'],
                'due_date'       => $data['payment_status'] === 'tempo' ? $data['due_date'] : null,
                'box_qty'        => $data['box_qty'],
                'total_pcs'      => $totalPcs,
                'price_per_box'  => $data['price_per_box'],
                'price_per_pcs'  => $modalPcs,
                'subtotal'       => $subtotal,
            ]);

            return $totalPcs;
        });

        return redirect()
            ->route('incoming-goods.index')
            ->with('success', "Faktur {$noFaktur} tersimpan. Stok bertambah " . number_format($totalPcs, 0, ',', '.') . ' Pcs.');
    }

    public function rollback(IncomingTransaction $transaction)
    {
        $no = $transaction->no_faktur;

        try {
            DB::transaction(function () use ($transaction) {
                $transaction->delete();
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('incoming-goods.index')
            ->with('success', "Transaksi {$no} berhasil di-rollback. Stok gudang telah dikurangi.");
    }

    public function payNow(IncomingTransaction $transaction)
    {
        if ($transaction->payment_status === 'lunas') {
            return back()->with('success', "Faktur {$transaction->no_faktur} sudah berstatus LUNAS.");
        }

        DB::transaction(function () use ($transaction) {
            $trx = IncomingTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($trx->payment_status === 'tempo') {
                $trx->update([
                    'payment_status' => 'lunas',
                    'due_date'       => null,
                ]);
            }
        });

        return redirect()
            ->route('incoming-goods.index')
            ->with('success', "Faktur {$transaction->no_faktur} telah dilunasi.");
    }

    public function export(Request $request)
    {
        $transactions = $this->buildFilteredQuery($request)
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->get();

        $filename = 'rekap-barang-masuk-' . now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No. Faktur',
                'Tanggal Masuk',
                'Supplier',
                'SKU',
                'Nama Barang',
                'Jumlah Box',
                'Total Pcs',
                'Modal per Box (Rp)',
                'Modal per Pcs (Rp)',
                'Total Transaksi (Rp)',
                'Status Bayar',
                'Jatuh Tempo',
            ], ';');

            foreach ($transactions as $trx) {
                fputcsv($handle, [
                    $trx->no_faktur,
                    $trx->received_at?->format('Y-m-d'),
                    $trx->supplier?->name ?? '-',
                    $trx->product?->sku ?? '-',
                    $trx->product?->name ?? '-',
                    $trx->box_qty,
                    $trx->total_pcs,
                    (int) $trx->price_per_box,
                    (int) $trx->price_per_pcs,
                    (int) $trx->subtotal,
                    strtoupper($trx->payment_status),
                    $trx->due_date?->format('Y-m-d') ?? '-',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = IncomingTransaction::with(['supplier', 'product']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('no_faktur', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        $status = strtoupper($request->input('status', 'SEMUA'));
        if (in_array($status, ['LUNAS', 'TEMPO'], true)) {
            $query->where('payment_status', strtolower($status));
        }

        return $query;
    }
}