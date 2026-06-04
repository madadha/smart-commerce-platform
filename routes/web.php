<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\InvoicePdfController;
use App\Http\Controllers\Storefront\StorefrontController;
use App\Http\Controllers\Storefront\StorefrontCartController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::middleware(['auth'])->get('/admin/invoices/{invoice}/pdf', [InvoicePdfController::class, 'show'])
    ->name('admin.invoices.pdf');
    Route::get('/', [StorefrontController::class, 'home'])->name('storefront.home');

Route::get('/', [StorefrontController::class, 'home'])->name('storefront.home');

Route::prefix('store')->name('storefront.')->group(function () {
    Route::get('/', [StorefrontController::class, 'home'])->name('index');
    Route::get('/products', [StorefrontController::class, 'products'])->name('products.index');
    Route::get('/products/{slug}', [StorefrontController::class, 'productShow'])->name('products.show');

    Route::post('/cart/add', [StorefrontCartController::class, 'add'])->name('cart.add');
});
});

require __DIR__.'/auth.php';
