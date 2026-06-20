# Smart Commerce Platform

Smart Commerce Platform is a Laravel 13 smart commerce system for managing products, customers, carts, checkout, orders, invoices, stock, multilingual storefront pages, and advanced admin order workflows.

The system is built with Laravel, MySQL, Filament Admin Panel, Livewire, Laravel Breeze, Spatie Roles & Permissions, Blade, Tailwind CSS, Vite, mPDF, and custom responsive storefront CSS.

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
| Authentication | Laravel Breeze |
| Permissions | Spatie Laravel Permission |
| Frontend | Blade, Tailwind CSS, Vite, custom CSS |
| PDF | mPDF |
| QR | Simple QR Code |
| Languages | Arabic, Hebrew, English |
| Local Environment | Laragon / Windows |
| Main Branch | `main` |

---

## Latest Completed Stages

- Stage 73: Storefront customer authentication UI upgrade.
- Stage 74: Customer profile, saved customer data, and checkout saved info.
- Stage 75: Storefront mobile and customer area polish.
- Stage 76: Customer type mode and reseller/company request logic.
- Stage 77: Move logout action to customer quick actions.
- Stage 78: Multilingual auth, logout, and header fix.

---

## Storefront Features

- Public homepage.
- Product listing page.
- Product details page.
- Header search connected to products page.
- Advanced filters: category, brand, price, rating, stock, and sale.
- Product cards with badges, ratings, stock status, compare, and wishlist actions.
- Cart page.
- Checkout page.
- Order success page.
- Customer order tracking.
- Customer account dashboard.
- Customer order history.
- Customer wishlist.
- Product comparison page.
- Responsive design for desktop and mobile.
- RTL support for Arabic and Hebrew.
- LTR support for English.

---

## Multilingual Support

The storefront supports:

- Arabic: `?lang=ar`
- Hebrew: `?lang=he`
- English: `?lang=en`

Arabic and Hebrew use RTL layout. English uses LTR layout. Storefront controllers resolve the locale from the request, store it in session, set the Laravel app locale, and pass `locale` and `direction` to the views.

Important rule: multilingual Blade views should use translation keys and should not contain fixed Arabic text unless it is intentionally language-specific.

---

## Customer Authentication

- Custom storefront login page.
- Custom storefront registration page.
- Forgot password page.
- Reset password page.
- Confirm password page.
- Verify email page.
- Guest header links for login and registration.
- Authenticated header links for account, orders, wishlist, and compare.
- Logout is placed inside the account dashboard quick actions.

---

## Customer Account Area

Route:

```text
/store/account
```

Features:

- Customer welcome hero.
- Customer profile card.
- Total orders.
- Total spending.
- Pending orders.
- Completed orders.
- Unpaid orders.
- Recent orders list.
- Latest order card.
- Quick actions card.
- Multilingual interface.
- Mobile-friendly layout.

Quick actions include:

- My Orders.
- Track Order.
- Browse Products.
- Profile Settings.
- Logout.

---

## Customer Profile

Route:

```text
/profile
```

Features:

- Storefront-style profile page.
- Profile information update.
- Saved customer address information.
- Password update.
- Account delete section.
- Responsive mobile design.

Saved customer information can include:

- Full name.
- Email.
- Phone.
- WhatsApp.
- City.
- Area.
- Street.
- Building number.
- Apartment number.
- Address notes.

---

## Customer Type System

Supported customer types:

- Regular Customer.
- Reseller.
- VIP Customer.
- Company.

Public registration always creates a regular customer account. Customers cannot directly choose Reseller, VIP, or Company during public registration. The admin can update the customer type from the Filament admin panel.

Configuration examples:

```env
CUSTOMER_TYPE_MODE=regular
CUSTOMER_ALLOW_RESELLER_REQUESTS=false
CUSTOMER_ALLOW_COMPANY_REQUESTS=false
CUSTOMER_ALLOW_VIP_REQUESTS=false
```

```env
CUSTOMER_TYPE_MODE=reseller
CUSTOMER_ALLOW_RESELLER_REQUESTS=true
```

After changing customer type settings:

```bash
php artisan config:clear
```

---

## Product Management

