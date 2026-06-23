<?php

use App\Http\Controllers\Admin\InvoicePdfController;
use App\Http\Controllers\Payments\PayPlusPaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\StorefrontCartController;
use App\Http\Controllers\Storefront\StorefrontCheckoutController;
use App\Http\Controllers\Storefront\StorefrontCompareController;
use App\Http\Controllers\Storefront\StorefrontController;
use App\Http\Controllers\Storefront\StorefrontOrderController;
use App\Http\Controllers\Storefront\StorefrontProductQuestionController;
use App\Http\Controllers\Storefront\StorefrontProductReviewController;
use App\Http\Controllers\Storefront\StorefrontWishlistController;
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

    Route::post('/products/{product}/reviews', [StorefrontProductReviewController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('products.reviews.store');

    Route::post('/products/{product}/questions', [StorefrontProductQuestionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('products.questions.store');

    Route::get('/compare', [StorefrontCompareController::class, 'index'])
        ->name('compare.index');

    Route::post('/compare/{product}/add', [StorefrontCompareController::class, 'add'])
        ->name('compare.add');

    Route::delete('/compare/{product}/remove', [StorefrontCompareController::class, 'remove'])
        ->name('compare.remove');

    Route::delete('/compare/clear/all', [StorefrontCompareController::class, 'clear'])
        ->name('compare.clear');

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

    Route::get('/checkout/shipping-quotes', [StorefrontCheckoutController::class, 'shippingQuotes'])
        ->middleware('throttle:30,1')
        ->name('checkout.shipping-quotes');

    Route::post('/checkout/place-order', [StorefrontCheckoutController::class, 'placeOrder'])
        ->middleware('throttle:10,1')
        ->name('checkout.place');

    Route::get('/checkout/success/{order}', [StorefrontCheckoutController::class, 'success'])
        ->name('checkout.success');

    Route::get('/track-order', [StorefrontOrderController::class, 'trackingForm'])
        ->name('orders.track');

    Route::post('/track-order/result', [StorefrontOrderController::class, 'trackingResult'])
        ->middleware('throttle:20,1')
        ->name('orders.track.result');

    Route::get('/account', [StorefrontOrderController::class, 'dashboard'])
        ->middleware('auth')
        ->name('account.dashboard');

    Route::get('/account/orders', [StorefrontOrderController::class, 'history'])
        ->middleware('auth')
        ->name('orders.history');

    Route::get('/wishlist', [StorefrontWishlistController::class, 'index'])
        ->middleware('auth')
        ->name('wishlist.index');

    Route::post('/wishlist', [StorefrontWishlistController::class, 'store'])
        ->middleware('auth')
        ->name('wishlist.store');

    Route::post('/wishlist/{product}/toggle', [StorefrontWishlistController::class, 'toggle'])
        ->middleware('auth')
        ->name('wishlist.toggle');

    Route::delete('/wishlist/{product}', [StorefrontWishlistController::class, 'destroy'])
        ->middleware('auth')
        ->name('wishlist.destroy');

    Route::get('/orders/{order}', [StorefrontOrderController::class, 'show'])
        ->middleware('signed')
        ->name('orders.show');

    Route::get('/orders/{order}/invoice', [StorefrontOrderController::class, 'invoice'])
        ->middleware('signed')
        ->name('orders.invoice');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return redirect()->route('storefront.account.dashboard', [
        'lang' => request('lang', session('storefront_locale', 'ar')),
    ]);
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

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/admin/invoices/{invoice}/pdf', [InvoicePdfController::class, 'show'])
        ->name('admin.invoices.pdf');
});

require __DIR__.'/auth.php';

Route::get('/payments/payplus/return/{payment}/{status}', [PayPlusPaymentController::class, 'return'])
    ->middleware('signed')
    ->name('payments.payplus.return');

Route::post('/payments/webhooks/payplus', [PayPlusPaymentController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->name('payments.webhooks.payplus');
