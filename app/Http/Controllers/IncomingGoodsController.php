<?php

namespace App\Http\Controllers;

use App\Models\CashOut;
use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use RuntimeException;

class IncomingGoodsController extends Controller
{
    public function index(Request $request)
    {
        $nowWib = now('Asia/Jakarta');
        $selectedMonth = trim((string) $request->input('month', ''));

        if (preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $periodDate = Carbon::createFromFormat('Y-m', $selectedMonth, 'Asia/Jakarta');
            $periodLabel = $periodDate->translatedFormat('M Y');
            $start = $periodDate->copy()->startOfMonth()->toDateString();
            $end = $periodDate->copy()->endOfMonth()->toDateString();
            $period = IncomingTransaction::whereBetween('received_at', [$start, $end]);
        } else {
            $selectedMonth = '';
            $periodLabel = 'Semua Periode';
            $period = IncomingTransaction::query();
        }

        $kpi = [
            'period_label' => $periodLabel,
            'month_total' => (int) (clone $period)->sum('subtotal'),
            'month_box' => (int) (clone $period)->sum('box_qty'),
            'month_pcs' => (int) (clone $period)->sum('total_pcs'),
            'month_count' => (clone $period)->count(),
            'lunas_total' => (int) (clone $period)->where('payment_status', 'lunas')->sum('subtotal'),
            'lunas_count' => (clone $period)->where('payment_status', 'lunas')->count(),
            'tempo_total' => (int) IncomingTransaction::where('payment_status', 'tempo')->sum('subtotal'),
            'tempo_count' => IncomingTransaction::where('payment_status', 'tempo')->count(),
            'tempo_soon' => IncomingTransaction::where('payment_status', 'tempo')
                ->whereDate('due_date', '<=', $nowWib->copy()->addDays(7)->toDateString())->count(),
            'sku_updated' => (clone $period)->distinct('product_id')->count('product_id'),
            'sku_names' => (clone $period)->with('product')->latest('id')->limit(3)->get()
                ->pluck('product.name')->filter()->unique()->implode(', '),
            'total_count' => (clone $period)->count(),
            'lunas_all' => (clone $period)->where('payment_status', 'lunas')->count(),
            'tempo_all' => (clone $period)->where('payment_status', 'tempo')->count(),
        ];

        $availableMonths = IncomingTransaction::query()
            ->selectRaw("DATE_FORMAT(received_at, '%Y-%m') as ym")
            ->whereNotNull('received_at')
            ->distinct()
            ->orderByDesc('ym')
            ->pluck('ym')
            ->push($nowWib->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values()
            ->mapWithKeys(fn(string $ym) => [
                $ym => Carbon::createFromFormat('Y-m', $ym, 'Asia/Jakarta')->translatedFormat('F Y'),
            ]);

        $suppliers = Supplier::orderBy('name')->get();
        $accounts = CashOut::ACCOUNTS;

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
        if (!in_array($status, ['LUNAS', 'TEMPO'], true)) {
            $status = 'SEMUA';
        }

        $transactions = $query
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $selectedProductId = $request->integer('product_id') ?: null;
        $nextNoFaktur = IncomingTransaction::generateNoFaktur(
            old('received_at', $nowWib->toDateString())
        );

        return view('incoming-goods.index', compact(
            'kpi',
            'suppliers',
            'accounts',
            'products',
            'transactions',
            'status',
            'selectedMonth',
            'availableMonths',
            'selectedProductId',
            'nextNoFaktur'
        ));
    }

    public function generateFaktur(Request $request)
    {
        $date = $request->input('received_at', now('Asia/Jakarta')->toDateString());

        return response()->json([
            'no_faktur' => IncomingTransaction::generateNoFaktur($date),
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:40',
            'address' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
        ]);

        $name = trim($data['name']);
        $words = preg_split('/\s+/', $name) ?: [];
        $initials = mb_strtoupper(
            count($words) >= 2
            ? mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1)
            : mb_substr($name, 0, 2)
        );

        $payload = ['name' => $name];
        if (Schema::hasColumn('suppliers', 'type')) {
            $payload['type'] = trim((string) ($data['type'] ?? '')) ?: 'Distributor Resmi';
        }
        if (Schema::hasColumn('suppliers', 'initials')) {
            $payload['initials'] = $initials;
        }
        if (Schema::hasColumn('suppliers', 'phone') && !empty($data['phone'])) {
            $payload['phone'] = trim($data['phone']);
        }
        if (Schema::hasColumn('suppliers', 'address') && !empty($data['address'])) {
            $payload['address'] = trim($data['address']);
        }

        $supplier = Supplier::create($payload);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Supplier {$supplier->name} berhasil ditambahkan.",
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'type' => $supplier->type ?? '',
                ],
            ]);
        }

        return back()->with('success', "Supplier {$supplier->name} berhasil ditambahkan.");
    }

    public function store(Request $request)
    {
        $request->merge([
            'box_qty' => preg_replace('/\D+/', '', (string) $request->input('box_qty', '')),
            'price_per_box' => preg_replace('/\D+/', '', (string) $request->input('price_per_box', '')),
        ]);

        $validAccounts = array_keys(CashOut::ACCOUNTS);

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'received_at' => 'required|date',
            'payment_status' => 'required|in:lunas,tempo',
            'payment_account' => ['nullable', Rule::in($validAccounts)],
            'due_date' => 'nullable|required_if:payment_status,tempo|date|after_or_equal:received_at',
            'box_qty' => 'required|integer|min:1',
            'price_per_box' => 'required|integer|min:1',
        ], [
            'due_date.required_if' => 'Tanggal jatuh tempo wajib diisi untuk pembayaran tempo.',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal masuk gudang.',
            'price_per_box.min' => 'Harga modal per box minimal Rp 1.',
        ]);

        [$totalPcs, $noFaktur] = DB::transaction(function () use ($data): array {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $noFaktur = IncomingTransaction::generateNoFaktur($data['received_at'], true);
            $totalPcs = $data['box_qty'] * $product->box_pcs_ratio;
            $modalPcs = (int) round(($data['box_qty'] * $data['price_per_box']) / max($totalPcs, 1));
            $subtotal = (int) round($data['box_qty'] * $data['price_per_box']);

            $trx = new IncomingTransaction([
                'no_faktur' => $noFaktur,
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
            $trx->paymentAccount = $data['payment_account'] ?? 'kas_tunai';
            $trx->save();

            return [$totalPcs, $noFaktur];
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
            ->with('success', "Transaksi {$no} berhasil dihapus. Stok gudang telah dikurangi.");
    }

    public function payNow(Request $request, IncomingTransaction $transaction)
    {
        if ($transaction->payment_status === 'lunas') {
            return back()->with('success', "Faktur {$transaction->no_faktur} sudah berstatus LUNAS.");
        }

        $validAccounts = array_keys(CashOut::ACCOUNTS);
        $validated = $request->validate([
            'payment_account' => ['nullable', Rule::in($validAccounts)],
        ]);

        $account = $validated['payment_account'] ?? 'kas_tunai';
        $labelAkun = CashOut::ACCOUNTS[$account]['label'] ?? ucfirst($account);

        DB::transaction(function () use ($transaction, $account) {
            $trx = IncomingTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($trx->payment_status === 'tempo') {
                $trx->paymentAccount = $account;
                $trx->payment_status = 'lunas';
                $trx->due_date = null;
                $trx->save();
            }
        });

        return redirect()
            ->route('incoming-goods.index')
            ->with('success', "Faktur {$transaction->no_faktur} telah dilunasi via {$labelAkun}.");
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
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        $status = strtoupper($request->input('status', 'SEMUA'));
        if (in_array($status, ['LUNAS', 'TEMPO'], true)) {
            $query->where('payment_status', strtolower($status));
        }

        $month = trim((string) $request->input('month', ''));
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            $dt = Carbon::createFromFormat('Y-m', $month, 'Asia/Jakarta');
            $query->whereBetween('received_at', [
                $dt->copy()->startOfMonth()->toDateString(),
                $dt->copy()->endOfMonth()->toDateString(),
            ]);
        }

        return $query;
    }
}