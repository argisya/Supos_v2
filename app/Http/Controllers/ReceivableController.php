<?php

namespace App\Http\Controllers;

use App\Models\OutgoingTransaction;
use App\Models\ReceivablePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $baseTempo = OutgoingTransaction::query()
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal');

        // ============ KPI ============
        $totalReceivable = (float) $baseTempo->clone()->sum(DB::raw('subtotal - paid_amount'));
        $totalInvoices = $baseTempo->clone()->count();

        $dueSoon = (float) $baseTempo->clone()
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->sum(DB::raw('subtotal - paid_amount'));
        $dueSoonCount = $baseTempo->clone()
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->count();
        $dueSoonCustomers = $baseTempo->clone()
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->distinct('customer_id')
            ->count('customer_id');

        $overdue = (float) $baseTempo->clone()
            ->where('due_date', '<', now()->startOfDay())
            ->sum(DB::raw('subtotal - paid_amount'));
        $overdueCount = $baseTempo->clone()
            ->where('due_date', '<', now()->startOfDay())
            ->count();
        $overdueCustomers = $baseTempo->clone()
            ->where('due_date', '<', now()->startOfDay())
            ->with('customer:id,name,phone,pic_name')
            ->get()
            ->pluck('customer.name', 'customer.id')
            ->filter();

        $notDueCount = max(0, $totalInvoices - $overdueCount - $dueSoonCount);

        $weekStart = now()->startOfWeek();
        $paidThisWeek = (float) ReceivablePayment::whereBetween('paid_at', [$weekStart, now()])->sum('amount');
        $paidWeekCnt = ReceivablePayment::whereBetween('paid_at', [$weekStart, now()])
            ->distinct('outgoing_transaction_id')
            ->count('outgoing_transaction_id');

        $paidMonthCnt = OutgoingTransaction::where('payment_status', 'lunas')
            ->whereHas('payments', fn($p) => $p->where('paid_at', '>=', now()->startOfMonth()))
            ->count();

        // ============ AGING & RATA-RATA TEMPO DINAMIS ============
        $aging = [
            'lancar' => (float) $baseTempo->clone()
                ->where('sold_at', '>=', now()->subDays(14))
                ->sum(DB::raw('subtotal - paid_amount')),
            'warning' => (float) $baseTempo->clone()
                ->where('sold_at', '>=', now()->subDays(30))
                ->where('sold_at', '<', now()->subDays(14))
                ->sum(DB::raw('subtotal - paid_amount')),
            'critical' => (float) $baseTempo->clone()
                ->where('sold_at', '<', now()->subDays(30))
                ->sum(DB::raw('subtotal - paid_amount')),
        ];
        $aging['lancar_count'] = $baseTempo->clone()->where('sold_at', '>=', now()->subDays(14))->count();
        $aging['warning_count'] = $baseTempo->clone()->whereBetween('sold_at', [now()->subDays(30), now()->subDays(15)])->count();
        $aging['critical_count'] = $baseTempo->clone()->where('sold_at', '<', now()->subDays(30))->count();

        $agingSum = max(0.01, $aging['lancar'] + $aging['warning'] + $aging['critical']);
        $aging['lancar_pct'] = round(($aging['lancar'] / $agingSum) * 100, 1);
        $aging['warning_pct'] = round(($aging['warning'] / $agingSum) * 100, 1);
        $aging['critical_pct'] = round(($aging['critical'] / $agingSum) * 100, 1);

        $tempoTrxDates = $baseTempo->clone()
            ->whereNotNull('due_date')
            ->get(['sold_at', 'due_date']);
        $avgTempoDays = $tempoTrxDates->isNotEmpty()
            ? round($tempoTrxDates->avg(fn($t) => max(1, $t->sold_at->startOfDay()->diffInDays($t->due_date->startOfDay()))), 1)
            : 14.0;

        // ============ TOP 5 MITRA PIUTANG TERBESAR ============
        $topDebtors = OutgoingTransaction::query()
            ->select(
                'customer_id',
                DB::raw('SUM(subtotal - paid_amount) as total_due'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('MIN(due_date) as nearest_due')
            )
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->groupBy('customer_id')
            ->orderByDesc('total_due')
            ->with('customer:id,name,pic_name,credit_limit')
            ->limit(5)
            ->get();

        // ============ QUERY TABEL DENGAN FILTER ============
        $filter = $request->input('filter', 'all');
        $query = $this->buildFilteredQuery($request, $filter);

        $transactions = $query->paginate(10)->withQueryString();

        // ============ DROPDOWN & DAFTAR WA UNTUK MODAL ============
        $allTempoTrx = OutgoingTransaction::with(['customer:id,name,pic_name,phone', 'payments'])
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->get();

        $priorityWaTrx = $allTempoTrx->filter(
            fn($t) => in_array($t->aging_status, ['overdue', 'due_soon'], true)
        )->values();

        return view('receivables.index', compact(
            'totalReceivable',
            'totalInvoices',
            'notDueCount',
            'dueSoon',
            'dueSoonCount',
            'dueSoonCustomers',
            'overdue',
            'overdueCount',
            'overdueCustomers',
            'paidThisWeek',
            'paidWeekCnt',
            'paidMonthCnt',
            'aging',
            'avgTempoDays',
            'topDebtors',
            'transactions',
            'filter',
            'allTempoTrx',
            'priorityWaTrx'
        ));
    }

    public function pay(Request $request, OutgoingTransaction $transaction)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'paid_at' => 'required|date',
            'payment_method' => 'nullable|string|in:Transfer Bank,Kas Tunai',
            'account' => 'required|string|max:100',
            'reference_no' => 'nullable|string|max:100',
            'proof_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:255',
        ], [
            'proof_image.image' => 'File bukti transfer harus berupa gambar.',
            'proof_image.mimes' => 'Format gambar yang didukung hanya JPG, JPEG, PNG, atau WEBP.',
            'proof_image.max' => 'Ukuran gambar bukti transfer maksimal 5 MB.',
        ]);

        if ($transaction->payment_status === 'lunas' || $transaction->remaining <= 0) {
            return back()
                ->withErrors(['amount' => 'Transaksi ini sudah lunas.'])
                ->with('failed_trx_id', $transaction->id);
        }

        if ($data['amount'] > $transaction->remaining + 0.01) {
            return back()
                ->withErrors([
                    'amount' => 'Jumlah melebihi sisa tagihan (Rp ' . number_format($transaction->remaining, 0, ',', '.') . ')',
                ])
                ->withInput()
                ->with('failed_trx_id', $transaction->id);
        }

        $method = str_contains($data['account'], 'Tunai') ? 'Kas Tunai' : 'Transfer Bank';

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')->store('receivable-proofs', 'public');
        }

        try {
            DB::transaction(function () use ($transaction, $data, $method, $proofPath) {
                ReceivablePayment::create([
                    'outgoing_transaction_id' => $transaction->id,
                    'customer_id' => $transaction->customer_id,
                    'paid_at' => $data['paid_at'],
                    'amount' => $data['amount'],
                    'payment_method' => $data['payment_method'] ?? $method,
                    'account' => $data['account'],
                    'reference_no' => $data['reference_no'] ?? null,
                    'proof_image' => $proofPath,
                    'notes' => $data['notes'] ?? null,
                ]);
            });
        } catch (\Throwable $e) {
            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }
            throw $e;
        }

        return redirect()
            ->route('receivables.index')
            ->with('success', 'Pembayaran Rp ' . number_format($data['amount'], 0, ',', '.') . " untuk {$transaction->no_invoice} berhasil dicatat. Kas Masuk otomatis ter-update.");
    }

    public function destroyPayment(ReceivablePayment $payment)
    {
        $noInvoice = $payment->transaction?->no_invoice ?? '-';
        $amount = number_format((float) $payment->amount, 0, ',', '.');

        DB::transaction(function () use ($payment) {
            $payment->delete();
        });

        return redirect()
            ->route('receivables.index')
            ->with('success', "Cicilan Rp {$amount} pada faktur {$noInvoice} dibatalkan. Saldo piutang & Kas Masuk telah disesuaikan.");
    }

    public function export(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $transactions = $this->buildFilteredQuery($request, $filter)->get();

        $filename = 'laporan-piutang-' . now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No. Faktur',
                'Tanggal Jual',
                'Jatuh Tempo',
                'Mitra Pelanggan',
                'PIC',
                'No. HP / WA',
                'Produk',
                'Box',
                'Total Pcs',
                'Total Tagihan (Rp)',
                'Sudah Dibayar (Rp)',
                'Sisa Tagihan (Rp)',
                'Status',
            ], ';');

            foreach ($transactions as $trx) {
                fputcsv($handle, [
                    $trx->no_invoice,
                    $trx->sold_at?->format('Y-m-d H:i') ?? '-',
                    $trx->due_date?->format('Y-m-d') ?? '-',
                    $trx->customer?->name ?? '-',
                    $trx->customer?->pic_name ?? '-',
                    $trx->customer?->phone ?? '-',
                    $trx->product?->name ?? '-',
                    $trx->box_qty,
                    $trx->total_pcs,
                    (int) $trx->subtotal,
                    (int) $trx->paid_amount,
                    (int) $trx->remaining,
                    strtoupper($trx->aging_status ?? $trx->payment_status),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFilteredQuery(Request $request, string &$filter)
    {
        $query = OutgoingTransaction::with(['customer', 'product', 'payments']);

        match ($filter) {
            'not_due' => $query->where('payment_status', 'tempo')->where('due_date', '>', now()->addDays(3)->endOfDay()),
            'due_soon' => $query->where('payment_status', 'tempo')->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()]),
            'overdue' => $query->where('payment_status', 'tempo')->where('due_date', '<', now()->startOfDay()),
            'paid' => $query->where('payment_status', 'lunas')
                ->whereHas('payments', fn($p) => $p->where('paid_at', '>=', now()->startOfMonth())),
            default => ($filter = 'all') && $query->where('payment_status', 'tempo')->whereRaw('paid_amount < subtotal'),
        };

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('no_invoice', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%")
                        ->orWhere('pic_name', 'like', "%{$search}%"));
            });
        }

        $sort = $request->input('sort', 'due_asc');
        match ($sort) {
            'remaining_desc' => $query->orderByRaw('(subtotal - paid_amount) DESC'),
            'amount_desc' => $query->orderByDesc('subtotal'),
            'oldest' => $query->orderBy('sold_at'),
            default => $query->orderByRaw('due_date IS NULL, due_date ASC'),
        };

        return $query;
    }
}