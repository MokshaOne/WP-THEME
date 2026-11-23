# M1O Transmission (m1o-v3)

The transmission console — compact brutalist landing theme for **m1o.at**.
Hard white rules, a channel-index ledger, and rationed gold magic: preloader gate,
gold particle field, decode labels, full-row inversion hover, scroll-velocity skew.

Version **3.0.0** · Author: Nishuthan Raveenthiran · Design & build: On1 Agency

---

## Drop-in successor to M1O Hub v2

The theme reads the **same wp_options** as `m1o-hub` v2:

| Option | Used for |
|---|---|
| `m1o_identity_*` | Name, tagline, location, status, est., email, CTA text |
| `m1o_channels` | Channel rows (group, title, host/descriptor, url, status) |
| `m1o_show_systembar` / `_leds` / `_cta` | Display toggles |

Switching the active theme from Hub v2 to Transmission keeps all content —
no migration, no plugins. New options in v3:

| Option | Used for |
|---|---|
| `m1o_music_embed` | Spotify or YouTube URL → real embedded player (CH row "Music") |
| `m1o_music_title` | Label under the Music row |
| `m1o_show_motion` | Motion layer on/off (GSAP preloader, particles, decode, skew) |
| `m1o_show_strip` | Imagery panel under the first channel row |

Channel mapping: group **Social** → the social row; every other group → a numbered
index row, in stored order. The Music embed (when a URL is set) appends as the last
numbered row.

## Install

1. Copy `m1o-v3/` to `wp-content/themes/` (or upload a ZIP of it).
2. Activate under **Appearance → Themes**.
3. Configure under the **M1O** admin menu (identity, channels, music embed, toggles).
4. Optional: set a **featured image on the front page** — it becomes the imagery
   strip under the first channel row (falls back to a drawn panel).
5. Optional: create a **Legal** menu (Impressum, Datenschutz) — it renders in the footer.
6. Music: paste any `open.spotify.com` track/album/playlist link or a YouTube link.
   YouTube renders via `youtube-nocookie.com`.

## Motion layer

GSAP 3.12.5 + ScrollTrigger, enqueued deferred from cdnjs (pinned).
Degrades gracefully: without JS, with `prefers-reduced-motion`, or with the
Motion toggle off, the page renders instantly and fully readable.

**Todo v3.1 (performance pass):** self-host GSAP + fonts (woff2 latin subset),
preload the two above-the-fold weights.

## Deviations from the M1O-Standard, and why

- **No ACF / no CPTs**: the site's data is one identity + a handful of channel
  rows. Reusing the Hub v2 wp_options keeps the live install's content intact on
  theme switch and keeps the stack at zero plugins. ACF would add a dependency
  without adding capability here.
- **GSAP from CDN** rather than self-hosted — flagged as the v3.1 perf task,
  mirroring the Hub v2 approach to fonts.
