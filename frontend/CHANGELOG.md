# Changelog — Raveenthiran

The version + build date shown in the site footer matches the top entry here.
Frontend version = `package.json`; WordPress theme version = `style.css` header.
Both are bumped together on each meaningful update.

## 2.1.0 — 2026-08-21 · SMTP mail

- **SMTP delivery** for enquiry emails (adapted from the Obscura theme).
  Configure under **Site settings → Mail (SMTP)** — host, port, TLS/SSL,
  username, password (or the `RVN_SMTP_PASS` constant in wp-config.php),
  from address + name. When off, the server's default PHP mail() is used.
  This makes the enquiry confirmation + studio notification actually arrive.

## 2.0.0 — 2026-08-21 · "Master" launch

The complete, production-ready site.

- **Design** — bespoke editorial theme adapted from Opta (CocoBasic): Playfair
  Display + Montserrat + Roboto, gold accent, light/dark, self-hosted fonts.
- **Home** — cinematic full-screen hero (Ken Burns, slide counter, prev/next,
  scroll cue) + editorial lede + staggered "Selected work" gallery.
- **Work** — album filter + grid/index toggle; **Project** — contained cover
  print, label/value meta, credits, gallery with click-zoom lightbox, and a
  "Next project" link.
- **Journal** — editorial blog from native WordPress posts (+ RSS feed).
- **Studio** — bio, portrait, count-up stats, clients, script flourish.
- **Enquire** — live price calculator + FAQ + real enquiry backend (stored in
  WordPress, emails studio + client, mailto fallback).
- **Identity** — "Raveenthiran" wordmark (header, footer, preloader), serif "R"
  favicon; cinematic overlay menu; gold scroll-progress bar.
- **Languages** — EN/DE interface switch.
- **SEO / perf** — canonical, Open Graph + Twitter, JSON-LD, sitemap, RSS,
  LCP preload, CLS-safe images, web manifest.
- **Hardening** — `.htaccess` (404, gzip, caching, security headers, HTTPS),
  full keyboard accessibility (focus traps, aria-live).
- **Docs** — `frontend/README.md` handbook.

<!-- Add the next release above this line, newest first. -->
