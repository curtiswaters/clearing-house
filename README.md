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

## Deploying

Because this is a single static HTML file plus one image, it can be hosted on
any static host with zero configuration:

- **GitHub Pages**: push this repo, then enable Pages in the repo settings
  (Settings → Pages → deploy from the `main` branch, root folder).
- **Netlify / Vercel / Cloudflare Pages**: drag-and-drop the folder or connect
  the repo — no build command needed.

## Data persistence

Listing data (including which businesses are "Featured") is stored via the
`window.storage` API, which is only available when this file is rendered
inside a Claude.ai artifact. If you deploy this outside of Claude.ai (GitHub
Pages, Netlify, etc.), that storage call will fail and the site will silently
fall back to the default hard-coded dataset in `index.html` on every load —
meaning Featured status won't persist between visits until a real backend
or database is wired up.

## Known limitations to address before going live

- **Payments**: the "Get Featured" checkout is a simulated Stripe flow for
  demo purposes — no real charge is processed. Wiring up real $30/mo billing
  requires a backend (e.g. Stripe Checkout Sessions + a webhook).
- **Contact form**: submits via `mailto:`, opening the visitor's own email
  client. It does not send email directly from a server.
- **Listing data**: hard-coded in `index.html`. For ongoing editing without
  redeploying code, consider moving it to a small database or a headless CMS.
