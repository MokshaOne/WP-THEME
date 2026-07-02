# Silence — setup checklist

A monochrome, single-screen portfolio. Install via **Appearance → Themes → Add New → Upload Theme**, activate, then:

## 1. Pages — assign a Template (Page → Attributes → Template)
| Title | Slug | Template |
|---|---|---|
| About | `about` | **About** |
| Enquire | `enquire` | **Enquire** |

## 2. Content
- **Projects** (admin → Projects) — set a **featured image** (becomes plate 01 + the index preview), add gallery images in the **Gallery — plates** box (selection order = display order; supports large galleries), fill **client / year / location**, assign a **Category**, tick **Featured on homepage** for the hero slideshow. Drag rows in the admin list to set the index order.
- **Journal** — optional dated entries at `/journal`.
- **Enquiries** — auto-saved on form submit; don't create manually.
- ⚠️ **After creating content: Settings → Permalinks → Save once** (flushes rewrites so `/work`, `/journal`, `/sl-og/…` resolve).

## 3. Settings — Appearance → Silence
- **§ Identity** — wordmark, tagline, location, availability note, about text.
- **§ Contact & elsewhere** — enquiry email + socials (shown on About).
- **§ SEO** — LocalBusiness schema fields, search-console verification, optional consent-gated tracking snippet.
- **§ Mail (SMTP)** — same proven Google-Workspace flow as Obscura: real mailbox as username, App Password (or `SL_SMTP_PASS` in wp-config.php), verified "Send mail as" alias as From. "Send a test email to me" button included.
- **§ Security** — optional Cloudflare Turnstile keys for the enquiry form (honeypot + rate-limit always run).
- **§ Motion** — hero interval; the two signature effects, both **on by default, opt-out**:
  - **Interface falls silent** — the chrome fades away after ~3.5 idle seconds, leaving only the photograph. Any movement wakes it. Desktop + motion-on only; keyboard focus always keeps the chrome visible.
  - **Plate drift** — very slow scale drift on the active hero plate.

## 4. Included from the proven Obscura backend (adapted, no plugins)
- **WebP delivery** — `.webp` twins per sub-size at upload + on-demand, `<picture>` wrapping, **Tools → Generate WebP** bulk page.
- **SEO** — meta/OG/Twitter tags, LocalBusiness + VisualArtwork + Breadcrumb JSON-LD, verification metas.
- **Share cards** — generated 1200×630 OG cards at `/sl-og/<id>.jpg` (GD; falls back to the featured image).
- **PWA** — installable/offline shell (set a Site Icon in the Customizer for a proper icon).
- **SMTP + Turnstile** — see § Mail / § Security above.

## 5. Notes
- The design is **monochrome by intent** — no accent color anywhere on the site. The only place a color appears is the generated share card (configurable in § Identity).
- Desktop is a fixed single screen (the Work index and reading pages scroll quietly inside it); ≤900px falls back to normal scrolling.
- ACF is **not required** (native meta boxes); if ACF Pro is active it simply takes precedence over the bundled polyfill.
