<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Statistik
        $stats = [
            'total_sku'        => Product::active()->count(),
            'avg_ratio'        => round(Product::active()->avg('box_pcs_ratio') ?? 0),
            'total_categories' => Category::count(),
            'last_sync'        => now()->format('H:i') . ' WIB',
        ];

        // List kategori + jumlah produk aktif
        $categories = Category::withCount([
            'products' => fn ($q) => $q->active(),
        ])->get();

        // Query dasar
        $query = Product::with('category')->active();

        // Search (nama, SKU, barcode)
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        $catSlug = $request->input('category', 'all');
        if ($catSlug !== 'all') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $catSlug));
        }

        // Filter ukuran box
        $boxSize = $request->input('box_size', 'all');
        $query->byBoxSize($boxSize);

        // Sorting
        match ($request->input('sort', 'latest')) {
            'name'        => $query->orderBy('name'),
            'ratio_desc'  => $query->orderByDesc('box_pcs_ratio'),
            'category'    => $query->orderBy(Category::select('name')
                                    ->whereColumn('categories.id', 'products.category_id')),
            default       => $query->latest(),
        };

        $products = $query->paginate(8)->withQueryString();

        return view('products.index', compact(
            'products', 'categories', 'stats', 'catSlug', 'boxSize'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'variant'       => 'nullable|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'sku'           => 'required|string|max:50|unique:products,sku',
            'barcode'       => 'nullable|string|max:50',
            'box_pcs_ratio' => 'required|integer|min:1',
        ]);

        $data['status'] = 'active';
        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', "Master Barang \"{$data['name']}\" berhasil ditambahkan!");
    }

    public function generateSku(Request $request)
    {
        $slug = strtoupper(substr($request->input('category_slug', 'gen'), 0, 3));
        $count = Product::where('sku', 'like', "SKU-{$slug}-%")->count() + 1;

        return response()->json([
            'sku' => sprintf('SKU-%s-%02d', $slug, $count),
        ]);
    }
}