# The Clearing House — Charlotte Estate & Cleanout Directory

A directory website listing estate sale cleanout, junk removal, and hoarding/biohazard
cleanup companies serving Charlotte, NC and the surrounding metro (including Rock Hill, SC).

Published by **Dominate Your Brand LLC**, 30 Gould St Suite R, Sheridan, WY 82801.
Contact: Curtis Waters · info@dominatewithbrand.com · (704) 345-2964

## What's in this repo

- `index.html` — the entire site (homepage, category pages, listing pages,
  About, Pricing, Contact, FAQ, and Privacy Policy). It's a single static
  HTML file with inline CSS and JS, using hash-based client-side routing
  (`#/`, `#/category/...`, `#/listing/...`, `#/about`, `#/pricing`,
  `#/contact`, `#/faq`, `#/privacy`).
- `hero_estatedir1.webp` — the homepage hero image. Must stay in the same folder
  as `index.html` (referenced by a relative path).
- `api/` — PHP endpoints backing the listing data and contact form
  (`listings.php`, `contact.php`, `db.php`, `config.example.php`). Requires
  PHP + MySQL, e.g. a cPanel hosting plan. Paid upgrades checkout happens
  entirely on Whop (see Payments below) — there's no payment code in `api/`.
- `sql/` — `schema.sql` (creates the `businesses` table) and `seed.php` (one-time
  import of the original listing data).
- `admin/` — password-protected panel (`admin/index.php`) for adding, editing,
  deleting, and toggling Verified/Featured/Category Sponsor status on
  listings without touching SQL directly.
- `sitemap.xml`, `robots.txt`, `llms.txt` — SEO/discovery files for
  `clearinghousecharlotte.com` (the site's canonical domain). See SEO below
  for why these are deliberately minimal.

## Running locally

The front end is a static file, but it now fetches listing data from the PHP
API, so serve it with PHP's built-in server rather than a plain static server:

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000`. You'll need a local MySQL database and an
`api/config.php` (see Deploying below) for `api/listings.php` to return real
data — without it, the site falls back to the built-in default listings with
`verified`, `featured`, and `category_sponsor` all false.

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
6. **Configure the contact form**: set `contact_from_email` in
   `api/config.php` to a real mailbox on your domain (e.g.
   `noreply@clearinghousecharlotte.com`, created in cPanel → Email Accounts).
   Most mail servers reject or spam-flag mail whose From address isn't on the
   sending domain, so this needs to match your domain, not
   `info@dominatewithbrand.com`.
7. **Set the admin password**: generate a hash for your chosen password with
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

Listing data, including Verified/Featured/Category Sponsor status, is
stored in MySQL and served through `api/listings.php`, so it persists
across visitors and reloads — this replaces the old `window.storage`
approach (which only ever worked inside a Claude.ai artifact).

## Payments

Checkout for all three paid upgrades happens entirely on **Whop**, not on
this site. Each pricing card on `#/pricing` is a plain link (opening in a
new tab, via `WHOP_LINKS` near the top of `index.html`'s `<script>`) to a
pre-built Whop product page:

- Verified Listing — $49/mo
- Featured Listing — $49/mo
- Category Sponsor — $250/mo

There is no Stripe integration, no checkout API endpoint, and no webhook —
`api/create-checkout-session.php` and `api/stripe-webhook.php` were removed
when this moved to Whop. That also means **there's no automatic link
between a Whop purchase and this site's database**: after a business pays
on Whop, the `verified`, `featured`, or `category_sponsor` flag has to be
turned on by hand in `/admin/`. The pricing page's "How this works" copy
tells buyers to reach out via the contact page once they've paid, which is
the trigger for that manual step.

If this becomes a volume/reliability problem, Whop does support webhooks
for completed purchases — wiring one up (similar in spirit to the old
Stripe webhook: verify the request, look up which business and tier it's
for, then apply the flags) would automate this, but nothing like that
exists yet.

## Contact form

Submitting the contact form now sends a real email via `api/contact.php`,
using PHP's built-in `mail()` (the server's local MTA) rather than opening
the visitor's own email client. It includes a hidden honeypot field for
basic spam filtering and strips newlines from user input before it reaches
any email header, to prevent header injection. See the Deploying steps above
for the one required config value (`contact_from_email`).

