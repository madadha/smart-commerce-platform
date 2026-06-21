# Production launch checklist

## Application

- Set `APP_ENV=production`, `APP_DEBUG=false`, the final HTTPS `APP_URL`, and a unique `APP_KEY`.
- Set `APP_TIMEZONE`, `APP_LOCALE`, and `APP_FALLBACK_LOCALE` for the target market.
- Configure the production database and run `php artisan migrate --force`.
- Run `php artisan storage:link`, then verify uploaded product images are publicly reachable.
- Run `php artisan optimize`, `php artisan filament:optimize`, and `php artisan view:cache` during deployment.

## Security

- Force HTTPS at the proxy/web-server and set `SESSION_SECURE_COOKIE=true` and `SESSION_ENCRYPT=true`.
- Replace every demo/admin password and remove accounts that are not required in production.
- Keep dependency auditing in deployment: `composer audit --no-interaction`.
- Back up the database and `storage/app/public` outside the application server.
- Restrict the admin panel by strong passwords and preferably MFA or an IP/VPN policy.

## Services

- Replace `MAIL_MAILER=log` with a production SMTP/API mail provider and test password reset and order emails.
- Run a persistent queue worker for `QUEUE_CONNECTION=database` (Supervisor/systemd on Linux).
- Add a cron entry for `php artisan schedule:run` once scheduled reminders/cleanup jobs are defined.
- Configure monitoring for HTTP errors, queue failures, disk usage, and expiring backups.

## Commerce

- The project records PayPal/Stripe as payment method labels, but a live gateway must be configured and verified before accepting card payments.
- Verify tax, shipping, currency rounding, stock deduction, refunds, and digital-code delivery against business rules.
- Test one complete physical order and one complete digital order in the production-like environment.

## Current automated checks

- Public storefront, products, product detail, cart, tracking, login, registration, password reset, and health routes return HTTP 200 locally.
- Security headers are enabled for web responses.
- Product YouTube embeds use `youtube-nocookie.com` and only render when enabled.
- The PHP test suite passes locally.
- Composer reports no known dependency vulnerability advisories after the targeted Guzzle updates.
