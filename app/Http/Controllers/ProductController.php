<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Peta standarisasi prefix SKU berdasarkan slug kategori.
     */
    private function resolveSkuPrefix(?string $categorySlug): string
    {
        return match (strtolower((string) $categorySlug)) {
            'sembako' => 'SMB',
            'makanan' => 'MKN',
            'snack' => 'SNK',
            'minuman', 'kopi' => 'BVR',
            'rumah' => 'HSE',
            default => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', (string) $categorySlug) ?: 'GEN', 0, 3)),
        };
    }

    /**
     * Menghasilkan kode SKU baku berikutnya dengan format SKU-<KATEGORI>-<NO>.
     */
    private function buildNextSku(?string $categorySlug): string
    {
        $prefix = $this->resolveSkuPrefix($categorySlug);

        $existingSkus = Product::where('sku', 'like', "SKU-{$prefix}-%")->pluck('sku');
        $maxNumber = 0;

        foreach ($existingSkus as $sku) {
            if (preg_match('/^SKU-' . preg_quote($prefix, '/') . '-(\d+)$/', $sku, $matches)) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        return sprintf('SKU-%s-%02d', $prefix, $maxNumber + 1);
    }

    private function catalogQuery(Request $request): Builder
    {
        $query = Product::with('category')
            ->withExists('incomingTransactions as has_modal')
            ->withExists('outgoingTransactions as has_outgoing')
            ->active();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('variant', 'like', "%{$search}%");
            });
        }

        $category = $request->input('category', 'all');
        if ($category !== 'all') {
            $query->whereHas('category', fn(Builder $q): Builder => $q->where('slug', $category));
        }

        $query->byBoxSize($request->input('box_size', 'all'));

        match ($request->input('sort', 'latest')) {
            'name' => $query->orderBy('name'),
            'ratio_desc' => $query->orderByDesc('box_pcs_ratio'),
            'category' => $query->orderBy(
                Category::select('name')->whereColumn('categories.id', 'products.category_id')
            ),
            default => $query->latest(),
        };

        return $query;
    }

    public function index(Request $request)
    {
        $latestUpdate = Product::max('updated_at');
        $lastSyncDate = $latestUpdate ? Carbon::parse($latestUpdate)->timezone('Asia/Jakarta') : null;

        $stats = [
            'total_sku' => Product::active()->count(),
            'avg_ratio' => round(Product::active()->avg('box_pcs_ratio') ?? 0),
            'total_categories' => Category::count(),
            'last_sync_label' => $lastSyncDate
                ? ($lastSyncDate->isToday() ? 'Hari ini' : $lastSyncDate->translatedFormat('d M Y'))
                : 'Belum ada data',
            'last_sync' => $lastSyncDate
                ? $lastSyncDate->format('H:i') . ' WIB'
                : '--:-- WIB',
        ];

        $categories = Category::withCount([
            'products' => fn(Builder $q) => $q->active(),
        ])->get();

        $catalogTotal = Product::active()->count();

        $selectedOldCategory = old('category_id')
            ? $categories->firstWhere('id', (int) old('category_id'))
            : $categories->first();
        $nextSku = $this->buildNextSku($selectedOldCategory?->slug);

        $catSlug = $request->input('category', 'all');
        $boxSize = $request->input('box_size', 'all');
        $products = $this->catalogQuery($request)->paginate(8)->withQueryString();

        return view('products.index', compact(
            'products',
            'categories',
            'stats',
            'catSlug',
            'boxSize',
            'catalogTotal',
            'nextSku'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'variant' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'box_pcs_ratio' => 'required|integer|min:1',
        ]);

        $product = DB::transaction(function () use ($data): Product {
            $category = Category::findOrFail($data['category_id']);
            // Kunci pembacaan nomor urut terakhir agar auto-increment SKU tetap aman
            Product::where('category_id', $category->id)->lockForUpdate()->get();

            $data['sku'] = $this->buildNextSku($category->slug);
            $data['status'] = 'active';

            return Product::create($data);
        });

        return redirect()
            ->route('products.index')
            ->with('success', "Master Barang \"{$product->name}\" ({$product->sku}) berhasil ditambahkan!");
    }

    public function generateSku(Request $request)
    {
        return response()->json([
            'sku' => $this->buildNextSku($request->input('category_slug')),
        ]);
    }

    public function show(Product $product)
    {
        $product->load('category');

        $incomingTransactions = $product->incomingTransactions()
            ->latest('received_at')
            ->latest('id')
            ->limit(5)
            ->get();

        $outgoingTransactions = $product->outgoingTransactions()
            ->latest('sold_at')
            ->latest('id')
            ->limit(5)
            ->get();

        $lastIncoming = $product->incomingTransactions()
            ->latest('received_at')
            ->latest('id')
            ->first();

        $history = $incomingTransactions
            ->map(fn(IncomingTransaction $transaction): array => [
                'created_at' => $transaction->created_at,
                'type' => 'Barang Masuk',
                'reference' => $transaction->no_faktur,
                'date' => $transaction->received_at->format('d/m/Y'),
                'box_qty' => $transaction->box_qty,
                'total_pcs' => $transaction->total_pcs,
            ])
            ->concat($outgoingTransactions->map(fn(OutgoingTransaction $transaction): array => [
                'created_at' => $transaction->created_at,
                'type' => 'Barang Keluar',
                'reference' => $transaction->no_invoice,
                'date' => $transaction->sold_at->format('d/m/Y'),
                'box_qty' => $transaction->box_qty,
                'total_pcs' => $transaction->total_pcs,
            ]))
            ->sortByDesc('created_at')
            ->take(10)
            ->values()
            ->map(function (array $transaction): array {
                unset($transaction['created_at']);

                return $transaction;
            });

        $ratio = max(1, (int) $product->box_pcs_ratio);
        $stockPcs = max(0, (int) $product->stock_pcs);
        $stockBoxes = intdiv($stockPcs, $ratio);
        $stockRemPcs = $stockPcs % $ratio;

        return response()->json([
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'variant' => $product->variant,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name ?? '-',
            'box_pcs_ratio' => $product->box_pcs_ratio,
            'created_at' => $product->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
            'updated_at' => $product->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
            'has_transactions' => $incomingTransactions->isNotEmpty() || $outgoingTransactions->isNotEmpty(),
            'recent_transactions' => $history,
            'stock_preview' => "{$stockBoxes} Box, {$stockRemPcs} Pcs",
            'last_modal_box' => $lastIncoming
                ? 'Rp ' . number_format((float) $lastIncoming->price_per_box, 0, ',', '.')
                : 'Belum ada Barang Masuk',
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'variant' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'box_pcs_ratio' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($data, $product): void {
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $ratioChanged = (int) $data['box_pcs_ratio'] !== (int) $lockedProduct->box_pcs_ratio;
            if (
                $ratioChanged
                && (
                    $lockedProduct->incomingTransactions()->exists()
                    || $lockedProduct->outgoingTransactions()->exists()
                )
            ) {
                throw ValidationException::withMessages([
                    'box_pcs_ratio' => 'Rasio tidak dapat diubah setelah produk memiliki transaksi. Buat SKU baru untuk jenis kemasan yang berbeda.',
                ]);
            }

            $lockedProduct->update($data);
        });

        return redirect()
            ->route('products.index', $request->only(['search', 'category', 'box_size', 'sort']))
            ->with('success', "Master Barang \"{$product->name}\" berhasil diperbarui!");
    }

    public function destroy(Product $product)
    {
        if ($product->incomingTransactions()->exists() || $product->outgoingTransactions()->exists()) {
            return back()->with('error', "Produk \"{$product->name}\" ({$product->sku}) tidak dapat dihapus karena sudah tercatat dalam riwayat transaksi.");
        }

        $name = $product->name;
        $sku = $product->sku;
        $product->delete();

        return back()->with('success', "Master Barang \"{$name}\" ({$sku}) berhasil dihapus.");
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
        ]);

        $products = Product::whereIn('id', $data['ids'])
            ->withExists(['incomingTransactions', 'outgoingTransactions'])
            ->get();

        $deleted = 0;
        $blocked = 0;

        foreach ($products as $item) {
            if ($item->incoming_transactions_exists || $item->outgoing_transactions_exists) {
                $blocked++;
                continue;
            }
            $item->delete();
            $deleted++;
        }

        if ($deleted === 0 && $blocked > 0) {
            return back()->with('error', "{$blocked} produk terpilih tidak dapat dihapus karena sudah memiliki riwayat transaksi.");
        }

        $message = "{$deleted} produk berhasil dihapus.";
        if ($blocked > 0) {
            $message .= " ({$blocked} produk dilewati karena sudah memiliki riwayat transaksi).";
        }

        return back()->with('success', $message);
    }

    public function export(Request $request)
    {
        $products = $this->catalogQuery($request)->get();
        $filename = 'master-barang-' . now('Asia/Jakarta')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Kode SKU', 'Nama Produk', 'Varian', 'Kategori', 'Isi Per Box (Pcs)']);

            foreach ($products as $p) {
                fputcsv($handle, [
                    $p->sku,
                    $p->name,
                    $p->variant ?? '-',
                    $p->category?->name ?? '-',
                    $p->box_pcs_ratio,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ], [
            'name.unique' => 'Nama kategori tersebut sudah tersedia.',
        ]);

        $slug = Str::slug($data['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(10, 99);
        }

        Category::create([
            'name' => trim($data['name']),
            'slug' => $slug,
        ]);

        return back()->with('success', "Kategori \"{$data['name']}\" berhasil ditambahkan!");
    }

    public function destroyCategory(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', "Kategori \"{$category->name}\" tidak bisa dihapus karena masih digunakan oleh produk.");
        }

        $name = $category->name;
        $category->delete();

        return back()->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'extensions:csv,txt', 'max:2048'],
        ], [
            'file.required' => 'Pilih file CSV yang akan diimpor.',
            'file.mimes' => 'File harus berisi data CSV.',
            'file.extensions' => 'Ekstensi file harus .csv atau .txt.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'File CSV tidak dapat dibaca.',
            ]);
        }

        try {
            $header = fgetcsv($handle, null, ',', '"', '');
            $normalizedHeader = is_array($header)
                ? array_map(static fn($column): string => strtolower(trim((string) $column)), $header)
                : [];

            if ($normalizedHeader !== []) {
                $normalizedHeader[0] = ltrim($normalizedHeader[0], "\xEF\xBB\xBF");
            }

            if ($normalizedHeader !== ['sku', 'nama produk', 'varian', 'kategori', 'isi per box']) {
                throw ValidationException::withMessages([
                    'file' => 'Header CSV harus berurutan: SKU, Nama Produk, Varian, Kategori, Isi Per Box.',
                ]);
            }

            $result = DB::transaction(function () use ($handle): array {
                $imported = 0;
                $skipped = 0;
                $rowNumber = 1;

                while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                    $rowNumber++;

                    if (count($row) === 1 && trim((string) $row[0]) === '') {
                        continue;
                    }

                    if (count($row) !== 5) {
                        throw ValidationException::withMessages([
                            'file' => "Baris {$rowNumber} harus memiliki tepat 5 kolom.",
                        ]);
                    }

                    $rawSku = strtoupper(trim((string) $row[0]));
                    $name = trim((string) $row[1]);
                    $variant = trim((string) $row[2]) ?: null;
                    $categoryName = trim((string) $row[3]);
                    $categorySlug = Str::slug($categoryName);
                    $ratioValue = trim((string) $row[4]);

                    if (
                        $name === ''
                        || $categoryName === ''
                        || $categorySlug === ''
                        || !ctype_digit($ratioValue)
                        || (int) $ratioValue < 1
                    ) {
                        throw ValidationException::withMessages([
                            'file' => "Baris {$rowNumber}: nama produk, kategori, dan isi per box wajib diisi dengan benar.",
                        ]);
                    }

                    if ($rawSku !== '' && Product::where('sku', $rawSku)->exists()) {
                        $skipped++;
                        continue;
                    }

                    $category = Category::firstOrCreate(
                        ['slug' => $categorySlug],
                        ['name' => $categoryName],
                    );

                    $sku = $rawSku !== '' ? $rawSku : $this->buildNextSku($category->slug);

                    Product::create([
                        'sku' => $sku,
                        'name' => $name,
                        'variant' => $variant,
                        'category_id' => $category->id,
                        'box_pcs_ratio' => (int) $ratioValue,
                        'status' => 'active',
                    ]);

                    $imported++;
                }

                if ($imported === 0 && $skipped === 0) {
                    throw ValidationException::withMessages([
                        'file' => 'CSV tidak berisi baris produk untuk diimpor.',
                    ]);
                }

                return compact('imported', 'skipped');
            });
        } finally {
            fclose($handle);
        }

        return back()->with(
            'success',
            "Import selesai: {$result['imported']} produk baru ditambahkan"
            . ($result['skipped'] ? " ({$result['skipped']} duplikat dilewati)." : '.'),
        );
    }
}