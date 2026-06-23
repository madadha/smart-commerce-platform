<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Company;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Language;
use App\Models\MediaFile;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProviderSetting;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductMedia;
use App\Models\ProductOption;
use App\Models\ProductQuestion;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\StorefrontSetting;
use App\Models\StorefrontSlide;
use App\Models\User;
use App\Observers\AuditModelObserver;
use App\Observers\OrderInventoryObserver;
use App\Observers\PaymentInventoryObserver;
use App\Observers\ShipmentNotificationObserver;
use App\Policies\AuditLogPolicy;
use App\Services\Audit\AuditLogger;
use App\Support\Localization\ActiveLanguageRegistry;
use App\Policies\CatalogResourcePolicy;
use App\Policies\CustomerResourcePolicy;
use App\Policies\DigitalCodeResourcePolicy;
use App\Policies\OrderResourcePolicy;
use App\Policies\PaymentProviderSettingPolicy;
use App\Policies\PaymentResourcePolicy;
use App\Policies\SettingsResourcePolicy;
use App\Policies\ShippingResourcePolicy;
use App\Policies\SupportResourcePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(ActiveLanguageRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TextInput::configureUsing(function (TextInput $component): void {
            $component->visible(fn (TextInput $component): bool => app(ActiveLanguageRegistry::class)
                ->shouldDisplayStatePath($component->getStatePath()));
        });

        Textarea::configureUsing(function (Textarea $component): void {
            $component->visible(fn (Textarea $component): bool => app(ActiveLanguageRegistry::class)
                ->shouldDisplayStatePath($component->getStatePath()));
        });

        Gate::before(fn (User $user): ?bool => $user->hasAnyRole(['super-admin', 'admin']) ? true : null);

        foreach ([Product::class, ProductMedia::class, ProductOption::class, ProductVariant::class, Category::class, Brand::class, MediaFile::class] as $model) {
            Gate::policy($model, CatalogResourcePolicy::class);
        }
        foreach ([Order::class, Cart::class, Invoice::class] as $model) {
            Gate::policy($model, OrderResourcePolicy::class);
        }
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Customer::class, CustomerResourcePolicy::class);
        foreach ([Shipment::class, ShippingMethod::class] as $model) {
            Gate::policy($model, ShippingResourcePolicy::class);
        }
        Gate::policy(Payment::class, PaymentResourcePolicy::class);
        Gate::policy(PaymentProviderSetting::class, PaymentProviderSettingPolicy::class);
        Gate::policy(ProductDigitalCode::class, DigitalCodeResourcePolicy::class);
        foreach ([Setting::class, StorefrontSetting::class, StorefrontSlide::class, Country::class, Currency::class, Language::class, Company::class, Coupon::class] as $model) {
            Gate::policy($model, SettingsResourcePolicy::class);
        }
        foreach ([ProductQuestion::class, ProductReview::class] as $model) {
            Gate::policy($model, SupportResourcePolicy::class);
        }
        Gate::policy(User::class, UserPolicy::class);

        Order::observe(OrderInventoryObserver::class);
        Payment::observe(PaymentInventoryObserver::class);
        Shipment::observe(ShipmentNotificationObserver::class);

        foreach ([
            User::class,
            Order::class,
            Payment::class,
            PaymentProviderSetting::class,
            Product::class,
            ProductMedia::class,
            ProductOption::class,
            ProductVariant::class,
            ProductDigitalCode::class,
            Setting::class,
            Shipment::class,
            ShippingMethod::class,
            StorefrontSetting::class,
            StorefrontSlide::class,
            Country::class,
            Currency::class,
            Language::class,
            Company::class,
            Coupon::class,
            Category::class,
            Brand::class,
            MediaFile::class,
            Customer::class,
        ] as $model) {
            $model::observe(AuditModelObserver::class);
        }
    }
}
