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

## Running locally

No build step — it's a static file. Just open `index.html` in a browser, or serve
the folder with any static file server, e.g.:

```bash
python3 -m http.server 8000
```

Then visit `http://localhost:8000`.

## Deploying (cPanel)

This is a single static HTML file plus one image, so it needs no build step
and no server-side language — any cPanel hosting plan can serve it as-is.

- **File Manager / FTP**: upload `index.html` and `hero_estatedir1.webp` into
  `public_html` (or the document root of a subdomain/addon domain), keeping
  both files in the same folder.
- **cPanel Git Version Control**: alternatively, use Git™ Version Control in
  cPanel to clone this repo directly onto the server, with the repository
  path set to the desired document root. Pulling future commits then
  redeploys the site.
- No `.htaccess` rewrite rules are required — the site uses hash-based
  client-side routing (`#/`, `#/category/...`, etc.), so every route resolves
  to the same `index.html` without any server-side rewriting.

## Data persistence

Listing data (including which businesses are "Featured") is stored via the
`window.storage` API, which only exists inside a Claude.ai artifact. On
cPanel (or any standalone host), every `window.storage` call fails and the
site silently falls back to the hard-coded `DEFAULT_BUSINESSES` list in
`index.html` on every page load. Concretely, this means **Featured status
does not persist** for other visitors or across reloads — marking a listing
Featured through the paid flow has no lasting effect until a real backend is
wired up. See the limitations below; this is a launch blocker if the $30/mo
Featured placement is meant to actually work.

## Known limitations to address before going live

- **Featured status doesn't persist** (see Data persistence above): without
  a real backend or database, paid Featured placement resets on every load.
  This blocks actually selling the feature.
- **Payments**: the "Get Featured" checkout is a simulated Stripe flow for
  demo purposes — no real charge is processed. Wiring up real $30/mo billing
  requires a backend (e.g. Stripe Checkout Sessions + a webhook).
- **Contact form**: submits via `mailto:`, opening the visitor's own email
  client. It does not send email directly from a server.
- **Listing data**: hard-coded in `index.html`. For ongoing editing without
  redeploying code, consider moving it to a small database or a headless CMS.
