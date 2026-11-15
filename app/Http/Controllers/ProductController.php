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
     * Peta standarisasi prefix SKU berdasarkan slug kategori (selaras dengan Seeder).
     */
    private function resolveSkuPrefix(?string $categorySlug): string
    {
        return match (strtolower((string) $categorySlug)) {
            'sembako'          => 'SMB',
            'makanan'          => 'MKN',
            'snack'            => 'SNK',
            'minuman', 'kopi'  => 'BVR',
            'rumah'            => 'HSE',
            default            => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', (string) $categorySlug) ?: 'GEN', 0, 3)),
        };
    }

    /**
     * Menghasilkan nomor SKU berikutnya yang dijamin unik.
     */
    private function buildNextSku(?string $categorySlug): string
    {
        $prefix = $this->resolveSkuPrefix($categorySlug);

        // Ambil nomor urut terbesar dari SKU dengan prefix yang sama
        $existingSkus = Product::where('sku', 'like', "SKU-{$prefix}-%")->pluck('sku');
        $maxNumber = 0;

        foreach ($existingSkus as $sku) {
            if (preg_match('/^SKU-'.preg_quote($prefix, '/').'-(\d+)$/', $sku, $matches)) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        return sprintf('SKU-%s-%02d', $prefix, $maxNumber + 1);
    }

    private function catalogQuery(Request $request): Builder
    {
        $query = Product::with('category')
            ->withExists('incomingTransactions as has_modal');

        $status = $request->input('status', 'active');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'archived') {
            $query->where('status', 'archived');
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $category = $request->input('category', 'all');
        if ($category !== 'all') {
            $query->whereHas('category', fn (Builder $query): Builder => $query->where('slug', $category));
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
        // Ambil waktu pembaruan terakhir dari database (bukan now())
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
                ? $lastSyncDate->format('H:i').' WIB'
                : '--:-- WIB',
        ];

        $status = $request->input('status', 'active');

        // Category counts match the selected status filter.
        $categories = Category::withCount([
            'products' => function (Builder $query) use ($status): void {
                if ($status === 'active') {
                    $query->active();
                } elseif ($status === 'archived') {
                    $query->where('status', 'archived');
                }
            },
        ])->get();
        $catalogTotal = Product::query()
            ->when($status === 'active', fn (Builder $query): Builder => $query->active())
            ->when($status === 'archived', fn (Builder $query): Builder => $query->where('status', 'archived'))
            ->count();

        // Default SKU awal untuk modal tambah berdasarkan kategori pertama
        $firstCategory = $categories->first();
        $nextSku = $this->buildNextSku($firstCategory?->slug);

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
        // Normalisasi SKU ke huruf besar & barcode kosong menjadi null agar unique aman
        $request->merge([
            'sku' => strtoupper(trim((string) $request->input('sku'))),
            'barcode' => trim((string) $request->input('barcode')) ?: null,
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'variant' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'box_pcs_ratio' => 'required|integer|min:1',
        ], [
            'sku.unique' => 'Kode SKU sudah terdaftar pada produk lain.',
            'barcode.unique' => 'Nomor Barcode ini sudah digunakan oleh produk lain.',
        ]);

        $data['status'] = 'active';
        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', "Master Barang \"{$data['name']}\" berhasil ditambahkan!");
    }

    public function generateSku(Request $request)
    {
        $sku = $this->buildNextSku($request->input('category_slug'));

        return response()->json([
            'sku' => $sku,
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
            ->map(fn (IncomingTransaction $transaction): array => [
                'created_at' => $transaction->created_at,
                'type' => 'Barang Masuk',
                'reference' => $transaction->no_faktur,
                'date' => $transaction->received_at->format('d/m/Y'),
                'box_qty' => $transaction->box_qty,
                'total_pcs' => $transaction->total_pcs,
            ])
            ->concat($outgoingTransactions->map(fn (OutgoingTransaction $transaction): array => [
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
            'barcode' => $product->barcode,
            'name' => $product->name,
            'variant' => $product->variant,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name ?? '-',
            'box_pcs_ratio' => $product->box_pcs_ratio,
            'status' => $product->status,
            'created_at' => $product->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB',
            'updated_at' => $product->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB',
            'has_transactions' => $incomingTransactions->isNotEmpty()
                || $outgoingTransactions->isNotEmpty(),
            'recent_transactions' => $history,
            'stock_preview' => "{$stockBoxes} Box, {$stockRemPcs} Pcs",
            'last_modal_box' => $lastIncoming
                ? 'Rp '.number_format((float) $lastIncoming->price_per_box, 0, ',', '.')
                : 'Belum ada Barang Masuk',
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->merge([
            'sku' => strtoupper(trim((string) $request->input('sku'))),
            'barcode' => trim((string) $request->input('barcode')) ?: null,
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'variant' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|max:50|unique:products,sku,'.$product->id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,'.$product->id,
            'box_pcs_ratio' => 'required|integer|min:1',
        ], [
            'sku.unique' => 'Kode SKU sudah terdaftar pada produk lain.',
            'barcode.unique' => 'Nomor Barcode ini sudah digunakan oleh produk lain.',
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
            ->route('products.index', $request->only(['search', 'category', 'box_size', 'sort', 'status']))
            ->with('success', "Master Barang \"{$product->name}\" berhasil diperbarui!");
    }

    public function archive(Product $product)
    {
        $product->update(['status' => 'archived']);

        return back()->with('success', "Produk \"{$product->name}\" telah diarsipkan.");
    }

    public function restore(Product $product)
    {
        $product->update(['status' => 'active']);

        return back()->with('success', "Produk \"{$product->name}\" berhasil dipulihkan ke katalog aktif.");
    }

    public function export(Request $request)
    {
        $products = $this->catalogQuery($request)->get();
        $filename = 'master-barang-'.now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM agar Excel otomatis membaca kolom & karakter dengan benar
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Kode SKU', 'Barcode', 'Nama Produk', 'Varian', 'Kategori', 'Isi Per Box (Pcs)', 'Status']);

            foreach ($products as $p) {
                fputcsv($handle, [
                    $p->sku,
                    $p->barcode ?? '-',
                    $p->name,
                    $p->variant ?? '-',
                    $p->category?->name ?? '-',
                    $p->box_pcs_ratio,
                    $p->status === 'archived' ? 'Diarsipkan' : 'Aktif Baku',
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
        // Pastikan slug tetap unik
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-'.rand(10, 99);
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

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
            'action' => 'required|in:archive,restore',
        ]);

        $newStatus = $data['action'] === 'archive' ? 'archived' : 'active';
        $count = Product::whereIn('id', $data['ids'])->update(['status' => $newStatus]);

        $label = $newStatus === 'archived' ? 'diarsipkan' : 'dipulihkan ke katalog aktif';

        return back()->with('success', "{$count} produk berhasil {$label}.");
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
                ? array_map(static fn ($column): string => strtolower(trim((string) $column)), $header)
                : [];

            if ($normalizedHeader !== []) {
                $normalizedHeader[0] = ltrim($normalizedHeader[0], "\xEF\xBB\xBF");
            }

            if ($normalizedHeader !== ['sku', 'barcode', 'nama produk', 'varian', 'kategori', 'isi per box']) {
                throw ValidationException::withMessages([
                    'file' => 'Header CSV harus berurutan: SKU, Barcode, Nama Produk, Varian, Kategori, Isi Per Box.',
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

                    if (count($row) !== 6) {
                        throw ValidationException::withMessages([
                            'file' => "Baris {$rowNumber} harus memiliki tepat 6 kolom.",
                        ]);
                    }

                    $sku = strtoupper(trim((string) $row[0]));
                    $barcode = trim((string) $row[1]) ?: null;
                    $name = trim((string) $row[2]);
                    $variant = trim((string) $row[3]) ?: null;
                    $categoryName = trim((string) $row[4]);
                    $categorySlug = Str::slug($categoryName);
                    $ratioValue = trim((string) $row[5]);

                    if (
                        $sku === ''
                        || $name === ''
                        || $categoryName === ''
                        || $categorySlug === ''
                        || ! ctype_digit($ratioValue)
                        || (int) $ratioValue < 1
                    ) {
                        throw ValidationException::withMessages([
                            'file' => "Baris {$rowNumber}: SKU, nama produk, kategori, dan isi per box wajib diisi dengan benar.",
                        ]);
                    }

                    if (
                        Product::where('sku', $sku)->exists()
                        || ($barcode !== null && Product::where('barcode', $barcode)->exists())
                    ) {
                        $skipped++;

                        continue;
                    }

                    $category = Category::firstOrCreate(
                        ['slug' => $categorySlug],
                        ['name' => $categoryName],
                    );

                    Product::create([
                        'sku' => $sku,
                        'barcode' => $barcode,
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
                .($result['skipped'] ? " ({$result['skipped']} duplikat dilewati)." : '.'),
        );
    }
}
