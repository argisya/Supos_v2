<?php

namespace App\Http\Controllers;

use App\Models\CashIn;
use App\Models\CashOut;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function resolveClientTimezone(Request $request): string
    {
        $tz = $request->cookie('client_tz');
        if ($tz && in_array($tz, timezone_identifiers_list(), true)) {
            return $tz;
        }
        return 'Asia/Jakarta';
    }

    public function index(Request $request)
    {
        $clientTz = $this->resolveClientTimezone($request);
        $nowLocal = now($clientTz);

        // ============ PERIOD (Berbasis waktu lokal client tanpa ubah default timezone) ============
        $period = $request->input('period', 'month');
        if (!in_array($period, ['today', '7d', 'month'], true)) {
            $period = 'month';
        }

        [$startLocal, $endLocal, $periodLabel] = match ($period) {
            'today' => [$nowLocal->copy()->startOfDay(), $nowLocal->copy()->endOfDay(), 'Hari Ini'],
            '7d' => [$nowLocal->copy()->subDays(6)->startOfDay(), $nowLocal->copy()->endOfDay(), '7 Hari Terakhir'],
            default => [$nowLocal->copy()->startOfMonth(), $nowLocal->copy()->endOfMonth(), $nowLocal->translatedFormat('M Y')],
        };

        $dbTz = config('app.timezone');
        $start = $startLocal->copy()->setTimezone($dbTz);
        $end = $endLocal->copy()->setTimezone($dbTz);
        $todayStart = $nowLocal->copy()->startOfDay()->setTimezone($dbTz);
        $todayEnd = $nowLocal->copy()->endOfDay()->setTimezone($dbTz);

        // ============ KPI 1: Omzet ============
        $periodRevenue = (float) OutgoingTransaction::whereBetween('sold_at', [$start, $end])->sum('subtotal');
        $todayRevenue = (float) OutgoingTransaction::whereBetween('sold_at', [$todayStart, $todayEnd])->sum('subtotal');
        $todayBoxQty = (int) OutgoingTransaction::whereBetween('sold_at', [$todayStart, $todayEnd])->sum('box_qty');

        // ============ KPI 2: Laba ============
        $periodProfit = (float) OutgoingTransaction::whereBetween('sold_at', [$start, $end])->sum('profit');
        $periodRealizedProfit = (float) OutgoingTransaction::whereBetween('sold_at', [$start, $end])
            ->where('payment_status', 'lunas')
            ->sum('profit');
        $todayProfit = (float) OutgoingTransaction::whereBetween('sold_at', [$todayStart, $todayEnd])->sum('profit');
        $marginPct = $periodRevenue > 0 ? round(($periodProfit / $periodRevenue) * 100, 2) : 0;
        $todayMargin = $todayRevenue > 0 ? round(($todayProfit / $todayRevenue) * 100, 2) : 0;

        // ============ KPI 3: Arus Kas ============
        $periodCashIn = (float) CashIn::whereBetween('received_at', [$start, $end])->sum('amount');
        $periodCashOut = (float) CashOut::whereBetween('paid_at', [$start, $end])->sum('amount');
        $netCash = $periodCashIn - $periodCashOut;

        $totalCashIn = (float) CashIn::sum('amount');
        $totalCashOut = (float) CashOut::sum('amount');
        $cashBalance = $totalCashIn - $totalCashOut;

        // ============ KPI 4: Aset Fisik & Piutang ============
        $allProducts = Product::active()
            ->addSelect([
                'last_hpp' => IncomingTransaction::select('price_per_box')
                    ->whereColumn('product_id', 'products.id')
                    ->orderByDesc('received_at')
                    ->orderByDesc('id')
                    ->limit(1)
            ])
            ->get();

        $stockValue = 0.0;
        $stockBoxCount = 0.0;
        $aman = $menipis = $habis = 0;

        foreach ($allProducts as $p) {
            $ratio = max(1, (int) $p->box_pcs_ratio);
            $threshold = max(1, (int) ($p->min_stock_box ?? 10));
            $box = $p->stock_pcs / $ratio;
            $hpp = (float) ($p->last_hpp ?? 0);

            $stockValue += $box * $hpp;
            $stockBoxCount += $box;

            if ((int) $p->stock_pcs === 0) {
                $habis++;
            } elseif ($box <= $threshold) {
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

        $totalSalesAll = (float) OutgoingTransaction::sum('subtotal');
        $collectRate = $totalSalesAll > 0
            ? round((max(0, $totalSalesAll - $piutangTotal) / $totalSalesAll) * 100, 1)
            : 100.0;

        // ============ RADAR 1: Stok Darurat ============
        $criticalStock = $allProducts
            ->filter(function ($p) {
                $ratio = max(1, (int) $p->box_pcs_ratio);
                $threshold = max(1, (int) ($p->min_stock_box ?? 10));
                return (int) $p->stock_pcs === 0 || ($p->stock_pcs / $ratio) <= $threshold;
            })
            ->sortBy('stock_pcs')
            ->take(4)
            ->values();

        // ============ RADAR 2: Piutang Overdue ============
        $localTodayDate = $nowLocal->toDateString();

        $overdueReceivables = OutgoingTransaction::with('customer')
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<', $localTodayDate)
            ->orderBy('due_date')
            ->take(4)
            ->get();

        $overdueTotal = (float) OutgoingTransaction::where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<', $localTodayDate)
            ->sum(DB::raw('subtotal - paid_amount'));

        $overdueCount = OutgoingTransaction::where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<', $localTodayDate)
            ->count();

        // ============ RADAR 3: Hutang Jatuh Tempo ============
        $upcomingDeadline = $nowLocal->copy()->addDays(5)->toDateString();

        $upcomingPayables = IncomingTransaction::with('supplier')
            ->where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<=', $upcomingDeadline)
            ->orderBy('due_date')
            ->take(4)
            ->get();

        $upcomingTotal = (float) IncomingTransaction::where('payment_status', 'tempo')
            ->whereRaw('paid_amount < subtotal')
            ->whereDate('due_date', '<=', $upcomingDeadline)
            ->sum(DB::raw('subtotal - paid_amount'));

        // ============ CHART: 7 hari terakhir (3 query ringkas, bukan 21 query) ============
        $chartStartDb = $nowLocal->copy()->subDays(6)->startOfDay()->setTimezone($dbTz);
        $chartEndDb = $nowLocal->copy()->endOfDay()->setTimezone($dbTz);

        $omzetByDate = OutgoingTransaction::whereBetween('sold_at', [$chartStartDb, $chartEndDb])
            ->get(['sold_at', 'subtotal'])
            ->groupBy(fn($r) => Carbon::parse($r->sold_at)->setTimezone($clientTz)->toDateString())
            ->map->sum('subtotal');

        $cashInByDate = CashIn::whereBetween('received_at', [$chartStartDb, $chartEndDb])
            ->get(['received_at', 'amount'])
            ->groupBy(fn($r) => Carbon::parse($r->received_at)->setTimezone($clientTz)->toDateString())
            ->map->sum('amount');

        $cashOutByDate = CashOut::whereBetween('paid_at', [$chartStartDb, $chartEndDb])
            ->get(['paid_at', 'amount'])
            ->groupBy(fn($r) => Carbon::parse($r->paid_at)->setTimezone($clientTz)->toDateString())
            ->map->sum('amount');

        $chartDays = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dLocal = $nowLocal->copy()->subDays($i);
            $dateKey = $dLocal->toDateString();

            $chartDays->push([
                'date' => $dLocal->copy(),
                'label' => $dLocal->translatedFormat('d M'),
                'is_today' => $i === 0,
                'omzet' => (float) ($omzetByDate[$dateKey] ?? 0),
                'cash_in' => (float) ($cashInByDate[$dateKey] ?? 0),
                'cash_out' => (float) ($cashOutByDate[$dateKey] ?? 0),
            ]);
        }

        $maxChart = max(
            $chartDays->max('omzet') ?: 0,
            $chartDays->max('cash_in') ?: 0,
            $chartDays->max('cash_out') ?: 0,
            1
        );

        $chartDays = $chartDays->map(function ($row) use ($maxChart) {
            $row['omzet_h'] = $row['omzet'] > 0 ? max(6, round(($row['omzet'] / $maxChart) * 100)) : 2;
            $row['cash_in_h'] = $row['cash_in'] > 0 ? max(6, round(($row['cash_in'] / $maxChart) * 100)) : 2;
            $row['cash_out_h'] = $row['cash_out'] > 0 ? max(6, round(($row['cash_out'] / $maxChart) * 100)) : 2;
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

        $topContribution = (float) $topProducts->sum('total_profit');
        $topPct = $periodProfit > 0 ? round(($topContribution / $periodProfit) * 100, 1) : 0;

        // ============ BREAKDOWN SUMBER KAS ============
        $kasBreakdown = CashIn::whereBetween('received_at', [$start, $end])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $rawKasTotal = (float) $kasBreakdown->sum();
        $kasTotal = max(0.01, $rawKasTotal);
        $kasTunai = (float) ($kasBreakdown['penjualan_tunai'] ?? 0);
        $kasPiutang = (float) ($kasBreakdown['pelunasan_piutang'] ?? 0);
        $kasLain = max(0.0, $rawKasTotal - $kasTunai - $kasPiutang);

        $kasTunaiPct = $rawKasTotal > 0 ? round(($kasTunai / $kasTotal) * 100, 1) : 0;
        $kasPiutangPct = $rawKasTotal > 0 ? round(($kasPiutang / $kasTotal) * 100, 1) : 0;
        $kasLainPct = $rawKasTotal > 0 ? round(($kasLain / $kasTotal) * 100, 1) : 0;

        // ============ AKTIVITAS TERKINI (Sinkron: INV/DO pakai nomor faktur, Kas Masuk/Keluar tanpa kode kas) ============
        $activities = collect();

        OutgoingTransaction::with(['product', 'customer'])->latest('sold_at')->limit(3)->get()
            ->each(fn($o) => $activities->push([
                'at' => Carbon::parse($o->sold_at)->setTimezone($clientTz),
                'ref' => $o->no_invoice,
                'ref_mono' => true,
                'type' => 'Penjualan Grosir',
                'icon' => 'outbox',
                'type_class' => 'bg-primary-container/25 text-primary',
                'entity' => $o->customer?->name ?? 'Pelanggan Umum',
                'entity_sub' => $o->customer?->type ?? 'Grosir',
                'detail' => "{$o->box_qty} Box " . ($o->product?->name ?? 'Produk'),
                'amount' => '+' . number_format((int) $o->subtotal, 0, ',', '.'),
                'amount_sub' => 'Laba Rp ' . number_format((int) $o->profit, 0, ',', '.'),
                'amount_class' => 'text-primary',
                'status' => $o->payment_status === 'lunas' ? 'Tersinkron' : 'Tempo',
                'status_icon' => $o->payment_status === 'lunas' ? 'done_all' : 'pending',
                'status_class' => $o->payment_status === 'lunas' ? 'text-primary' : 'text-tertiary',
            ]));

        CashIn::where('category', '!=', 'penjualan_tunai')->latest('received_at')->limit(2)->get()
            ->each(function ($ci) use ($activities, $clientTz) {
                $catLabel = match ($ci->category) {
                    'pelunasan_piutang' => 'Pelunasan Piutang',
                    'modal_pemilik' => 'Setoran Modal',
                    default => 'Kas Masuk Non-Op',
                };
                $account = $ci->account_label ?? 'Kas Tunai';
                $activities->push([
                    'at' => Carbon::parse($ci->received_at)->setTimezone($clientTz),
                    'ref' => $account,
                    'ref_mono' => false,
                    'type' => $catLabel,
                    'icon' => 'payments',
                    'type_class' => 'bg-secondary-container/30 text-secondary',
                    'entity' => filled($ci->source_label) ? $ci->source_label : ($ci->category_label ?? $catLabel),
                    'entity_sub' => $account,
                    'detail' => $ci->notes ?? 'Penerimaan kas masuk',
                    'amount' => '+' . number_format((int) $ci->amount, 0, ',', '.'),
                    'amount_sub' => 'Kas Bertambah',
                    'amount_class' => 'text-primary',
                    'status' => 'Tersinkron',
                    'status_icon' => 'verified',
                    'status_class' => 'text-primary',
                ]);
            });

        IncomingTransaction::with(['product', 'supplier'])->latest('received_at')->limit(2)->get()
            ->each(fn($i) => $activities->push([
                'at' => Carbon::parse($i->received_at)->setTimezone($clientTz),
                'ref' => $i->no_faktur,
                'ref_mono' => true,
                'type' => 'Barang Masuk',
                'icon' => 'move_to_inbox',
                'type_class' => 'bg-tertiary-container/30 text-tertiary',
                'entity' => $i->supplier?->name ?? 'Supplier',
                'entity_sub' => $i->supplier?->type ?? 'Principal',
                'detail' => "{$i->box_qty} Box " . ($i->product?->name ?? 'Produk'),
                'amount' => $i->payment_status === 'lunas'
                    ? '-' . number_format((int) $i->subtotal, 0, ',', '.')
                    : '(Hutang +' . number_format((int) $i->subtotal, 0, ',', '.') . ')',
                'amount_sub' => 'HPP Terkunci',
                'amount_class' => $i->payment_status === 'lunas' ? 'text-error' : 'text-on-surface-variant',
                'status' => 'Masuk Fisik',
                'status_icon' => 'inventory_2',
                'status_class' => 'text-tertiary',
            ]));

        CashOut::latest('paid_at')->limit(2)->get()
            ->each(function ($c) use ($activities, $clientTz) {
                $account = $c->account_label ?? 'Kas Tunai';
                $activities->push([
                    'at' => Carbon::parse($c->paid_at)->setTimezone($clientTz),
                    'ref' => $account,
                    'ref_mono' => false,
                    'type' => 'Kas Keluar',
                    'icon' => 'account_balance_wallet',
                    'type_class' => 'bg-error-container/30 text-error',
                    'entity' => $account,
                    'entity_sub' => $c->category_label ?? 'Operasional',
                    'detail' => $c->notes ?? '-',
                    'amount' => '-' . number_format((int) $c->amount, 0, ',', '.'),
                    'amount_sub' => 'Kas Berkurang',
                    'amount_class' => 'text-error',
                    'status' => 'Ada Bukti',
                    'status_icon' => 'receipt',
                    'status_class' => 'text-on-surface-variant',
                ]);
            });

        $activities = $activities->sortByDesc('at')->take(6)->values();

        // ============ HEALTH % (Tepat 100%) ============
        $totalSku = $allProducts->count();
        $amanPct = $totalSku > 0 ? (int) round(($aman / $totalSku) * 100) : 0;
        $menipisPct = $totalSku > 0 ? (int) round(($menipis / $totalSku) * 100) : 0;
        $habisPct = $totalSku > 0 ? max(0, 100 - $amanPct - $menipisPct) : 0;

        return view('dashboard.index', compact(
            'nowLocal',
            'period',
            'periodLabel',
            'start',
            'end',
            'periodRevenue',
            'todayRevenue',
            'todayBoxQty',
            'periodProfit',
            'periodRealizedProfit',
            'todayProfit',
            'marginPct',
            'todayMargin',
            'periodCashIn',
            'periodCashOut',
            'netCash',
            'cashBalance',
            'stockValue',
            'piutangTotal',
            'piutangCount',
            'totalAssets',
            'stockBoxCount',
            'collectRate',
            'aman',
            'menipis',
            'habis',
            'totalSku',
            'amanPct',
            'menipisPct',
            'habisPct',
            'criticalStock',
            'overdueReceivables',
            'overdueTotal',
            'overdueCount',
            'upcomingPayables',
            'upcomingTotal',
            'chartDays',
            'topProducts',
            'topContribution',
            'topPct',
            'kasTunai',
            'kasPiutang',
            'kasLain',
            'kasTotal',
            'kasTunaiPct',
            'kasPiutangPct',
            'kasLainPct',
            'activities'
        ));
    }
}