<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OutgoingTransaction;
use App\Models\ReceivablePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $baseTempo = OutgoingTransaction::query()
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal');

        // ============ KPI ============
        $totalReceivable   = (float) $baseTempo->clone()->sum(DB::raw('subtotal - paid_amount'));
        $totalInvoices     = $baseTempo->clone()->count();

        $dueSoon           = (float) $baseTempo->clone()
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)])
            ->sum(DB::raw('subtotal - paid_amount'));
        $dueSoonCount      = $baseTempo->clone()
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)])
            ->count();
        $dueSoonCustomers  = $baseTempo->clone()
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)])
            ->distinct('customer_id')->count('customer_id');

        $overdue           = (float) $baseTempo->clone()
            ->where('due_date', '<', now()->startOfDay())
            ->sum(DB::raw('subtotal - paid_amount'));
        $overdueCount      = $baseTempo->clone()
            ->where('due_date', '<', now()->startOfDay())
            ->count();
        $overdueCustomers  = $baseTempo->clone()
            ->where('due_date', '<', now()->startOfDay())
            ->with('customer:id,name,phone')
            ->get()
            ->pluck('customer.name', 'customer.id')
            ->filter();

        $weekStart    = now()->startOfWeek();
        $paidThisWeek = (float) ReceivablePayment::whereBetween('paid_at', [$weekStart, now()])->sum('amount');
        $paidWeekCnt  = ReceivablePayment::whereBetween('paid_at', [$weekStart, now()])
            ->distinct('outgoing_transaction_id')->count('outgoing_transaction_id');

        // ============ AGING (berdasar umur invoice / sold_at) ============
        $aging = [
            'lancar'   => (float) $baseTempo->clone()
                ->where('sold_at', '>=', now()->subDays(14))
                ->sum(DB::raw('subtotal - paid_amount')),
            'warning'  => (float) $baseTempo->clone()
                ->where('sold_at', '>=', now()->subDays(30))
                ->where('sold_at', '<', now()->subDays(14))
                ->sum(DB::raw('subtotal - paid_amount')),
            'critical' => (float) $baseTempo->clone()
                ->where('sold_at', '<', now()->subDays(30))
                ->sum(DB::raw('subtotal - paid_amount')),
        ];
        $aging['lancar_count']   = $baseTempo->clone()->where('sold_at', '>=', now()->subDays(14))->count();
        $aging['warning_count']  = $baseTempo->clone()->whereBetween('sold_at', [now()->subDays(30), now()->subDays(15)])->count();
        $aging['critical_count'] = $baseTempo->clone()->where('sold_at', '<', now()->subDays(30))->count();

        $agingSum = max(0.01, $aging['lancar'] + $aging['warning'] + $aging['critical']);
        $aging['lancar_pct']   = round(($aging['lancar']   / $agingSum) * 100, 1);
        $aging['warning_pct']  = round(($aging['warning']  / $agingSum) * 100, 1);
        $aging['critical_pct'] = round(($aging['critical'] / $agingSum) * 100, 1);

        // ============ QUERY TABEL dengan filter ============
        $filter = $request->input('filter', 'all');

        $query = OutgoingTransaction::with(['customer', 'product']);

        match ($filter) {
            'not_due'  => $query->where('payment_status', 'tempo')->where('due_date', '>=', now()),
            'due_soon' => $query->where('payment_status', 'tempo')->whereBetween('due_date', [now(), now()->addDays(3)]),
            'overdue'  => $query->where('payment_status', 'tempo')->where('due_date', '<', now()->startOfDay()),
            'paid'     => $query->where('payment_status', 'lunas')
                                ->whereHas('payments', fn ($p) => $p->where('paid_at', '>=', now()->startOfMonth())),
            default    => $filter = 'all' and $query->where('payment_status', 'tempo')->whereRaw('paid_amount < subtotal'),
        };

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('no_invoice', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")
                      ->orWhere('pic_name', 'like', "%{$search}%"));
            });
        }

        $sort = $request->input('sort', 'due_asc');
        match ($sort) {
            'remaining_desc' => $query->orderByRaw('(subtotal - paid_amount) DESC'),
            'amount_desc'    => $query->orderByDesc('subtotal'),
            'oldest'         => $query->orderBy('sold_at'),
            default          => $query->orderByRaw('due_date IS NULL, due_date ASC'),
        };

        $transactions = $query->paginate(10)->withQueryString();

        // ============ TRANSACTION untuk QUICK PANEL ============
        $selectedId = $request->input('pay');
        $selectedTrx = $selectedId
            ? OutgoingTransaction::with('customer', 'product')->find($selectedId)
            : $transactions->first();
        // fallback: kalau selectedTrx sudah lunas / tidak tempo, jangan tampilkan form
        if ($selectedTrx && ($selectedTrx->payment_status === 'lunas' || $selectedTrx->remaining <= 0)) {
            $selectedTrx = null;
        }

        // ============ DROPDOWN untuk MODAL ============
        $allTempoTrx = OutgoingTransaction::with('customer:id,name')
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->orderBy('due_date')
            ->get();

        return view('receivables.index', compact(
            'totalReceivable', 'totalInvoices',
            'dueSoon', 'dueSoonCount', 'dueSoonCustomers',
            'overdue', 'overdueCount', 'overdueCustomers',
            'paidThisWeek', 'paidWeekCnt',
            'aging', 'transactions', 'filter',
            'selectedTrx', 'allTempoTrx'
        ));
    }

    public function pay(Request $request, OutgoingTransaction $transaction)
    {
        $data = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'paid_at'        => 'required|date',
            'payment_method' => 'required|string|in:Transfer Bank,Kas Tunai',
            'account'        => 'nullable|string|max:100',
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:255',
        ]);

        if ($transaction->payment_status === 'lunas' || $transaction->remaining <= 0) {
            return back()->withErrors(['amount' => 'Transaksi ini sudah lunas.']);
        }

        if ($data['amount'] > $transaction->remaining + 0.01) {
            return back()->withErrors([
                'amount' => 'Jumlah melebihi sisa tagihan (Rp ' . number_format($transaction->remaining, 0, ',', '.') . ')',
            ])->withInput();
        }

        DB::transaction(function () use ($transaction, $data) {
            ReceivablePayment::create([
                'outgoing_transaction_id' => $transaction->id,
                'customer_id'             => $transaction->customer_id,
                'paid_at'                 => $data['paid_at'],
                'amount'                  => $data['amount'],
                'payment_method'          => $data['payment_method'],
                'account'                 => $data['account'] ?? 'Kas Tunai',
                'reference_no'            => $data['reference_no'] ?? null,
                'notes'                   => $data['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('receivables.index')
            ->with('success', "Pembayaran Rp " . number_format($data['amount'], 0, ',', '.') . " untuk {$transaction->no_invoice} berhasil dicatat. Kas Masuk otomatis ter-update.");
    }
}