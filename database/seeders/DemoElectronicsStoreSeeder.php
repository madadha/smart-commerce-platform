<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoElectronicsStoreSeeder extends Seeder
{
    public function run(): void
    {
        $currency = $this->currency();

        $brands = $this->brands();
        $categories = $this->categories();

        $products = $this->products();

        foreach ($products as $index => $item) {
            $brand = $brands[$item['brand']] ?? null;
            $category = $categories[$item['category']] ?? null;

            $imagePath = $this->createSvgImage(
                $item['slug'],
                $item['brand'],
                $item['name']['en'],
                $item['badge'],
                $item['gradient'][0],
                $item['gradient'][1]
            );

            $product = Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'sku' => $item['sku'],
                    'barcode' => $item['barcode'] ?? null,
                    'product_type' => 'physical',
                    'status' => 'active',
                    'brand_id' => $brand?->id,
                    'currency_id' => $currency?->id,
                    'main_image' => $imagePath,
                    'price' => $item['price'],
                    'sale_price' => $item['sale_price'],
                    'cost_price' => round($item['price'] * 0.76, 2),
                    'track_stock' => true,
                    'stock_quantity' => $item['stock'],
                    'min_stock_quantity' => 3,
                    'requires_shipping' => true,
                    'weight' => $item['weight'] ?? 0.500,
                    'length' => $item['length'] ?? 20,
                    'width' => $item['width'] ?? 15,
                    'height' => $item['height'] ?? 7,
                    'specifications' => $item['specifications'],
                    'notes' => [
                        'ar' => 'منتج تجريبي لعرض شكل المتجر قبل الرفع للهوست.',
                        'he' => 'מוצר דמו להצגת מראה החנות לפני העלאה לאחסון.',
                        'en' => 'Demo product for storefront preview before hosting deployment.',
                    ],
                    'seo_title' => [
                        'ar' => $item['name']['ar'],
                        'he' => $item['name']['he'],
                        'en' => $item['name']['en'],
                    ],
                    'seo_description' => $item['short_description'],
                    'is_featured' => $index < 12,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            if ($category) {
                DB::table('category_product')->updateOrInsert(
                    [
                        'category_id' => $category->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'sort_order' => $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $this->createOptionsAndVariants($product, $item);
        }
    }

    private function currency(): ?Currency
    {
        return Currency::updateOrCreate(
            ['code' => 'ILS'],
            [
                'name' => [
                    'ar' => 'شيكل إسرائيلي',
                    'he' => 'שקל ישראלי',
                    'en' => 'Israeli Shekel',
                ],
                'symbol' => '₪',
                'country_code' => 'IL',
                'exchange_rate' => 1,
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    private function brands(): array
    {
        $items = [
            'Apple' => ['ar' => 'آبل', 'he' => 'אפל', 'en' => 'Apple'],
            'Samsung' => ['ar' => 'سامسونج', 'he' => 'סמסונג', 'en' => 'Samsung'],
            'Lenovo' => ['ar' => 'لينوفو', 'he' => 'לנובו', 'en' => 'Lenovo'],
            'HP' => ['ar' => 'إتش بي', 'he' => 'HP', 'en' => 'HP'],
            'ASUS' => ['ar' => 'أسوس', 'he' => 'אסוס', 'en' => 'ASUS'],
            'Sony' => ['ar' => 'سوني', 'he' => 'סוני', 'en' => 'Sony'],
            'JBL' => ['ar' => 'جي بي إل', 'he' => 'JBL', 'en' => 'JBL'],
            'Logitech' => ['ar' => 'لوجيتك', 'he' => 'לוגיטק', 'en' => 'Logitech'],
            'Xiaomi' => ['ar' => 'شاومي', 'he' => 'שיאומי', 'en' => 'Xiaomi'],
            'Microsoft' => ['ar' => 'مايكروسوفت', 'he' => 'מיקרוסופט', 'en' => 'Microsoft'],
            'Nintendo' => ['ar' => 'نينتندو', 'he' => 'נינטנדו', 'en' => 'Nintendo'],
            'Canon' => ['ar' => 'كانون', 'he' => 'קנון', 'en' => 'Canon'],
        ];

        $brands = [];

        foreach ($items as $key => $name) {
            $brands[$key] = Brand::updateOrCreate(
                ['slug' => Str::slug($key)],
                [
                    'name' => $name,
                    'description' => [
                        'ar' => 'علامة تجارية تقنية ضمن بيانات العرض التجريبية.',
                        'he' => 'מותג טכנולוגי כחלק מנתוני הדמו.',
                        'en' => 'Technology brand included in demo storefront data.',
                    ],
                    'website_url' => null,
                    'is_active' => true,
                    'sort_order' => count($brands) + 1,
                ]
            );
        }

        return $brands;
    }

    private function categories(): array
    {
        $items = [
            'Smartphones' => ['icon' => '📱', 'name' => ['ar' => 'الهواتف الذكية', 'he' => 'סמארטפונים', 'en' => 'Smartphones']],
            'Laptops' => ['icon' => '💻', 'name' => ['ar' => 'اللابتوبات', 'he' => 'מחשבים ניידים', 'en' => 'Laptops']],
            'Tablets' => ['icon' => '📲', 'name' => ['ar' => 'الأجهزة اللوحية', 'he' => 'טאבלטים', 'en' => 'Tablets']],
            'Gaming' => ['icon' => '🎮', 'name' => ['ar' => 'الألعاب', 'he' => 'גיימינג', 'en' => 'Gaming']],
            'Audio' => ['icon' => '🎧', 'name' => ['ar' => 'الصوتيات', 'he' => 'אודיו', 'en' => 'Audio']],
            'Accessories' => ['icon' => '⌨️', 'name' => ['ar' => 'الإكسسوارات', 'he' => 'אביזרים', 'en' => 'Accessories']],
            'Smart Watches' => ['icon' => '⌚', 'name' => ['ar' => 'الساعات الذكية', 'he' => 'שעונים חכמים', 'en' => 'Smart Watches']],
            'TVs' => ['icon' => '📺', 'name' => ['ar' => 'الشاشات والتلفزيونات', 'he' => 'טלוויזיות ומסכים', 'en' => 'TVs & Displays']],
            'Cameras' => ['icon' => '📷', 'name' => ['ar' => 'الكاميرات', 'he' => 'מצלמות', 'en' => 'Cameras']],
        ];

        $categories = [];

        foreach ($items as $key => $item) {
            $categories[$key] = Category::updateOrCreate(
                ['slug' => Str::slug($key)],
                [
                    'name' => $item['name'],
                    'description' => [
                        'ar' => 'قسم تجريبي لعرض منتجات إلكترونية داخل المتجر.',
                        'he' => 'קטגוריית דמו להצגת מוצרי אלקטרוניקה בחנות.',
                        'en' => 'Demo category for showcasing electronics products.',
                    ],
                    'icon' => $item['icon'],
                    'is_active' => true,
                    'show_in_menu' => true,
                    'sort_order' => count($categories) + 1,
                ]
            );
        }

        return $categories;
    }

    private function createOptionsAndVariants(Product $product, array $item): void
    {
        if (empty($item['variants'])) {
            return;
        }

        ProductOption::updateOrCreate(
            [
                'product_id' => $product->id,
                'slug' => 'model',
            ],
            [
                'name' => [
                    'ar' => 'الخيار / النسخة',
                    'he' => 'אפשרות / דגם',
                    'en' => 'Option / Model',
                ],
                'type' => 'select',
                'values' => collect($item['variants'])->map(fn ($variant) => [
                    'ar' => $variant['name']['ar'],
                    'he' => $variant['name']['he'],
                    'en' => $variant['name']['en'],
                ])->values()->toArray(),
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        foreach ($item['variants'] as $order => $variant) {
            ProductVariant::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sku' => $variant['sku'],
                ],
                [
                    'name' => $variant['name'],
                    'option_values' => [
                        'model' => $variant['name'],
                    ],
                    'price' => $variant['price'],
                    'sale_price' => $variant['sale_price'] ?? null,
                    'cost_price' => round($variant['price'] * 0.76, 2),
                    'track_stock' => true,
                    'stock_quantity' => $variant['stock'],
                    'min_stock_quantity' => 2,
                    'weight' => $item['weight'] ?? 0.500,
                    'is_default' => $order === 0,
                    'is_active' => true,
                    'sort_order' => $order + 1,
                ]
            );
        }
    }

    private function createSvgImage(string $slug, string $brand, string $title, string $badge, string $from, string $to): string
    {
        $directory = storage_path('app/public/demo-products');
        File::ensureDirectoryExists($directory);

        $filename = $slug . '.svg';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeBrand = htmlspecialchars($brand, ENT_QUOTES, 'UTF-8');
        $safeBadge = htmlspecialchars($badge, ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$from}"/>
      <stop offset="100%" stop-color="{$to}"/>
    </linearGradient>
    <radialGradient id="glow" cx="72%" cy="18%" r="60%">
      <stop offset="0%" stop-color="rgba(255,255,255,0.35)"/>
      <stop offset="100%" stop-color="rgba(255,255,255,0)"/>
    </radialGradient>
    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="24" stdDeviation="24" flood-color="#020617" flood-opacity=".28"/>
    </filter>
  </defs>
  <rect width="1200" height="900" rx="64" fill="url(#bg)"/>
  <circle cx="960" cy="120" r="360" fill="url(#glow)"/>
  <circle cx="160" cy="760" r="260" fill="rgba(255,255,255,.10)"/>
  <rect x="120" y="120" width="960" height="660" rx="56" fill="rgba(255,255,255,.13)" stroke="rgba(255,255,255,.35)" stroke-width="2"/>
  <g filter="url(#shadow)">
    <rect x="380" y="220" width="440" height="360" rx="46" fill="rgba(255,255,255,.94)"/>
    <rect x="430" y="270" width="340" height="220" rx="30" fill="rgba(15,23,42,.10)"/>
    <circle cx="600" cy="380" r="86" fill="rgba(37,99,235,.15)"/>
    <path d="M545 410h110l-18 72h-74l-18-72zm20-36h70l18 36H547l18-36z" fill="#1d4ed8"/>
  </g>
  <text x="120" y="100" fill="rgba(255,255,255,.82)" font-size="38" font-family="Arial, sans-serif" font-weight="800">{$safeBrand}</text>
  <text x="600" y="670" fill="#ffffff" font-size="58" font-family="Arial, sans-serif" font-weight="900" text-anchor="middle">{$safeTitle}</text>
  <rect x="430" y="715" width="340" height="56" rx="28" fill="rgba(255,255,255,.18)" stroke="rgba(255,255,255,.28)"/>
  <text x="600" y="752" fill="#ffffff" font-size="28" font-family="Arial, sans-serif" font-weight="800" text-anchor="middle">{$safeBadge}</text>
</svg>
SVG;

        File::put($path, $svg);

        return 'demo-products/' . $filename;
    }

    private function products(): array
    {
        return [
            [
                'sku' => 'DEMO-IPHONE-16-PM-256',
                'slug' => 'demo-iphone-16-pro-max-256',
                'brand' => 'Apple',
                'category' => 'Smartphones',
                'badge' => 'Premium Smartphone',
                'gradient' => ['#0f172a', '#2563eb'],
                'name' => ['ar' => 'iPhone 16 Pro Max 256GB', 'he' => 'iPhone 16 Pro Max 256GB', 'en' => 'iPhone 16 Pro Max 256GB'],
                'short_description' => ['ar' => 'هاتف ذكي فاخر بشاشة كبيرة وأداء قوي.', 'he' => 'סמארטפון פרימיום עם מסך גדול וביצועים חזקים.', 'en' => 'Premium smartphone with a large display and powerful performance.'],
                'description' => ['ar' => 'منتج تجريبي يحاكي أجهزة الهواتف المتقدمة لعرض شكل المتجر، الفلاتر، الأسعار، والعروض.', 'he' => 'מוצר דמו המדמה סמארטפון מתקדם להצגת מראה החנות, מחירים ומבצעים.', 'en' => 'Demo product inspired by premium smartphones to preview storefront cards, prices, filters, and offers.'],
                'price' => 4999.00,
                'sale_price' => 4699.00,
                'stock' => 8,
                'weight' => 0.240,
                'specifications' => ['screen' => '6.9 inch', 'storage' => '256GB', 'network' => '5G'],
                'variants' => [
                    ['sku' => 'DEMO-IPHONE-16-PM-256-BLK', 'name' => ['ar' => 'أسود 256GB', 'he' => 'שחור 256GB', 'en' => 'Black 256GB'], 'price' => 4699, 'sale_price' => 4499, 'stock' => 5],
                    ['sku' => 'DEMO-IPHONE-16-PM-512-BLK', 'name' => ['ar' => 'أسود 512GB', 'he' => 'שחור 512GB', 'en' => 'Black 512GB'], 'price' => 5499, 'sale_price' => null, 'stock' => 3],
                ],
            ],
            [
                'sku' => 'DEMO-GALAXY-S25-256',
                'slug' => 'demo-galaxy-s25-ultra-256',
                'brand' => 'Samsung',
                'category' => 'Smartphones',
                'badge' => 'Android Flagship',
                'gradient' => ['#111827', '#7c3aed'],
                'name' => ['ar' => 'Samsung Galaxy S25 Ultra 256GB', 'he' => 'Samsung Galaxy S25 Ultra 256GB', 'en' => 'Samsung Galaxy S25 Ultra 256GB'],
                'short_description' => ['ar' => 'هاتف أندرويد متقدم بكاميرا قوية وشاشة مميزة.', 'he' => 'מכשיר אנדרואיד מתקדם עם מצלמה חזקה ומסך איכותי.', 'en' => 'Advanced Android phone with a strong camera and premium display.'],
                'description' => ['ar' => 'بيانات تجريبية لعرض شكل منتج هاتف ضمن المتجر.', 'he' => 'נתוני דמו להצגת מוצר סמארטפון בחנות.', 'en' => 'Demo product data for showing a smartphone item inside the store.'],
                'price' => 4599.00,
                'sale_price' => 4199.00,
                'stock' => 11,
                'weight' => 0.230,
                'specifications' => ['screen' => '6.8 inch', 'storage' => '256GB', 'camera' => 'Pro camera'],
                'variants' => [
                    ['sku' => 'DEMO-GALAXY-S25-256-GRAY', 'name' => ['ar' => 'رمادي 256GB', 'he' => 'אפור 256GB', 'en' => 'Gray 256GB'], 'price' => 4199, 'sale_price' => 3999, 'stock' => 7],
                    ['sku' => 'DEMO-GALAXY-S25-512-BLK', 'name' => ['ar' => 'أسود 512GB', 'he' => 'שחור 512GB', 'en' => 'Black 512GB'], 'price' => 4899, 'sale_price' => null, 'stock' => 4],
                ],
            ],
            [
                'sku' => 'DEMO-MACBOOK-AIR-M3',
                'slug' => 'demo-macbook-air-m3-13',
                'brand' => 'Apple',
                'category' => 'Laptops',
                'badge' => 'Light Laptop',
                'gradient' => ['#1e293b', '#0ea5e9'],
                'name' => ['ar' => 'MacBook Air M3 13-inch', 'he' => 'MacBook Air M3 13 אינץ׳', 'en' => 'MacBook Air M3 13-inch'],
                'short_description' => ['ar' => 'لابتوب خفيف مناسب للعمل والدراسة.', 'he' => 'מחשב נייד קל לעבודה וללימודים.', 'en' => 'Lightweight laptop for work and study.'],
                'description' => ['ar' => 'لابتوب تجريبي لعرض فئة الحواسيب المحمولة في المتجر.', 'he' => 'מחשב נייד דמו להצגת קטגוריית מחשבים ניידים.', 'en' => 'Demo laptop for showcasing the laptop category.'],
                'price' => 5499.00,
                'sale_price' => 5199.00,
                'stock' => 6,
                'weight' => 1.240,
                'specifications' => ['cpu' => 'M3', 'ram' => '8GB', 'storage' => '256GB SSD'],
                'variants' => [
                    ['sku' => 'DEMO-MACBOOK-AIR-M3-256', 'name' => ['ar' => '256GB SSD', 'he' => '256GB SSD', 'en' => '256GB SSD'], 'price' => 5199, 'sale_price' => null, 'stock' => 6],
                    ['sku' => 'DEMO-MACBOOK-AIR-M3-512', 'name' => ['ar' => '512GB SSD', 'he' => '512GB SSD', 'en' => '512GB SSD'], 'price' => 6199, 'sale_price' => 5899, 'stock' => 2],
                ],
            ],
            [
                'sku' => 'DEMO-LENOVO-IDEAPAD-I5',
                'slug' => 'demo-lenovo-ideapad-i5',
                'brand' => 'Lenovo',
                'category' => 'Laptops',
                'badge' => 'Everyday Laptop',
                'gradient' => ['#0f172a', '#16a34a'],
                'name' => ['ar' => 'Lenovo IdeaPad Intel i5', 'he' => 'Lenovo IdeaPad Intel i5', 'en' => 'Lenovo IdeaPad Intel i5'],
                'short_description' => ['ar' => 'حاسوب عملي للاستخدام اليومي بسعر مناسب.', 'he' => 'מחשב שימושי לשימוש יומיומי במחיר נוח.', 'en' => 'Practical everyday laptop at a good price.'],
                'description' => ['ar' => 'منتج تجريبي لفحص عرض اللابتوبات والعروض.', 'he' => 'מוצר דמו לבדיקת תצוגת מחשבים ניידים ומבצעים.', 'en' => 'Demo item for testing laptop listings and deals.'],
                'price' => 2899.00,
                'sale_price' => 2499.00,
                'stock' => 14,
                'weight' => 1.700,
                'specifications' => ['cpu' => 'Intel i5', 'ram' => '16GB', 'storage' => '512GB SSD'],
            ],
            [
                'sku' => 'DEMO-HP-PAVILION-15',
                'slug' => 'demo-hp-pavilion-15',
                'brand' => 'HP',
                'category' => 'Laptops',
                'badge' => 'Work Laptop',
                'gradient' => ['#1e1b4b', '#2563eb'],
                'name' => ['ar' => 'HP Pavilion 15 Ryzen 7', 'he' => 'HP Pavilion 15 Ryzen 7', 'en' => 'HP Pavilion 15 Ryzen 7'],
                'short_description' => ['ar' => 'لابتوب قوي للعمل والدراسة وتعدد المهام.', 'he' => 'מחשב חזק לעבודה, לימודים וריבוי משימות.', 'en' => 'Powerful laptop for work, study, and multitasking.'],
                'description' => ['ar' => 'بيانات عرض تجريبية لفئة الحواسيب.', 'he' => 'נתוני תצוגה דמו לקטגוריית מחשבים.', 'en' => 'Demo display data for laptops category.'],
                'price' => 3499.00,
                'sale_price' => null,
                'stock' => 9,
                'weight' => 1.800,
                'specifications' => ['cpu' => 'Ryzen 7', 'ram' => '16GB', 'storage' => '1TB SSD'],
            ],
            [
                'sku' => 'DEMO-ASUS-TUF-A15',
                'slug' => 'demo-asus-tuf-gaming-a15',
                'brand' => 'ASUS',
                'category' => 'Gaming',
                'badge' => 'Gaming Laptop',
                'gradient' => ['#020617', '#dc2626'],
                'name' => ['ar' => 'ASUS TUF Gaming A15', 'he' => 'ASUS TUF Gaming A15', 'en' => 'ASUS TUF Gaming A15'],
                'short_description' => ['ar' => 'لابتوب ألعاب بأداء قوي وتصميم احترافي.', 'he' => 'מחשב גיימינג עם ביצועים חזקים ועיצוב מקצועי.', 'en' => 'Gaming laptop with strong performance and pro design.'],
                'description' => ['ar' => 'منتج ألعاب تجريبي لعرض العروض والمخزون.', 'he' => 'מוצר גיימינג דמו להצגת מבצעים ומלאי.', 'en' => 'Demo gaming item for deals and stock display.'],
                'price' => 5499.00,
                'sale_price' => 4999.00,
                'stock' => 5,
                'weight' => 2.300,
                'specifications' => ['gpu' => 'RTX Graphics', 'ram' => '16GB', 'display' => '144Hz'],
            ],
            [
                'sku' => 'DEMO-PS5-SLIM',
                'slug' => 'demo-playstation-5-slim',
                'brand' => 'Sony',
                'category' => 'Gaming',
                'badge' => 'Gaming Console',
                'gradient' => ['#0f172a', '#334155'],
                'name' => ['ar' => 'PlayStation 5 Slim Console', 'he' => 'PlayStation 5 Slim', 'en' => 'PlayStation 5 Slim Console'],
                'short_description' => ['ar' => 'جهاز ألعاب منزلي لتجربة لعب حديثة.', 'he' => 'קונסולת משחקים ביתית לחוויית משחק מתקדמת.', 'en' => 'Home gaming console for a modern gaming experience.'],
                'description' => ['ar' => 'جهاز تجريبي لفحص قسم الألعاب.', 'he' => 'מוצר דמו לבדיקת אזור הגיימינג.', 'en' => 'Demo console for testing the gaming section.'],
                'price' => 2499.00,
                'sale_price' => 2299.00,
                'stock' => 10,
                'weight' => 3.200,
                'specifications' => ['storage' => '1TB', 'edition' => 'Slim', 'controller' => 'Included'],
            ],
            [
                'sku' => 'DEMO-NINTENDO-SWITCH-OLED',
                'slug' => 'demo-nintendo-switch-oled',
                'brand' => 'Nintendo',
                'category' => 'Gaming',
                'badge' => 'Portable Gaming',
                'gradient' => ['#1e293b', '#ef4444'],
                'name' => ['ar' => 'Nintendo Switch OLED', 'he' => 'Nintendo Switch OLED', 'en' => 'Nintendo Switch OLED'],
                'short_description' => ['ar' => 'جهاز ألعاب محمول مع شاشة OLED.', 'he' => 'קונסולה ניידת עם מסך OLED.', 'en' => 'Portable gaming console with OLED display.'],
                'description' => ['ar' => 'منتج تجريبي للألعاب المحمولة.', 'he' => 'מוצר דמו לקונסולות ניידות.', 'en' => 'Demo product for portable gaming devices.'],
                'price' => 1699.00,
                'sale_price' => 1549.00,
                'stock' => 13,
                'weight' => 0.900,
                'specifications' => ['display' => 'OLED', 'mode' => 'Handheld/Docked', 'storage' => '64GB'],
            ],
            [
                'sku' => 'DEMO-IPAD-AIR-M2',
                'slug' => 'demo-ipad-air-m2',
                'brand' => 'Apple',
                'category' => 'Tablets',
                'badge' => 'Creative Tablet',
                'gradient' => ['#312e81', '#0ea5e9'],
                'name' => ['ar' => 'iPad Air M2 11-inch', 'he' => 'iPad Air M2 11 אינץ׳', 'en' => 'iPad Air M2 11-inch'],
                'short_description' => ['ar' => 'تابلت سريع وخفيف للعمل والترفيه.', 'he' => 'טאבלט מהיר וקל לעבודה ובידור.', 'en' => 'Fast and light tablet for work and entertainment.'],
                'description' => ['ar' => 'تابلت تجريبي لعرض فئة الأجهزة اللوحية.', 'he' => 'טאבלט דמו להצגת קטגוריית טאבלטים.', 'en' => 'Demo tablet for showcasing tablet category.'],
                'price' => 3299.00,
                'sale_price' => null,
                'stock' => 7,
                'weight' => 0.470,
                'specifications' => ['chip' => 'M2', 'display' => '11 inch', 'storage' => '128GB'],
            ],
            [
                'sku' => 'DEMO-GALAXY-TAB-S10',
                'slug' => 'demo-galaxy-tab-s10',
                'brand' => 'Samsung',
                'category' => 'Tablets',
                'badge' => 'Android Tablet',
                'gradient' => ['#0f172a', '#0891b2'],
                'name' => ['ar' => 'Samsung Galaxy Tab S10', 'he' => 'Samsung Galaxy Tab S10', 'en' => 'Samsung Galaxy Tab S10'],
                'short_description' => ['ar' => 'تابلت أندرويد بشاشة واضحة وأداء متوازن.', 'he' => 'טאבלט אנדרואיד עם מסך איכותי וביצועים מאוזנים.', 'en' => 'Android tablet with a clear display and balanced performance.'],
                'description' => ['ar' => 'بيانات تجريبية لفئة التابلت.', 'he' => 'נתוני דמו לקטגוריית טאבלטים.', 'en' => 'Demo data for tablet category.'],
                'price' => 2999.00,
                'sale_price' => 2799.00,
                'stock' => 9,
                'weight' => 0.520,
                'specifications' => ['display' => 'AMOLED', 'storage' => '256GB', 'pen' => 'Supported'],
            ],
            [
                'sku' => 'DEMO-APPLE-WATCH-S10',
                'slug' => 'demo-apple-watch-series-10',
                'brand' => 'Apple',
                'category' => 'Smart Watches',
                'badge' => 'Smart Watch',
                'gradient' => ['#111827', '#f97316'],
                'name' => ['ar' => 'Apple Watch Series 10', 'he' => 'Apple Watch Series 10', 'en' => 'Apple Watch Series 10'],
                'short_description' => ['ar' => 'ساعة ذكية لمتابعة الصحة والإشعارات.', 'he' => 'שעון חכם למעקב בריאות והתראות.', 'en' => 'Smart watch for health tracking and notifications.'],
                'description' => ['ar' => 'ساعة تجريبية لعرض المنتجات الصغيرة.', 'he' => 'שעון דמו להצגת מוצרים קטנים.', 'en' => 'Demo watch for small product display.'],
                'price' => 1899.00,
                'sale_price' => 1699.00,
                'stock' => 15,
                'weight' => 0.080,
                'specifications' => ['case' => 'Aluminum', 'connectivity' => 'GPS', 'water' => 'Water resistant'],
            ],
            [
                'sku' => 'DEMO-XIAOMI-WATCH-S3',
                'slug' => 'demo-xiaomi-watch-s3',
                'brand' => 'Xiaomi',
                'category' => 'Smart Watches',
                'badge' => 'Budget Watch',
                'gradient' => ['#14532d', '#22c55e'],
                'name' => ['ar' => 'Xiaomi Watch S3', 'he' => 'Xiaomi Watch S3', 'en' => 'Xiaomi Watch S3'],
                'short_description' => ['ar' => 'ساعة ذكية بسعر مناسب وبطارية قوية.', 'he' => 'שעון חכם במחיר נוח עם סוללה חזקה.', 'en' => 'Affordable smartwatch with strong battery life.'],
                'description' => ['ar' => 'منتج تجريبي لفئة الساعات الذكية.', 'he' => 'מוצר דמו לקטגוריית שעונים חכמים.', 'en' => 'Demo item for smartwatch category.'],
                'price' => 699.00,
                'sale_price' => 599.00,
                'stock' => 20,
                'weight' => 0.070,
                'specifications' => ['battery' => 'Long battery', 'display' => 'AMOLED', 'sports' => 'Multi-sport'],
            ],
            [
                'sku' => 'DEMO-SONY-WH1000XM5',
                'slug' => 'demo-sony-wh-1000xm5',
                'brand' => 'Sony',
                'category' => 'Audio',
                'badge' => 'Noise Cancelling',
                'gradient' => ['#020617', '#475569'],
                'name' => ['ar' => 'Sony WH-1000XM5 Headphones', 'he' => 'Sony WH-1000XM5', 'en' => 'Sony WH-1000XM5 Headphones'],
                'short_description' => ['ar' => 'سماعات لاسلكية مع عزل ضوضاء متقدم.', 'he' => 'אוזניות אלחוטיות עם סינון רעשים מתקדם.', 'en' => 'Wireless headphones with advanced noise cancelling.'],
                'description' => ['ar' => 'سماعات تجريبية لعرض قسم الصوتيات.', 'he' => 'אוזניות דמו להצגת אזור האודיו.', 'en' => 'Demo headphones for showcasing audio section.'],
                'price' => 1699.00,
                'sale_price' => 1399.00,
                'stock' => 18,
                'weight' => 0.250,
                'specifications' => ['wireless' => 'Bluetooth', 'anc' => 'Active noise cancelling', 'battery' => 'Up to 30 hours'],
            ],
            [
                'sku' => 'DEMO-JBL-FLIP-6',
                'slug' => 'demo-jbl-flip-6-speaker',
                'brand' => 'JBL',
                'category' => 'Audio',
                'badge' => 'Portable Speaker',
                'gradient' => ['#7f1d1d', '#f97316'],
                'name' => ['ar' => 'JBL Flip 6 Bluetooth Speaker', 'he' => 'JBL Flip 6 רמקול Bluetooth', 'en' => 'JBL Flip 6 Bluetooth Speaker'],
                'short_description' => ['ar' => 'سبيكر بلوتوث محمول بصوت قوي.', 'he' => 'רמקול Bluetooth נייד עם צליל חזק.', 'en' => 'Portable Bluetooth speaker with powerful sound.'],
                'description' => ['ar' => 'منتج تجريبي صغير لفحص شكل البطاقات.', 'he' => 'מוצר דמו קטן לבדיקת כרטיסי מוצר.', 'en' => 'Small demo product for product card preview.'],
                'price' => 599.00,
                'sale_price' => 499.00,
                'stock' => 22,
                'weight' => 0.550,
                'specifications' => ['battery' => '12 hours', 'waterproof' => 'IP67', 'connection' => 'Bluetooth'],
            ],
            [
                'sku' => 'DEMO-AIRPODS-PRO-2',
                'slug' => 'demo-airpods-pro-2',
                'brand' => 'Apple',
                'category' => 'Audio',
                'badge' => 'True Wireless',
                'gradient' => ['#1e3a8a', '#60a5fa'],
                'name' => ['ar' => 'AirPods Pro 2', 'he' => 'AirPods Pro 2', 'en' => 'AirPods Pro 2'],
                'short_description' => ['ar' => 'سماعات لاسلكية صغيرة بعزل ضوضاء.', 'he' => 'אוזניות אלחוטיות קטנות עם סינון רעשים.', 'en' => 'Compact wireless earbuds with noise cancellation.'],
                'description' => ['ar' => 'بيانات تجريبية لسماعات لاسلكية.', 'he' => 'נתוני דמו לאוזניות אלחוטיות.', 'en' => 'Demo data for wireless earbuds.'],
                'price' => 1099.00,
                'sale_price' => 999.00,
                'stock' => 17,
                'weight' => 0.090,
                'specifications' => ['charging' => 'Wireless case', 'anc' => 'Yes', 'fit' => 'In-ear'],
            ],
            [
                'sku' => 'DEMO-LOGITECH-MX-MASTER-3S',
                'slug' => 'demo-logitech-mx-master-3s',
                'brand' => 'Logitech',
                'category' => 'Accessories',
                'badge' => 'Pro Mouse',
                'gradient' => ['#064e3b', '#14b8a6'],
                'name' => ['ar' => 'Logitech MX Master 3S Mouse', 'he' => 'Logitech MX Master 3S', 'en' => 'Logitech MX Master 3S Mouse'],
                'short_description' => ['ar' => 'ماوس احترافي للعمل والإنتاجية.', 'he' => 'עכבר מקצועי לעבודה ופרודוקטיביות.', 'en' => 'Professional mouse for productivity and work.'],
                'description' => ['ar' => 'إكسسوار تجريبي لفحص قسم الملحقات.', 'he' => 'אביזר דמו לבדיקת קטגוריית אביזרים.', 'en' => 'Demo accessory for testing accessories category.'],
                'price' => 449.00,
                'sale_price' => 399.00,
                'stock' => 30,
                'weight' => 0.150,
                'specifications' => ['connectivity' => 'Bluetooth/USB', 'battery' => 'Rechargeable', 'dpi' => 'High precision'],
            ],
            [
                'sku' => 'DEMO-LOGITECH-MX-KEYS',
                'slug' => 'demo-logitech-mx-keys',
                'brand' => 'Logitech',
                'category' => 'Accessories',
                'badge' => 'Keyboard',
                'gradient' => ['#0f172a', '#64748b'],
                'name' => ['ar' => 'Logitech MX Keys Keyboard', 'he' => 'Logitech MX Keys מקלדת', 'en' => 'Logitech MX Keys Keyboard'],
                'short_description' => ['ar' => 'لوحة مفاتيح لاسلكية مريحة للعمل.', 'he' => 'מקלדת אלחוטית נוחה לעבודה.', 'en' => 'Comfortable wireless keyboard for work.'],
                'description' => ['ar' => 'منتج تجريبي لعرض الإكسسوارات.', 'he' => 'מוצר דמו להצגת אביזרים.', 'en' => 'Demo product for accessories display.'],
                'price' => 499.00,
                'sale_price' => null,
                'stock' => 16,
                'weight' => 0.650,
                'specifications' => ['layout' => 'Full size', 'wireless' => 'Yes', 'backlight' => 'Yes'],
            ],
            [
                'sku' => 'DEMO-SAMSUNG-55-QLED',
                'slug' => 'demo-samsung-55-qled-tv',
                'brand' => 'Samsung',
                'category' => 'TVs',
                'badge' => 'Smart TV',
                'gradient' => ['#111827', '#1d4ed8'],
                'name' => ['ar' => 'Samsung 55-inch QLED Smart TV', 'he' => 'Samsung QLED Smart TV 55 אינץ׳', 'en' => 'Samsung 55-inch QLED Smart TV'],
                'short_description' => ['ar' => 'تلفزيون ذكي 55 بوصة بجودة صورة عالية.', 'he' => 'טלוויזיה חכמה 55 אינץ׳ באיכות תמונה גבוהה.', 'en' => '55-inch smart TV with high image quality.'],
                'description' => ['ar' => 'شاشة تجريبية لفحص المنتجات الكبيرة.', 'he' => 'מסך דמו לבדיקת מוצרים גדולים.', 'en' => 'Demo display product for large product preview.'],
                'price' => 2999.00,
                'sale_price' => 2599.00,
                'stock' => 6,
                'weight' => 14.000,
                'specifications' => ['size' => '55 inch', 'panel' => 'QLED', 'resolution' => '4K'],
            ],
            [
                'sku' => 'DEMO-SONY-65-OLED',
                'slug' => 'demo-sony-65-oled-tv',
                'brand' => 'Sony',
                'category' => 'TVs',
                'badge' => 'OLED Display',
                'gradient' => ['#020617', '#7c2d12'],
                'name' => ['ar' => 'Sony 65-inch OLED TV', 'he' => 'Sony OLED TV 65 אינץ׳', 'en' => 'Sony 65-inch OLED TV'],
                'short_description' => ['ar' => 'شاشة OLED كبيرة لتجربة مشاهدة سينمائية.', 'he' => 'מסך OLED גדול לחוויית צפייה קולנועית.', 'en' => 'Large OLED TV for cinematic viewing.'],
                'description' => ['ar' => 'بيانات تجريبية لقسم الشاشات.', 'he' => 'נתוני דמו לקטגוריית מסכים.', 'en' => 'Demo data for TV category.'],
                'price' => 6499.00,
                'sale_price' => 5999.00,
                'stock' => 4,
                'weight' => 20.000,
                'specifications' => ['size' => '65 inch', 'panel' => 'OLED', 'resolution' => '4K'],
            ],
            [
                'sku' => 'DEMO-CANON-EOS-R50',
                'slug' => 'demo-canon-eos-r50-camera',
                'brand' => 'Canon',
                'category' => 'Cameras',
                'badge' => 'Mirrorless Camera',
                'gradient' => ['#450a0a', '#dc2626'],
                'name' => ['ar' => 'Canon EOS R50 Camera Kit', 'he' => 'Canon EOS R50 ערכת מצלמה', 'en' => 'Canon EOS R50 Camera Kit'],
                'short_description' => ['ar' => 'كاميرا خفيفة للتصوير والفيديو.', 'he' => 'מצלמה קלה לצילום ווידאו.', 'en' => 'Light camera for photography and video.'],
                'description' => ['ar' => 'كاميرا تجريبية لفحص فئة الكاميرات.', 'he' => 'מצלמת דמו לבדיקת קטגוריית מצלמות.', 'en' => 'Demo camera for testing cameras category.'],
                'price' => 3299.00,
                'sale_price' => 2999.00,
                'stock' => 7,
                'weight' => 0.950,
                'specifications' => ['type' => 'Mirrorless', 'lens' => 'Kit lens', 'video' => '4K'],
            ],
            [
                'sku' => 'DEMO-MICROSOFT-SURFACE',
                'slug' => 'demo-microsoft-surface-pro',
                'brand' => 'Microsoft',
                'category' => 'Tablets',
                'badge' => '2-in-1',
                'gradient' => ['#1e293b', '#0284c7'],
                'name' => ['ar' => 'Microsoft Surface Pro', 'he' => 'Microsoft Surface Pro', 'en' => 'Microsoft Surface Pro'],
                'short_description' => ['ar' => 'جهاز 2 في 1 للعمل والتنقل.', 'he' => 'מכשיר 2 ב-1 לעבודה וניידות.', 'en' => '2-in-1 device for work and mobility.'],
                'description' => ['ar' => 'منتج تجريبي متعدد الاستخدام.', 'he' => 'מוצר דמו רב שימושי.', 'en' => 'Multipurpose demo product.'],
                'price' => 4799.00,
                'sale_price' => 4499.00,
                'stock' => 6,
                'weight' => 0.900,
                'specifications' => ['type' => '2-in-1', 'storage' => '256GB', 'keyboard' => 'Supported'],
            ],
        ];
    }
}
