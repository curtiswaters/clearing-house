# The Clearing House — Charlotte Estate & Cleanout Directory

A directory website listing estate sale cleanout, junk removal, and hoarding/biohazard
cleanup companies serving Charlotte, NC and the surrounding metro (including Rock Hill, SC).

Published by **Dominate Your Brand LLC**, 30 Gould St Suite R, Sheridan, WY 82801.
Contact: Curtis Waters · info@dominatewithbrand.com · (704) 345-2964

If something breaks in production, check [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
first — it covers real issues hit deploying this exact stack (NameHero
cPanel + Cloudflare), not just generic advice.

## What's in this repo

- `index.html` — the homepage plus the SPA for About, Pricing, Contact, FAQ,
  and Privacy Policy, using hash-based client-side routing (`#/about`,
  `#/pricing`, `#/contact`, `#/faq`, `#/privacy`, `#/search`). Category and
  listing pages used to live here too (`#/category/...`, `#/listing/...`);
  they're now real server-rendered pages — see "Routing" below — and
  index.html's router just redirects those old hash routes to the new URLs.
- `category.php` / `listing.php` — server-rendered pages for
  `/category/{slug}/` and `/listing/{id}/`, each with its own title, meta
  description, canonical URL, and JSON-LD. See "Routing" below.
- `guides.php` / `guide.php` — server-rendered free articles at `/guides/`
  and `/guides/{slug}/`, adapted from chapters of the paid "Family Guide to
  Estate Cleanouts" (sold on Whop). See "Guides" below.
- `style.css` — shared styles for every page (`index.html`, `category.php`,
  `listing.php`, `guides.php`, `guide.php`) so they look identical without
  duplicating a stylesheet per file.
- `partials/` — PHP includes shared by `category.php`, `listing.php`,
  `guides.php`, and `guide.php`: `categories.php` (category metadata),
  `guide-content.php` (guide article content), `render-helpers.php`
  (escaping, card markup, sorting — the PHP equivalents of `index.html`'s JS
  helpers), `header.php`, `footer.php`, `analytics.php` (Google Analytics
  snippet).
- `.htaccess` — rewrites pretty URLs (`/category/{slug}/`, `/listing/{id}/`,
  `/guides/`, `/guides/{slug}/`, `/sitemap.xml`) to the PHP scripts that
  actually handle them.
- `hero_estatedir1.webp` — the homepage hero image.
- `api/` — PHP endpoints backing the listing data and contact form
  (`listings.php`, `contact.php`, `db.php`, `config.example.php`). Requires
  PHP + MySQL, e.g. a cPanel hosting plan. Paid upgrades checkout happens
  entirely on Whop (see Payments below) — there's no payment code in `api/`.
- `sql/` — `schema.sql` (creates the `businesses` table) and `seed.php` (one-time
  import of the original listing data).
- `admin/` — password-protected panel (`admin/index.php`) for adding, editing,
  deleting, and toggling Verified/Featured/Category Sponsor status on
  listings without touching SQL directly.
- `sitemap.php`, `robots.txt`, `llms.txt` — SEO/discovery files for
  `clearinghousecharlotte.com` (the site's canonical domain). `sitemap.php`
  is served at `/sitemap.xml` via `.htaccess` and generates itself fresh
  from the database on every request (homepage + every category + every
  listing), so it can't go stale the way a hand-written file would.

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
   document root), get the whole repo onto the server, e.g. into
   `public_html`. Make sure `.htaccess` comes along — it's a dotfile, so
   confirm your upload method doesn't skip hidden files.
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

`.htaccess` needs `mod_rewrite` enabled, which is the default on cPanel/NameHero
shared hosting — nothing to configure there.

## Routing

Category and listing pages are real, independently crawlable URLs, not hash
fragments in the SPA:

- `/category/{slug}/` → rewritten by `.htaccess` to `category.php?slug={slug}`
- `/listing/{id}/` → rewritten to `listing.php?id={id}`

Each of those scripts queries the `businesses` table directly (via
`api/db.php`) and renders a complete HTML page server-side — its own
`<title>`, meta description, canonical URL, and JSON-LD (`LocalBusiness` for
a listing page) — so a crawler sees real, page-specific content without
executing any JavaScript. They share `partials/header.php`,
`partials/footer.php`, and `style.css` with `index.html` so the site looks
identical whether a page came from the SPA or from PHP.

About, Pricing, Contact, FAQ, Privacy, and Search are deliberately **not**
part of this — they stay as hash routes inside `index.html`'s SPA, since
none of them are the long-tail search traffic this change targets (a
specific business's name, or "estate sale companies in Charlotte NC," not
"The Clearing House FAQ"). They could get the same treatment later if that
changes.

Old links to `#/category/{slug}` or `#/listing/{id}` (e.g. anything already
indexed or bookmarked from before this change) still work — `index.html`'s
router detects them and redirects to the new real URL via
`location.replace()`.

`category.php` and `listing.php`'s search boxes submit a plain GET to `/`
(they have no router of their own), and `index.html` picks up the `?q=`
parameter on load and redirects into its own `#/search?q=...` route.

Duplicating markup logic between JS (`index.html`) and PHP
(`partials/render-helpers.php`) is an accepted tradeoff here — a single
shared templating layer across both would be a bigger rewrite than this
project's size and traffic justify. If the two ever visibly drift (e.g. a
badge added to one card renderer and not the other), that's the place to
look.

