# Smart Commerce Platform

Smart Commerce Platform is a modern Laravel-based e-commerce management system for physical products, digital products, digital codes, customers, orders, invoices, payments, shipping, product reviews, wishlists, comparison, stock control, and multilingual storefront pages.

The project is designed as an admin-driven commerce platform using Laravel, MySQL, Filament Admin Panel, Livewire, Breeze Authentication, Spatie Roles & Permissions, and a responsive public storefront.

---

## Project Information

| Item | Details |
|---|---|
| Project Name | Smart Commerce Platform |
| Repository | `madadha/smart-commerce-platform` |
| Framework | Laravel 13 |
| PHP | PHP 8.4 |
| Database | MySQL |
| Admin Panel | Filament |
| Frontend | Blade, Tailwind CSS, Vite |
| Authentication | Laravel Breeze |
| Permissions | Spatie Laravel Permission |
| Local Environment | Laragon / Windows |
| Languages | Arabic, Hebrew, English |
| Main Branch | `main` |

---

## Main Features

### Storefront

- Public storefront homepage.
- Product listing page.
- Product details page.
- Search and advanced filters.
- Category filtering.
- Brand filtering.
- Price filtering.
- Sorting options.
- Product cards with ratings, badges, stock status, compare, and wishlist actions.
- Recently viewed products.
- RTL support for Arabic and Hebrew.
- LTR support for English.

### Product Management

- Physical products.
- Digital products.
- Services.
- Product variants.
- Product options.
- Multi-language product names and descriptions.
- SKU and barcode support.
- Product images and media gallery.
- Product badges.
- Product reviews and rating summaries.
- Product questions and answers.
- Stock tracking.
- Low stock alerts.
- Admin stock quick actions.

### Cart & Checkout

- Storefront cart.
- Add to cart.
- Update cart item quantity.
- Remove cart item.
- Checkout page.
- Customer details form.
- Shipping method selection.
- Payment method selection.
- Order creation from cart.
- Stock validation before order creation.
- Prevent out-of-stock products from being added to cart.
- Prevent ordering quantity greater than available stock.
- Stock deduction after successful order creation.

### Orders

- Admin order management.
- Storefront order details page.
- Signed order links for customers.
- Order status timeline for customers.
- Order tracking page.
- Order history for authenticated customers.
- Order status history in admin panel.
- Automatic status history logging when order status changes.
- Status history modal in Filament order edit page.

Supported order statuses:

- Pending
- Processing
- Shipped
- Completed
- Cancelled
- Refunded

### Invoices

- Customer invoice PDF download.
- Signed invoice download route.
- Arabic/Hebrew/English support.
- RTL/LTR support.
- Correct currency display.
- QR code inside the invoice.
- Order link inside the invoice.
- Professional invoice layout.
- mPDF support for better Arabic rendering.

### Emails

- Order created email.
- Order completed email.
- Email includes order summary.
- Email includes signed order link.
- Email includes signed invoice link.
- Email sending is wrapped safely so order creation or update does not fail if email delivery fails.

### Payments & Shipping

- Payment methods and payment status fields.
- Shipping methods module.
- Shipping cost support.
- Free shipping logic support.
- Home delivery and pickup support.
- Multi-currency support.

### Customers

- Customer records.
- Customer phone, email, city, and address.
- Link customers to orders.
- Authenticated account dashboard.
- Customer order history.

### Admin Panel

- Filament resources.
- Product management.
- Order management.
- Customer management.
- Shipping method management.
- Invoice and order tools.
- Status history button inside order edit page.
- Low stock navigation badge.
- Quick restock actions.

---

## Core Modules

### 1. Languages Module

The platform supports multilingual content using JSON fields.

Example:

```json
{
  "ar": "الاسم بالعربية",
  "he": "שם בעברית",
  "en": "Name in English"
}
```

Supported languages:

- Arabic
- Hebrew
- English

---

### 2. Countries & Currencies Module

The platform supports multiple countries and currencies.

Examples:

- Israel / ILS
- Jordan / JOD
- Egypt / EGP
- United Arab Emirates / AED

---

### 3. Products Module

The product module is the core of the platform.

Product fields include:

- Multi-language name.
- Slug.
- Short description.
- Full description.
- SKU.
- Barcode.
- Product type.
- Product status.
- Brand.
- Company.
- Currency.
- Price.
- Sale price.
- Cost price.
- Main image.
- Gallery images.
- Stock quantity.
- Minimum stock quantity.
- Shipping data.
- SEO title.
- SEO description.
- Featured status.
- Active status.
- Sort order.

Supported product types:

- Physical product.
- Digital product.
- Digital card.
- Digital file.
- Service.
- Subscription.
- Bundle.

---

### 4. Reviews & Ratings

