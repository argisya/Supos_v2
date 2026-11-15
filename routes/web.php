<?php

use App\Http\Controllers\IncomingGoodsController;
use App\Http\Controllers\OutgoingGoodsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('products.index'));

// Master Barang
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/generate-sku', [ProductController::class, 'generateSku'])->name('products.generate-sku');
Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('/products/bulk-status', [ProductController::class, 'bulkStatus'])->name('products.bulk-status');
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::patch('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::patch('/products/{product}/archive', [ProductController::class, 'archive'])->name('products.archive');
Route::patch('/products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');

// Manajemen Kategori Cepat
Route::post('/categories', [ProductController::class, 'storeCategory'])->name('categories.store');
Route::delete('/categories/{category}', [ProductController::class, 'destroyCategory'])->name('categories.destroy');

// Barang Masuk
Route::get('/incoming-goods', [IncomingGoodsController::class, 'index'])->name('incoming-goods.index');
Route::get('/incoming-goods/export', [IncomingGoodsController::class, 'export'])->name('incoming-goods.export');
Route::post('/incoming-goods', [IncomingGoodsController::class, 'store'])->name('incoming-goods.store');
Route::delete('/incoming-goods/{transaction}', [IncomingGoodsController::class, 'rollback'])->name('incoming-goods.rollback');
Route::patch('/incoming-goods/{transaction}/pay', [IncomingGoodsController::class, 'payNow'])->name('incoming-goods.pay');

// Barang Keluar
Route::get('/outgoing-goods', [OutgoingGoodsController::class, 'index'])->name('outgoing-goods.index');
Route::post('/outgoing-goods', [OutgoingGoodsController::class, 'store'])->name('outgoing-goods.store');
Route::delete('/outgoing-goods/{transaction}', [OutgoingGoodsController::class, 'rollback'])->name('outgoing-goods.rollback');

// Stok Barang
Route::prefix('stock')->name('stock.')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('index');
    Route::post('/alert-threshold', [StockController::class, 'updateAlertThreshold'])->name('alert-threshold');
    Route::post('/opname', [StockController::class, 'storeOpname'])->name('opname');
    Route::get('/export', [StockController::class, 'export'])->name('export');
    Route::get('/{product}/mutations', [StockController::class, 'mutations'])->name('mutations');
});

// Buku Tagihan
Route::get('/receivables', [ReceivableController::class, 'index'])->name('receivables.index');
Route::post('/receivables/{transaction}/pay', [ReceivableController::class, 'pay'])->name('receivables.pay');
