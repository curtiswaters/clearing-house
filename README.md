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
- `api/` — PHP endpoints backing the listing data (`listings.php`, `feature.php`,
  `db.php`, `config.example.php`). Requires PHP + MySQL, e.g. a cPanel hosting plan.
- `sql/` — `schema.sql` (creates the `businesses` table) and `seed.php` (one-time
  import of the original listing data).

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

No `.htaccess` rewrite rules are needed for routing — the site uses
hash-based client-side routing (`#/`, `#/category/...`, etc.), so every route
resolves to the same `index.html`.

## Data persistence

Listing data, including which businesses are "Featured", is now stored in
MySQL and served through `api/listings.php`, so it persists across visitors
and reloads — this replaces the old `window.storage` approach (which only
ever worked inside a Claude.ai artifact).

One gap remains: `api/feature.php`, which the "Get Featured" checkout calls
to mark a listing featured, has **no payment verification** — it's a stopgap
that trusts any request it receives. It exists only so Featured status has
somewhere real to persist while the payment flow is still mocked. See the
Payments item below; once real Stripe billing is wired up, `feature.php`
should be removed and featured status should only ever be set from a
verified Stripe webhook.

## Known limitations to address before going live

- **Payments**: the "Get Featured" checkout is a simulated Stripe flow for
  demo purposes — no real charge is processed, and `api/feature.php` (which
  it calls) has no payment verification. Wiring up real $30/mo billing
  requires Stripe Checkout Sessions plus a webhook that sets `featured = 1`
  only after a verified successful payment.
- **Contact form**: submits via `mailto:`, opening the visitor's own email
  client. It does not send email directly from a server.
- **Listing data editing**: adding/editing/removing listings currently
  requires direct SQL (e.g. via phpMyAdmin). A small admin page would let
  this happen without touching the database directly.