## Admin panel

`/admin/` is a small password-protected panel (single PHP file, no JS
framework) for listing management: add, edit, delete, and toggle Verified,
Featured, and Category Sponsor directly, without writing SQL. Checking
Category Sponsor for a business is blocked with an error if another
business in the same category already has it — the same exclusivity rule
`create-checkout-session.php` enforces for paid signups. The panel is
protected by one shared password (`admin_password_hash` in `api/config.php`)
plus a CSRF token on every write and a short delay after repeated failed
logins — reasonable for a single site owner, but not meant for multiple
admin users or a public-facing login at scale.

## Pricing page

`#/pricing` presents three paid upgrades on top of the free basic listing
every business gets: Verified Listing ($49/mo), Featured Profile (+$49/mo,
requires Verified), and Category Sponsor ($250/mo, exclusive — one per
category, includes the other two). "Get Verified" and "Advertise" CTAs in
the header and footer link here. Each plan's button opens its Whop checkout
page in a new tab — see Payments above for how that connects (or doesn't
yet, automatically) back to this site's data.

## SEO

The site's canonical domain, `https://clearinghousecharlotte.com/`, is
hardcoded in a few places rather than derived at runtime:

- `<link rel="canonical">` and the Open Graph/Twitter meta tags in
  `index.html`'s `<head>`.
- The `SITE_URL` constant near the top of `index.html`'s `<script>`, used to
  build every URL in the JSON-LD structured data (Organization, WebSite,
  ItemList of businesses, FAQPage).
- `sitemap.xml`, `robots.txt` (which points at the sitemap), and `llms.txt`.

If the domain ever changes, all of these need updating — search `clearinghousecharlotte.com` across the repo.

**Sitemap limitation, on purpose**: `sitemap.xml` lists exactly one URL (the
homepage). This isn't an oversight — the whole site is one HTML document
using hash-based client routing (`#/category/...`, `#/listing/...`), and URL
fragments are never sent to the server, so search engines can't crawl or
index them as separate pages. That means category and listing pages
currently can't rank individually in search results, which matters a lot for
a local directory whose value is largely in those listing pages showing up
for searches like a specific company's name. Fixing this for real would mean
giving categories and listings real server-side paths (e.g. via a small PHP
router reading from the `businesses` table) instead of hash fragments — a
bigger change than adding a sitemap, and worth doing deliberately rather than
as a side effect of this task.

`llms.txt` works around the same limitation for AI/LLM tools by embedding
the actual business data (name, phone, city, website, one-liner) as plain
text rather than only linking to hash-fragment URLs those tools likely can't
fetch. It's a manually-maintained snapshot, so it will drift from the live
database over time as listings are added or edited via `/admin/`.

## Known limitations to address before going live

- **No automatic payment → status link**: since checkout happens on Whop
  with no webhook back to this site, every Verified/Featured/Category
  Sponsor activation is a manual step in `/admin/` after someone notices a
  Whop sale. This works for low volume but doesn't scale, and creates a
  window where a business has paid but doesn't yet show its upgrade.
- **Category Sponsor exclusivity is admin-side only**: `/admin/` blocks
  checking Category Sponsor for a business if another one in the same
  category already has it, but since Whop doesn't know about this
  exclusivity rule at all, nothing stops two businesses in the same
  category from both buying the Category Sponsor product before the first
  purchase gets manually applied. Whoever gets flagged first in `/admin/`
  wins; the other would need a manual refund/conversation.
- **No subscription-lifecycle handling**: cancellations or failed renewals
  on Whop don't automatically un-set a business's flags — that's also a
  manual `/admin/` step for now.
