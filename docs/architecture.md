\# Smart Commerce Platform - Architecture



\## Project Name

Smart Commerce Platform



\## Project Goal

Build a modern AI-ready multilingual modular e-commerce platform.



The system is not only a digital cards store. It must support:

\- General products

\- Digital cards

\- Physical products

\- Digital files

\- Services

\- Subscriptions

\- Resellers / agents

\- Multi-language content

\- Multi-currency

\- Dynamic storefront

\- Product display control

\- Shipping and delivery

\- Online and manual payments

\- QR codes and barcodes

\- Social sharing

\- Future AI features



\---



\## Main Technology Stack



\- Laravel 13

\- PHP 8.4

\- MySQL

\- Filament Admin Panel

\- Livewire

\- Blade

\- Tailwind CSS

\- Alpine.js

\- GitHub

\- VS Code

\- Laragon



\---



\## Development Rules



\### 1. No Hard Coding

Do not hard-code:

\- Languages

\- Currencies

\- Product types

\- Shipping methods

\- Payment methods

\- Storefront sections

\- Theme colors

\- Social platforms



All must be controlled from the database and admin panel when possible.



\### 2. Thin Controllers

Controllers must stay simple.



Controllers should only:

\- Receive request

\- Validate request

\- Call Service or Action

\- Return response or view



Business logic must not be placed inside controllers.



\### 3. Use Services

Business logic should be placed inside service classes.



Examples:

\- ProductService

\- CurrencyService

\- LanguageService

\- CartService

\- OrderService

\- ShippingService

\- PaymentService

\- ThemeService

\- ShareService

\- QrCodeService

\- ResellerService

\- AIService



\### 4. Use Actions

Important operations should be placed inside Action classes.



Examples:

\- CreateProductAction

\- UpdateProductAction

\- AddToCartAction

\- CreateOrderAction

\- CalculateOrderTotalsAction

\- GenerateProductQrAction

\- ReserveDigitalCodeAction



\### 5. Use Enums

Fixed values must use Enums.



Examples:

\- ProductType

\- ProductStatus

\- OrderStatus

\- PaymentStatus

\- ShippingMethodType

\- ShipmentStatus

\- LanguageDirection

\- CurrencySymbolPosition

\- MediaRole

\- SharePlatform



\### 6. Use Form Requests

Validation must be placed in Form Request classes when the form is large.



Examples:

\- StoreProductRequest

\- UpdateProductRequest

\- CheckoutRequest

\- StoreCategoryRequest



\### 7. Use Policies

Permissions must be controlled using Policies and roles.



Examples:

\- ProductPolicy

\- OrderPolicy

\- UserPolicy

\- ResellerPolicy



\### 8. Use Jobs for Heavy Tasks

Heavy operations should run using Jobs and Queues.



Examples:

\- SendOrderEmailJob

\- GenerateProductImagesJob

\- AnalyzeProductImageJob

\- GenerateProductDescriptionJob

\- ProcessPaymentWebhookJob



\---



\## Main Modules



\### 1. Core Module

Responsible for:

\- Settings

\- General configuration

\- Activity logs

\- System helpers



\### 2. Users \& Permissions Module

Responsible for:

\- Users

\- Roles

\- Permissions

\- Admin

\- Manager

\- Employee

\- Reseller

\- Customer



\### 3. Localization Module

Responsible for:

\- Languages

\- RTL / LTR

\- Active languages

\- Default language

\- Translatable fields



Supported languages at first:

\- Arabic

\- Hebrew

\- English



Languages must be activated or deactivated from the admin panel.



\### 4. Countries \& Currencies Module

Responsible for:

\- Countries

\- Currencies

\- Exchange rates

\- Default currency

\- Active currencies

\- Currency formatting



Initial currencies:

\- ILS

\- JOD

\- AED

\- EGP

\- USD

\- SAR



\### 5. Companies Module

Responsible for:

\- Companies

\- Suppliers

\- Partners

\- Manufacturers

\- Service providers



\### 6. Brands Module

Responsible for:

\- Brands

\- Brand logo

\- Brand page

\- Brand products



Examples:

