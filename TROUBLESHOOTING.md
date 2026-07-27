# Troubleshooting

Practical notes from deploying this site on NameHero (cPanel) with Cloudflare
as DNS. Organized by symptom, not chronologically — if something breaks,
find the closest matching symptom below before re-diagnosing from scratch.

## First moves, always

- **Read `api/error_log` before guessing.** PHP fatal errors (bad DB
  credentials, a broken `require`, a syntax error) land here with a full
  stack trace and line number. This solved more of our issues, faster, than
  any other single step — check it before speculating about permissions,
  Cloudflare, or anything else.
- **`curl -s -D - -o /dev/null <url>`** shows real response headers without
  browser caching getting in the way. `x-turbo-charged-by: LiteSpeed` in the
  response confirms a request actually reached the NameHero origin (as
  opposed to being answered by Cloudflare's edge or a different vhost).
- **A browser showing something broken isn't proof the server is broken.**
  Browsers (and Windows' DNS cache) hold onto stale state aggressively.
  Reproduce with `curl` from a machine that was never involved before
  concluding the server itself is wrong.

## "Every page 403s, including files that definitely exist"

This happened right after the initial `cPanel Git Version Control` clone.

1. Check permissions with `namei -l /home/user/path/to/some/file` — this
   prints the permissions of *every* directory in the path at once, which
   is much faster than checking each folder individually in File Manager.
   We were looking at `chclt`'s own permissions (which were fine) and
   missing that a parent or sibling directory was the actual problem.
2. Watch for **case-sensitivity confusion**: cPanel showed both `chclt` and
   `CHCLT` as separate top-level folders on this account. Linux filesystems
   are case-sensitive; always confirm the *exact* Document Root shown in
   cPanel → Domains, character-for-character, against where you're actually
   looking.
3. If permissions and paths check out and it's still a blanket 403 with no
   `.htaccess` explanation, the vhost itself may not be fully registered on
   the server — see the AutoSSL section below, since this turned out to be
   the actual root cause for us.

## Cloudflare + AutoSSL chicken-and-egg (SSL never issues, site 403s over HTTPS)

**Symptom**: cPanel's AutoSSL keeps failing HTTP-based domain validation with
something like *"resolved to an IP that does not exist on this server"* or a
403/404 on the `.well-known` challenge file, and the site itself 403s over
HTTPS but the origin is otherwise fine.

**What's actually happening**: if Cloudflare is set to force HTTPS
("Always Use HTTPS") and the SSL/TLS encryption mode is **Full** or
**Full (strict)**, every request — including AutoSSL's own validation
fetch — gets forced through HTTPS to the origin via SNI. If the origin
doesn't have a working SSL vhost for that exact hostname yet (because
AutoSSL hasn't succeeded yet), the connection falls through to some other
default vhost on the shared server, which 403s. This is a loop: SSL can't
issue until validation succeeds, but validation can't succeed while
Cloudflare insists on HTTPS to an origin with no matching cert yet.

**Fix**:
1. Cloudflare → SSL/TLS → Overview → temporarily switch to **Flexible**
   (Cloudflare↔origin traffic becomes plain HTTP; visitors still see
   `https://` since Cloudflare terminates TLS on their end).
2. Re-run AutoSSL (cPanel → SSL/TLS Status → Run AutoSSL). It should
   succeed now that requests reach the real vhost.
3. Once a real certificate is issued and confirmed (see below), switch
   Cloudflare back to **Full (strict)** for actual end-to-end encryption.

**Confirming the cert is real and covers the right domain** — don't just
trust the cPanel status icon in isolation:
```bash
echo | openssl s_client -connect <origin-ip>:443 -servername yourdomain.com 2>/dev/null | openssl x509 -noout -subject -issuer -dates
echo | openssl s_client -connect <origin-ip>:443 -servername yourdomain.com 2>/dev/null | openssl x509 -noout -ext subjectAltName
```
The `subjectAltName` (SAN) list is what actually matters for hostname
matching — the `CN` field can show a different "primary" name (e.g. a
cPanel auto-generated technical hostname) even when the cert is completely
valid for your domain, as long as your domain is in the SAN list. Don't
panic at a mismatched CN alone.

**A wildcard cert (`*.yourdomain.com`) will likely never validate** if
Cloudflare (not cPanel) is your authoritative DNS — wildcard validation
needs a DNS TXT record cPanel can't add on its own. This is expected and
harmless to ignore as long as the plain domain and `www` are covered.

## Database connection errors after everything else looks fine

**Symptom**: `PHP Fatal error: Uncaught PDOException: SQLSTATE[HY000]
[1045] Access denied for user 'X'@'localhost'` in `api/error_log`.

MySQL gives the *same generic error* whether the password is wrong or the
username doesn't exist at all — it doesn't reveal which, by design. Don't
assume `api/config.php`'s username is correct just because it's what's
currently configured.

1. Test the credentials directly, bypassing PHP and the app entirely:
   ```bash
   mysql -u the_username -p -h localhost the_database_name
   ```
   This isolates whether the problem is MySQL-side (wrong password, or the
   user was never actually granted access to that database) or
   `config.php`-side (typo, wrong variable, file not saved).
2. Cross-check the exact username against cPanel → MySQL® Databases →
   "Current Users" — **we lost real time to `config.php` having
   `dbuser` when the actual created user was `chuser`**, a name mismatch
   that produced an identical-looking "access denied" error to a wrong
   password.
3. The database password (`'pass'` in `config.php`) is always **plain
   text** — MySQL does its own hashing internally. Don't confuse this with
   `admin_password_hash` in the same file, which genuinely is a bcrypt hash
   because our own PHP code (`admin/index.php`) checks it with
   `password_verify()`, not MySQL.

**A quiet trap**: `index.html` falls back to hardcoded demo data when
`api/listings.php` fails, so the homepage can look completely normal for
hours (or longer) while the real database connection is silently broken.
`category.php`/`listing.php` have no such fallback and will 500 instead —
if those 500 while the homepage looks fine, suspect exactly this.

## `git pull` refuses with "local changes would be overwritten," but you never edited those files

Before assuming real edits happened on the server, check whether it's just
file-mode noise:
```bash
git diff path/to/file
```
If the output is only `old mode 100644` / `new mode 100755` with no `+`/`-`
content lines, it's a permissions change (e.g. from `chmod`-ing files while
debugging a 403), not a real edit — safe to discard:
```bash
git checkout -- path/to/file
git pull origin main
```
If it shows actual content diffs instead, stop and look at them before
discarding anything — something was genuinely edited directly on the server.

## Bash swallowing/mangling a password with `!` in it

Interactive bash treats `!` as a history-expansion character **inside
double quotes**, but not inside single quotes. A command like:
```bash
php -r "echo password_hash('MyPass!!', PASSWORD_DEFAULT), PHP_EOL;"
```
can silently mangle `MyPass!!` via history expansion. Swap the quoting —
single quotes for the outer shell argument, double quotes for the inner PHP
string:
```bash
php -r 'echo password_hash("MyPass!!", PASSWORD_DEFAULT), PHP_EOL;'
```

## Whop-specific gotchas

- There's no webhook from Whop back to this site, so a purchase never
  automatically flips a business's `verified`/`featured`/`category_sponsor`
  flag — that's always a manual step in `/admin/` after noticing a sale.
- Category Sponsor's "one per category" exclusivity is enforced by
  `/admin/` only. Whop has no idea that rule exists, so nothing stops two
  businesses from both buying that product for the same category before the
  first purchase gets manually applied.

Both are also documented in `README.md`'s "Known limitations" section —
noted here too since they're the kind of thing worth checking first if a
business reports "I paid but nothing changed."
