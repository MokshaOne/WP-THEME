# Silence — setup checklist

A monochrome portfolio that scrolls like a film — **Silence III**: a narrative homepage (sticky opening plate → full-viewport work chapters with parallax → pinned horizontal film strip → statement + stats → giant italic CTA), a cursor-trailing project index, vertical galleries with alternating alignment, the full booking flow (live price calculator → PDF estimate auto-reply → FAQ), and an interface that falls silent when you stop moving.

**Silence shares Obscura's data completely.** Same post types (`nr_project`, `nr_journal`, categories), same fields (`project_gallery`, `project_client`, `project_year`, `project_location`, `featured_on_homepage`), same settings options (`nr_email`, `nr_smtp_*`, socials, SEO, Turnstile — even the SMTP app password). Activate either theme and everything is just there; switch back and forth freely.

## 0. On activation — automatic
- **About & Enquire pages** are created with the right templates assigned (existing same-slug pages are reused, never duplicated).
- Your projects, journal, galleries, and settings are live immediately — nothing to import, because both themes read the same rows.
- Then do one thing: **Settings → Permalinks → Save** (flushes rewrites so `/work` and `/journal` resolve — note Silence uses `/work` where Obscura used `/portfolio`).

## 1. Content
- **Projects** — featured image (= plate 01 + index preview), gallery in the **Gallery — plates** box (order = display order; images **and videos**; built for large collections), client / year / location, a Category, **Featured on homepage** for the hero. Drag rows in the admin list to set the index order.
- **Journal** — dated entries at `/journal`.
- **Enquiries** — auto-saved on form submit (same `nr_enquiry` records as Obscura).

## 2. ACF Pro (optional)
With ACF Pro active, Silence registers the **same "Project Details" field group as Obscura** (gallery, cover, number, year, client, location, category slug, frames, featured) — identical editing UI in both themes. Without ACF, Silence's native meta boxes cover gallery + client/year/location/featured; the bundled polyfill keeps `get_field()` working either way.

## 3. Settings — Appearance → Silence
One page, shared values: § Identity (wordmark/tagline/location/availability/about lede/share-card accent) · § Contact & elsewhere · § SEO (LocalBusiness schema, verification, consent-gated tracking snippet) · § Mail (SMTP — same proven Google-Workspace flow and the same stored credential as Obscura, test button included) · § Security (Turnstile) · § Motion.
- **Hero interval** is shared with Obscura: Obscura saves milliseconds, Silence seconds — values over 100 are treated as ms automatically.
- **Motion toggles** (Silence-only, both on by default, opt-out): **Interface falls silent** (chrome fades after ~3.5 idle seconds; any movement wakes it; keyboard focus keeps it visible) and **Plate drift**.

## 3b. Enquire — calculator, PDF, FAQ
- The Enquire page carries the **live price calculator** (shooting types, extras, travel km) reading the **same pricing config as Obscura** — the ACF **"Pricing & Quote"** options page under Appearance (with sensible built-in defaults if you never configure it). Deep links prefill it: `/enquire/?service=portrait`.
- Submissions are **recomputed server-side**, logged as `nr_enquiry`, mailed to you with the breakdown, and the visitor gets an **auto-reply with the branded PDF estimate** attached (dependency-free, inc/pdf.php).
- The **FAQ accordion** below reads the `faq_items` ACF repeater on the Enquire page (same field as Obscura), with built-in defaults.

## 3c. About — the studio page
Statement lede + bio + studio address, portrait (featured image), the **stats row**, **testimonials** (`nr_testimonial` — your existing entries), and the **clients wall** — all from § Identity settings + shared content.

## 4. Included infrastructure (no plugins)
WebP delivery + **Tools → Generate WebP** bulk page · **[nr_map]** Leaflet map (monochromed) + per-project coordinates · **[nr_compare]** before/after slider · enquiry **insights** dashboard · bulk project **importer** · `nr_project_series` + tags taxonomies · SEO meta/OG/Twitter + LocalBusiness/VisualArtwork/Breadcrumb JSON-LD · generated 1200×630 share cards at `/nr-og/<id>.jpg` (same endpoint + cache as Obscura) · PWA offline shell · SMTP with test button · Cloudflare Turnstile on the enquiry form (honeypot + rate-limit always on).

## 5. Notes
- The design is **monochrome by intent** — the only color on the whole site is inside your photographs. (The share-card accent is the single exception, and it never appears on the site itself.)
- The whole site scrolls naturally on every device now; the pinned film-strip and parallax are desktop-only flourishes. All motion honors `prefers-reduced-motion`, and content is fully visible without JavaScript.
- Fonts are self-hosted (Instrument Serif, Inter Tight, JetBrains Mono) — zero external requests.