\- Apple

\- Samsung

\- Sony

\- PlayStation

\- Xbox



\### 7. Catalog Module

Responsible for:

\- Nested categories

\- Products

\- Product variants

\- Product options

\- Product specifications

\- Product notes

\- Product content blocks

\- Product tags



Categories must support unlimited nesting.



Example:

Electronics > Phones > iPhone > iPhone 16



\### 8. Media Module

Responsible for:

\- Product images

\- Gallery images

\- Banner images

\- QR images

\- Barcode images

\- Alt text

\- Metadata

\- Future AI image analysis



\### 9. Storefront Module

Responsible for:

\- Visitor website

\- Header

\- Footer

\- Mega menu

\- Home page

\- Category page

\- Product page

\- Cart page

\- Checkout page

\- Customer account



\### 10. Theme Module

Responsible for:

\- Logo

\- Favicon

\- Colors

\- Header style

\- Footer style

\- Product card style

\- Dark mode option

\- Mobile bottom navigation



\### 11. Product Display Manager Module

Responsible for controlling how products appear on the storefront.



It must support:

\- Product sections

\- Grid view

\- Slider view

\- Carousel view

\- Horizontal scroll

\- Manual products

\- Products by category

\- Products by brand

\- Latest products

\- Featured products

\- Discounted products

\- Best sellers



\### 12. Social Sharing Module

Responsible for:

\- WhatsApp sharing

\- Facebook sharing

\- Telegram sharing

\- X sharing

\- Email sharing

\- Copy link



\### 13. QR \& Barcode Module

Responsible for:

\- Product QR

\- Category QR

\- Order QR

\- Invoice QR

\- Reseller store QR

\- Coupon QR

\- Product barcode



\### 14. Short Links \& Referrals Module

Responsible for:

\- Short product links

\- Reseller referral links

\- Click tracking

\- Conversion tracking



\### 15. Cart Module

Responsible for:

\- Guest cart

\- User cart

\- Merge cart after login

\- Cart totals

\- Quantity update

\- Cart items



\### 16. Checkout Module

Responsible for:

\- Customer details

\- Shipping step

\- Payment step

\- Order review

\- Coupon apply

\- Order creation



\### 17. Shipping \& Delivery Module

Responsible for:

\- Home delivery

\- Pickup

\- Express delivery

\- Standard delivery

\- Free shipping

\- External company delivery

\- Shipping companies

\- Pickup locations

\- Shipping zones

\- Shipping rates

\- Shipments

\- Tracking



\### 18. Payments Module

Responsible for:

\- Payment methods

\- Payment gateways

\- Online payments

\- Manual payments

\- Wallet

\- Reseller credit

\- Transactions

\- Webhooks

\- Refunds



Payment providers will be integrated later.



\### 19. Orders Module

Responsible for:

\- Orders

\- Order items

\- Order status

\- Payment status

\- Shipping status

\- Order snapshots

\- Invoices



\### 20. Digital Codes Module

Responsible for:

\- Digital card codes

\- Code status

\- Code reservation

\- Code delivery after payment



\### 21. Resellers Module

Responsible for:

\- Reseller profiles

\- Reseller levels

\- Reseller stores

\- Reseller prices

\- Reseller commissions

\- Reseller referral links



\### 22. Reports Module

Responsible for:

\- Sales reports

\- Orders reports

\- Products reports

\- Reseller reports

\- Low stock reports

\- Payment reports



\### 23. Notifications Module

Responsible for:

\- Email notifications

\- Database notifications

\- Future WhatsApp / SMS notifications



\### 24. AI Ready Module

Responsible for future AI features:

\- Generate product descriptions

\- Translate products

\- Generate SEO

\- Analyze images

\- Generate tags

\- Generate FAQ

\- Smart search

\- Product recommendations

\- Admin assistant

\- Customer assistant



No external AI calls should be implemented in the first version.



\---



\## Database Design Rules



\### Translatable Fields

Use JSON fields for user-facing text.



Example:

```json

{

&#x20; "ar": "هاتف آيفون",

&#x20; "he": "אייפון",

&#x20; "en": "iPhone"

}

