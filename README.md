# Smart Commerce Platform

Smart Commerce Platform is a modern Laravel 13 based e-commerce management system designed to support physical products, digital products, product variants, digital codes, customers, orders, payments, shipping methods, media management, multi-language content, and multi-currency support.

The system is built as an advanced admin-driven commerce platform using Laravel, MySQL, Filament Admin Panel, Breeze Authentication, and Spatie Roles & Permissions.

---

## Project Information

**Project Name:** Smart Commerce Platform  
**Repository:** https://github.com/madadha/smart-commerce-platform  
**Framework:** Laravel 13  
**Database:** MySQL  
**Admin Panel:** Filament  
**Authentication:** Laravel Breeze  
**Permissions:** Spatie Laravel Permission  
**Environment:** Laragon / Windows  
**Main Language Support:** Arabic, Hebrew, English  

---

## Main Features

### Authentication & Admin Panel

- Laravel Breeze authentication.
- Filament admin dashboard.
- Admin login.
- User roles and permissions.
- Admin protected resources.

---

## Core Modules

### 1. Languages Module

The platform supports multiple languages and stores translatable fields as JSON.

Supported languages:

- Arabic
- Hebrew
- English

Many modules use fields like:

```json
{
  "ar": "الاسم بالعربية",
  "he": "שם בעברית",
  "en": "Name in English"
}

2. Countries & Currencies Module

The system supports countries and currencies for international commerce.

Examples:

Israel / ILS
Jordan / JOD
Egypt / EGP
United Arab Emirates / AED

This allows the platform to work according to customer country and business needs.

3. Settings Module

A general settings system was added to control future global platform options such as:

Site name
Default currency
Default country
General configuration
Future payment and shipping settings
4. Companies & Brands Module

The platform supports both brands and companies.

Brands

Examples:

Apple
Samsung
Sony
PlayStation
Xbox
Nintendo

Brand fields include:

Multi-language name
Slug
Logo
Banner image
Website URL
SEO title
SEO description
Active status
Sort order
Companies

Companies can represent suppliers, partners, service providers, manufacturers, or reseller companies.

Company fields include:

Multi-language name
Slug
Type
Logo
Email
Phone
Website
Country
Notes
Active status
Sort order
5. Nested Categories Module

The category system supports unlimited nested categories using parent_id.

Example:

Electronics
 └── Phones
     └── iPhone
         └── iPhone 16

Digital Cards
 └── Gaming
     └── PlayStation
         └── PlayStation US

Category fields include:

Parent category
Multi-language name
Slug
Multi-language description
Image
Icon
Banner image
SEO title
SEO description
Active status
Show in menu
Sort order

The system can generate a full category path such as:

إلكترونيات > هواتف > iPhone > iPhone 16
6. Media Library Module

The system includes a media management module for handling uploaded files and images.

Supported media information:

File path
Disk
Type
MIME type
Size
Width and height
Multi-language title
Multi-language alt text
Description
Metadata
AI generated alt text support for future use
Uploaded by user
Active status

This module will support future AI-based image analysis and automatic descriptions.

7. Products Module

The Products module is the core of the commerce platform.

Product fields include:

Multi-language name
Slug
Short description
Full description
SKU
Barcode
Product type
Product status
Brand
Company
Currency
Main image
Main media image
Price
Sale price
Cost price
Stock tracking
Stock quantity
Minimum stock quantity
Shipping settings
Weight and dimensions
Specifications
Notes
SEO title
SEO description
Featured status
Active status
Sort order

Supported product types:

Physical Product
Digital Card
Digital File
Service
Subscription
Bundle

Supported product statuses:

Draft
Active
Inactive
Out of Stock
Archived

Products can be linked to multiple categories.

8. Direct Product Image Upload

Products support direct image upload from the product form.

The product can now have:

A direct uploaded main image
Or an image selected from the Media Library

This makes product management easier from the admin panel.

9. Product Media Gallery Module

Each product can have multiple images.

Supported image roles:

Main image
Gallery image
Detail image
Look image
Banner image
Package image

Example:

iPhone 16 Pro Max
- Main image
- Back side image
- Color image
- Box image
- Promotional image

Each media record can include:

Product
Uploaded image
Media Library image
Role
Alt text in Arabic, Hebrew, and English
Active status
Sort order
10. Product Options & Variants Module

The system supports product options and variants.

Example:

Product: iPhone 16 Pro Max

Options:
- Color
- Storage

Variants:
- Black / 256GB
- Black / 512GB
- Gold / 256GB
Product Options

Option fields include:

Product
Multi-language name
Slug
Type
Values
Required status
Active status
Sort order

Option types:

Select
Color
Text
Button
Product Variants

Variant fields include:

Product
Multi-language name
SKU
Barcode
Option values
Image
Media Library image
Price
Sale price
Cost price
Stock quantity
Minimum stock
Weight and dimensions
Default variant status
Active status
Sort order
11. Digital Codes / Product Inventory Module

This module supports digital products such as:

PlayStation cards
Apple Gift Cards
Xbox cards
Steam codes
Netflix subscription codes
Warranty or service codes

Digital code fields include:

Product
Product variant
Code
Status
Source
Expiration date
Reserved by user
Reserved date
Sold to user
Sold date
Internal notes
Active status
Sort order

Supported digital code statuses:

Available
Reserved
Sold
Cancelled
Expired

Codes are masked in the admin table for security.

Example:

PSN-********-0001
12. Customers Module

The platform includes a complete customer management module.

Customer fields include:

Linked user
Country
Customer type
Status
First name
Last name
Email
Phone
WhatsApp
Identity number
Birth date
Company name
Tax number
City
Area
Street
Building
Apartment
Postal code
Address notes
Internal notes
Marketing approval
Active status
Sort order

Customer types:

Regular Customer
Reseller
VIP Customer
Company

Customer statuses:

Active
Inactive
Blocked

This prepares the system for orders, invoices, shipping, and reseller features.

13. Orders Module

The Orders module manages customer orders.

Order fields include:

Order number
Customer
User
Currency
Order status
Payment status
Payment method
Shipping method
Subtotal
Discount total
Tax total
Shipping total
Grand total
Paid total
Billing address
Shipping address
Customer notes
Internal notes
Ordered date
Paid date
Completed date
Cancelled date
Active status
Sort order

Order statuses:

Pending
Processing
Completed
Cancelled
Refunded

Payment statuses:

Unpaid
Paid
Partially Paid
Refunded
Failed
14. Order Items Management

Order items can be managed inside the order form using a Filament Repeater.

Each order item includes:

Product
Product variant
Digital code
Item type
Product name
SKU
Quantity
Unit price
Discount
Tax
Options
Notes

The system recalculates order totals based on order items.

15. Payments Module

The Payments module manages payment transactions linked to orders.

Payment fields include:

Payment number
Order
Customer
Currency
Payment method
Payment status
Amount
Refunded amount
Transaction ID
Provider
Provider reference
Provider payload
Paid date
Failed date
Refunded date
Internal notes
Active status
Sort order

Supported payment methods:

Cash
Credit Card
Bank Transfer
PayPal
PayPlus
Stripe

Payment transaction statuses:

Pending
Paid
Failed
Cancelled
Refunded

The payment module automatically syncs order payment totals and updates the order payment status.

16. Shipping Methods Module

The Shipping Methods module manages delivery methods.

Supported shipping methods:

Home Delivery
Pickup
Express Delivery
Standard Delivery
Free Delivery
External Shipping Company

Shipping method fields include:

Multi-language name
Slug
Multi-language description
Type
Country
Currency
Base cost
Free shipping minimum total
Minimum delivery days
Maximum delivery days
External company name
External company phone
External company website
Allowed cities
Excluded cities
Requires address
Default method
Active status
Sort order

Arabic labels include:

توصيل للبيت
استلام ذاتي
توصيل سريع
توصيل عادي
توصيل مجاني
شركة توصيل خارجية
17. Linking Shipping Methods With Orders

Orders are now linked to shipping methods using:

orders.shipping_method_id → shipping_methods.id

This is stronger than storing only a text value.

The order can now calculate shipping cost based on the selected shipping method.

Example:

If order total is less than 500 → shipping cost = 30
If order total is 500 or more → shipping cost = 0
Technical Stack
PHP 8.4
Laravel 13
MySQL
Filament Admin Panel
Laravel Breeze
Spatie Laravel Permission
Livewire
Tailwind CSS
Vite
Git / GitHub
Laragon
Installation

Clone the repository:

git clone https://github.com/madadha/smart-commerce-platform.git
cd smart-commerce-platform

Install PHP dependencies:

composer install

Install JavaScript dependencies:

npm install

Create environment file:

cp .env.example .env

Generate application key:

php artisan key:generate

Configure database in .env:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_commerce_platform
DB_USERNAME=root
DB_PASSWORD=

Set application URL:

APP_URL=http://smart-commerce-platform.test:8080
FILESYSTEM_DISK=public

Run migrations:

php artisan migrate

Create storage link:

php artisan storage:link

Run development server assets:

npm run dev
Useful Commands

Clear Laravel cache:

php artisan optimize:clear

Check migration status:

php artisan migrate:status

Run migrations:

php artisan migrate

Run specific seeder:

php artisan db:seed --class=SeederName

Open Tinker:

php artisan tinker

Check Git status:

git status

Commit changes:

git add .
git commit -m "Commit message"
git push
Seeders

The project includes seeders for demo data, such as:

RolePermissionSeeder
LanguageSeeder
CountrySeeder
CurrencySeeder
SettingSeeder
BrandSeeder
CompanySeeder
CategorySeeder
ProductSeeder
ProductOptionsSeeder
ProductDigitalCodeSeeder
CustomerSeeder
OrderSeeder
PaymentSeeder
ShippingMethodSeeder

To run a seeder:

php artisan db:seed --class=SeederName

Example:

php artisan db:seed --class=ProductSeeder
Admin URLs

Filament admin panel:

/admin

Important admin pages:

/admin/languages
/admin/countries
/admin/currencies
/admin/settings
/admin/brands
/admin/companies
/admin/categories
/admin/media-files
/admin/products
/admin/product-media
/admin/product-options
/admin/product-variants
/admin/product-digital-codes
/admin/customers
/admin/orders
/admin/payments
/admin/shipping-methods
Project Structure

Important folders:

app/Models
app/Enums
app/Filament/Resources
database/migrations
database/seeders
resources/views
routes
config

Main models:

Language
Country
Currency
Setting
Brand
Company
Category
MediaFile
Product
ProductMedia
ProductOption
ProductVariant
ProductDigitalCode
Customer
Order
OrderItem
Payment
ShippingMethod
Database Design Summary

The project uses a modular commerce database structure.

Main tables:

users
roles
permissions
languages
countries
currencies
settings
brands
companies
categories
media_files
products
category_product
product_media
product_options
product_variants
product_digital_codes
customers
orders
order_items
payments
shipping_methods
Future Development Plan

Planned next modules:

1. Invoices Module
Invoice number
Linked order
Customer
Invoice status
PDF generation
Tax details
2. Coupons Module
Coupon code
Discount type
Discount value
Usage limits
Expiration date
3. Cart Module
Shopping cart
Cart items
Guest/customer cart
Checkout process
4. Public Storefront
Product listing
Product details
Categories page
Search and filters
Cart and checkout
5. AI Features

Future AI features may include:

AI product description generation
AI SEO title generation
AI alt text for images
AI product image analysis
AI recommendations
Smart product tagging
6. Payment Gateway Integration

Planned integrations:

PayPlus
Stripe
PayPal
Bank transfer verification
7. Shipping Integration

Future support:

External shipping providers
Tracking numbers
Delivery status
Shipping labels
Notes

This project is currently under active development.

Some modules are already functional in the admin panel, while others are planned for the next development phases.

The project is designed with a modular structure so each business feature can grow independently.

Developer

Alaa Almadadha
Software Engineer & Computer Science Teacher
GitHub: https://github.com/madadha

License

This project is currently private/internal unless a license is added later.


# Smart Commerce Platform

Smart Commerce Platform is a modern Laravel-based e-commerce system designed to support dynamic products, multilingual storefronts, customer accounts, carts, checkout, orders, digital codes, inventory deduction, product reviews, wishlist, and a premium customer experience.

The platform is built with a clean modular structure, Laravel best practices, Filament Admin Panel, responsive Blade storefront pages, multilingual support, and a scalable database-driven architecture.

---

## Project Status

Current development stage:

```text
✅ Storefront Home Page
✅ Product Listing Page
✅ Product Details Page
✅ Cart System
✅ Checkout System
✅ Convert Cart To Order
✅ Stock Deduction After Checkout
✅ Digital Codes Assignment
✅ Signed Order Details Page
✅ Order Tracking Page
✅ Customer Order History Page
✅ Premium Customer Account Dashboard
✅ Customer Wishlist Page
✅ Product Reviews & Ratings
✅ Product Rating Stars On Product Cards
✅ Unified Storefront Design Direction
```

---

## Main Technologies

- Laravel 13
- PHP 8.4
- MySQL
- Blade Templates
- Filament Admin Panel
- Laravel Authentication
- Eloquent ORM
- Laravel Migrations
- Laravel Routes
- Responsive CSS
- Multilingual UI
- RTL / LTR Support

---

## Core Features

### 1. Dynamic Storefront

The storefront is fully dynamic and database-driven.

It includes:

- Homepage
- Featured categories
- Featured products
- Latest products
- Product listing
- Product filtering
- Product search
- Product sorting
- Product details
- Related products
- Product media
- Product variants
- Brand display
- Currency display

---

### 2. Multilingual Support

The system supports:

```text
Arabic
Hebrew
English
```

The storefront detects and stores the selected language using:

```php
lang=ar
lang=he
lang=en
```

RTL support is enabled for Arabic and Hebrew.

LTR support is enabled for English.

---

### 3. Cart System

The cart module supports:

- Add product to cart
- Update quantity
- Remove cart item
- Cart totals
- Cart status
- Active cart session
- Product image display
- Product name localization
- Product price calculation

---

### 4. Checkout System

The checkout page supports:

- Customer name
- Email
- Phone
- City
- Address
- Shipping method
- Payment method
- Customer notes
- Order summary
- Validation
- Error handling
- Success redirection

---

### 5. Convert Cart To Order

When the customer places an order, the system converts the active cart into a real order.

The process includes:

- Creating or updating customer
- Creating order
- Creating order items
- Saving totals
- Saving customer notes
- Saving shipping information
- Creating pending payment
- Marking cart as converted

---

### 6. Inventory Deduction

After checkout, the system automatically handles inventory.

Supported behavior:

```text
✅ Deduct product stock
✅ Deduct variant stock
✅ Validate stock before checkout
✅ Prevent checkout if stock is not enough
✅ Add inventory notes to order items
```

Example order item note:

```text
Stock deducted from product. Quantity: 1
```

---

### 7. Digital Codes

The system supports digital products and digital codes.

When a digital product is ordered:

```text
✅ Available digital codes are assigned
✅ Code status is changed to sold
✅ Order ID is attached if supported
✅ Order Item ID is attached if supported
✅ Sold date is saved if supported
```

---

### 8. Signed Order Details Page

After successful checkout, the customer is redirected to a secure signed order details page.

Route example:

```text
/store/orders/{order}?signature=...
```

The page displays:

- Order number
- Order status
- Payment status
- Ordered date
- Customer details
- Shipping method
- Order items
- Totals
- Digital codes if available

The page is protected using Laravel signed URLs.

---

### 9. Order Tracking Page

Customers can track their order without logging in.

Tracking requires:

```text
Order Number
Phone Number
```

Routes:

```text
GET  /store/track-order
POST /store/track-order/result
```

The tracking system searches the order safely using available database columns and customer relation.

---

### 10. Customer Order History

Logged-in customers can view their order history.

Route:

```text
/store/account/orders
```

The page displays:

- Order number
- Order date
- Order status
- Payment status
- Items count
- Grand total
- View details button

---

### 11. Customer Account Dashboard

A premium customer dashboard was added.

Route:

```text
/store/account
```

Dashboard includes:

- Welcome section
- Customer profile card
- Total orders
- Total spent
- Pending orders
- Completed orders
- Unpaid orders
- Latest order
- Recent orders
- Quick actions
- Links to order history, tracking page, products, and profile settings

---

### 12. Wishlist System

A customer wishlist module was added.

Routes:

```text
GET     /store/wishlist
POST    /store/wishlist
POST    /store/wishlist/{product}/toggle
DELETE  /store/wishlist/{product}
```

Features:

```text
✅ Add product to wishlist
✅ Remove product from wishlist
✅ Toggle wishlist item
✅ Wishlist page
✅ Heart button on product cards
✅ Heart button on product details page
✅ Auth protection
```

Database table:

```text
customer_wishlists
```

Main fields:

```text
id
user_id
product_id
created_at
updated_at
```

---

### 13. Product Reviews & Ratings

A moderated product reviews system was added.

Features:

```text
✅ Customer can submit review
✅ Guest can submit review
✅ Name field
✅ Email field
✅ Star rating from 1 to 5
✅ Optional comment
✅ Review status is pending by default
✅ Admin can approve review
✅ Admin can reject review
✅ Only approved reviews appear publicly
✅ Reviews appear on product details page
✅ Rating summary appears on product cards
```

Database table:

```text
product_reviews
```

Main fields:

```text
id
product_id
user_id
reviewer_name
reviewer_email
rating
comment
status
locale
approved_by
approved_at
rejected_at
ip_address
user_agent
is_active
sort_order
created_at
updated_at
```

---

### 14. Product Rating Stars On Cards

Product cards now display:

```text
Star rating
Average rating
Reviews count
```

This appears in:

- Product listing page
- Homepage featured products
- Homepage latest products
- Related products

Partial file:

```text
resources/views/storefront/products/partials/rating-summary.blade.php
```

---

## Admin Panel Features

The project uses Filament Admin Panel.

Admin modules include:

- Products
- Categories
- Brands
- Orders
- Customers
- Payments
- Shipping
- Invoices
- Coupons
- Product Reviews
- Digital Codes
- Product Variants
- Inventory-related data

---

## Product Reviews Admin

A Filament resource was added for product reviews.

Admin can:

```text
✅ View reviews
✅ Create reviews
✅ Edit reviews
✅ Delete reviews
✅ Approve reviews
✅ Reject reviews
```

Resource path:

```text
app/Filament/Resources/ProductReviews
```

---

## Design System

The storefront uses a unified design direction.

### Storefront Public Pages

Style direction:

```text
Light UI
Blue primary color
White product cards
Soft shadows
Premium black/gold header and footer accents
```

### Customer Account Area

Style direction:

```text
Dark premium UI
Gold accent
Blue secondary color
Dashboard-style cards
Luxury customer experience
```

### Important CSS Structure

The main CSS file should stay as the base file:

```text
public/css/storefront/storefront.css
```

Design overrides should be placed separately:

```text
public/css/storefront/design-overrides.css
```

The correct order inside layout is:

```blade
<link rel="stylesheet" href="{{ asset('css/storefront/storefront.css') }}?v={{ filemtime(public_path('css/storefront/storefront.css')) }}">

