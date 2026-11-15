<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomingGoodsController;
use App\Http\Controllers\OutgoingGoodsController;

Route::get('/', fn () => redirect()->route('products.index'));

// Master Barang (existing)
Route::get('/products',              [ProductController::class, 'index'])->name('products.index');
Route::post('/products',             [ProductController::class, 'store'])->name('products.store');
Route::get('/products/generate-sku', [ProductController::class, 'generateSku'])->name('products.generate-sku');

// Barang Masuk
Route::get('/incoming-goods',                    [IncomingGoodsController::class, 'index'])->name('incoming-goods.index');
Route::post('/incoming-goods',                   [IncomingGoodsController::class, 'store'])->name('incoming-goods.store');
Route::delete('/incoming-goods/{transaction}',   [IncomingGoodsController::class, 'rollback'])->name('incoming-goods.rollback');
Route::patch('/incoming-goods/{transaction}/pay',[IncomingGoodsController::class, 'payNow'])->name('incoming-goods.pay');

// Barang Keluar
Route::get('/outgoing-goods',                  [OutgoingGoodsController::class, 'index'])->name('outgoing-goods.index');
Route::post('/outgoing-goods',                 [OutgoingGoodsController::class, 'store'])->name('outgoing-goods.store');
Route::delete('/outgoing-goods/{transaction}', [OutgoingGoodsController::class, 'rollback'])->name('outgoing-goods.rollback');