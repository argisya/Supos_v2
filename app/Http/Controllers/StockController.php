<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /** Ambang batas stok menipis (dalam Box) */
    private const LOW_STOCK_THRESHOLD = 10;

    public function index(Request $request)
    {
        // ========== SUBQUERY: HPP & mutasi terakhir per produk ==========
        $subqueries = [
            'last_hpp' => IncomingTransaction::select('price_per_box')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->limit(1),
            'last_incoming_at' => IncomingTransaction::select('created_at')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('created_at')
                ->limit(1),
            'last_incoming_pcs' => IncomingTransaction::select('total_pcs')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('created_at')
                ->limit(1),
            'last_outgoing_at' => OutgoingTransaction::select('created_at')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('created_at')
                ->limit(1),
            'last_outgoing_pcs' => OutgoingTransaction::select('total_pcs')
                ->whereColumn('product_id', 'products.id')
                ->orderByDesc('created_at')
                ->limit(1),
        ];

        // ========== AGREGAT (untuk KPI, hitung dari seluruh produk aktif) ==========
        $allProducts = Product::active()->addSelect($subqueries)->get();

        $totalAssetValue = 0.0;
        $totalVolumePcs  = 0;
        $aman = $menipis = $habis = 0;

        foreach ($allProducts as $p) {
            $ratio    = max(1, $p->box_pcs_ratio);
            $stockBox = $p->stock_pcs / $ratio;
            $hpp      = (float) ($p->last_hpp ?? 0);

            $totalAssetValue += $stockBox * $hpp;
            $totalVolumePcs  += $p->stock_pcs;

            if ($p->stock_pcs === 0) {
                $habis++;
            } elseif ($stockBox <= self::LOW_STOCK_THRESHOLD) {
                $menipis++;
            } else {
                $aman++;
            }
        }

        $totalProducts  = $allProducts->count();
        $totalVolumeBox = $allProducts->sum(fn ($p) => $p->stock_pcs / max(1, $p->box_pcs_ratio));

        // ========== MUTASI HARI INI ==========
        $today = today();
        $todayInBox  = (int) IncomingTransaction::whereDate('received_at', $today)->sum('box_qty');
        $todayOutBox = (int) OutgoingTransaction::whereDate('sold_at', $today)->sum('box_qty');

        // ========== QUERY TABEL dengan filter ==========
        $query = Product::active()->with('category')->addSelect($subqueries);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $cat = $request->input('category', 'all');
        if ($cat !== 'all') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $cat));
        } else {
            $cat = 'all';
        }

        $status = strtoupper($request->input('status', 'SEMUA'));
        $threshold = self::LOW_STOCK_THRESHOLD;
        match ($status) {
            'AMAN'    => $query->whereRaw("stock_pcs > {$threshold} * box_pcs_ratio"),
            'MENIPIS' => $query->whereRaw("stock_pcs > 0 AND stock_pcs <= {$threshold} * box_pcs_ratio"),
            'HABIS'   => $query->where('stock_pcs', 0),
            default   => $status = 'SEMUA',
        };

        $sort = $request->input('sort', 'stok_asc');
        match ($sort) {
            'stok_asc'   => $query->orderBy('stock_pcs'),
            'stok_desc'  => $query->orderByDesc('stock_pcs'),
            'nilai_desc' => $query->orderByRaw(
                'stock_pcs * COALESCE((SELECT price_per_box FROM incoming_transactions WHERE product_id = products.id ORDER BY received_at DESC, id DESC LIMIT 1), 0) DESC'
            ),
            'nama_asc' => $query->orderBy('name'),
            'mutasi' => $query->orderByRaw(
                'GREATEST(
                    COALESCE((SELECT MAX(created_at) FROM incoming_transactions WHERE product_id = products.id), "1970-01-01"),
                    COALESCE((SELECT MAX(created_at) FROM outgoing_transactions WHERE product_id = products.id), "1970-01-01")
                ) DESC'
            ),
            default => $query->orderBy('stock_pcs'),
        };

        $products = $query->paginate(10)->withQueryString();

        // ========== KATEGORI untuk filter dropdown ==========
        $categories = Category::withCount(['products' => fn ($q) => $q->active()])->get();

        return view('stock.index', compact(
            'products', 'categories',
            'totalProducts', 'totalAssetValue', 'totalVolumePcs', 'totalVolumeBox',
            'aman', 'menipis', 'habis',
            'todayInBox', 'todayOutBox',
            'cat', 'status', 'sort'
        ));
    }
}