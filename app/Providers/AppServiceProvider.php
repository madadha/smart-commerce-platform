<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Observers\OrderInventoryObserver;
use App\Observers\PaymentInventoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderInventoryObserver::class);
        Payment::observe(PaymentInventoryObserver::class);
    }
}
