# Kavithai (Modernized) — v2.1 · Relief

A calm, dignified WordPress theme for the Tamil poet
**கவிஞர் பாவலர்மணி வெற்றிச்செல்வி** (ஜெகதீஸ்வரி ரவீந்திரன்) —
Eelam Tamil, now in Austria, working in classical *akaval* verse.

Designed by [On1 Agency](https://on1.agency) · MMXXVI.

---

## ⚡ What changed in v2.1 (Relief)

### Design direction — South Asian Editorial

Inspired by Tara Books, the Folio Society, and Penguin Modern Classics —
type-led, restrained, with one custom mark and one signature ornament.

### Brand & palette

- **The seal** — Tamil `க` monogram in a hairline ochre circle, used in
  three places only: top-left of the nav (with hover-reveal label),
  end-of-poem mark, and the footer.
- **Two working colors + two accents** — paper `#F0E7CC` + ink `#1A0E04`
  with oxblood `#6B1414` for emphasis and ochre `#8B5A0F` for hairlines
  and the seal only.
- **Relief letterforms** — large outlined Tamil characters used as
  backdrop graphics on the hero, featured poem, pull quote, about,
  and footer. The signature visual move.
- **AAA-readable secondary text** — the muted color is now `#5C4530`
  (7.4:1 on paper at 19px regular — real AAA, not a marketing AAA).

### Typography

- **Noto Serif Tamil** (300/400/500/600) — display + body
- **EB Garamond** (400/500, italic) — Latin sidekick for numerals,
  dates, English words, small caps
- **Body 19px / line-height 1.85**, optical kerning, ligatures,
  tabular numerals, hanging punctuation enabled
- **Tamil numerals** (௧, ௨, ௩…) used as ornamental counts on books,
  selected works, and poem-Nº footers
- **Drop caps** on each poem's first line — the full Tamil grapheme
  cluster (consonant + dependent vowel), set in oxblood

### Layout patterns

- **Magazine masthead band** at the top of every page —
  `Nº [count] · Akaval | க | MMXXVI · Wien`
- **Bilingual section headings** — Tamil large + tracked Latin
  transliteration small caps below (`கவிதைகள் / POEMS`, etc.)
- **Marginalia poem metadata** — Year, Place, For, Form, From, Views
  set in the left gutter of each single-poem
- **Asterism (⁂)** between stanzas inside a poem and as a section
  break instead of hairline rules
- **TOC-style archives** — poems listed as Nº · year · title · first
  line, with Tamil ornamental numerals in the leftmost column
- **Books grouped by decade** on the books archive
- **Colophon footer** — typeface attributions (Pria Ravichandran for
  Noto Serif Tamil, Georg Duffner for EB Garamond, after Robert
  Granjon's specimens of MDLXXXII), Roman-numeral date,
  agency credit in tracked small caps

### Photos

- ★ **Featured images on Poems** (added in v2.0)
- ★ **Featured images on Awards** (v2.0)
- ★ **Dedicated "Poet portrait" upload** in `🌸 என் தளம்` settings page,
  with 4:5 aspect-ratio preview
- ★ **Focal-point picker** — click on the image to mark where the face
  is; the site crops around that point at every size
- About page uses its own Featured Image (falls back to the poet portrait)

### Reader features (front-end)

- ★ **Audio "Listen" mode** — surfaces `_kv_audio_url` meta as a player
  block at the top of every poem
- ★ **Font size +/- buttons** on every poem (persisted per browser via
  localStorage; CSS uses `--reader-scale` custom property)
- ★ **Print as single-page PDF** — clean print styles, hides nav/footer
- ★ **Reading progress bar** — sticky oxblood hairline at the top
- ★ **Ambient reading mode** — body class toggle for distraction-free
- ★ **Random poem** button — `kv_random` AJAX endpoint
- ★ **Reactions form** — now with honeypot + per-IP rate limit + length
  caps (hardened in v2.1)

### Admin features (kept from v2.0; restyled in v2.1)

| Feature | Where |
|---|---|
| ✓ Autosave toast indicator (every 30s, friendly "சேமிக்கப்பட்டது") | Editor screen |
| ✓ Schedule poem publishing (with a sidebar helper card) | Sidebar of any poem |
| ✓ Full-text search across all poems | `கவிதைகள் → 🔍 வரிகள் தேடு` |
| ✓ Year column + bulk-edit year | Poem list |
| ✓ Trash + 30-day restore safety net (countdown now correct) | `கவிதைகள் → 🗑 குப்பை` |
| ✓ Print-friendly proof view | Row action on each poem |
| ✓ Friendlier media upload page | `Media → Add New` |
| ✓ Scheduled poems widget | Dashboard sidebar |
| ✓ Word + Google Docs importer | `📥 இறக்குமதி` |
| ✓ PDF book export | `📄 PDF ஏற்றுமதி` |

### Security & accessibility hardening (CP6)

- ✅ `current_user_can('edit_post', $id)` on every save_post handler
- ✅ Per-object capability check on trash restore (was nonce-only)
- ✅ Nonce check added to focal-point save
- ✅ Reactions endpoint: **honeypot** + **per-IP rate limit** (max 3/hr)
  + name capped at 80 chars + message at 800 + stored reactions capped
  at 200 newest per post
- ✅ Settings vs. ACF reconciliation — `kv_opt` now single source of
  truth (theme_mods); ACF read path removed
- ✅ Trash widget "0 days remaining" bug fixed (was reading `$post`
  outside scope)
- ✅ Google Fonts removed from **proof view + PDF export** — both now use
  the self-hosted `assets/fonts.css` (Noto Serif Tamil .woff2 in
  `assets/fonts/`). The main front-end + admin still load **EB Garamond**
  from `fonts.googleapis.com` — deferred to v2.2, see Roadmap
- ✅ Defense-in-depth capability checks on settings + PDF export
  handlers

### Removed from v2.0

- Decorative grain noise on `body::after` (read as "indie blog circa 2018")
- Emoji icons in nav (admin still uses some for familiarity)
- Book 3D-tilt-on-hover (gimmick)
- 6-color gradient book covers (replaced by 2 muted treatments)
- Decorative `data-l="அ"` book-letter avatars
- Two-accent (oxblood + gold) palette → single accent with hairline ochre
- ACF read path in `kv_opt` (single source of truth)

---

## 📁 File structure

```
kavithai-modernized/
├── style.css                       v2.1 design system (~1k lines)
├── functions.php                   Theme setup, CPTs, customizer, helpers
├── header.php / footer.php         Masthead + nav + colophon
├── front-page.php                  Hero + featured + TOC + books + awards
│                                   + pull quote + 4-section about + contact
├── single-kavithai_gedicht.php     Single poem · audio · marginalia · drop cap
├── single-kavithai_nul.php         Single book · cover · TOC of poems
├── archive-kavithai_gedicht.php    Sidebar filters + TOC archive
├── archive-kavithai_nul.php        Books grouped by decade
├── page-ennai-parri.php            About — 4 bilingual sections + awards
├── page-thodarbu.php               Contact page (mail handler preserved)
├── page.php / index.php / 404.php
└── inc/
    ├── seal.php                    kv_seal(), kv_seal_set(),
    │                               kv_relief(), kv_asterism(),
    │                               kv_eyebrow_bi() — design building blocks
    ├── admin-dashboard.php         Dashboard widgets (welcome, stats,
    │                               recent, scheduled, views, upload)
    ├── admin-features.php          Autosave, schedule, search, bulk,
    │                               trash (fixed), proof (self-hosted fonts)
    ├── admin-style.php             v2.1 admin restyle — paper + ink + ochre
    ├── settings-page.php           '🌸 என் தளம்' (with cap check)
    ├── importer.php                Word + Google Docs import (untouched in
    │                               v2.1; CP4 adds keep-book + PDF download)
    └── pdf-export.php              PDF book export (self-hosted fonts, cap check)
```

---

## 🛠 Installation

1. Zip the `kavithai-modernized/` folder
2. WordPress admin → Appearance → Themes → Add New → Upload Theme
3. Activate "Kavithai (Modernized) — Relief"
4. Go to `🌸 என் தளம்` in the admin menu → upload your portrait + fill
   in name/location/email
5. Settings → Reading → Your homepage displays → "A static page"
   (or let it pick up `front-page.php` automatically)

---

## 🎯 Focal-point picker (how it works)

For any featured image (on a poem, book, or the global portrait):
1. Upload the image as usual
2. A "🎯 புகைப்பட மையம்" panel appears below the Featured Image box
3. Click on the image where the face / focal point is
4. A red dot marks the spot; the site will keep that point visible
   when cropping for hero, thumbnails, cards, etc.

---

## 📐 Image size recommendations

| Where | Best aspect ratio | Min dimensions |
|---|---|---|
| Poet portrait (hero) | 4:5 (3:5 also works) | 720 × 900 px |
| Poem featured image | 16:9 | 1600 × 900 px |
| Book cover | 2:3 | 400 × 600 px |
| About-page photo | 3:4 | 600 × 800 px |

JPG or WebP. Avoid PNG for photos.

---

## 🔠 Type colophon

- Body & display: **Noto Serif Tamil** by Pria Ravichandran (Google Fonts, self-hosted)
- Latin numerals, dates, small caps: **EB Garamond** by Georg Duffner
  after Robert Granjon's specimens of MDLXXXII (CDN in v2.1 — local in v2.2)

---

## 🗺 Roadmap

- **CP4** — Importer: keep imported book as a single PDF-downloadable
  unit *and* split into individual poems
- **CP5** — Full QA pass: code review, security audit, accessibility
  audit against the v2.1 build
- **v2.2** — Self-host EB Garamond (`assets/fonts/eb-garamond-*.woff2`)
  to remove the last Google Fonts CDN call
