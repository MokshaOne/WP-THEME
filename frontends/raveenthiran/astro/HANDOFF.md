# Raveenthiran — Session Handoff & Next Steps

Paste this into a new chat to continue. It summarises the project, the exact
current state, the one open blocker, and what to do next.

---

## 1. What this project is

A **headless photography portfolio** for **raveenthiran.com** (Nishuthan
Raveenthiran, Vienna).

```
WordPress (private NAS)  =  content + admin ONLY (data via REST)
        │  build-time fetch
Astro static frontend (frontend/)   =  the actual website design
        │  GitHub Action → FTPS
easyname shared hosting             =  serves raveenthiran.com (behind Cloudflare)
```

- The site is **static (SSG)**: WordPress is queried only at build time, so the
  live site is fast and works even if the NAS is offline.
- If the API is unreachable at build, `src/lib/wp.ts` falls back to built-in
  **SAMPLE** data → the build never breaks (this is why sandbox builds show
  placeholder projects — the agent proxy blocks the WordPress host with 403).

## 2. Repo orientation (IMPORTANT — reduces confusion)

The repo `MokshaOne/WP-THEME` contains ~20 OLD theme folders + ZIPs (Still,
Obscura, M1O, Silence, Aurelius, etc.) from earlier experiments **before** we
settled on headless. They are **dead weight and irrelevant** to the live site.

**Only TWO things are the current site:**
| Path | Role |
|---|---|
| `frontend/` | the Astro design → becomes raveenthiran.com |
| `raveenthiran-headless.zip` (+ `raveenthiran-headless/`) | the WordPress data/admin theme |

(Optional future task: clean up / archive the old theme folders.)

## 3. Working branch & PR

- Branch: **`claude/headless-wordpress-easyname-8eu9fj`**
- PR **#20** (draft) → base `Theme-folder`. All work is pushed here.
- Deploy workflow: `.github/workflows/deploy-frontend.yml` (builds Astro, FTPS
  to easyname on push, nightly, or manual; now also purges Cloudflare).
- Commit convention: end messages with the Co-Authored-By + Claude-Session
  trailers used throughout the branch history.

## 4. Current version: **v2.7.0**

The footer of the live site shows `· v{version} · {build date}` — this is the
proof of what's live and whether the cache is fresh. Version lives in
`frontend/package.json` + `raveenthiran-headless/style.css` (bumped together);
`frontend/CHANGELOG.md` has the per-version log.

## 5. What's built (design + features)

**Design (theme.css v4 "Master", adapted from the Opta/CocoBasic template):**
Playfair Display + Montserrat + Roboto (self-hosted, GDPR), gold accent,
light/dark. Cinematic full-screen home hero (Ken Burns, slide counter,
prev/next, scroll cue), editorial staggered galleries, contained "gallery
print" project hero (portrait-safe), signature black footer (monogram-style
wordmark, GPS coords, 3 columns), cinematic overlay menu, preloader, gold
scroll-progress bar. "Raveenthiran" **wordmark** (not an abstract monogram).

**Pages:** Home, Work (album filter + grid/index toggle), Project (meta pairs,
credits, gallery + click-zoom lightbox, next-project), Series overview +
detail, Journal (native WP posts) list + article, Studio (script flourish +
count-up stats), Enquire (live price calculator + FAQ + real backend), bespoke
404, offline.

**Features (versioned):**
- 2.0.0 Master launch · 2.1.0 SMTP mail · 2.1.1 form-action HTTPS fix
- 2.2.0 Cloudflare **Turnstile** spam shield (Site settings → Security)
- 2.3.0 branded **PDF estimate** attached to the enquiry auto-reply
- 2.4.0 **Enquiry insights** dashboard widget
- 2.5.0 **[rvn_compare]** before/after slider (shortcode + JS)
- 2.6.0 **PWA** service worker + /offline/
- 2.7.0 **Series** taxonomy + pages
- (workflow) **auto Cloudflare cache purge** after deploy

**Backend REST (functions.php):** Work CPT + work_category/work_service/
work_series taxonomies; `project` (client/year/location/website/services/
series/credits/featured_home), `gallery` (ACF gallery, AVIF srcset, w/h),
`seo`; `/rvn/v1/site` (Studio/Contact/Pricing/FAQ/Security); `/rvn/v1/enquiry`
(POST: honeypot + Turnstile + store as private `rvn_enquiry` + email studio +
client auto-reply with PDF). SMTP router. i18n EN/DE interface toggle.

