<?php

namespace App\Http\Controllers;

use App\Models\CashIn;
use App\Models\CashOut;
use App\Models\IncomingTransaction;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashOutController extends Controller
{
    public function index(Request $request)
    {
        $nowWib = now('Asia/Jakarta');
        $todayStart = $nowWib->copy()->startOfDay()->timezone(config('app.timezone'));
        $todayEnd = $nowWib->copy()->endOfDay()->timezone(config('app.timezone'));
        $monthStart = $nowWib->copy()->startOfMonth()->timezone(config('app.timezone'));
        $monthEnd = $nowWib->copy()->endOfMonth()->timezone(config('app.timezone'));

        // ============ KPI (AKURAT ZONA WAKTU WIB) ============
        $monthQuery = CashOut::whereBetween('paid_at', [$monthStart, $monthEnd]);

        $monthTotal = (float) (clone $monthQuery)->sum('amount');
        $monthCount = (clone $monthQuery)->count();

        $todayQuery = CashOut::whereBetween('paid_at', [$todayStart, $todayEnd]);
        $todayTotal = (float) (clone $todayQuery)->sum('amount');
        $todayCount = (clone $todayQuery)->count();

        $todaySupplier = (float) (clone $todayQuery)
            ->whereIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier'])
            ->sum('amount');
        $todayOps = max(0, $todayTotal - $todaySupplier);

        // ============ SALDO REALTIME PER AKUN (In - Out) ============
        $inByAccount = CashIn::selectRaw('account, SUM(amount) as total')->groupBy('account')->pluck('total', 'account');
        $outByAccount = CashOut::selectRaw('account, SUM(amount) as total')->groupBy('account')->pluck('total', 'account');
        $balances = [];
        foreach (CashIn::ACCOUNTS as $key => $_) {
            $balances[$key] = max(0, (float) ($inByAccount[$key] ?? 0) - (float) ($outByAccount[$key] ?? 0));
        }

        // ============ DOMINAN KATEGORI BULAN INI ============
        $catBreakdown = (clone $monthQuery)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn($r) => [$r->category => ['total' => (float) $r->total, 'count' => $r->count]]);
        $catTotal = max(0.01, $catBreakdown->sum(fn($v) => $v['total']));

        // ============ HUTANG SUPPLIER <= 5 HARI ============
        $upcomingDue = IncomingTransaction::with(['supplier', 'product'])
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<=', $nowWib->copy()->addDays(5)->toDateString())
            ->orderBy('due_date')
            ->get();

        // ============ SEMUA HUTANG TEMPO (dropdown modal) ============
        $openTempo = IncomingTransaction::with(['supplier', 'product'])
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->orderBy('due_date')
            ->get();

        // ============ BIAYA RUTIN TERAKHIR ============
        $recurringCats = ['biaya_listrik', 'logistik_armada', 'bensin_distribusi'];
        $recurringCosts = collect($recurringCats)->mapWithKeys(function ($cat) {
            return [$cat => CashOut::where('category', $cat)->latest('paid_at')->first()];
        });

        // ============ QUERY TABEL ============
        [$query, $cat, $acc, $sort, $from, $to] = $this->buildFilteredQuery($request);
        $cashOuts = $query->paginate(10)->withQueryString();

        // ============ TAB COUNTS (SINGLE QUERY OPTIMIZED) ============
        $countsRow = CashOut::selectRaw("
            COUNT(*) as total_all,
            SUM(CASE WHEN category IN ('kulakan_lunas', 'pelunasan_hutang_supplier') THEN 1 ELSE 0 END) as total_supplier,
            SUM(CASE WHEN category NOT IN ('kulakan_lunas', 'pelunasan_hutang_supplier') THEN 1 ELSE 0 END) as total_operasional,
            SUM(CASE WHEN source_id IS NULL THEN 1 ELSE 0 END) as total_manual
        ")->first();

        $tabCounts = [
            'all' => (int) ($countsRow->total_all ?? 0),
            'supplier' => (int) ($countsRow->total_supplier ?? 0),
            'operasional' => (int) ($countsRow->total_operasional ?? 0),
            'manual' => (int) ($countsRow->total_manual ?? 0),
        ];

        $hasActiveFilter = $cat !== 'all' || $acc !== 'all' || filled($request->search) || filled($from) || filled($to);

        return view('cash-outs.index', compact(
            'monthTotal',
            'monthCount',
            'todayTotal',
            'todayCount',
            'todaySupplier',
            'todayOps',
            'balances',
            'catBreakdown',
            'catTotal',
            'upcomingDue',
            'openTempo',
            'recurringCats',
            'recurringCosts',
            'cashOuts',
            'cat',
            'acc',
            'sort',
            'from',
            'to',
            'tabCounts',
            'hasActiveFilter'
        ));
    }

    /** Halaman cetak bukti kas keluar (A4 Portrait — selaras dengan Kas Masuk) */
    public function receipt(CashOut $cashOut)
    {
        $cashOut->load(['source.supplier']);
        return view('cash-outs.receipt', compact('cashOut'));
    }

    /** Simpan pengeluaran operasional manual */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:biaya_operasional,biaya_listrik,gaji_staff,logistik_armada,bensin_distribusi,lain_lain',
            'account' => 'required|in:bca,mandiri,kas_tunai',
            'amount' => 'required|integer|min:1',
            'paid_at' => 'nullable|date',
            'notes' => 'required|string|max:255',
        ], [
            'category.required' => 'Kategori pengeluaran wajib dipilih.',
            'amount.integer' => 'Nominal pengeluaran harus berupa angka bulat tanpa desimal.',
            'amount.min' => 'Nominal pengeluaran minimal Rp 1.',
            'notes.required' => 'Keterangan pengeluaran wajib diisi.',
        ]);

        $paidAt = filled($data['paid_at'])
            ? Carbon::parse($data['paid_at'], 'Asia/Jakarta')->timezone(config('app.timezone'))
            : now();

        try {
            Cache::lock('cash_account_balance_' . $data['account'], 5)->block(3, function () use ($data, $paidAt) {
                $availableBalance = $this->accountBalance($data['account']);
                if ($data['amount'] > $availableBalance) {
                    throw ValidationException::withMessages([
                        'amount' => 'Saldo ' . CashIn::ACCOUNTS[$data['account']]['label'] . ' tidak mencukupi (tersedia: Rp ' . number_format(max(0, $availableBalance), 0, ',', '.') . ').',
                    ]);
                }

                DB::transaction(function () use ($data, $paidAt) {
                    CashOut::create([
                        'code' => CashOut::generateCode(false, true),
                        'category' => $data['category'],
                        'account' => $data['account'],
                        'amount' => $data['amount'],
                        'paid_at' => $paidAt,
                        'source_type' => null,
                        'source_id' => null,
                        'notes' => $data['notes'],
                    ]);
                });
            });
        } catch (LockTimeoutException $e) {
            return back()
                ->with('error', 'Sistem sedang memproses transaksi lain pada rekening ini. Silakan coba beberapa detik lagi.')
                ->withInput(['active_tab' => 'ops'] + $request->all());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput(['active_tab' => 'ops'] + $request->all());
        }

        return redirect()->route('cash-outs.index')
            ->with('success', 'Pengeluaran Rp ' . number_format($data['amount'], 0, ',', '.') . ' berhasil dicatat.');
    }

    /** Bayar hutang supplier (cicilan / pelunasan) */
    public function paySupplier(Request $request, IncomingTransaction $transaction)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:1',
            'paid_at' => 'required|date',
            'account' => 'required|in:bca,mandiri,kas_tunai',
            'notes' => 'nullable|string|max:255',
        ], [
            'amount.integer' => 'Jumlah pembayaran harus berupa angka bulat tanpa desimal.',
            'amount.min' => 'Jumlah pembayaran minimal Rp 1.',
            'paid_at.required' => 'Tanggal pembayaran wajib diisi.',
        ]);

        $method = $data['account'] === 'kas_tunai' ? 'Kas Tunai' : 'Transfer Bank';
        $paidAt = Carbon::parse($data['paid_at'], 'Asia/Jakarta')->timezone(config('app.timezone'));

        try {
            Cache::lock('cash_account_balance_' . $data['account'], 5)->block(3, function () use ($transaction, $data, $method, $paidAt) {
                DB::transaction(function () use ($transaction, $data, $method, $paidAt) {
                    $lockedTrx = IncomingTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

                    if ($lockedTrx->payment_status === 'lunas' || $lockedTrx->remaining <= 0) {
                        throw ValidationException::withMessages([
                            'amount' => 'Faktur ini sudah berstatus LUNAS.',
                        ]);
                    }

                    if ($data['amount'] > $lockedTrx->remaining + 0.01) {
                        throw ValidationException::withMessages([
                            'amount' => 'Jumlah melebihi sisa hutang (Rp ' . number_format($lockedTrx->remaining, 0, ',', '.') . ').',
                        ]);
                    }

                    $availableBalance = $this->accountBalance($data['account']);
                    if ($data['amount'] > $availableBalance) {
                        throw ValidationException::withMessages([
                            'amount' => 'Saldo ' . CashIn::ACCOUNTS[$data['account']]['label'] . ' tidak mencukupi (tersedia: Rp ' . number_format(max(0, $availableBalance), 0, ',', '.') . ').',
                        ]);
                    }

                    SupplierPayment::create([
                        'incoming_transaction_id' => $lockedTrx->id,
                        'supplier_id' => $lockedTrx->supplier_id,
                        'paid_at' => $paidAt,
                        'amount' => $data['amount'],
                        'payment_method' => $method,
                        'account' => $data['account'],
                        'reference_no' => $lockedTrx->no_faktur,
                        'notes' => $data['notes'] ?: "Pembayaran faktur {$lockedTrx->no_faktur}",
                    ]);
                });
            });
        } catch (LockTimeoutException $e) {
            return back()
                ->with('error', 'Sistem sedang memproses transaksi lain pada rekening ini. Silakan coba beberapa detik lagi.')
                ->withInput(['active_tab' => 'supplier', 'selected_trx_id' => $transaction->id] + $request->all());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput(['active_tab' => 'supplier', 'selected_trx_id' => $transaction->id] + $request->all());
        }

        return redirect()->route('cash-outs.index')
            ->with('success', 'Pembayaran Rp ' . number_format($data['amount'], 0, ',', '.') . ' untuk faktur ' . $transaction->no_faktur . ' berhasil diposting.');
    }

    /** Hapus entri kas keluar khusus input manual */
    public function destroy(CashOut $cashOut)
    {
        if ($cashOut->is_auto) {
            return back()->with('error', 'Entri otomatis terikat dengan transaksi supplier dan tidak dapat dihapus dari halaman ini.');
        }

        $amountLabel = 'Rp ' . number_format((int) $cashOut->amount, 0, ',', '.');
        $cashOut->delete();

        return redirect()->route('cash-outs.index')
            ->with('success', "Entri pengeluaran manual senilai {$amountLabel} berhasil dihapus dan saldo rekening dikembalikan.");
    }

    /** Ekspor CSV mutasi kas keluar sesuai filter aktif + baris total pengeluaran */
    public function export(Request $request)
    {
        [$query] = $this->buildFilteredQuery($request);
        $rows = $query->get();

        $filename = 'rekap-kas-keluar-' . now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Tanggal & Waktu (WIB)',
                'Sumber Jurnal',
                'Kategori',
                'Tujuan / Penerima',
                'No. Referensi / Faktur',
                'Akun Sumber',
                'Nominal (Rp)',
                'Keterangan',
            ], ';');

            $grandTotal = 0;

            foreach ($rows as $co) {
                $recipient = $co->source?->supplier?->name ?? 'Pengeluaran Operasional';
                $paidWib = $co->paid_at?->copy()->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '-';
                $amount = (int) round($co->amount);
                $grandTotal += $amount;

                fputcsv($handle, [
                    $paidWib,
                    $co->is_auto ? 'Otomatis' : 'Manual',
                    $co->category_label,
                    $recipient,
                    $co->reference_no ?? '-',
                    $co->account_label,
                    $amount,
                    $co->notes ?? '-',
                ], ';');
            }

            fputcsv($handle, ['', '', '', '', '', '', '', ''], ';');
            fputcsv($handle, [
                '',
                '',
                '',
                '',
                '',
                'TOTAL KAS KELUAR',
                $grandTotal,
                '',
            ], ';');

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
        if (!array_key_exists($acc, CashIn::ACCOUNTS)) {
            $acc = 'all';
        }
        if ($acc !== 'all') {
            $query->where('account', $acc);
        }

        $from = $request->input('from');
        $to = $request->input('to');
        if ($from) {
            $fromStart = Carbon::parse($from, 'Asia/Jakarta')->startOfDay()->timezone(config('app.timezone'));
            $query->where('paid_at', '>=', $fromStart);
        }
        if ($to) {
            $toEnd = Carbon::parse($to, 'Asia/Jakarta')->endOfDay()->timezone(config('app.timezone'));
            $query->where('paid_at', '<=', $toEnd);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('source', function ($sq) use ($search) {
                        $sq->whereHas('supplier', fn($sup) => $sup->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc' => $query->orderBy('amount'),
            default => $query->orderByDesc('paid_at')->orderByDesc('id'),
        };

        return [$query, $cat, $acc, $sort, $from, $to];
    }

    private function accountBalance(string $key): float
    {
        $in = (float) CashIn::where('account', $key)->sum('amount');
        $out = (float) CashOut::where('account', $key)->sum('amount');
        return $in - $out;
    }
}