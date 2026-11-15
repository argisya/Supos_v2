<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('products.index'));

Route::get('/products',            [ProductController::class, 'index'])->name('products.index');
Route::post('/products',           [ProductController::class, 'store'])->name('products.store');
Route::get('/products/generate-sku', [ProductController::class, 'generateSku'])->name('products.generate-sku');