<link rel="stylesheet" href="{{ asset('css/storefront/design-overrides.css') }}?v={{ filemtime(public_path('css/storefront/design-overrides.css')) }}">
```

Do not replace the full storefront.css with override-only CSS.

---

## Important Routes

### Storefront

```text
GET /store
GET /store/products
GET /store/products/{slug}
GET /store/cart
GET /store/checkout
POST /store/checkout/place-order
```

### Orders

```text
GET /store/orders/{order}
GET /store/track-order
POST /store/track-order/result
GET /store/account/orders
```

### Account

```text
GET /store/account
```

### Wishlist

```text
GET /store/wishlist
POST /store/wishlist
POST /store/wishlist/{product}/toggle
DELETE /store/wishlist/{product}
```

### Reviews

```text
POST /store/products/{product}/reviews
```

---

## Setup Instructions

Clone the project:

```bash
git clone https://github.com/madadha/smart-commerce-platform.git
```

Enter project folder:

```bash
cd smart-commerce-platform
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create environment file:

```bash
cp .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Run seeders if available:

```bash
php artisan db:seed
```

Create storage link:

```bash
php artisan storage:link
```

Clear cache:

```bash
php artisan optimize:clear
```

Run the project:

```bash
php artisan serve
```

---

## Useful Development Commands

Clear all caches:

```bash
php artisan optimize:clear
```

Check routes:

```bash
php artisan route:list
```

Check wishlist routes:

```bash
php artisan route:list | findstr wishlist
```

Check review routes:

```bash
php artisan route:list | findstr reviews
```

Check account routes:

```bash
php artisan route:list | findstr account
```

Run tinker:

```bash
php artisan tinker
```

---

## Testing Reviews Quickly

Create a review from the storefront, then approve it using Tinker:

```bash
php artisan tinker
```

```php
$review = \App\Models\ProductReview::latest()->first();
$review->approve(1);
exit
```

---

## Testing Latest Order

```bash
php artisan tinker
```

```php
$order = \App\Models\Order::latest()->first();