Products support customer reviews.

Features:

- Review form on product details page.
- Rating summary on product page.
- Rating display on product cards.
- Admin review management.
- Approved/pending review workflow.

---

### 5. Wishlist

Authenticated customers can save products to wishlist.

Features:

- Wishlist page.
- Toggle wishlist from product card.
- Add/remove wishlist items.
- Auth-protected wishlist routes.

---

### 6. Product Compare

Customers can compare multiple products.

Features:

- Add product to comparison.
- Remove product from comparison.
- Clear comparison list.
- Comparison page.

---

### 7. Product Questions & Answers

Customers can ask questions on product pages.

Features:

- Product question form.
- Admin question management.
- Answer display on storefront.
- Active/approved question logic.

---

### 8. Stock System

The platform includes a practical stock management system.

Features:

- Stock status display on product cards and product pages.
- In stock / low stock / out of stock states.
- Low stock admin alerts.
- Admin navigation badge for low stock products.
- Quick stock actions from product table.
- Prevent out-of-stock add-to-cart.
- Validate stock before checkout.
- Deduct stock after successful order.

---

### 9. Checkout & Order Creation

Checkout creates orders from the active cart.

Checkout flow:

1. Customer adds products to cart.
2. Customer opens checkout.
3. Customer enters contact and shipping details.
4. System validates cart stock.
5. System converts cart to order.
6. System deducts stock.
7. System clears cart session.
8. System redirects customer to signed order page.
9. System sends order-created email if an email exists.

---

### 10. Order Status History

Every order status change from the admin panel can be recorded.

Stored data:

- Order ID.
- User ID.
- Old status.
- New status.
- Note.
- Change date.

The status history is displayed in a modal inside the Filament order edit page.

---

### 11. Invoice PDF System

Invoices are generated as PDF files for customer orders.

Features:

- Download invoice PDF.
- Signed invoice route.
- QR code to signed order page.
- Customer details.
- Order details.
- Items table.
- Totals section.
- Arabic-friendly rendering using mPDF.
- Currency display with fixed LTR formatting.

---

### 12. Email System

The platform sends customer emails for important order events.

Current emails:

- Order created email.
- Order completed email.

Each email includes:

- Order number.
- Order summary.
- Signed order link.
- Signed invoice download link.

---

## Important Routes

```php
Route::get('/store/orders/{order}', [StorefrontOrderController::class, 'show'])
    ->middleware('signed')
    ->name('storefront.orders.show');

Route::get('/store/orders/{order}/invoice', [StorefrontOrderController::class, 'invoice'])
    ->middleware('signed')
    ->name('storefront.orders.invoice');
```

---

## Technical Stack

- PHP 8.4
- Laravel 13
- MySQL
- Filament Admin Panel
- Livewire
- Laravel Breeze
- Spatie Laravel Permission
- Blade
- Tailwind CSS
- Vite
- mPDF
- Simple QR Code
- Git / GitHub
- Laragon

---

## Installation

Clone the repository:

```bash
git clone https://github.com/madadha/smart-commerce-platform.git
cd smart-commerce-platform
```

Install PHP dependencies:

```bash
composer install
```

Install JavaScript dependencies:

```bash
npm install
```

Create environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Configure database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_commerce_platform
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

Build assets:

```bash
npm run build
```

Clear cache:

```bash
php artisan optimize:clear
```

---

## Mail Configuration

Example SMTP configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=465
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME="Smart Commerce Platform"
```

Do not commit real mail passwords or `.env` files to GitHub.

---

## Useful Commands

```bash
php artisan optimize:clear
php artisan view:clear
composer dump-autoload
php artisan route:list
php artisan migrate
npm run build
```

Check invoice route:

```bash
php artisan route:list | findstr invoice
```

Check Git status:

```bash
git status
```

---

## Git Workflow

```bash
git status
git add .
git commit -m "Update smart commerce platform features"
git push origin main
```

---

## Security Notes

- Keep `.env` out of Git.
- Do not commit SMTP passwords.
- Signed routes are used for customer order and invoice links.
- Digital codes should be masked in admin listings.
- Customer emails should be sent safely without breaking order flow.

---

## Current Development Status

Completed major features:

- Storefront product pages.
- Wishlist.
- Reviews and ratings.
- Product compare.
- Recently viewed products.
- Product questions and answers.
- Advanced product filters.
- Product badges.
- Stock status system.
- Out-of-stock cart protection.
- Stock deduction after order.
- Low stock admin alerts.
- Admin stock quick actions.
- Customer invoice PDF.
- Invoice QR code.
- Order-created email.
- Order-completed email.
- Customer order status timeline.
- Admin order status history.

---

## Author

Developed by Alaa AlMadadha.

---

## License

This project is for educational and development purposes.
