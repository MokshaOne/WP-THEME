# Obscura — build & optimization playbook

How the **raveenthiran-obscura** theme was built and tuned, written so the next
person (or session) can continue with the same method. The matching Claude Code
skill lives at `.claude/skills/obscura-wp-theme/SKILL.md`.

> Context: a Vienna photographer's portfolio (~200 projects × ~14 photos),
> WordPress on **easyname shared hosting**, all domains proxied through **free
> Cloudflare**, English/international focus. Philosophy: **self-hosted, no plugins**
> where reasonable; ship every change as an **installable ZIP**.

---

## 1. The arc
1. Analysed why award (Awwwards-tier) sites felt different → clean rebuild as
   **"Obscura"** (near-black `#0B0C10` / bone `#F2EFE9` / amber `#F2A03D`, Inter
   Tight + JetBrains Mono, single-screen desktop / scrolling mobile).
2. Drafted **100 ideas** (`docs/IDEAS-100.md`), tiered easy/medium/hard, and built
   them in batches — each marked ✅ with the version it shipped in.
3. Merged the funnel: one **Enquire** page (booking + contact + price estimate +
   FAQ), dropped LatePoint and redundant pages.
4. Built the hard/Tier-3 items (WebGL hero, view-transition morph, OG cards,
   PDF estimates, map, series, tags…), then **operational** wins (SMTP, WebP,
   performance, a self-hosted measurement loop).

## 2. Principles that worked
- **Measure before fixing.** Every perf change started from a fresh PageSpeed run
  and the **LCP breakdown**, not assumptions. Stale numbers caused wrong guesses
  (we chased "the hero image" when it was already AVIF; the real culprit was
  render-blocking CSS + Google ad scripts).
- **Risky = opt-in, default off.** WebGL, morph, distortion, sound, async-CSS, etc.
  all ship behind a Theme Settings toggle so the live site never changes until the
  owner previews and approves. Zero regressions resulted.
- **Verify, don't assume.** Lint everything (`php -l`, `node --check`, CSS brace
  balance). For non-WP-coupled logic (PDF, WebP, DOM rewrite) write a throwaway stub
  harness and actually run it.
- **No-plugin where sensible.** SMTP, WebP, OG cards, PDF, spam shield, analytics,
  map — all built into the theme rather than adding plugins.
- **Honesty about diminishing returns.** Stopped the perf chase at ~90 mobile /
  98 desktop and said so, instead of burning effort for a vanity 100.

## 3. Ship workflow (every change)
`edit → lint → (stub-test) → bump NR_THEME_VERSION + style.css → build root ZIP →
commit + push (claude/obscura-rebuild, PR #7) → SendUserFile the ZIP → mark ✅ in
IDEAS-100 / update SETUP.md`.

## 4. Performance — what moved the needle
Mobile **70 → 90**, Desktop **86 → 98**, Accessibility **95 → 100**, SEO **100**.

| Lever | Action | Result |
|---|---|---|
| Render-blocking CSS (~2,070 ms) | inline `fonts.css`; opt-in async `theme.css` | FCP 2.7 s → 0.9 s |
| Third-party JS (176 KiB) | remove GTM / `gpt.js` / Site Kit (owner) | TBT → 0 ms |
| Images | AVIF originals but JPEG sub-sizes → theme makes WebP twins + `<picture>` + bulk generator; realistic `sizes` | LCP 6.8 s → ~2.8 s |
| Contrast | tab labels & cookie text brightened | A11y → 100 |
| TTFB | W3TC Disk: Enhanced page cache; **minify OFF** | faster repeat loads |

**Don't repeat:** inlining the *whole* `theme.css` (v4.19) bloated HTML and made
FCP worse — only inline the tiny `fonts.css`. Cloudflare Polish/Mirage are **paid**
(Pro) — do image work in the theme instead.

## 5. Email deliverability — the silent lever
Contact forms are worthless if mail lands in spam. We added theme SMTP
(`inc/smtp.php`) through **Google Workspace** and fixed DNS in Cloudflare:
- Auth with the **real mailbox** (`hq@m1o.at`) + App Password; send **From** a
  verified "Send mail as" alias (`office@raveenthiran.com`).
- SPF (one record), **DKIM** — must click **"Start authentication"** in the Google
  Admin Console or Google signs with the generic `gappssmtp.com` key and DMARC
  fails alignment. DMARC `p=none` with `rua`.
- Verify with **port25** (`check-auth@verifier.port25.com`). Target:
  `spf=pass`, `dkim=pass header.d=<your-domain>`, DMARC pass. Achieved.
- Trap: duplicate DKIM/SPF TXT records → `temperror`. Exactly one of each.

## 6. Measurement loop (no analytics subscription)
`inc/insights.php` + attribution fields capture which **project** drove each
enquiry; the "Enquiry insights" dashboard widget shows counts (7/30/90/all) and
the top-converting projects. You can improve what you measure, privately.

## 6b. Journal & taxonomy (v4.32–4.33)
The journal now mirrors the portfolio: a horizontal **card rail** with desktop
prev/next arrows + category chips (`archive-nr_journal.php`), and a fixed
two-pane single post (`.nr-jpost`: image + scrollable article, pull-quotes,
drop-cap, "More notes" related strip). A single **`taxonomy.php`** renders
series / tag / project-cat / journal-cat archives as the same rail; **`search.php`**
shows mixed results. All carry Article/Breadcrumb schema and are in the sitemap.

## 6c. The 50-item review (v4.33–4.39)
A structured review (`docs/IMPROVEMENTS-50.md`) shipped in 7 batches: journal/
catalogue fixes, design polish (plate numbers, ghost hero numeral, chip counts),
journal-OG cards, archive meta-descriptions, ⌘K journal search, admin UX (journal
columns, **theme-health dashboard**, enquiry CSV export, importer dedupe-by-hash,
reset-to-defaults), perf (eager LCP card, lazy Leaflet, cache headers), white
date-picker icon, hreflang, `[nr_faq]` shortcode + FAQPage schema, marquee,
signature. Two items intentionally skipped with rationale (JS-split, critical-CSS).
**Next ideas live in `docs/IDEAS-NEXT.md`** (incl. real gaps: screenshot.png,
load_theme_textdomain, editor styles).

## 7. Owner's standing to-dos (no code)
- Run **Tools → Generate WebP**, then purge W3TC + Cloudflare.
- Fill content into the new fields: **Tags**, **Series**, map **coordinates**,
  a "process" paragraph on flagship projects.
- Replace Site Kit with **Cloudflare Web Analytics**; deactivate Debloat / CPT UI.
- Submit to **Awwwards / SiteInspire / CSSDA** — "award-winning" is half curation
  and submission, not only code.

## 8. Map of the theme
`functions.php` (setup, enqueue, CPTs, helpers, module loader) · `inc/`
(acf-polyfill, functions-additions, acf-fields, performance, seo, theme-settings,
quote, tier1, tier2, medium, importer, security, pwa, compare, og-cards, pdf,
series, interlink, map, smtp, insights, webp, admin-extras) · templates
(front-page, archive/single for project & journal, **taxonomy**, **search**,
page-enquire, page-about, page-impressum/datenschutz/agb, 404, index, header,
footer) · `assets/` (css/theme.css, css/fonts.css, js/theme.js, js/webgl-hero.js,
fonts/ incl. inter-tight TTFs for GD).

_Current release: **v4.39.0**. Roadmaps: `docs/IDEAS-100.md` (original 100),
`docs/IMPROVEMENTS-50.md` (review, all addressed), `docs/IDEAS-NEXT.md` (what's next)._
