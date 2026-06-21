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

## Production Completion Roadmap

This roadmap is the agreed execution order for taking the platform from its current development state to a safe production launch. Complete and verify each phase before moving to the next one.

### Current Readiness Snapshot

| Area | Estimated readiness | Notes |
| --- | ---: | --- |
| Storefront UI and localization | 85% | Responsive Arabic/Hebrew RTL and English LTR flows are available. |
| Catalog, media, options, and variants | 80% | Product gallery, video, options, variants, and digital codes are managed from Filament. |
| Cart and order creation | 65% | Main flow exists, but inventory and payment lifecycle hardening is required. |
| Inventory and digital fulfillment | 50% | Concurrency, reservation, cancellation, and release rules must be completed. |
| Live payments and refunds | 20% | Payment records exist; a real gateway and verified webhooks are not implemented yet. |
| Admin authorization and security | 45% | Roles exist, but resource-level policies and stronger admin controls are required. |
| Automated commerce coverage | 25% | Authentication and basic storefront tests pass; critical commerce flows need coverage. |
| Production operations | 50% | Deployment, queues, mail, backups, monitoring, and live credentials remain. |

Readiness percentages are planning estimates based on the current codebase, not release guarantees.

### Phase 1 — Checkout, Inventory, and Digital Codes

Target: make order creation transactional, concurrency-safe, and predictable.

- [x] Consolidate all checkout inventory handling into one service.
- [x] Remove duplicate product stock deduction during checkout.
- [x] Deduct stock from the selected variant instead of the base product when applicable.
- [x] Lock affected products, variants, and digital codes before validation and deduction.
- [x] Reserve digital codes when an order is created and mark them sold only after confirmed payment.
- [ ] Release reserved stock and digital codes after payment failure, cancellation, or reservation expiry.
- [ ] Define explicit inventory behavior for physical, digital, service, subscription, and bundle products.
- [ ] Centralize subtotal, coupon, tax, shipping, and grand-total calculations.
- [ ] Add tests for insufficient stock, last-item concurrency, variants, and digital-code allocation.

Exit criteria: no double deduction, no overselling under concurrent checkout, and no digital code delivered before payment.

### Phase 2 — Automated Quality Gates

Target: protect every critical commerce flow before integrating live money.

- [ ] Add cart tests for products, variants, quantities, prices, and SKU snapshots.
- [ ] Add checkout tests for guest and authenticated customers.
- [ ] Add coupon, tax, shipping, order-total, and currency tests.
- [ ] Add order, invoice, cancellation, refund, and digital-delivery tests.
- [ ] Add login, registration, password reset, throttling, and validation tests.
- [ ] Verify Arabic, Hebrew, and English locale behavior, including RTL/LTR direction.
- [ ] Add authorization tests for every admin role and protected resource.
- [ ] Add GitHub Actions for PHPUnit, Composer audit, PHP formatting, and frontend builds.

Exit criteria: all critical scenarios run automatically and must pass before a branch can be merged.

### Phase 3 — Live Payments

Target: integrate one production payment provider correctly before adding more gateways.

- [ ] Define a provider-independent payment gateway contract.
- [ ] Integrate the first selected live payment provider.
- [ ] Create and persist payment attempts with unique idempotency keys.
- [ ] Verify webhook signatures and reject invalid callbacks.
- [ ] Make webhook processing idempotent so duplicate events cannot duplicate payment or fulfillment.
- [ ] Support pending, paid, failed, cancelled, partially refunded, and refunded states.
- [ ] Support full and partial refunds with an audit trail.
- [ ] Add payment reconciliation and failed-webhook monitoring.
- [ ] Fulfill digital orders only after a verified paid event.

Exit criteria: successful, failed, cancelled, duplicated, delayed, and refunded payment scenarios pass in the provider sandbox.

### Phase 4 — Shipping and Fulfillment

Target: make physical and digital order delivery operationally complete.

- [ ] Add shipping zones and location-based rates.
- [ ] Support delivery and store-pickup methods.
- [ ] Validate shipping eligibility and cost on the server during checkout.
- [ ] Add shipment records, carrier, tracking number, and fulfillment timestamps.
- [ ] Define valid order and shipment status transitions.
- [ ] Send localized order, payment, shipment, cancellation, and digital-delivery emails.
- [ ] Prevent digital codes from appearing in public logs, notifications, or unauthorized screens.

Exit criteria: one physical and one digital test order can be completed end-to-end from checkout to fulfillment.

### Phase 5 — Authorization and Security

Target: protect customer data, administration, files, and privileged operations.

- [ ] Add Laravel policies for all Filament resources and sensitive actions.
- [ ] Define permissions for Super Admin, Admin, Orders Manager, Catalog Manager, and Support roles.
- [ ] Restrict refunds, digital-code access, role changes, settings, and exports to authorized roles.
- [ ] Enable multi-factor authentication for privileged admin accounts.
- [ ] Add an audit log for sensitive administrative changes.
- [ ] Validate uploaded file MIME types, extensions, sizes, and access rules.
- [ ] Review signed links, session security, rate limits, and password-reset behavior.
- [ ] Perform responsive browser testing for login and registration across supported locales.

Exit criteria: each role can access only its intended resources, and all sensitive actions are auditable.

### Phase 6 — Production Operations and Launch

Target: deploy a supportable, observable, and recoverable production system.

- [ ] Complete the [`docs/production-checklist.md`](docs/production-checklist.md) requirements.
- [ ] Configure production environment variables, HTTPS, secure cookies, and trusted proxies.
- [ ] Configure production database migrations and optimized indexes.
- [ ] Configure SMTP and verify localized transactional email delivery.
- [ ] Run a supervised queue worker and scheduled task runner.
- [ ] Configure automated database and uploaded-file backups and test restoration.
- [ ] Configure application error tracking, logs, uptime checks, and payment alerts.
- [ ] Build and cache production assets, configuration, routes, and views.
- [ ] Perform staging acceptance tests on mobile, tablet, and desktop.
- [ ] Prepare a rollback procedure and complete a final launch checklist review.

Exit criteria: staging passes the full acceptance suite, backups can be restored, monitoring is active, and rollback is documented.

### Initial Launch Scope

The first production release should focus on:

- Physical and digital products.
- Product media, options, and selectable variants.
- Guest and customer checkout.
- One live payment gateway.
- Delivery and store pickup.
- Inventory and digital-code fulfillment.
- Orders, invoices, localized notifications, and role-based administration.
- Arabic, Hebrew, and English storefronts.

Defer advanced subscriptions, multi-vendor marketplace features, reseller commissions, AI features, multiple payment gateways, referrals, and advanced analytics until the core launch is stable.

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
