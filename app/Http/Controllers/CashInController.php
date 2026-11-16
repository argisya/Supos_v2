<?php

namespace App\Http\Controllers;

use App\Models\CashIn;
use App\Models\OutgoingTransaction;
use App\Models\ReceivablePayment;
use Illuminate\Http\Request;

class CashInController extends Controller
{
    public function index(Request $request)
    {
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        // ============ KPI ============
        $monthTotal   = (float) CashIn::whereBetween('received_at', [$monthStart, $monthEnd])->sum('amount');
        $monthCount   = CashIn::whereBetween('received_at', [$monthStart, $monthEnd])->count();
        $todayTotal   = (float) CashIn::whereDate('received_at', today())->sum('amount');
        $todayCount   = CashIn::whereDate('received_at', today())->count();

        // Breakdown sumber dana bulan ini
        $catBreakdown = CashIn::whereBetween('received_at', [$monthStart, $monthEnd])
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->category => ['total' => (float) $r->total, 'count' => $r->count]]);

        // Breakdown per akun
        $accountBreakdown = CashIn::whereBetween('received_at', [$monthStart, $monthEnd])
            ->selectRaw('account, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('account')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->account => ['total' => (float) $r->total, 'count' => $r->count]]);

        // Saldo kumulatif per akun (all time, karena cash_out belum ada)
        $accountBalances = CashIn::selectRaw('account, SUM(amount) as total')
            ->groupBy('account')
            ->pluck('total', 'account')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // Rekonsiliasi — hitung dari sumber asli
        $fromSales     = (float) OutgoingTransaction::where('payment_status', 'lunas')
                            ->whereDoesntHave('payments')
                            ->whereMonth('sold_at', now()->month)
                            ->whereYear('sold_at', now()->year)
                            ->sum('subtotal');
        $fromReceivable = (float) ReceivablePayment::whereMonth('paid_at', now()->month)
                            ->whereYear('paid_at', now()->year)
                            ->sum('amount');

        $cashInFromSales      = (float) CashIn::where('category', 'penjualan_tunai')->whereMonth('received_at', now()->month)->sum('amount');
        $cashInFromReceivable = (float) CashIn::where('category', 'pelunasan_piutang')->whereMonth('received_at', now()->month)->sum('amount');

        $reconTotal  = $fromSales + $fromReceivable;
        $reconCashIn = $cashInFromSales + $cashInFromReceivable;
        $reconOK     = abs($reconTotal - $reconCashIn) < 0.01;

        // ============ Query tabel ============
        $query = CashIn::with('source');

        // Filter kategori
        $cat = $request->input('category', 'all');
        if ($cat === 'manual') {
            $query->whereNull('source_id');
        } elseif (in_array($cat, ['penjualan_tunai', 'pelunasan_piutang'], true)) {
            $query->where('category', $cat);
        } else {
            $cat = 'all';
        }

        // Filter akun
        $acc = $request->input('account', 'all');
        if (array_key_exists($acc, CashIn::ACCOUNTS)) {
            $query->where('account', $acc);
        } else {
            $acc = 'all';
        }

        // Search
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc'  => $query->orderBy('amount'),
            default       => $query->orderByDesc('received_at')->orderByDesc('id'),
        };

        $cashIns = $query->paginate(10)->withQueryString();

        // Tab counts (unfiltered by category)
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
            'category'    => 'required|in:refund_supplier,modal_pemilik,penjualan_palet,pendapatan_non_ops,deposit_kembali',
            'account'     => 'required|in:bca,mandiri,kas_tunai',
            'amount'      => 'required|numeric|min:1',
            'received_at' => 'nullable|date',
            'notes'       => 'required|string|max:255',
        ], [
            'category.required' => 'Pilih kategori penerimaan.',
            'account.required'  => 'Pilih akun tujuan kas.',
            'amount.min'        => 'Nominal harus lebih dari 0.',
            'notes.required'    => 'Keterangan wajib diisi untuk audit trail.',
        ]);

        CashIn::create([
            'code'         => CashIn::generateCode(false),
            'category'     => $data['category'],
            'account'      => $data['account'],
            'amount'       => $data['amount'],
            'received_at'  => $data['received_at'] ?? now(),
            'source_type'  => null,
            'source_id'    => null,
            'reference_no' => null,
            'notes'        => $data['notes'],
        ]);

        return redirect()
            ->route('cash-ins.index')
            ->with('success', 'Penerimaan kas sebesar Rp ' . number_format($data['amount'], 0, ',', '.') . ' berhasil dicatat ke ' . CashIn::ACCOUNTS[$data['account']]['label'] . '.');
    }
}