## Guides

`/guides/` and `/guides/{slug}/` (same `.htaccess`-rewrite pattern as
categories/listings) are free, shortened articles adapted from chapters of
*The Family Guide to Estate Cleanouts* — the $19.97 digital download sold on
Whop (see Payments below). There are three: sorting/categorizing an estate,
valuing estate sale items, and a step-by-step cleanout timeline.

The content is deliberately **not the PDF's text** — it's a fresh, shorter
rewrite that leaves out the guide's actual checklists, call scripts, and
company-screening matrix, which stay exclusive to the paid download. Each
page ends with a `.guide-promo` CTA (the same component used on the
homepage) whose copy is specific to what that article didn't cover, linking
to `WHOP_GUIDE_LINK` (defined once in `partials/render-helpers.php`, kept in
sync with `WHOP_LINKS.guide` in `index.html`'s `<script>`).

Content lives in `partials/guide-content.php` as a plain PHP array (title,
description, intro, an ordered list of heading/body sections, and the CTA
body) rather than a database table — this is editorial content that changes
rarely and doesn't need `/admin/` editing. Adding a fourth guide means
adding one more entry to that array; `guides.php` and `sitemap.php` both
pick it up automatically since they iterate over the same array rather than
listing slugs by hand.

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
business in the same category already has it — see "Known limitations"
below for why this check only exists here and not on the Whop side. The
panel is protected by one shared password (`admin_password_hash` in `api/config.php`)
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
  `index.html`, `category.php`, and `listing.php`.
- The `SITE_URL` constant near the top of `index.html`'s `<script>` (used to
  build the JSON-LD in the SPA) and the `$siteUrl` variable in `sitemap.php`.
- `robots.txt` (which points at the sitemap) and `llms.txt`.

If the domain ever changes, all of these need updating — search `clearinghousecharlotte.com` across the repo.

Category and listing pages are real URLs now (see "Routing" above), and
`/sitemap.xml` lists every one of them, generated fresh from the database on
each request — so this no longer has the single-URL limitation an earlier
version of this README described. `llms.txt` still exists alongside it:
`sitemap.xml`/real URLs help traditional crawlers, but `llms.txt` is aimed
at simpler LLM/agent fetchers that may not execute JavaScript at all — it
embeds business data as plain text so that content doesn't require any
crawling or rendering to be readable. It's a manually-maintained snapshot,
so unlike the sitemap, it will drift from the live database over time as
listings are added or edited via `/admin/`.

## Analytics

Google Analytics (GA4, `gtag.js`) is wired into every public page — inline
in `index.html`'s `<head>`, and via `partials/analytics.php` (included in
`category.php` and `listing.php`) so the tracking snippet only needs
updating in one PHP place if the Measurement ID ever changes. `/admin/` is
deliberately excluded, so admin activity doesn't get mixed into visitor
analytics.

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