## 6. THE OPEN BLOCKER (start here next session)

**The user reports the live site "still looks like the first release."** The
deploy logs prove otherwise: the v2.7.0 build with the user's REAL projects
(`work/amber-noise/`, `work/fragmented-realities/`) is uploaded to easyname and
files are being *replaced* on the server. **So it IS deployed — what they see
is CACHE** (Cloudflare edge + browser; possibly the new service worker too).

**Do this first:**
1. Have the user open **raveenthiran.com in a private/incognito window** and
   scroll to the **footer**. If it shows `v2.7.0 · 21 Aug 2026` → they're on the
   new design (it was browser cache). If old/absent → Cloudflare edge cache.
2. Cloudflare: **Caching → Purge Everything** (one time). The deploy now
   auto-purges IF the user sets repo secrets `CF_ZONE_ID` + `CF_API_TOKEN`
   (Cloudflare → Zone ID; My Profile → API Tokens → "Edit Cloudflare Zone
   Cache" template). Guide them to set these.
3. Note: the beautiful site is **raveenthiran.com**; the NAS WordPress is just the
   admin (plain WordPress — that's normal).

Do NOT build more features until the user confirms they can SEE the current
design live. Building on top of something they can't see is the core of their
frustration.

## 7. User sentiment (read this)

The user is frustrated: web firms estimated the work at ~€4k and called it a
"basic website". They want **masterclass / award-level** design AND usability,
**self-serviceable**, without paying €25k. The honest gap: the design is a
**premium-template adaptation**, which pros recognise as "standard". To move
from "basic" to "Site of the Day" needs a **bespoke signature interaction /
motion layer** — e.g. Lenis smooth scroll, a custom cursor, scroll-driven
parallax/reveals, kinetic (word-mask) typography, and possibly a WebGL image
effect. This was about to be built (`lenis` was installed) when the user said
**STOPP** — so it is NOT built, and the install was reverted (repo is clean).

**Next session, after confirming the site is live:** propose + build the
signature motion layer to elevate it beyond "basic". Keep it tasteful/"dezent"
but unmistakably custom. Verify with Playwright screenshots.

## 8. Not-yet-built from the agreed "worth it" list

- 🗺️ **Locations map** — needs a lat/lng ACF field per project + a Leaflet map
  page (self-host Leaflet via npm; OSM tiles).
- ⬆️ **Bulk importer** — WP admin: upload a ZIP of folders → one Work project
  each (folder = title, images = gallery, first = cover).
- (Explicitly SKIPPED as low headless value: WebP-twins, auto-interlink,
  dynamic OG cards.)

## 9. Sandbox / working notes for the next session

- The agent proxy **blocks the WordPress host AND raveenthiran.com (403)** — you cannot
  fetch live data or the live site. Builds use SAMPLE fallback; that's expected
  and fine (green builds). To preview with images, temporarily patch the SAMPLE
  in `wp.ts` to point at local `/public/_fix/*.svg`, build, screenshot, then
  `git checkout -- frontend/src/lib/wp.ts` and remove the fixtures.
- Screenshots: `npm i -D playwright@1.56` (it gets pruned sometimes — reinstall
  as needed), launch chromium at
  `/opt/pw-browsers/chromium-1194/chrome-linux/chrome`; `npx astro preview`.
- Run git/zip commands from the **repo root** (`cd` persists across Bash calls;
  running from `frontend/` breaks `zip raveenthiran-headless` and `git add`).
- Bump BOTH versions (package.json + style.css) on meaningful changes; add a
  CHANGELOG entry; re-zip the theme when `raveenthiran-headless/**` changes.

## 10. The user's to-do checklist

- [ ] Upload the latest **raveenthiran-headless.zip** (v2.7.0) in WP → Themes →
      overwrite/activate; ACF → Sync if prompted; Settings → Permalinks → Save.
- [ ] Set `CF_ZONE_ID` + `CF_API_TOKEN` repo secrets (auto cache-purge).
- [ ] Confirm FTP deploy secrets are set (`FTP_SERVER` etc.) — deploys are
      running, so they likely are.
- [ ] Configure **SMTP** (Site settings → Mail) for reliable enquiry emails.
- [ ] Optionally set **Turnstile** keys (Site settings → Security).
- [ ] Verify live via incognito + footer version.
