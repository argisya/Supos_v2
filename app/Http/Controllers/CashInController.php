<?php

namespace App\Http\Controllers;

use App\Models\CashIn;
use App\Models\OutgoingTransaction;
use App\Models\ReceivablePayment;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CashInController extends Controller
{
    public function index(Request $request)
    {
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        // ============ KPI ============
        $monthTotal = (float) CashIn::whereBetween('received_at', [$monthStart, $monthEnd])->sum('amount');
        $monthCount = CashIn::whereBetween('received_at', [$monthStart, $monthEnd])->count();
        $todayTotal = (float) CashIn::whereDate('received_at', today())->sum('amount');
        $todayCount = CashIn::whereDate('received_at', today())->count();

        // Breakdown sumber dana bulan ini
        $catBreakdown = CashIn::whereBetween('received_at', [$monthStart, $monthEnd])
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->category => ['total' => (float) $r->total, 'count' => $r->count]]);

        // Breakdown per akun bulan ini
        $accountBreakdown = CashIn::whereBetween('received_at', [$monthStart, $monthEnd])
            ->selectRaw('account, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('account')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->account => ['total' => (float) $r->total, 'count' => $r->count]]);

        // Saldo per akun (dikurangi cash_outs jika tabel sudah ada)
        $inTotals = CashIn::selectRaw('account, SUM(amount) as total')
            ->groupBy('account')
            ->pluck('total', 'account');

        $outTotals = Schema::hasTable('cash_outs')
            ? DB::table('cash_outs')->selectRaw('account, SUM(amount) as total')->groupBy('account')->pluck('total', 'account')
            : collect();

        $accountBalances = [];
        foreach (array_keys(CashIn::ACCOUNTS) as $accKey) {
            $accountBalances[$accKey] = (float) ($inTotals[$accKey] ?? 0) - (float) ($outTotals[$accKey] ?? 0);
        }

        // Rekonsiliasi bulan berjalan
        $fromSales = (float) OutgoingTransaction::where('payment_status', 'lunas')
            ->whereDoesntHave('payments')
            ->whereBetween('sold_at', [$monthStart, $monthEnd])
            ->sum('subtotal');

        $fromReceivable = (float) ReceivablePayment::whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $cashInFromSales = (float) CashIn::where('category', 'penjualan_tunai')
            ->whereBetween('received_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $cashInFromReceivable = (float) CashIn::where('category', 'pelunasan_piutang')
            ->whereBetween('received_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $reconTotal  = $fromSales + $fromReceivable;
        $reconCashIn = $cashInFromSales + $cashInFromReceivable;
        $reconOK     = abs($reconTotal - $reconCashIn) < 0.01;

        // ============ Query Tabel ============
        $cat  = $request->input('category', 'all');
        $acc  = $request->input('account', 'all');
        $sort = $request->input('sort', 'latest');

        if (! in_array($cat, ['all', 'penjualan_tunai', 'pelunasan_piutang', 'manual'], true)) {
            $cat = 'all';
        }
        if ($acc !== 'all' && ! array_key_exists($acc, CashIn::ACCOUNTS)) {
            $acc = 'all';
        }

        $query = $this->buildFilteredQuery($request, $cat, $acc);

        match ($sort) {
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc'  => $query->orderBy('amount'),
            default       => $query->orderByDesc('received_at')->orderByDesc('id'),
        };

        $cashIns = $query->paginate(10)->withQueryString();

        $tabCounts = [
            'all'               => CashIn::count(),
            'penjualan_tunai'   => CashIn::where('category', 'penjualan_tunai')->count(),
            'pelunasan_piutang' => CashIn::where('category', 'pelunasan_piutang')->count(),
            'manual'            => CashIn::whereNull('source_id')->count(),
        ];

        return view('cash-ins.index', compact(
            'monthTotal', 'monthCount', 'todayTotal', 'todayCount',
            'catBreakdown', 'accountBreakdown', 'accountBalances',
            'fromSales', 'fromReceivable', 'cashInFromSales', 'cashInFromReceivable',
            'reconTotal', 'reconCashIn', 'reconOK',
            'cashIns', 'cat', 'acc', 'sort', 'tabCounts'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category'     => 'required|in:refund_supplier,modal_pemilik,penjualan_palet,pendapatan_non_ops,deposit_kembali',
            'account'      => 'required|in:bca,mandiri,kas_tunai',
            'amount'       => 'required|numeric|min:1',
            'received_at'  => 'nullable|date',
            'reference_no' => 'nullable|string|max:100',
            'notes'        => 'required|string|max:255',
        ], [
            'category.required' => 'Pilih kategori penerimaan kas.',
            'account.required'  => 'Pilih akun tujuan kas.',
            'amount.min'        => 'Nominal harus lebih dari 0.',
            'notes.required'    => 'Keterangan wajib diisi untuk audit trail.',
        ]);

        DB::transaction(function () use ($data) {
            CashIn::create([
                'code'         => CashIn::generateCode(false),
                'category'     => $data['category'],
                'account'      => $data['account'],
                'amount'       => $data['amount'],
                'received_at'  => $data['received_at'] ?? now(),
                'source_type'  => null,
                'source_id'    => null,
                'reference_no' => $data['reference_no'] ?? null,
                'notes'        => $data['notes'],
            ]);
        });

        return redirect()
            ->route('cash-ins.index')
            ->with('success', 'Penerimaan kas Rp ' . number_format($data['amount'], 0, ',', '.') . ' berhasil dicatat ke ' . CashIn::ACCOUNTS[$data['account']]['label'] . '.');
    }

    public function destroy(CashIn $cashIn)
    {
        if ($cashIn->is_auto) {
            return back()->withErrors([
                'error' => "Mutasi otomatis ({$cashIn->code}) tidak dapat dihapus di sini. Lakukan pembatalan dari modul Barang Keluar atau Buku Tagihan.",
            ]);
        }

        $code = $cashIn->code;
        $cashIn->delete();

        return redirect()
            ->route('cash-ins.index')
            ->with('success', "Entri kas masuk manual {$code} berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $cat  = $request->input('category', 'all');
        $acc  = $request->input('account', 'all');
        $rows = $this->buildFilteredQuery($request, $cat, $acc)
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->get();

        $filename = 'buku-kas-masuk-' . now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No. Kas Masuk',
                'Waktu Diterima',
                'Tipe Jurnal',
                'Sumber / Mitra',
                'Kategori',
                'Akun Penampung',
                'No. Referensi',
                'Keterangan',
                'Nominal (Rp)',
            ], ';');

            foreach ($rows as $ci) {
                $sourceName = $ci->source?->customer?->name ?? $ci->source?->product?->name ?? 'Penerimaan Manual';
                fputcsv($handle, [
                    $ci->code,
                    $ci->received_at?->format('Y-m-d H:i') ?? '-',
                    $ci->is_auto ? 'OTOMATIS' : 'MANUAL',
                    $sourceName,
                    $ci->category_label,
                    $ci->account_label,
                    $ci->reference_no ?? '-',
                    $ci->notes ?? '-',
                    (int) $ci->amount,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFilteredQuery(Request $request, string $cat, string $acc)
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ], [
            'date_from.date_format'  => 'Format tanggal awal tidak valid.',
            'date_to.date_format'    => 'Format tanggal akhir tidak valid.',
            'date_to.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
        ]);

        $query = CashIn::with(['source' => function (MorphTo $morphTo) {
            $morphTo->morphWith([
                OutgoingTransaction::class => ['customer', 'product'],
                ReceivablePayment::class   => ['customer', 'transaction'],
            ]);
        }]);

        if ($cat === 'manual') {
            $query->whereNull('source_id');
        } elseif (in_array($cat, ['penjualan_tunai', 'pelunasan_piutang'], true)) {
            $query->where('category', $cat);
        }

        if ($acc !== 'all' && array_key_exists($acc, CashIn::ACCOUNTS)) {
            $query->where('account', $acc);
        }

        if ($dateFrom = $filters['date_from'] ?? null) {
            $query->whereDate('received_at', '>=', $dateFrom);
        }

        if ($dateTo = $filters['date_to'] ?? null) {
            $query->whereDate('received_at', '<=', $dateTo);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHasMorph(
                        'source',
                        [OutgoingTransaction::class, ReceivablePayment::class],
                        function ($sourceQuery, string $sourceType) use ($search) {
                            if ($sourceType === OutgoingTransaction::class) {
                                $sourceQuery
                                    ->where('no_invoice', 'like', "%{$search}%")
                                    ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"))
                                    ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"));

                                return;
                            }

                            $sourceQuery
                                ->whereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('transaction', fn ($transactionQuery) => $transactionQuery->where('no_invoice', 'like', "%{$search}%"));
                        }
                    );
            });
        }

        return $query;
    }
}