- Physical products.
- Digital products.
- Digital cards.
- Digital files.
- Services.
- Subscriptions.
- Bundles.
- Product variants.
- Product options.
- Multi-language product names and descriptions.
- SKU and barcode.
- Product images and galleries.
- Brands and categories.
- Currency support.
- Sale price and regular price.
- Product status.
- Featured products.
- Product reviews.
- Product questions and answers.
- Product badges.
- Stock management.

---

## Cart and Checkout

- Add products to cart.
- Update cart item quantity.
- Remove cart item.
- Prevent out-of-stock add-to-cart.
- Validate quantity before checkout.
- Create order from active cart.
- Deduct stock after successful order.
- Clear cart after order creation.
- Redirect customer to signed order page.
- Save customer checkout information for future orders.

---

## Orders

Storefront order features:

- Signed order details page.
- Signed invoice download route.
- Order tracking form.
- Customer order history.
- Order status and payment status display.
- Digital code display logic.

Admin order features:

- Order management.
- Order status history.
- Status history modal.
- Order notes.
- Order attachments.
- Order activity log.
- Internal order tasks.
- Order reminders.
- Follow-up board.
- Order priority system.

Supported order statuses:

- Pending.
- Processing.
- Shipped.
- Completed.
- Cancelled.
- Refunded.

---

## Invoices

- Customer invoice PDF download.
- Signed invoice route.
- Arabic, Hebrew, and English support.
- RTL and LTR support.
- Currency display fix.
- QR code inside invoice.
- Signed order link inside invoice.
- mPDF rendering.

---

## Emails

- Order created email.
- Order completed email.
- Email includes order summary.
- Email includes signed order link.
- Email includes signed invoice link.
- Email sending is safely wrapped so order actions do not fail if email delivery fails.

---

## Admin Panel

Filament admin panel modules include:

- Products.
- Categories.
- Brands.
- Customers.
- Orders.
- Shipping methods.
- Reviews.
- Questions.
- Stock tools.
- Invoices.
- Customer type management.
- Order workflow tools.

Order edit page can include:

- Status History.
- Notes.
- Attachments.
- Activity Log.
- Tasks.
- Reminders.
- Follow-up Board.
- Priority.

---

## Important Routes

```php
Route::get('/', [StorefrontController::class, 'home'])->name('storefront.home');
Route::get('/store/products', [StorefrontController::class, 'products'])->name('storefront.products.index');
Route::get('/store/cart', [StorefrontCartController::class, 'index'])->name('storefront.cart.index');
Route::get('/store/checkout', [StorefrontCheckoutController::class, 'index'])->name('storefront.checkout.index');
Route::get('/store/account', [StorefrontOrderController::class, 'dashboard'])->middleware('auth')->name('storefront.account.dashboard');
Route::get('/store/account/orders', [StorefrontOrderController::class, 'history'])->middleware('auth')->name('storefront.orders.history');
Route::get('/store/orders/{order}', [StorefrontOrderController::class, 'show'])->middleware('signed')->name('storefront.orders.show');
Route::get('/store/orders/{order}/invoice', [StorefrontOrderController::class, 'invoice'])->middleware('signed')->name('storefront.orders.invoice');
```

---

## Main Database Tables

- `users`
- `customers`
- `products`
- `product_reviews`
- `product_questions`
- `wishlists`
- `compare_items`
- `carts`
- `cart_items`
- `orders`
- `order_items`
- `order_status_histories`
- `order_notes`
- `order_attachments`
- `order_activities`
- `order_tasks`
- `order_reminders`
- `shipping_methods`
- `currencies`
- `product_digital_codes`

---

## Useful Commands

```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
composer dump-autoload
php artisan route:list
php artisan migrate
php artisan migrate:status
php artisan storage:link
npm run build
```

---

## Git Workflow

```bash
git status
git add .
git commit -m "Update storefront customer account and multilingual auth flow"
git pull --rebase origin main
git push origin main
```

If there is nothing new to commit locally:

```bash
git pull --rebase origin main
git push origin main
```

---

## Security Notes

- Keep `.env` out of Git.
- Signed routes protect customer order and invoice links.
- Public registration creates regular customers only.
- Admin controls customer type upgrades.
- Internal notes, attachments, tasks, reminders, priorities, and activity logs are admin-only features.

---

## Author

Developed by Alaa AlMadadha.

---

## License

This project is for educational and development purposes.
