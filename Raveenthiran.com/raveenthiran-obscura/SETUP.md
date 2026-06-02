# Obscura — setup checklist

After uploading & activating the theme (**Appearance → Themes → Add New → Upload Theme**):

## 1. Pages to create — assign a Template (Page → Attributes → Template)
| Title | Slug | Template | Required |
|---|---|---|---|
| Enquire | `enquire` | **Enquire** | ✅ — the merged booking + contact + price calculator + FAQ; the "Book a shoot" button points here |
| About (or Studio) | `about` | **About** | ✅ — the "Studio" nav link |
| Booking confirmed | `booking-confirmed` | **Booking confirmed** | ⬜ optional thank-you landing |

## 2. Optional legal pages — no template, just the slug (auto-styled)
- **Impressum** → slug `impressum`
- **AGB** → slug `agb`
- **Datenschutz** → slug `datenschutz`

## 3. Content (post types — no pages needed)
- **Projects** → archive at `/portfolio`. Add featured image + gallery + fields. Tick **"Featured on homepage"** for the hero slider. Add **Categories** → they become the filter chips. Drag rows in the admin list to reorder (controls portfolio + hero order).
- **Journal** → archive at `/journal`. Add entries.
- **Testimonials** → title = source, content = the quote (shown on About + review schema).
- **Enquiries** → auto-saved when the form is submitted (don't create).

## 4. Settings & finishing
- **Appearance → Theme Settings** — studio info, email, socials, stats, and: WhatsApp, Instagram grid (one `image_url | post_url` per line), footer-CTA label, Booking URL, Press-kit URL.
- The front-page hero shows automatically.
- ⚠️ **After creating pages: Settings → Permalinks → Save once** (flushes rewrites so `/portfolio`, `/journal`, `/project/…`, `/enquire`, `/projects.json` resolve).
- **Menus** are optional — without one the theme shows: Showcase · Work · Studio · Journal (+ the Book-a-shoot button).
- **Regenerate Thumbnails** (plugin) once if you have existing images, so the uncropped hero + responsive sizes apply.

## 5. Spam shield, app install & retouch slider (new in 4.21)
- **Turnstile (spam protection)** — at **Cloudflare → Turnstile** create a widget for the domain, then paste the **Site key + Secret key** into **Theme Settings → § Security**. The Enquire form then shows a no-puzzle checkbox and verifies submissions server-side. Leave blank to keep the form as-is (honeypot + rate-limit still run).
- **Installable / offline (PWA)** — works automatically. Set a **Site Icon** (Customizer → Site Identity) so phones can "Add to Home Screen" with a proper icon; the site then loads an offline cached shell on flaky connections.
- **WebGL hero transitions (new in 4.22)** — **Theme Settings → § Visual effects → WebGL hero transitions**. Off by default. When on, the homepage hero dissolves between slides with a shader "melt" (desktop only; auto-falls back to the normal crossfade on mobile, reduced-motion, or unsupported devices). Toggle it, reload the homepage, and judge it live — nothing else changes.
- **Before / after slider** — in any Journal post or page, drop the shortcode:
  `[nr_compare before="123" after="456"]` (media-library image IDs) — optional `before_label="Film" after_label="Graded" start="50"`.

## 6. Performance (optional, recommended)
- Paste the snippet in **`htaccess-snippet.txt`** into your site's root `.htaccess` (gzip + far-future caching for assets/fonts). This also covers minification's goal (smaller transfer) safely.
- Put **Cloudflare** (free) in front of the domain for edge caching / lower TTFB.
