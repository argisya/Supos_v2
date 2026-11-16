<?php

namespace App\Http\Controllers;

use App\Models\CashIn;
use App\Models\CashOut;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ============ PERIOD ============
        $period = $request->input('period', 'month');
        [$start, $end, $periodLabel] = match ($period) {
            'today' => [today()->startOfDay(), today()->endOfDay(), 'Hari Ini'],
            '7d'    => [now()->subDays(6)->startOfDay(), now()->endOfDay(), '7 Hari Terakhir'],
            default => [now()->startOfMonth(), now()->endOfMonth(), now()->translatedFormat('M Y')],
        };
        if ($period !== 'today' && $period !== '7d') $period = 'month';

        // ============ KPI 1: Omzet ============
        $periodRevenue = (float) OutgoingTransaction::whereBetween('sold_at', [$start, $end])->sum('subtotal');
        $todayRevenue  = (float) OutgoingTransaction::whereDate('sold_at', today())->sum('subtotal');
        $todayBoxQty   = (int) OutgoingTransaction::whereDate('sold_at', today())->sum('box_qty');

        // ============ KPI 2: Laba ============
        $periodProfit = (float) OutgoingTransaction::whereBetween('sold_at', [$start, $end])->sum('profit');
        $todayProfit  = (float) OutgoingTransaction::whereDate('sold_at', today())->sum('profit');
        $marginPct    = $periodRevenue > 0 ? round(($periodProfit / $periodRevenue) * 100, 2) : 0;
        $todayMargin  = $todayRevenue > 0 ? round(($todayProfit / $todayRevenue) * 100, 2) : 0;

        // ============ KPI 3: Arus Kas ============
        $periodCashIn  = (float) CashIn::whereBetween('received_at', [$start, $end])->sum('amount');
        $periodCashOut = (float) CashOut::whereBetween('paid_at', [$start, $end])->sum('amount');
        $netCash       = $periodCashIn - $periodCashOut;

        // Saldo kas aktual (all-time, bukan period)
        $totalCashIn   = (float) CashIn::sum('amount');
        $totalCashOut  = (float) CashOut::sum('amount');
        $cashBalance   = $totalCashIn - $totalCashOut;

        // ============ KPI 4: Aset Fisik & Piutang ============
        $allProducts = Product::active()
            ->addSelect(['last_hpp' => IncomingTransaction::select('price_per_box')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->limit(1)])
            ->get();

        $stockValue    = 0.0;
        $stockBoxCount = 0.0;
        $aman = $menipis = $habis = 0;

        foreach ($allProducts as $p) {
            $ratio = max(1, $p->box_pcs_ratio);
            $box   = $p->stock_pcs / $ratio;
            $hpp   = (float) ($p->last_hpp ?? 0);

            $stockValue    += $box * $hpp;
            $stockBoxCount += $box;

            if ($p->stock_pcs === 0) {
                $habis++;
            } elseif ($box <= 10) {
                $menipis++;
            } else {
                $aman++;
            }
        }

        $piutangTotal = (float) OutgoingTransaction::where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->sum(DB::raw('subtotal - paid_amount'));

        $piutangCount = OutgoingTransaction::where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->count();

        $totalAssets = $stockValue + $piutangTotal;

        // Kolektibilitas = lunas / total piutang pernah ada
        $lunasTotal  = (float) OutgoingTransaction::where('payment_status', 'lunas')->sum('subtotal');
        $tempoTotal  = (float) OutgoingTransaction::where('payment_status', 'tempo')->sum('subtotal');
        $collectRate = ($lunasTotal + $tempoTotal) > 0 ? round(($lunasTotal / ($lunasTotal + $tempoTotal)) * 100, 1) : 0;

        // ============ RADAR 1: Stok Darurat ============
        $criticalStock = $allProducts
            ->filter(fn ($p) => $p->stock_pcs === 0 || ($p->stock_pcs / max(1, $p->box_pcs_ratio)) <= 10)
            ->sortBy('stock_pcs')
            ->take(4)
            ->values();

        // ============ RADAR 2: Piutang Overdue ============
        $overdueReceivables = OutgoingTransaction::with('customer')
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->where('due_date', '<', now()->startOfDay())
            ->orderBy('due_date')
            ->take(4)
            ->get();

        $overdueTotal = (float) OutgoingTransaction::where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->where('due_date', '<', now()->startOfDay())
            ->sum(DB::raw('subtotal - paid_amount'));

        $overdueCount = OutgoingTransaction::where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->where('due_date', '<', now()->startOfDay())
            ->count();

        // ============ RADAR 3: Hutang Jatuh Tempo ============
        $upcomingPayables = IncomingTransaction::with('supplier')
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<=', now()->addDays(5))
            ->orderBy('due_date')
            ->take(4)
            ->get();

        $upcomingTotal = (float) IncomingTransaction::where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<=', now()->addDays(5))
            ->sum(DB::raw('subtotal - paid_amount'));

        // ============ CHART: 7 hari ============
        $chartDays = collect();
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $chartDays->push([
                'date'     => $d->copy(),
                'label'    => $d->translatedFormat('d M'),
                'is_today' => $d->isToday(),
                'omzet'    => (float) OutgoingTransaction::whereDate('sold_at', $d)->sum('subtotal'),
                'cash_in'  => (float) CashIn::whereDate('received_at', $d)->sum('amount'),
                'cash_out' => (float) CashOut::whereDate('paid_at', $d)->sum('amount'),
            ]);
        }
        $maxChart = max(
            $chartDays->max('omzet') ?: 0,
            $chartDays->max('cash_in') ?: 0,
            $chartDays->max('cash_out') ?: 0,
            1
        );
        $chartDays = $chartDays->map(function ($row) use ($maxChart) {
            $row['omzet_h']    = max(2, round(($row['omzet']    / $maxChart) * 100));
            $row['cash_in_h']  = max(2, round(($row['cash_in']  / $maxChart) * 100));
            $row['cash_out_h'] = max(2, round(($row['cash_out'] / $maxChart) * 100));
            return $row;
        });

        // ============ TOP 5 SKU ============
        $topProducts = OutgoingTransaction::selectRaw('product_id, SUM(box_qty) as total_box, SUM(subtotal) as total_omzet, SUM(profit) as total_profit')
            ->whereBetween('sold_at', [$start, $end])
            ->groupBy('product_id')
            ->orderByDesc('total_profit')
            ->limit(5)
            ->with('product')
            ->get();

        $topContribution = $topProducts->sum('total_profit');
        $topPct = $periodProfit > 0 ? round(($topContribution / $periodProfit) * 100, 1) : 0;

        // ============ BREAKDOWN SUMBER KAS ============
        $kasBreakdown = CashIn::whereBetween('received_at', [$start, $end])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $kasTotal   = (float) max(0.01, $kasBreakdown->sum());
        $kasTunai   = (float) ($kasBreakdown['penjualan_tunai'] ?? 0);
        $kasPiutang = (float) ($kasBreakdown['pelunasan_piutang'] ?? 0);
        $kasLain    = (float) ($kasTotal - $kasTunai - $kasPiutang);

        $kasTunaiPct   = round(($kasTunai   / $kasTotal) * 100, 1);
        $kasPiutangPct = round(($kasPiutang / $kasTotal) * 100, 1);
        $kasLainPct    = round(max(0, $kasLain / $kasTotal * 100), 1);

        // ============ AKTIVITAS TERKINI ============
        $activities = collect();

        OutgoingTransaction::with(['product', 'customer'])->latest('sold_at')->limit(3)->get()
            ->each(fn ($o) => $activities->push([
                'at'          => $o->sold_at,
                'ref'         => $o->no_invoice,
                'type'        => 'Penjualan Grosir',
                'icon'        => 'outbox',
                'type_class'  => 'bg-primary-container/20 text-primary',
                'entity'      => $o->customer->name,
                'entity_sub'  => $o->customer->type,
                'detail'      => "{$o->box_qty} Box {$o->product->name}",
                'amount'      => '+' . number_format((int) $o->subtotal, 0, ',', '.'),
                'amount_sub'  => 'Laba Rp ' . number_format((int) $o->profit, 0, ',', '.'),
                'amount_class'=> 'text-primary',
                'status'      => $o->payment_status === 'lunas' ? 'Tersinkron' : 'Tempo',
                'status_icon' => $o->payment_status === 'lunas' ? 'done_all' : 'pending',
                'status_class'=> $o->payment_status === 'lunas' ? 'text-primary' : 'text-tertiary',
            ]));

        IncomingTransaction::with(['product', 'supplier'])->latest('received_at')->limit(2)->get()
            ->each(fn ($i) => $activities->push([
                'at'          => $i->received_at,
                'ref'         => $i->no_faktur,
                'type'        => 'Barang Masuk',
                'icon'        => 'move_to_inbox',
                'type_class'  => 'bg-surface-variant text-tertiary',
                'entity'      => $i->supplier->name,
                'entity_sub'  => $i->supplier->type ?? 'Supplier',
                'detail'      => "{$i->box_qty} Box {$i->product->name}",
                'amount'      => $i->payment_status === 'lunas'
                                    ? '-' . number_format((int) $i->subtotal, 0, ',', '.')
                                    : '(Hutang +' . number_format((int) $i->subtotal, 0, ',', '.') . ')',
                'amount_sub'  => 'HPP Terkunci',
                'amount_class'=> $i->payment_status === 'lunas' ? 'text-error' : 'text-on-surface-variant',
                'status'      => 'Masuk Fisik',
                'status_icon' => 'inventory_2',
                'status_class'=> 'text-tertiary',
            ]));

        CashOut::latest('paid_at')->limit(2)->get()
            ->each(fn ($c) => $activities->push([
                'at'          => $c->paid_at,
                'ref'         => $c->code,
                'type'        => 'Kas Keluar',
                'icon'        => 'account_balance_wallet',
                'type_class'  => 'bg-error-container/30 text-error',
                'entity'      => $c->account_label,
                'entity_sub'  => $c->category_label,
                'detail'      => $c->notes ?? '-',
                'amount'      => '-' . number_format((int) $c->amount, 0, ',', '.'),
                'amount_sub'  => 'Beban Berjalan',
                'amount_class'=> 'text-error',
                'status'      => 'Ada Bukti',
                'status_icon' => 'receipt',
                'status_class'=> 'text-on-surface-variant',
            ]));

        $activities = $activities->sortByDesc('at')->take(5)->values();

        // ============ HEALTH % ============
        $amanPct    = $totalSku = $allProducts->count();
        $amanPct    = $totalSku > 0 ? round(($aman    / $totalSku) * 100) : 0;
        $menipisPct = $totalSku > 0 ? round(($menipis / $totalSku) * 100) : 0;
        $habisPct   = $totalSku > 0 ? round(($habis   / $totalSku) * 100) : 0;

        return view('dashboard.index', compact(
            'period', 'periodLabel', 'start', 'end',
            'periodRevenue', 'todayRevenue', 'todayBoxQty',
            'periodProfit', 'todayProfit', 'marginPct', 'todayMargin',
            'periodCashIn', 'periodCashOut', 'netCash', 'cashBalance',
            'stockValue', 'piutangTotal', 'piutangCount', 'totalAssets',
            'stockBoxCount', 'collectRate',
            'aman', 'menipis', 'habis', 'totalSku', 'amanPct', 'menipisPct', 'habisPct',
            'criticalStock', 'overdueReceivables', 'overdueTotal', 'overdueCount',
            'upcomingPayables', 'upcomingTotal',
            'chartDays', 'topProducts', 'topContribution', 'topPct',
            'kasTunai', 'kasPiutang', 'kasLain', 'kasTotal',
            'kasTunaiPct', 'kasPiutangPct', 'kasLainPct',
            'activities'
        ));
    }
}