---
name: wordpress-theme
description: >-
  Build, extend, optimize, debug, and ship bespoke (classic, non-block) WordPress
  PHP themes. Use for any WordPress theme work: adding features/CPTs/settings,
  fixing template/CSS/JS bugs, PageSpeed/Lighthouse performance tuning, image/WebP
  delivery, email deliverability (SMTP + SPF/DKIM/DMARC), accessibility, and
  cutting installable theme ZIP releases. Encodes a proven, plugin-light
  methodology and ship workflow for shared-hosting + Cloudflare sites.
---

# WordPress bespoke-theme craft

A reusable playbook for hand-built classic WordPress themes, especially on
**shared hosting behind Cloudflare**, with a **plugin-light / self-hosted** bias.

## Working principles
- **Measure before fixing.** Start perf work from a fresh `pagespeed.web.dev`
  mobile run and read the **LCP breakdown** (`TTFB + load delay + load time +
  render delay`). Never act on stale numbers or guesses.
- **Risky features are opt-in, default OFF.** Anything that can change the live
  look or break (WebGL, View Transitions, async CSS, sound, animations) ships
  behind a settings toggle defaulting to off, with graceful fallback
  (prefers-reduced-motion, capability checks, `<noscript>`). The owner previews
  and keeps it only if they like it.
- **Verify, don't assume.** Lint everything; for logic that doesn't need WordPress
  (PDF bytes, image conversion, DOM rewrites) write a throwaway stub harness and
  actually run it.
- **No plugin where the theme can do it cleanly** (SMTP, WebP, OG cards, PDF,
  spam shield, lightweight analytics) — fewer moving parts, no bloat.
- **Be honest about diminishing returns.** Stop perf work around ~90 mobile /
  ~95+ desktop; say so instead of chasing a vanity 100.

## Theme conventions to favour
- Options as `wp_options` rows read through one helper (`opt('key', $default)`);
  keep a single `defaults()` array as the whitelist; render toggles consistently
  and make sure "off" persists (track checkbox keys explicitly).
- Make ACF **optional** with a small polyfill so the theme runs without it.
- One feature = one `inc/<name>.php` guarded by `if (!defined('ABSPATH')) exit;`,
  registered in a central includes array.
- Self-host fonts (woff2, subset, `font-display:swap`) instead of Google Fonts.
- Inline design tokens in `:root` in the header for instant theming.

## Ship workflow (every change)
`edit (match surrounding style) → lint (php -l, node --check, CSS brace balance)
→ optional stub-test → bump theme Version (functions.php + style.css header) →
build installable ZIP → commit on a feature branch + push → hand the ZIP to the
owner with a short "what changed / how to test" → update the changelog/roadmap`.
Build the ZIP excluding `.git`, `.DS_Store`, and existing zips.

## Performance playbook (in priority order)
1. **Render-blocking CSS** — inline tiny CSS (just `@font-face`) into `<head>`
   with **absolute** URLs; for the main stylesheet offer an **opt-in** async load
   (`rel=preload ... onload="this.rel='stylesheet'"` + `<noscript>`). **Do not
   inline the whole stylesheet** — it bloats HTML and worsens FCP.
2. **Remove unused third-party JS** — Google Tag Manager / `gpt.js` / Site Kit are
   common offenders (100+ KiB). Replace with Cloudflare Web Analytics (free,
   cookieless). Owner-side.
3. **Images / WebP without a plugin** — if the host can't write AVIF/WebP
   sub-sizes, generate a `.webp` twin per `jpg/png` sub-size with GD (at upload via
   `wp_generate_attachment_metadata`, plus a batched admin "generate all" page),
   and centrally wrap `wp_get_attachment_image()` output in
   `<picture><source type=image/webp>` (covers `get_the_post_thumbnail` too).
   Cache-safe with `picture{display:contents}`. Make the LCP preload WebP-aware
   (`type=image/webp`) to avoid double downloads. Fix overstated `sizes`
   (e.g. `100vw` for an `object-fit:contain` hero → realistic vw).
4. **TTFB** on shared hosting — add a page cache (W3TC **Disk: Enhanced**, or
   **LiteSpeed Cache** if the host runs LiteSpeed; check the `Server:` header).
   Keep **Minify OFF** (breaks JS/CSS; Brotli already shrinks transfer). Exclude
   form pages and any virtual script/JSON endpoints from the cache.
5. Re-measure: purge page cache **and** Cloudflare, reload twice in incognito,
   confirm in DevTools → Network (real file name + type + size).
- Cloudflare **Polish / Mirage / Image Resizing are paid (Pro)** — don't rely on
  them on a free plan; do format work in the theme.

## Email deliverability playbook (forms must not land in spam)
- Send through authenticated SMTP via `phpmailer_init` (e.g. Google Workspace:
  `smtp.gmail.com:587` STARTTLS). **Username = the real login mailbox**, *not* an
  alias; use a 16-char **App Password** (or a `wp-config` constant); **From** may
  be any **verified "Send mail as"** address. Set the envelope `Sender` for SPF
  alignment. Add a "send test email" button.
- DNS (at the DNS host, often Cloudflare): exactly **one** SPF TXT
  (`v=spf1 include:_spf.google.com ... ~all`); **DKIM** generated in the provider
  console and you **must enable/"Start authentication"** or it signs with a
  generic key and DMARC fails alignment; **DMARC** `v=DMARC1; p=none; rua=...`.
- Verify with **port25** (`check-auth@verifier.port25.com`, unlimited) — target
  `spf=pass` and `dkim=pass header.d=<your-domain>`. Duplicate DKIM/SPF records →
  `temperror`; keep exactly one of each.

## Useful no-plugin techniques
- Virtual endpoints (service worker, manifest, OG image, JSON feeds) served via
  `template_redirect` matching `REQUEST_URI` — no rewrite-flush needed.
- Dependency-free single-page PDF (standard Helvetica, no embedded fonts; encode
  text to Windows-1252 for €/umlauts).
- Composited OG share cards with GD (bundle a TTF for `imagettftext`).
- Self-hosted "measurement loop": store attribution on form submissions and show
  a dashboard widget of what converts — no analytics subscription.

## Gotchas
- Exact-string editors fail on tab/whitespace mismatches — read the file first or
  use precise `\t` indentation.
- A WordPress-coupled file `exit`s under `if(!defined('ABSPATH'))` in a bare PHP
  harness — `define('ABSPATH', ...)` and stub `add_action`/`add_filter`/`__`/etc.
- `getElementById` is unreliable without a DTD — use `DOMDocument::documentElement`
  with `LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD` when rewriting `the_content`.
