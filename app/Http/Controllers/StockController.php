<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $subqueries = [
            'last_price_box' => IncomingTransaction::select('price_per_box')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->limit(1),
            'last_incoming_at' => IncomingTransaction::select('received_at')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->limit(1),
            'last_outgoing_at' => OutgoingTransaction::select('sold_at')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('sold_at')
                ->orderByDesc('id')
                ->limit(1),
        ];

        $allProducts = Product::active()
            ->with('category')
            ->addSelect($subqueries)
            ->get();

        $totalAssetValue = 0.0;
        $totalVolumeBox  = 0;
        $totalVolumePcs  = 0;
        $aman = $menipis = $habis = 0;

        foreach ($allProducts as $p) {
            $ratio     = max(1, (int) $p->box_pcs_ratio);
            $threshold = max(1, (int) ($p->min_stock_box ?? 10));
            $boxFloat  = $p->stock_pcs / $ratio;
            $priceBox  = (float) ($p->last_price_box ?? 0);

            $totalAssetValue += $boxFloat * $priceBox;
            $totalVolumeBox  += intdiv((int) $p->stock_pcs, $ratio);
            $totalVolumePcs  += (int) $p->stock_pcs;

            if ((int) $p->stock_pcs <= 0) {
                $habis++;
            } elseif ($boxFloat <= $threshold) {
                $menipis++;
            } else {
                $aman++;
            }
        }

        $today = now('Asia/Jakarta')->toDateString();
        $todayInBox  = (int) IncomingTransaction::whereDate('received_at', $today)->sum('box_qty');
        $todayOutBox = (int) OutgoingTransaction::whereDate('sold_at', $today)->sum('box_qty');

        $kpi = [
            'total_sku'        => $allProducts->count(),
            'total_asset'      => (int) round($totalAssetValue),
            'total_volume_box' => $totalVolumeBox,
            'total_volume_pcs' => $totalVolumePcs,
            'aman'             => $aman,
            'menipis'          => $menipis,
            'habis'            => $habis,
            'today_in_box'     => $todayInBox,
            'today_out_box'    => $todayOutBox,
        ];

        $status = strtoupper($request->input('status', 'SEMUA'));
        if (! in_array($status, ['AMAN', 'MENIPIS', 'HABIS'], true)) {
            $status = 'SEMUA';
        }

        $cat  = $request->input('category', 'all');
        $sort = $request->input('sort', 'stok_asc');

        $products = $this->buildFilteredQuery($request, $status, $cat, $sort, $subqueries)
            ->paginate(12)
            ->withQueryString();

        $categories     = Category::withCount(['products' => fn ($q) => $q->active()])->orderBy('name')->get();
        $productOptions = $allProducts->sortBy('name')->values();

        return view('stock.index', compact(
            'kpi', 'products', 'categories', 'productOptions', 'status', 'cat', 'sort'
        ));
    }

    public function updateAlertThreshold(Request $request)
    {
        $data = $request->validate([
            'scope'         => 'required|in:single,all',
            'product_id'    => 'nullable|required_if:scope,single|exists:products,id',
            'min_stock_box' => 'required|integer|min:1|max:10000',
        ]);

        if ($data['scope'] === 'all') {
            Product::active()->update(['min_stock_box' => $data['min_stock_box']]);
            $msg = "Batas minimum stok seluruh produk diatur ke {$data['min_stock_box']} Box.";
        } else {
            $product = Product::findOrFail($data['product_id']);
            $product->update(['min_stock_box' => $data['min_stock_box']]);
            $msg = "Batas alert {$product->name} diatur ke {$data['min_stock_box']} Box.";
        }

        return redirect()->route('stock.index')->with('success', $msg);
    }

    public function storeOpname(Request $request)
    {
        $data = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'physical_box' => 'required|integer|min:0',
            'physical_pcs' => 'required|integer|min:0',
            'notes'        => 'nullable|string|max:255',
        ]);

        $summary = DB::transaction(function () use ($data) {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $ratio   = max(1, (int) $product->box_pcs_ratio);

            $oldStockPcs = (int) $product->stock_pcs;
            $newStockPcs = ($data['physical_box'] * $ratio) + $data['physical_pcs'];
            $diffPcs     = $newStockPcs - $oldStockPcs;

            $product->update(['stock_pcs' => $newStockPcs]);

            return [
                'name' => $product->name,
                'diff' => $diffPcs,
                'new'  => $newStockPcs,
            ];
        });

        $diffLabel = $summary['diff'] >= 0 ? '+' . number_format($summary['diff'], 0, ',', '.') : number_format($summary['diff'], 0, ',', '.');

        return redirect()
            ->route('stock.index')
            ->with('success', "Opname {$summary['name']} tersimpan (Selisih {$diffLabel} Pcs → Stok Baru: " . number_format($summary['new'], 0, ',', '.') . ' Pcs).');
    }

    public function mutations(Product $product)
    {
        $incoming = IncomingTransaction::with('supplier')
            ->where('product_id', $product->id)
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->limit(25)
            ->get()
            ->map(fn ($trx) => [
                'type'       => 'MASUK',
                'ref_no'     => $trx->no_faktur,
                'date'       => $trx->received_at?->format('d M Y') ?? '-',
                'sort_date'  => ($trx->received_at?->format('Y-m-d') ?? '1970-01-01') . ' ' . ($trx->created_at?->format('H:i:s') ?? '00:00:00'),
                'party'      => $trx->supplier?->name ?? 'Supplier',
                'box_qty'    => (int) $trx->box_qty,
                'total_pcs'  => (int) $trx->total_pcs,
                'unit_price' => (int) $trx->price_per_box,
                'subtotal'   => (int) $trx->subtotal,
            ]);

        $outgoing = OutgoingTransaction::where('product_id', $product->id)
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->limit(25)
            ->get()
            ->map(fn ($trx) => [
                'type'       => 'KELUAR',
                'ref_no'     => $trx->no_invoice ?? $trx->no_faktur ?? ('OUT-' . $trx->id),
                'date'       => $trx->sold_at?->format('d M Y') ?? '-',
                'sort_date'  => ($trx->sold_at?->format('Y-m-d') ?? '1970-01-01') . ' ' . ($trx->created_at?->format('H:i:s') ?? '00:00:00'),
                'party'      => $trx->customer_name ?? 'Pelanggan Grosir',
                'box_qty'    => (int) ($trx->box_qty ?? 0),
                'total_pcs'  => (int) ($trx->total_pcs ?? $trx->qty_pcs ?? 0),
                'unit_price' => (int) ($trx->price_per_box ?? $trx->sell_price ?? 0),
                'subtotal'   => (int) ($trx->subtotal ?? $trx->total_price ?? 0),
            ]);

        $history = $incoming->concat($outgoing)
            ->sortByDesc('sort_date')
            ->values()
            ->take(30);

        $ratio = max(1, (int) $product->box_pcs_ratio);

        return response()->json([
            'product' => [
                'id'            => $product->id,
                'sku'           => $product->sku,
                'name'          => $product->name,
                'ratio'         => $ratio,
                'stock_pcs'     => (int) $product->stock_pcs,
                'stock_box'     => intdiv((int) $product->stock_pcs, $ratio),
                'rem_pcs'       => (int) $product->stock_pcs % $ratio,
                'min_stock_box' => (int) ($product->min_stock_box ?? 10),
            ],
            'mutations' => $history,
        ]);
    }

    public function export(Request $request)
    {
        $subqueries = [
            'last_price_box' => IncomingTransaction::select('price_per_box')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->limit(1),
        ];

        $status = strtoupper($request->input('status', 'SEMUA'));
        if (! in_array($status, ['AMAN', 'MENIPIS', 'HABIS'], true)) {
            $status = 'SEMUA';
        }
        $cat  = $request->input('category', 'all');
        $sort = $request->input('sort', 'stok_asc');

        $products = $this->buildFilteredQuery($request, $status, $cat, $sort, $subqueries)->get();
        $filename = 'laporan-stok-gudang-' . now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'SKU',
                'Nama Barang',
                'Kategori',
                'Rasio (Pcs/Box)',
                'Batas Alert (Box)',
                'Sisa Stok (Box Utuh)',
                'Sisa Eceran (Pcs)',
                'Total Stok (Pcs)',
                'HPP Terakhir / Box (Rp)',
                'Estimasi Nilai Aset (Rp)',
                'Status Stok',
            ], ';');

            foreach ($products as $p) {
                $ratio     = max(1, (int) $p->box_pcs_ratio);
                $threshold = max(1, (int) ($p->min_stock_box ?? 10));
                $boxUtuh   = intdiv((int) $p->stock_pcs, $ratio);
                $sisaPcs   = (int) $p->stock_pcs % $ratio;
                $boxFloat  = $p->stock_pcs / $ratio;
                $hppBox    = (float) ($p->last_price_box ?? 0);
                $aset      = (int) round($boxFloat * $hppBox);

                $stLabel = $p->stock_pcs <= 0 ? 'HABIS' : ($boxFloat <= $threshold ? 'MENIPIS' : 'AMAN');

                fputcsv($handle, [
                    $p->sku,
                    $p->name,
                    $p->category?->name ?? '-',
                    $ratio,
                    $threshold,
                    $boxUtuh,
                    $sisaPcs,
                    (int) $p->stock_pcs,
                    (int) $hppBox,
                    $aset,
                    $stLabel,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFilteredQuery(Request $request, string $status, string $cat, string $sort, array $subqueries)
    {
        $lastPriceSub = '(SELECT price_per_box FROM incoming_transactions WHERE product_id = products.id ORDER BY received_at DESC, id DESC LIMIT 1)';

        $query = Product::active()
            ->with('category')
            ->addSelect($subqueries);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($cat !== 'all') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $cat));
        }

        if ($status === 'HABIS') {
            $query->where('stock_pcs', '<=', 0);
        } elseif ($status === 'MENIPIS') {
            $query->whereRaw('stock_pcs > 0 AND stock_pcs <= COALESCE(min_stock_box, 10) * GREATEST(box_pcs_ratio, 1)');
        } elseif ($status === 'AMAN') {
            $query->whereRaw('stock_pcs > COALESCE(min_stock_box, 10) * GREATEST(box_pcs_ratio, 1)');
        }

        match ($sort) {
            'stok_desc'  => $query->orderByDesc('stock_pcs'),
            'nilai_desc' => $query->orderByRaw("((stock_pcs / GREATEST(box_pcs_ratio, 1)) * COALESCE({$lastPriceSub}, 0)) DESC"),
            'mutasi'     => $query->orderByRaw('GREATEST(COALESCE((SELECT MAX(received_at) FROM incoming_transactions WHERE product_id = products.id), "1970-01-01"), COALESCE((SELECT MAX(sold_at) FROM outgoing_transactions WHERE product_id = products.id), "1970-01-01")) DESC'),
            'nama_asc'   => $query->orderBy('name'),
            default      => $query->orderBy('stock_pcs')->orderBy('name'),
        };

        return $query;
    }
}