<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('storefront_settings')) {
            Schema::create('storefront_settings', function (Blueprint $table) {
                $table->id();
                $table->json('store_name')->nullable();
                $table->json('store_tagline')->nullable();
                $table->json('topbar_text')->nullable();
                $table->string('logo_path')->nullable();
                $table->string('favicon_path')->nullable();
                $table->json('footer_description')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->string('whatsapp')->nullable();
                $table->json('address')->nullable();
                $table->string('facebook_url')->nullable();
                $table->string('instagram_url')->nullable();
                $table->string('tiktok_url')->nullable();
                $table->string('youtube_url')->nullable();
                $table->string('primary_color')->default('#2563eb');
                $table->string('primary_hover_color')->default('#1d4ed8');
                $table->string('secondary_color')->default('#0ea5e9');
                $table->string('accent_color')->default('#d4a24c');
                $table->string('dark_color')->default('#0b1120');
                $table->string('background_color')->default('#f8fafc');
                $table->string('card_color')->default('#ffffff');
                $table->string('text_color')->default('#0f172a');
                $table->string('muted_text_color')->default('#64748b');
                $table->json('hero_badge')->nullable();
                $table->json('hero_title')->nullable();
                $table->json('hero_text')->nullable();
                $table->json('hero_primary_button_text')->nullable();
                $table->string('hero_primary_button_url')->nullable();
                $table->json('hero_secondary_button_text')->nullable();
                $table->string('hero_secondary_button_url')->nullable();
                $table->boolean('show_categories_section')->default(true);
                $table->boolean('show_featured_section')->default(true);
                $table->boolean('show_latest_section')->default(true);
                $table->boolean('show_brands_section')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('storefront_settings') && DB::table('storefront_settings')->count() === 0) {

            DB::table('storefront_settings')->insert([
                'store_name' => json_encode([
                    'ar' => 'Smart Commerce',
                    'he' => 'Smart Commerce',
                    'en' => 'Smart Commerce',
                ], JSON_UNESCAPED_UNICODE),
                'store_tagline' => json_encode([
                    'ar' => 'Marketplace Platform',
                    'he' => 'Marketplace Platform',
                    'en' => 'Marketplace Platform',
                ], JSON_UNESCAPED_UNICODE),
                'topbar_text' => json_encode([
                    'ar' => 'Smart Commerce Platform — تجربة تجارة ديناميكية متعددة اللغات',
                    'he' => 'Smart Commerce Platform — חוויית מסחר דינמית ורב־לשונית',
                    'en' => 'Smart Commerce Platform — Dynamic multilingual commerce experience',
                ], JSON_UNESCAPED_UNICODE),
                'footer_description' => json_encode([
                    'ar' => 'منصة تجارة ديناميكية متعددة اللغات للمنتجات، الطلبات، السلة، الدفع والفواتير.',
                    'he' => 'פלטפורמת מסחר דינמית ורב־לשונית למוצרים, הזמנות, עגלה, תשלום וחשבוניות.',
                    'en' => 'Dynamic multilingual commerce platform for products, orders, cart, checkout and invoices.',
                ], JSON_UNESCAPED_UNICODE),
                'hero_badge' => json_encode([
                    'ar' => 'منصة تجارة ذكية',
                    'he' => 'פלטפורמת מסחר חכמה',
                    'en' => 'Smart Commerce Platform',
                ], JSON_UNESCAPED_UNICODE),
                'hero_title' => json_encode([
                    'ar' => 'تجربة شراء حديثة لكل أنواع المنتجات',
                    'he' => 'חוויית קנייה מודרנית לכל סוגי המוצרים',
                    'en' => 'Modern shopping experience for every product type',
                ], JSON_UNESCAPED_UNICODE),
                'hero_text' => json_encode([
                    'ar' => 'منتجات، كودات رقمية، عروض، سلة، دفع وفواتير — كل شيء ديناميكي ومتعدد اللغات.',
                    'he' => 'מוצרים, קודים דיגיטליים, מבצעים, עגלה, תשלום וחשבוניות — הכל דינמי ורב־לשוני.',
                    'en' => 'Products, digital codes, deals, cart, checkout and invoices — fully dynamic and multilingual.',
                ], JSON_UNESCAPED_UNICODE),
                'hero_primary_button_text' => json_encode([
                    'ar' => 'تسوق الآن',
                    'he' => 'קנה עכשיו',
                    'en' => 'Shop Now',
                ], JSON_UNESCAPED_UNICODE),
                'hero_primary_button_url' => '/store/products',
                'hero_secondary_button_text' => json_encode([
                    'ar' => 'العروض',
                    'he' => 'מבצעים',
                    'en' => 'View Deals',
                ], JSON_UNESCAPED_UNICODE),
                'hero_secondary_button_url' => '/store/products?on_sale=1',
                'primary_color' => '#2563eb',
                'primary_hover_color' => '#1d4ed8',
                'secondary_color' => '#0ea5e9',
                'accent_color' => '#d4a24c',
                'dark_color' => '#0b1120',
                'background_color' => '#f8fafc',
                'card_color' => '#ffffff',
                'text_color' => '#0f172a',
                'muted_text_color' => '#64748b',
                'show_categories_section' => true,
                'show_featured_section' => true,
                'show_latest_section' => true,
                'show_brands_section' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_settings');
    }
};
