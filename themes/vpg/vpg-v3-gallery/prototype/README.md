# VPG v3 — "Gallery" static prototype

A complete, **static HTML prototype** of the Vienna Photo Group site in the new
**Gallery** design direction — crisp white, image-led, one red accent, the
Archivo variable grotesque, English copy, magazine-issue framing. This is the
look to approve *before* it gets wired into WordPress.

> Direction 2 — "Gallery" from the design system zip. Inspired by LensCulture /
> 1854 (BJP). The older warm-paper/sienna/German `vpg.css` system is **not** used
> here; this is the agreed replacement.

## Pages

| File | What it is |
|---|---|
| `index.html` | Home — hero, ticker, cover story, map band, latest journal, stats, join CTA |
| `magazine.html` | Current issue + contents + back-issue archive |
| `map.html` | The Map — static map embed, category chips, location cards, how-it-works |
| `journal.html` | Article index — featured piece, filter chips, list, newsletter |
| `about.html` | Story, stats, team, FAQ |
| `join.html` | Pricing tiers, what-your-fee-does, sign-up form, FAQ |
| `gallery.css` | The whole stylesheet — tokens + home components + inner-page components |

## Design system

- **Type:** Archivo (one variable font; `wdth 125` for display).
- **Colour:** white `#FFFFFF` / off-white `#F5F4F1` / near-black ink `#0B0B0B`,
  a single red accent `#E5341F`. One accent only — red does the pointing.
- **Motion:** restrained — `.45s` hovers, a 1.04 image scale, a marquee ticker,
  a one-time rise on the hero. Everything respects `prefers-reduced-motion`.
- **JS:** none. Mobile nav (`:checked`), FAQ (`<details>`) and hovers are CSS-only.

## Notes / TODO before WordPress

- Images are `loremflickr` placeholders keyed to Vienna — **swap for licensed
  member photography** before launch.
- `map.html` shows a static map image as a stand-in for the live Leaflet map
  that already exists in `vpg-v2-coop`; wire the real engine in during the port.
- Forms (`join`, newsletter) are inert (`onsubmit="return false"`) — they hook
  into the existing submission/membership handlers in the WordPress build.
- Footer legal/secondary links currently point to section anchors on existing
  pages; they map to real templates (`page-imprint`, `page-privacy`, …) on port.
