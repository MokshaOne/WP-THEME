# Obscura — ops notes (owner actions)

Three IDEAS-200 quick wins are **host/edge configuration**, not theme code — the
deliverable is the recipe. Do these once on easyname + Cloudflare.

## #86 — Confirm Brotli / gzip on static assets
The theme ships plain `.css`/`.js`; compression happens at the edge.
- **Cloudflare** compresses automatically. Verify:
  `curl -sI -H 'Accept-Encoding: br' https://raveenthiran.com/wp-content/themes/raveenthiran-obscura/assets/css/theme.css | grep -i content-encoding`
  → expect `content-encoding: br` (or `gzip`).
- If missing: Cloudflare dashboard → **Speed → Optimization → Content** → ensure
  Brotli is on (it is by default on the free plan).
- easyname (origin) Apache: gzip via `mod_deflate` is usually on; Cloudflare in
  front makes origin compression secondary.

## #92 — Cloudflare cache rules + APO (copy-paste)
Free-plan setup that pairs well with this theme (W3TC Disk page cache at origin):
1. **Caching → Configuration:** Caching Level *Standard*, Browser Cache TTL
   *Respect Existing Headers* (the theme already sends `max-age` on
   `/sitemap*.xml`, `/feed/json`, `/projects.json`, `/nr-og/*`).
2. **Rules → Cache Rules** — *Cache assets hard*:
   - When incoming requests match: `URI Path` *contains* `/wp-content/themes/`
     **or** `/wp-content/uploads/`
   - Then: *Eligible for cache*, **Edge TTL** 1 month, **Browser TTL** 1 week.
3. **Rules → Cache Rules** — *Bypass admin/forms*:
   - When: `URI Path` contains `/wp-admin/` **or** `/wp-login` **or** Cookie
     contains `wordpress_logged_in`
   - Then: *Bypass cache*.
4. **APO** (Automatic Platform Optimization) is a paid add-on — skip on free;
   the rules above cover most of the benefit. Don't enable Rocket Loader (it
   reorders the theme's deferred JS) or Mirage/Polish (paid; the theme already
   makes WebP twins).
5. After deploys: **Caching → Purge Everything** once, then W3TC → empty all caches.

## #183 — Login hardening (WordPress)
The theme can't safely change auth, but here's the checklist:
- **Rotate the Application Password** used by any MCP/REST integration regularly;
  treat a pasted password as compromised and revoke it in *Users → Profile →
  Application Passwords*.
- Enable **2FA** (e.g. the *Two-Factor* feature plugin or your host's option) for
  every admin account.
- Rename the lone admin away from `admin`/`administrator`; give editors the
  **Studio Assistant** role (shipped in the theme) instead of Administrator.
- Cloudflare **WAF → Rate limiting**: throttle `POST /wp-login.php` (e.g. 5/min/IP).
- Keep `DISALLOW_FILE_EDIT` true in `wp-config.php`; the theme's honeytoken
  (`#185`) soft-bans scanners that probe bait paths.

## #192 — Off-site backup (recommendation)
The dashboard "content health" widget nags monthly. To actually move bytes off the host:
- **Uploads + DB to Backblaze B2 / S3** (cheap, ~€0.005/GB): run `wp db export`
  + `rclone sync wp-content/uploads b2:bucket/uploads` from cron, or use a backup
  plugin pointed at B2/S3.
- Keep the **theme ZIP** in the GitHub Release (auto-created on tag, see
  `.github/workflows/release.yml`) — that's your code backup.
- Verify a restore once a quarter; an untested backup isn't a backup.

## #32 — Cloudflare HTML edge-cache (logged-out only)
Cache the HTML at the edge for anonymous visitors; always bypass for logged-in.
**Rules → Cache Rules** (free plan):
- *Match:* `Cookie` does **not** contain `wordpress_logged_in` **and** `URI Path`
  does not start with `/wp-admin/`, `/wp-json/`, `/nr-`, `/?nr_`
- *Then:* Eligible for cache · Edge TTL **2 h** · respect the theme's `Vary: Save-Data`.
- Add a **bypass** rule for `Cookie contains wordpress_logged_in` (and the enquiry
  POST to `/wp-admin/admin-post.php`).
Purge on deploy (theme version bump busts asset URLs automatically).

## #34 — Off-site uptime check
The theme's daily self-test runs *on* the host, so it can't see a full outage.
Add a free external monitor (UptimeRobot / Better Stack / Cron-job.org) hitting
`https://raveenthiran.com/?nr_random=1` every 5 min from outside, alerting by email/
Telegram. Point it at a lightweight URL, not the homepage, to keep logs clean.
