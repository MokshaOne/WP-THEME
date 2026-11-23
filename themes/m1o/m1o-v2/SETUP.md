# M1O v2 — setup checklist

After uploading & activating the theme (**Appearance → Themes → Add New → Upload Theme**):

## ✨ New in v2 (read first)
- **Free-build pages** — two new page templates let you hand-code pages inside WordPress:
  - **Canvas (free build)** → *Page → Attributes → Template → "Canvas (free build)"*. Write raw HTML/CSS/JS in the editor (Custom HTML block or the Code editor) — it renders verbatim, full-bleed, inside the normal header/footer.
  - **Blank Canvas (zero chrome)** → same menu, pick *"Blank Canvas"*. You own the entire `<body>` — no theme header/footer at all. The closest thing to a hand-coded static page, but with a WordPress URL + CMS. Great for experiments and one-off art-directed layouts.
- **Couture headline font** — the largest headlines (homepage hero + page titles) use **Avoiste Laverta**. It's a licensed asset kept out of this public repo, so **drop `avoiste-laverta.woff2` (+ optional `.woff`) into `assets/fonts/`** on your install to activate it (details in `assets/fonts/AVOISTE-README.md`). Without it the hero cleanly falls back to Syne. Revert anytime by clearing the `--ff-hero` CSS variable.
- **Obsidian film-grain** — a subtle static grain layer removes the "flat black" look. Tune or disable via the `--grain-opacity` CSS variable (default `.038`; set `0` to turn off).
- **Reduced-motion hardening** — all animation now degrades cleanly for visitors who set "reduce motion".
- **Still pending your files:** self-hosted Leaflet for the map (drop `leaflet.js` + `leaflet.css` into `assets/vendor/leaflet/` — currently still loads from the unpkg CDN). See CHANGELOG-M1O-V2.md.

---

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
- **Keyword tags (new in 4.23)** — Projects now have a **Tags** taxonomy alongside Categories. Add tags (e.g. *Vienna, night, 35mm*) to projects; they appear as a second chip row on `/portfolio` that **combines** with the category/year filter (pick a category, then narrow by one or more tags).
- **Video plates (new in 4.23)** — add an **mp4/webm video** straight into a project's **Gallery** field; it renders inline as a muted, looping autoplay plate (with a "Motion" badge). Set a featured image on the video attachment for a poster frame.
- **Series / collections (new in 4.24)** — group projects with the **Series** taxonomy (Projects → Series). A project that belongs to one shows a "Series · Name" meta row + a **"More from this series"** nav, and gets a `/series/<slug>` archive.
- **Dynamic share cards (new in 4.24)** — each project automatically gets a composited **1200×630 Open Graph card** (photo + title + category·year) at `/nr-og/<id>.jpg`, used for `og:image`. Needs the **GD** PHP extension (standard on easyname); falls back to the plain featured image if GD is unavailable. Cards are cached and refresh when you edit the project.
- **PDF estimate (new in 4.24)** — when a visitor submits the Enquire form with a calculated price, a **branded one-page PDF estimate** is attached to their auto-reply email. No plugin/library needed.
- **Shared-element morph (new in 4.24)** — optional. **Theme Settings → § Visual effects → Shared-element page morph**. Off by default; when on, clicking a portfolio card morphs it into the project hero (View Transitions API; browsers without support just navigate normally).
- **More opt-in effects (new in 4.25)** — all **off by default** under **Theme Settings → § Visual effects** (flip on, reload, judge live; each falls back cleanly):
  - **Card hover distortion** — liquid SVG ripple on portfolio images on hover.
  - **Line-reveal headings** — display headings rise line-by-line behind a mask.
  - **Interface sound** — a tiny mute toggle appears; ticks play only after the visitor unmutes.
  - **Generative favicon** — draws an accent monogram favicon when no Site Icon is set.
  - **Auto internal linking** — links the first mention of another project's title inside project text (SEO).
- **Map of project locations (new in 4.25)** — add coordinates in the **"Location (map)"** box on each project, then place the shortcode **`[nr_map]`** on any page (e.g. a "Map" page) for a dark Leaflet/OpenStreetMap with a pin per project. `[nr_map height="600"]` to set the height.

## 6. Email delivery — Google Workspace SMTP (new in 4.27)
So enquiries + auto-replies arrive instead of going to spam. **Theme Settings → § Mail (SMTP)**:
1. In the **real mailbox** (the account you log into, e.g. `hq@m1o.at`): enable **2-Step Verification**, then **Google Account → Security → App passwords** → create a 16-char password.
2. Make sure the From address is verified: that account's Gmail → **Settings → Accounts → Send mail as** must list e.g. `office@raveenthiran.com`.
3. Fill in: **Enable SMTP** ✓ · Host `smtp.gmail.com` · Port `587` / STARTTLS · **Username** = `hq@m1o.at` (the real mailbox, *not* the alias) · **App Password** · **From** = `office@raveenthiran.com` · From name = studio.
   - More secure option: put the password in `wp-config.php` as `define('NR_SMTP_PASS','xxxx');` and leave the field blank.
4. Click **"Send a test email to me"** to verify.
5. **DNS (in Cloudflare for raveenthiran.com)** for deliverability: SPF `v=spf1 include:_spf.google.com ~all`; DKIM from Admin console (Apps → Gmail → Authenticate email); DMARC on `_dmarc`: `v=DMARC1; p=none; rua=mailto:office@raveenthiran.com`.

## 7. Performance (optional, recommended)
- Paste the snippet in **`htaccess-snippet.txt`** into your site's root `.htaccess` (gzip + far-future caching for assets/fonts). This also covers minification's goal (smaller transfer) safely.
- Put **Cloudflare** (free) in front of the domain for edge caching / lower TTFB.
