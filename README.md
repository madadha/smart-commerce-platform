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