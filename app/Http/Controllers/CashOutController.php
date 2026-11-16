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
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        // ============ KPI ============
        $monthTotal = (float) CashOut::whereBetween('paid_at', [$monthStart, $monthEnd])->sum('amount');
        $monthCount = CashOut::whereBetween('paid_at', [$monthStart, $monthEnd])->count();

        $todayTotal = (float) CashOut::whereDate('paid_at', today())->sum('amount');
        $todayCount = CashOut::whereDate('paid_at', today())->count();

        $todaySupplier = (float) CashOut::whereDate('paid_at', today())
            ->whereIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier'])
            ->sum('amount');
        $todayOps = $todayTotal - $todaySupplier;

        // ============ SALDO PER AKUN (in - out) ============
        $inByAccount  = CashIn::selectRaw('account, SUM(amount) as total')->groupBy('account')->pluck('total', 'account');
        $outByAccount = CashOut::selectRaw('account, SUM(amount) as total')->groupBy('account')->pluck('total', 'account');
        $balances = [];
        foreach (CashIn::ACCOUNTS as $key => $_) {
            $balances[$key] = (float) ($inByAccount[$key] ?? 0) - (float) ($outByAccount[$key] ?? 0);
        }

        // ============ DOMINAN KATEGORI ============
        $catBreakdown = CashOut::whereBetween('paid_at', [$monthStart, $monthEnd])
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->category => ['total' => (float) $r->total, 'count' => $r->count]]);
        $catTotal = max(0.01, $catBreakdown->sum(fn ($v) => $v['total']));

        // ============ HUTANG SUPPLIER < 5 HARI ============
        $upcomingDue = IncomingTransaction::with('supplier')
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<=', now()->addDays(5))
            ->orderBy('due_date')
            ->get();

        // ============ SEMUA HUTANG TEMPO (untuk dropdown form) ============
        $openTempo = IncomingTransaction::with('supplier')
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->orderBy('due_date')
            ->get();

        // ============ BIAYA RUTIN (last paid per kategori) ============
        $recurringCats = ['biaya_listrik', 'logistik_armada', 'bensin_distribusi'];
        $recurringCosts = collect($recurringCats)->mapWithKeys(function ($cat) {
            return [$cat => CashOut::where('category', $cat)->latest('paid_at')->first()];
        });

        // ============ QUERY TABEL ============
        $query = CashOut::with('source');

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
        if (! array_key_exists($acc, CashIn::ACCOUNTS)) $acc = 'all';
        if ($acc !== 'all') $query->where('account', $acc);

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

        $cashOuts = $query->paginate(10)->withQueryString();

        // ============ TAB COUNTS ============
        $tabCounts = [
            'all'         => CashOut::count(),
            'supplier'    => CashOut::whereIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier'])->count(),
            'operasional' => CashOut::whereNotIn('category', ['kulakan_lunas', 'pelunasan_hutang_supplier'])->count(),
            'manual'      => CashOut::whereNull('source_id')->count(),
        ];

        return view('cash-outs.index', compact(
            'monthTotal', 'monthCount', 'todayTotal', 'todayCount',
            'todaySupplier', 'todayOps',
            'balances', 'catBreakdown', 'catTotal',
            'upcomingDue', 'openTempo',
            'recurringCats', 'recurringCosts',
            'cashOuts', 'cat', 'acc', 'sort', 'tabCounts'
        ));
    }

    /** Simpan manual expense (biaya operasional / gaji / dll) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:biaya_operasional,biaya_listrik,gaji_staff,logistik_armada,bensin_distribusi,lain_lain',
            'account'  => 'required|in:bca,mandiri,kas_tunai',
            'amount'   => 'required|numeric|min:1',
            'paid_at'  => 'nullable|date',
            'notes'    => 'required|string|max:255',
        ]);

        if ($data['amount'] > $this->accountBalance($data['account'])) {
            return back()->withErrors([
                'amount' => 'Saldo ' . CashIn::ACCOUNTS[$data['account']]['label'] . ' tidak mencukupi (tersedia: Rp ' . number_format($this->accountBalance($data['account']), 0, ',', '.') . ').',
            ])->withInput();
        }

        CashOut::create([
            'code'         => CashOut::generateCode(false),
            'category'     => $data['category'],
            'account'      => $data['account'],
            'amount'       => $data['amount'],
            'paid_at'      => $data['paid_at'] ?? now(),
            'source_type'  => null,
            'source_id'    => null,
            'notes'        => $data['notes'],
        ]);

        return redirect()->route('cash-outs.index')
            ->with('success', 'Pengeluaran Rp ' . number_format($data['amount'], 0, ',', '.') . ' berhasil dicatat.');
    }

    /** Bayar hutang supplier (cicilan) */
    public function paySupplier(Request $request, IncomingTransaction $transaction)
    {
        $data = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'paid_at'        => 'required|date',
            'payment_method' => 'required|in:Transfer Bank,Kas Tunai',
            'account'        => 'required|in:bca,mandiri,kas_tunai',
            'notes'          => 'nullable|string|max:255',
        ]);

        if ($transaction->payment_status === 'lunas' || $transaction->remaining <= 0) {
            return back()->withErrors(['amount' => 'Transaksi ini sudah lunas.'])->withInput();
        }

        if ($data['amount'] > $transaction->remaining + 0.01) {
            return back()->withErrors([
                'amount' => 'Jumlah melebihi sisa hutang (Rp ' . number_format($transaction->remaining, 0, ',', '.') . ').',
            ])->withInput();
        }

        if ($data['amount'] > $this->accountBalance($data['account'])) {
            return back()->withErrors([
                'amount' => 'Saldo ' . CashIn::ACCOUNTS[$data['account']]['label'] . ' tidak mencukupi.',
            ])->withInput();
        }

        DB::transaction(function () use ($transaction, $data) {
            SupplierPayment::create([
                'incoming_transaction_id' => $transaction->id,
                'supplier_id'             => $transaction->supplier_id,
                'paid_at'                 => $data['paid_at'],
                'amount'                  => $data['amount'],
                'payment_method'          => $data['payment_method'],
                'account'                 => $data['account'],
                'notes'                   => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('cash-outs.index')
            ->with('success', 'Pembayaran Rp ' . number_format($data['amount'], 0, ',', '.') . ' untuk faktur ' . $transaction->no_faktur . ' berhasil dicatat.');
    }

    private function accountBalance(string $key): float
    {
        $in  = (float) CashIn::where('account', $key)->sum('amount');
        $out = (float) CashOut::where('account', $key)->sum('amount');
        return $in - $out;
    }
}