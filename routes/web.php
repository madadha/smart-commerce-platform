<?php

use App\Http\Controllers\Admin\InvoicePdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\StorefrontCartController;
use App\Http\Controllers\Storefront\StorefrontCheckoutController;
use App\Http\Controllers\Storefront\StorefrontController;
use App\Http\Controllers\Storefront\StorefrontOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Storefront Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [StorefrontController::class, 'home'])
    ->name('storefront.home');

Route::prefix('store')->name('storefront.')->group(function () {
    Route::get('/', [StorefrontController::class, 'home'])
        ->name('index');

    Route::get('/products', [StorefrontController::class, 'products'])
        ->name('products.index');

    Route::get('/products/{slug}', [StorefrontController::class, 'productShow'])
        ->name('products.show');

    Route::get('/cart', [StorefrontCartController::class, 'index'])
        ->name('cart.index');

    Route::post('/cart/add', [StorefrontCartController::class, 'add'])
        ->name('cart.add');

    Route::patch('/cart/items/{item}', [StorefrontCartController::class, 'updateItem'])
        ->name('cart.items.update');

    Route::delete('/cart/items/{item}', [StorefrontCartController::class, 'removeItem'])
        ->name('cart.items.remove');

    Route::get('/checkout', [StorefrontCheckoutController::class, 'index'])
        ->name('checkout.index');

    Route::post('/checkout/place-order', [StorefrontCheckoutController::class, 'placeOrder'])
        ->name('checkout.place');

    Route::get('/checkout/success/{order}', [StorefrontCheckoutController::class, 'success'])
        ->name('checkout.success');

    Route::get('/track-order', [StorefrontOrderController::class, 'trackingForm'])
        ->name('orders.track');

    Route::post('/track-order/result', [StorefrontOrderController::class, 'trackingResult'])
        ->name('orders.track.result');

    Route::get('/account/orders', [StorefrontOrderController::class, 'history'])
        ->middleware('auth')
        ->name('orders.history');

    Route::get('/orders/{order}', [StorefrontOrderController::class, 'show'])
        ->middleware('signed')
        ->name('orders.show');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy']);

    Route::get('/admin/invoices/{invoice}/pdf', [InvoicePdfController::class, 'show'])
        ->name('admin.invoices.pdf');
});

require __DIR__ . '/auth.php';