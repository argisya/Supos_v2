<?php

namespace App\Http\Controllers;

use App\Models\CashIn;
use App\Models\CashOut;
use App\Models\IncomingTransaction;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashOutController extends Controller
{
    public function index(Request $request)
    {
        $now = now('Asia/Jakarta');

        // ============ KPI ============
        $monthQuery = CashOut::whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month);

        $monthTotal = (float) (clone $monthQuery)->sum('amount');
        $monthCount = (clone $monthQuery)->count();

        $todayTotal = (float) CashOut::whereDate('paid_at', $now->toDateString())->sum('amount');
        $todayCount = CashOut::whereDate('paid_at', $now->toDateString())->count();

        $todaySupplier = (float) CashOut::whereDate('paid_at', $now->toDateString())
            ->whereIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier'])
            ->sum('amount');
        $todayOps = max(0, $todayTotal - $todaySupplier);

        // ============ SALDO REALTIME PER AKUN (In - Out) ============
        $inByAccount  = CashIn::selectRaw('account, SUM(amount) as total')->groupBy('account')->pluck('total', 'account');
        $outByAccount = CashOut::selectRaw('account, SUM(amount) as total')->groupBy('account')->pluck('total', 'account');
        $balances = [];
        foreach (CashIn::ACCOUNTS as $key => $_) {
            $balances[$key] = (float) ($inByAccount[$key] ?? 0) - (float) ($outByAccount[$key] ?? 0);
        }

        // ============ DOMINAN KATEGORI BULAN INI ============
        $catBreakdown = (clone $monthQuery)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->category => ['total' => (float) $r->total, 'count' => $r->count]]);
        $catTotal = max(0.01, $catBreakdown->sum(fn ($v) => $v['total']));

        // ============ HUTANG SUPPLIER <= 5 HARI ============
        $upcomingDue = IncomingTransaction::with(['supplier', 'product'])
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<=', $now->copy()->addDays(5)->toDateString())
            ->orderBy('due_date')
            ->get();

        // ============ SEMUA HUTANG TEMPO (dropdown modal) ============
        $openTempo = IncomingTransaction::with(['supplier', 'product'])
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->orderBy('due_date')
            ->get();

        // ============ BIAYA RUTIN TERAKHIR ============
        $recurringCats  = ['biaya_listrik', 'logistik_armada', 'bensin_distribusi'];
        $recurringCosts = collect($recurringCats)->mapWithKeys(function ($cat) {
            return [$cat => CashOut::where('category', $cat)->latest('paid_at')->first()];
        });

        // ============ QUERY TABEL ============
        [$query, $cat, $acc, $sort, $from, $to] = $this->buildFilteredQuery($request);
        $cashOuts = $query->paginate(10)->withQueryString();

        // ============ TAB COUNTS ============
        $tabCounts = [
            'all'         => CashOut::count(),
            'supplier'    => CashOut::whereIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier'])->count(),
            'operasional' => CashOut::whereNotIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier'])->count(),
            'manual'      => CashOut::whereNull('source_id')->count(),
        ];

        $hasActiveFilter = $cat !== 'all' || $acc !== 'all' || filled($request->search) || filled($from) || filled($to);

        return view('cash-outs.index', compact(
            'monthTotal', 'monthCount', 'todayTotal', 'todayCount',
            'todaySupplier', 'todayOps',
            'balances', 'catBreakdown', 'catTotal',
            'upcomingDue', 'openTempo',
            'recurringCats', 'recurringCosts',
            'cashOuts', 'cat', 'acc', 'sort', 'from', 'to',
            'tabCounts', 'hasActiveFilter'
        ));
    }

    /** Simpan pengeluaran operasional manual */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:biaya_operasional,biaya_listrik,gaji_staff,logistik_armada,bensin_distribusi,lain_lain',
            'account'  => 'required|in:bca,mandiri,kas_tunai',
            'amount'   => 'required|numeric|min:1',
            'paid_at'  => 'nullable|date',
            'notes'    => 'required|string|max:255',
        ], [
            'category.required' => 'Kategori pengeluaran wajib dipilih.',
            'amount.min'        => 'Nominal pengeluaran minimal Rp 1.',
            'notes.required'    => 'Keterangan pengeluaran wajib diisi.',
        ]);

        if ($data['amount'] > $this->accountBalance($data['account'])) {
            return back()->withErrors([
                'amount' => 'Saldo ' . CashIn::ACCOUNTS[$data['account']]['label'] . ' tidak mencukupi (tersedia: Rp ' . number_format($this->accountBalance($data['account']), 0, ',', '.') . ').',
            ])->withInput(['active_tab' => 'ops'] + $request->all());
        }

        DB::transaction(function () use ($data) {
            CashOut::create([
                'code'        => CashOut::generateCode(false, true),
                'category'    => $data['category'],
                'account'     => $data['account'],
                'amount'      => $data['amount'],
                'paid_at'     => $data['paid_at'] ?? now('Asia/Jakarta'),
                'source_type' => null,
                'source_id'   => null,
                'notes'       => $data['notes'],
            ]);
        });

        return redirect()->route('cash-outs.index')
            ->with('success', 'Pengeluaran Rp ' . number_format($data['amount'], 0, ',', '.') . ' berhasil dicatat.');
    }

    /** Bayar hutang supplier (cicilan / pelunasan) */
    public function paySupplier(Request $request, IncomingTransaction $transaction)
    {
        $data = $request->validate([
            'amount'  => 'required|numeric|min:1',
            'paid_at' => 'required|date',
            'account' => 'required|in:bca,mandiri,kas_tunai',
            'notes'   => 'nullable|string|max:255',
        ], [
            'amount.min'       => 'Jumlah pembayaran minimal Rp 1.',
            'paid_at.required' => 'Tanggal pembayaran wajib diisi.',
        ]);

        if ($transaction->payment_status === 'lunas' || $transaction->remaining <= 0) {
            return back()->withErrors(['amount' => 'Faktur ini sudah berstatus LUNAS.'])
                ->withInput(['active_tab' => 'supplier', 'selected_trx_id' => $transaction->id] + $request->all());
        }

        if ($data['amount'] > $transaction->remaining + 0.01) {
            return back()->withErrors([
                'amount' => 'Jumlah melebihi sisa hutang (Rp ' . number_format($transaction->remaining, 0, ',', '.') . ').',
            ])->withInput(['active_tab' => 'supplier', 'selected_trx_id' => $transaction->id] + $request->all());
        }

        if ($data['amount'] > $this->accountBalance($data['account'])) {
            return back()->withErrors([
                'amount' => 'Saldo ' . CashIn::ACCOUNTS[$data['account']]['label'] . ' tidak mencukupi (tersedia: Rp ' . number_format($this->accountBalance($data['account']), 0, ',', '.') . ').',
            ])->withInput(['active_tab' => 'supplier', 'selected_trx_id' => $transaction->id] + $request->all());
        }

        $method = $data['account'] === 'kas_tunai' ? 'Kas Tunai' : 'Transfer Bank';

        DB::transaction(function () use ($transaction, $data, $method) {
            SupplierPayment::create([
                'incoming_transaction_id' => $transaction->id,
                'supplier_id'             => $transaction->supplier_id,
                'paid_at'                 => $data['paid_at'],
                'amount'                  => $data['amount'],
                'payment_method'          => $method,
                'account'                 => $data['account'],
                'reference_no'            => $transaction->no_faktur,
                'notes'                   => $data['notes'] ?: "Pembayaran faktur {$transaction->no_faktur}",
            ]);
        });

        return redirect()->route('cash-outs.index')
            ->with('success', 'Pembayaran Rp ' . number_format($data['amount'], 0, ',', '.') . ' untuk faktur ' . $transaction->no_faktur . ' berhasil diposting.');
    }

    /** Hapus entri kas keluar khusus input manual */
    public function destroy(CashOut $cashOut)
    {
        if ($cashOut->is_auto) {
            return back()->with('error', "Entri otomatis ({$cashOut->code}) terikat dengan transaksi supplier dan tidak dapat dihapus dari halaman ini.");
        }

        $code = $cashOut->code;
        $cashOut->delete();

        return redirect()->route('cash-outs.index')
            ->with('success', "Entri pengeluaran manual {$code} berhasil dihapus dan saldo rekening dikembalikan.");
    }

    /** Ekspor CSV mutasi kas keluar sesuai filter aktif */
    public function export(Request $request)
    {
        [$query] = $this->buildFilteredQuery($request);
        $rows = $query->get();

        $filename = 'rekap-kas-keluar-' . now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No. Kas Keluar',
                'Waktu Pengeluaran',
                'Sumber Jurnal',
                'Kategori',
                'Tujuan / Penerima',
                'No. Referensi / Faktur',
                'Akun Sumber',
                'Nominal (Rp)',
                'Keterangan',
            ], ';');

            foreach ($rows as $co) {
                $recipient = $co->source?->supplier?->name ?? 'Pengeluaran Operasional';
                fputcsv($handle, [
                    $co->code,
                    $co->paid_at?->format('Y-m-d H:i'),
                    $co->is_auto ? 'Otomatis' : 'Manual',
                    $co->category_label,
                    $recipient,
                    $co->reference_no ?? '-',
                    $co->account_label,
                    (int) $co->amount,
                    $co->notes ?? '-',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFilteredQuery(Request $request): array
    {
        $query = CashOut::with(['source.supplier']);

        $cat = $request->input('category', 'all');
        if ($cat === 'manual') {
            $query->whereNull('source_id');
        } elseif ($cat === 'supplier') {
            $query->whereIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier']);
        } elseif ($cat === 'operasional') {
            $query->whereNotIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier']);
        } else {
            $cat = 'all';
        }

        $acc = $request->input('account', 'all');
        if (! array_key_exists($acc, CashIn::ACCOUNTS)) {
            $acc = 'all';
        }
        if ($acc !== 'all') {
            $query->where('account', $acc);
        }

        $from = $request->input('from');
        $to   = $request->input('to');
        if ($from) {
            $query->whereDate('paid_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('paid_at', '<=', $to);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc'  => $query->orderBy('amount'),
            default       => $query->orderByDesc('paid_at')->orderByDesc('id'),
        };

        return [$query, $cat, $acc, $sort, $from, $to];
    }

    private function accountBalance(string $key): float
    {
        $in  = (float) CashIn::where('account', $key)->sum('amount');
        $out = (float) CashOut::where('account', $key)->sum('amount');
        return $in - $out;
    }
}