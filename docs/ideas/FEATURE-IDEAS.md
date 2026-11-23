# Obscura — 20 features & ideas worth building

Curated for **raveenthiran.com** (photography portfolio, ~200 projects × ~14 photos,
WordPress on easyname). Ordered by impact-to-effort. Each notes the *why*, rough
effort, and whether it helps **Awwwards (A)**, **conversion/clients (C)**, or
**SEO/perf (S)**.

---

## Tier A — the "$10K experience" layer (do these for Awwwards)

1. **WebGL image transitions between projects** *(A · large)*
   GPU displacement/morph when opening a project — images as textures on planes
   (OGL ~16 KB). The single biggest jury differentiator. Needs on-device tuning;
   ship behind a feature flag with the current `<img>` as guaranteed fallback.

2. **Shared-element page transition** *(A · large)*
   The clicked card flies into the project hero (View Transitions API on same-doc,
   or a FLIP clone). Turns navigation into choreography. Pairs with #1.

3. **Cinematic intro on first load** *(A · medium)*
   Upgrade the preloader into a 2–3s branded title sequence (wordmark draw-in,
   counter, first hero reveal). Sets "this is crafted" in the first seconds.

4. **Custom WebGL hover-distortion on the grid** *(A · medium)*
   Ripple / RGB-shift / liquid warp on portfolio thumbnails under the cursor.
   Desktop-only, reduced-motion-safe.

5. **Kinetic / variable-weight type on the hero** *(A · medium)*
   Animate Inter Tight weight + letter-spacing on load and slide-change
   (line-mask reveals). Type that *moves* reads premium.

---

## Tier C — turn visitors into paying clients

6. **Project inquiry from the project page** *(C · small)*
   "Commission similar" already deep-links to Enquire with the type pre-filled —
   extend it to also pass the project title so the brief starts pre-written.

7. **Client galleries / proofing** *(C · large)*
   Private, password-protected delivery galleries (per client) with favourites +
   download. Replaces Pixieset; keeps clients on your domain. Big retention lever.

8. **Testimonials / press strip** *(C · small)*
   The `nr_testimonial` CPT already exists but isn't surfaced — show a rotating
   quote + client logos on Home/About. Social proof anchors the $10K price.

9. **"Selected clients" + recognition wall** *(C · small)*
   Logos (SZ, NYT Mag, Belvedere…) + awards. The recognition parser already
   exists in `functions.php` — give it a dedicated section.

10. **Multi-step quote → PDF estimate** *(C · medium)*
    Extend the calculator: email the visitor a branded PDF estimate and notify
    you. Moves "price check" into a real lead.

11. **Availability / booking calendar** *(C · medium)*
    A real date-picker on Enquire (Cal.com embed via the iframe slot, or native)
    showing open shoot windows — reduces back-and-forth.

---

## Tier S — SEO, performance, reach

12. **Critical-CSS + async stylesheet** *(S · medium)*
    Inline above-the-fold CSS, load the 74 KB `theme.css` non-blocking. The main
    remaining mobile **FCP** win. Needs care (FOUC) — do with on-device checks.

13. **AVIF images + Cloudflare in front** *(S · small)*
    Add AVIF to the existing WebP pipeline; put Cloudflare (free) before easyname
    for edge caching → lower TTFB + LCP on mobile.

14. **Bilingual DE/EN with hreflang** *(S · medium)*
    You ship German legal pages — go fully bilingual with `hreflang`. Doubles
    organic reach in the DACH market.

15. **Per-project rich SEO** *(S · small)*
    Unique meta titles/descriptions per project + `ImageObject` schema with
    captions/EXIF (camera, lens). Photography-specific SERP wins.

16. **Journal / editorial blog** *(S · medium)*
    A `nr_journal` CPT for behind-the-scenes essays — the content engine that
    earns long-tail search and gives returning visitors a reason.

---

## Tier X — distinctive touches

17. **EXIF "shot on" overlay** *(A/C · small)*
    The EXIF capture already runs on upload — surface camera/lens/ISO as an
    optional caption on plates. Photographers and clients love it.

18. **Sound design (opt-in)** *(A · small)*
    Subtle UI ticks on hover/slide-change with a persistent mute toggle. A
    common Awwwards signature; ship muted by default.

19. **Light/day theme variant** *(A · medium)*
    A bone-white inverse of Catalogue Noir, toggled or time-of-day aware. The
    color system already supports it (tokens are configurable).

20. **Filterable, map, or year-timeline archive views** *(A/C · medium)*
    Beyond the grid: a "by year" timeline or a shoot-location map for 200
    projects — makes a large body of work feel authored, not dumped.

---

### Suggested next three
**#1 WebGL transitions** (the Awwwards lever) → **#12 critical-CSS + #13 Cloudflare**
(lock in mobile speed) → **#7 client galleries** (the feature clients pay for).