$order->order_number;
$order->items()->count();
$order->items()->pluck('notes');
$order->grand_total;

exit
```

---

## Testing Digital Codes

```bash
php artisan tinker
```

```php
\DB::table('product_digital_codes')
    ->select('status', \DB::raw('count(*) as total'))
    ->groupBy('status')
    ->get();

exit
```

---

## Latest Development Milestones

### Stage 39

Stock and digital codes deduction after checkout.

### Stage 40

Signed customer order details page.

### Stage 41

Order tracking page using order number and phone.

### Stage 42

Customer order history page.

### Stage 43

Premium customer account dashboard.

### Stage 44

Customer wishlist page and heart buttons.

### Stage 45

Moderated product reviews and ratings.

### Stage 46

Product rating summary displayed on product cards.

---

## Git Commands

After any successful update:

```bash
git status
git add .
git commit -m "Update smart commerce storefront features"
git push
```

Recommended commit for latest updates:

```bash
git add .
git commit -m "Add wishlist reviews ratings and customer dashboard updates"
git push
```

---

## Project Goal

The goal of Smart Commerce Platform is to become a powerful, scalable, multilingual, modern e-commerce platform that supports both physical and digital products while offering a premium customer experience and a clean management system for store owners.

---

## Author

Developed by Alaa AlMadadha.

```text
Smart Commerce Platform
Modern Laravel E-Commerce System
```