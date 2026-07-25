# The Clearing House — Charlotte Estate & Cleanout Directory

A directory website listing estate sale cleanout, junk removal, and hoarding/biohazard
cleanup companies serving Charlotte, NC and the surrounding metro (including Rock Hill, SC).

Published by **Dominate Your Brand LLC**, 30 Gould St Suite R, Sheridan, WY 82801.
Contact: Curtis Waters · info@dominatewithbrand.com · (704) 345-2964

## What's in this repo

- `index.html` — the entire site (homepage, category pages, listing pages, About,
  Contact, FAQ, and Privacy Policy). It's a single static HTML file with inline
  CSS and JS, using hash-based client-side routing (`#/`, `#/category/...`,
  `#/listing/...`, `#/about`, `#/contact`, `#/faq`, `#/privacy`).
- `hero_estatedir1.webp` — the homepage hero image. Must stay in the same folder
  as `index.html` (referenced by a relative path).
- `api/` — PHP endpoints backing the listing data, payments, and contact
  form (`listings.php`, `create-checkout-session.php`, `stripe-webhook.php`,
  `contact.php`, `db.php`, `config.example.php`). Requires PHP + MySQL, e.g.
  a cPanel hosting plan. Talks to Stripe directly over cURL — no
  Composer/SDK needed.
- `sql/` — `schema.sql` (creates the `businesses` table) and `seed.php` (one-time
  import of the original listing data).
- `admin/` — password-protected panel (`admin/index.php`) for adding, editing,
  deleting, and featuring/unfeaturing listings without touching SQL directly.

## Running locally

The front end is a static file, but it now fetches listing data from the PHP
API, so serve it with PHP's built-in server rather than a plain static server:

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000`. You'll need a local MySQL database and an
`api/config.php` (see Deploying below) for `api/listings.php` to return real
data — without it, the site falls back to the built-in default listings with
`featured` always false.

## Deploying (cPanel)

The front end (`index.html` + the hero image) needs no build step, but the
listing data now lives in MySQL via the PHP endpoints in `api/`, so a couple
of one-time setup steps are required in addition to uploading files.

1. **Upload the files**: via File Manager/FTP or cPanel's Git Version Control
   (Settings → clone this repo with the repository path set to the desired
   document root), get `index.html`, `hero_estatedir1.webp`, `api/`, and
   `sql/` onto the server, e.g. into `public_html`.
2. **Create the database**: in cPanel → MySQL® Databases, create a database
   and a user with full privileges on it.
3. **Configure the connection**: copy `api/config.example.php` to
   `api/config.php` and fill in the database name, username, and password
   from step 2. `api/config.php` is gitignored — it's never committed and
   must be created directly on the server (or copied up separately).
4. **Create the table and seed data**: run `sql/schema.sql` against the
   database (via phpMyAdmin's Import tab), then run `sql/seed.php` once
   (either `php sql/seed.php` over SSH, or by visiting the file once in a
   browser) to load the original listing data. Re-running `seed.php` is
   safe — it skips rows that already exist.
5. Once seeded, consider moving or deleting `sql/seed.php` so it isn't left
   reachable on the live site indefinitely.
6. **Configure Stripe**: add your Stripe secret key to `api/config.php`
   (`stripe_secret_key`, test-mode `sk_test_...` to start). In the Stripe
   Dashboard → Developers → Webhooks, add an endpoint pointing at
   `https://yourdomain.com/api/stripe-webhook.php`, subscribed to the
   `checkout.session.completed` event, then copy its signing secret into
   `api/config.php` as `stripe_webhook_secret`.
7. **Configure the contact form**: set `contact_from_email` in
   `api/config.php` to a real mailbox on your domain (e.g.
   `noreply@yourdomain.com`, created in cPanel → Email Accounts). Most mail
   servers reject or spam-flag mail whose From address isn't on the sending
   domain, so this needs to match your domain, not `info@dominatewithbrand.com`.
8. **Set the admin password**: generate a hash for your chosen password with
   `php -r "echo password_hash('your-password-here', PASSWORD_DEFAULT), \"\n\";"`
   and put the result in `api/config.php` as `admin_password_hash` (the
   plain-text password itself never goes in the file). Make sure the site
   has HTTPS (cPanel's AutoSSL is usually one click) before using `/admin/`,
   since the login form has no other protection against the password being
   read off the network.

No `.htaccess` rewrite rules are needed for routing — the site uses
hash-based client-side routing (`#/`, `#/category/...`, etc.), so every route
resolves to the same `index.html`.

## Data persistence

Listing data, including which businesses are "Featured", is stored in MySQL
and served through `api/listings.php`, so it persists across visitors and
reloads — this replaces the old `window.storage` approach (which only ever
worked inside a Claude.ai artifact).

## Payments

The "Get Featured" flow uses real Stripe Checkout: clicking "Continue to
secure checkout" calls `api/create-checkout-session.php`, which creates a
$30/mo subscription Checkout Session via Stripe's REST API and redirects the
browser to Stripe's own hosted payment page — card details are never
collected on this site. After a successful payment, Stripe calls
`api/stripe-webhook.php`, which verifies the request actually came from
Stripe (via signature check) before setting `featured = 1` for that
business. The browser is then returned to the site with a confirmation
message, though the DB update happens independently via the webhook, so
there can be a short (usually sub-second) delay before the listing shows as
Featured on reload.

Both endpoints call Stripe's API directly over cURL rather than the official
`stripe-php` SDK, so no Composer install is required on the server.

## Contact form

Submitting the contact form now sends a real email via `api/contact.php`,
using PHP's built-in `mail()` (the server's local MTA) rather than opening
the visitor's own email client. It includes a hidden honeypot field for
basic spam filtering and strips newlines from user input before it reaches
any email header, to prevent header injection. See the Deploying steps above
for the one required config value (`contact_from_email`).

## Admin panel

`/admin/` is a small password-protected panel (single PHP file, no JS
framework) for listing management: add, edit, delete, and toggle Featured
directly, without writing SQL. It's protected by one shared password
(`admin_password_hash` in `api/config.php`) plus a CSRF token on every write
and a short delay after repeated failed logins — reasonable for a single
site owner, but not meant for multiple admin users or a public-facing login
at scale.

## Known limitations to address before going live

- **Subscription lifecycle**: cancellations, failed renewal payments, or
  chargebacks aren't handled — the webhook only reacts to
  `checkout.session.completed`. Un-featuring a business when its
  subscription lapses would need additional webhook events (e.g.
  `customer.subscription.deleted`, `invoice.payment_failed`) and a way to
  match a subscription back to a business (e.g. storing the Stripe
  subscription ID on the row when it's first created).
