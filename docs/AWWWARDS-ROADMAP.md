# From "nice WordPress theme" to a $10K Awwwards-grade site

A grounded audit of **raveenthiran — Catalogue Noir** against the reference set, and an
honest, phased roadmap to close the gap.

Reference set analysed:
`ibrahemghareib.com` · `kookie-kollective.com` · `victorfuruya.com` · `02px.com`
· `fourmula.ai` · `detroit.paris` (LV Grasse) · `cynx.io` · `boazwalma.com` · `podium.global`

> First diagnostic fact: **every one of those sites returned HTTP 403 to an automated
> fetch.** They're SPAs behind Cloudflare, hydrated by JavaScript, serving WebGL canvases —
> there is no server-rendered HTML document to scrape. Your theme returns a clean,
> crawlable HTML page. That single difference is the whole story in miniature: they are
> *applications that render an experience*; yours is *a document that displays content*.

---

## 1. The honest verdict

Your theme is **not bad** — that's the important part. It is a genuinely well-built
WordPress theme: coherent design system, real performance plumbing (WebP conversion, LQIP
blur-up, EXIF, retina image sizes), strong technical SEO (schema graph, OG/Twitter,
sitemap, breadcrumbs, REST hardening), full editorial control panel. On a scale of
WordPress photography themes it sits in the top ~10%.

But "top 10% of WordPress themes" and "Awwwards Site of the Day" are **different leagues
measured on different axes.** You are not losing on engineering hygiene. You are losing on
the two things Awwwards juries actually reward:

1. **GPU-driven motion & interaction** (the craft-code layer) — you have *none*.
2. **A bespoke creative concept + world-class content** (the art-direction layer).

Everything below makes that concrete.

---

## 2. What the $10K sites have that you don't

These sites share a near-identical technical DNA. None of it is in your codebase today
(verified: `grep` for `webgl|canvas|three|gsap|lenis` in `assets/js/theme.js` → zero hits).

| Layer | What the reference sites do | What you do today |
|---|---|---|
| **Rendering** | Images are **WebGL textures on planes** (Three.js / OGL / R3F). Enables shader transitions, hover distortion, RGB-shift, scroll-curl. | Plain `<img>` tags in the DOM. |
| **Image transitions** | GPU **displacement / morph** between projects (the signature Awwwards move). | CSS cross-fade + Ken Burns. The "nice theme" version. |
| **Scroll** | **Lenis / Locomotive** inertia smooth-scroll + GSAP **ScrollTrigger** choreography (parallax, pin, scrub). | Native browser scroll. Reads as "default". |
| **Page transitions** | **Shared-element morphs** — the clicked image flies into the next page's hero (FLIP / View Transitions / Barba). | A flat amber **wipe** overlay. |
| **Typography** | **Line/char reveal masks** (SplitText), kinetic type, variable-weight animation on scroll. | Static type (alternate-word em-bold is a nice touch, but it doesn't *move*). |
| **Intro** | A **preloader**: 0→100 counter, logo draw-in, orchestrated first paint. Sets the tone in 2s. | None — page just appears. |
| **Micro-interaction** | Magnetic buttons, cursor that *reacts to content*, marquees, sticky-skew, custom easing curves. | Custom cursor exists (good!) but it's label-swap only, not magnetic/physics. |
| **Concept** | A **singular creative idea** per site + art-directed, rule-breaking grid. | One reusable template applied uniformly to every project (a *catalogue*, by design). |
| **Stack** | Next.js / Nuxt / SvelteKit + headless CMS, on Vercel/Netlify edge. | Classic PHP WordPress theme. |

**The uncomfortable 50%:** Awwwards' public scoring is **Design 40 / Usability 30 /
Creativity 20 / Content 10**. Roughly half the score is *concept + content + photography* —
things no theme can supply. `detroit.paris`'s Louis Vuitton Grasse page wins because it's a
bespoke immersive narrative built around extraordinary assets, not because of a slider
widget. **A reusable theme is, by definition, the opposite of a bespoke concept.** This is
the deepest reason a templated site struggles to win SOTD.

---

## 3. Concrete findings in *your* code (the fixable list)

Real issues found reading the `Theme-folder` branch — these are the gap, made specific:

1. **Zero GPU layer.** `assets/js/theme.js` (497 lines) is vanilla DOM/IO/rAF. No Three.js,
   no shaders, no GSAP, no Lenis. This is the #1 thing standing between you and Awwwards.
2. **No `srcset` on the heaviest images.** `front-page.php` hero and `single-nr_project.php`
   plates output a single-resolution `<img src>`. A phone downloads a 2400×1600 hero. (Your
   archive cards *do* get srcset via `wp_get_attachment_image` — so the fix is to use that
   helper everywhere, or hand-build `srcset`.) Direct LCP win.
3. **Render-blocking fonts.** You load **6 weights of Inter Tight + 2 of JetBrains Mono**
   from Google Fonts. Self-host as `woff2`, subset to Latin, preload the 2 above-the-fold
   weights, drop the rest. Saves 200–400ms to first paint.
4. **Dead duplication.** `next-level/assets/theme.css` and `theme.js` are **byte-identical**
   copies of `assets/`. Either it's the real build target (then wire it up) or it's dead
   weight shipping to users. Remove or consolidate.
5. **Page-wipe ≠ transition.** The amber wipe in `footer.php` hides a full page reload. It
   *feels* like a transition but it's a curtain over a hard navigation. Real sites keep the
   canvas alive across routes.
6. **Sitemap has no images.** `inc/seo.php` emits a clean URL sitemap but no
   `<image:image>` entries — a missed image-SEO win for a *photography* site with 200×14
   photos. Add an image sitemap.
7. **Placeholder/sample data baked into templates.** `front-page.php` and
   `archive-nr_project.php` ship hardcoded "Nachtdienst / Naschmarkt" samples. Fine as
   empty-state, but a tell that the *real* hero content isn't driving the design yet.

None of these are embarrassing. #1 is the league change; #2–#4 are quick perf wins.

---

## 4. Where you already meet the bar (keep these — they're assets)

- **A real, restrained design system.** Catalogue Noir (warm near-black, one amber accent,
  Inter Tight + JetBrains Mono) shows taste. Most failed Awwwards entries lack exactly this.
- **Performance plumbing.** WebP auto-convert, LQIP blur-up, EXIF capture, retina image
  sizes. Genuinely above-average.
- **Technical SEO.** schema.org `@graph`, `VisualArtwork` per project, FAQPage, breadcrumbs,
  OG/Twitter, robots, REST hardening. This is *better* than most agency builds.
- **Editorial control.** 730-line Theme Settings panel = non-technical content management.
- **Accessibility basics.** Skip link, ARIA roles, focus management, reduced-motion respect.

You're not starting from zero. You're starting from a strong *foundation* that's missing its
*show layer*.

---

## 5. The architecture decision (this is the real fork)

You said: *changing from WordPress is fine, as long as managing ~200 projects × 14 photos
stays easy.* That constraint is the deciding factor. Three honest paths:

### Option A — Headless WordPress + Next.js front-end ⭐ recommended
Keep WordPress as the **admin/CMS only** (you keep the media library, ACF, the 200×14
workflow you already know). Expose content via **WPGraphQL**. Build a new front-end in
**Next.js + React-Three-Fiber + GSAP + Lenis**, deployed on **Vercel** edge.
- ✅ Content management stays exactly as easy as today (WP admin).
- ✅ Framework-grade motion, WebGL, shared-element transitions, edge performance.
- ✅ This is *literally how cynx / podium / fourmula-class sites are built.*
- ⚠️ Two systems to run; a real front-end build, not a weekend.
- **Best fit for you. Easy content + Awwwards ceiling.**

### Option B — Stay full WordPress, bolt on the WebGL layer
Add Three.js/OGL + Lenis + GSAP into the existing theme; build the preloader, smooth-scroll,
shader image transitions inside `theme.js`.
- ✅ Smallest migration, one system, keep everything you built.
- ⚠️ You fight WP's full-page-reload model for transitions (the canvas dies on navigation).
- ⚠️ TTFB/perf ceiling is lower than a static/edge front-end.
- **Can reach "Honorable Mention / nominee." Hard to reach SOTD.**

### Option C — Static (Astro/Nuxt) + headless CMS (Sanity/Storyblok)
Cleanest performance; structured content modeling for 200×14 is excellent in Sanity.
- ✅ Best Lighthouse/Core Web Vitals; great DX.
- ⚠️ You abandon the WP admin you know; content re-modeling + migration of 200 projects.

**Recommendation: Option A.** It's the only path that satisfies *both* of your constraints —
"managing 200×14 stays easy" (WordPress admin) **and** "win on Awwwards" (Next + WebGL front
end). Option B is the pragmatic stepping-stone if you want to validate the motion first
without re-platforming.

---

## 6. The roadmap — phased, prioritised

### Phase 0 — Concept & content *(do this first; it's 50% of the score)*
- [ ] Define **one creative concept** for the site (a verb, a metaphor, a constraint). Not "a
      portfolio" — e.g. "a darkroom you scroll through", "contact sheets that develop on
      hover". Awwwards rewards the *idea*.
- [ ] Curate ruthlessly. **8–12 hero projects**, not 200, drive the design. The 200 live in a
      deeper archive. Juries judge the first screen and 3 clicks.
- [ ] Get the photography to hero grade (sequencing, retouch, consistent color). No motion
      saves weak images.
- [ ] Write editorial copy with a voice. Captions, project intros, an about with a point of view.

### Phase 1 — Motion foundation *(works on current WordPress — Option B start)*
- [ ] Add **Lenis** smooth-scroll.
- [ ] Add **GSAP + ScrollTrigger**; scroll-linked reveals, parallax depth, pinned sections.
- [ ] **SplitText** line/char reveal masks on every heading.
- [ ] Build a **preloader** (0→100, logo draw-in, gate first paint).
- [ ] Upgrade the cursor to **magnetic** (lerp toward target, scale on hover, snap to CTAs).
- [ ] Replace `ease` with bespoke cubic-beziers / custom easing. Motion identity.

### Phase 2 — The WebGL layer *(the league change)*
- [ ] Render hero + grid images as **WebGL textures** (OGL is lightest; R3F if on Next).
- [ ] **Displacement/morph transition** between projects (the signature move).
- [ ] **Hover distortion** (ripple / RGB-shift / curl) on grid images.
- [ ] **Scroll-velocity skew** on the grid (images lean into momentum).
- [ ] Lazy-init WebGL; static `<img>` fallback for no-WebGL / reduced-motion / crawlers.

### Phase 3 — Choreographed navigation
- [ ] **Shared-element page transition** — clicked card morphs into the project hero (FLIP /
      View Transitions API on same-doc, or framework router transition on Next).
- [ ] Keep the WebGL canvas **alive across routes** (the reason Option A wins here).
- [ ] Retire the amber wipe (or keep only as reduced-motion fallback).

### Phase 4 — Performance & SEO hardening *(the Awwwards "develop" score + Google)*
- [ ] **Self-host fonts** (woff2, Latin subset, `font-display:swap`, preload 2 weights).
- [ ] **`srcset` + `sizes` on hero and project plates** (fix finding #2). Serve **AVIF**, WebP
      fallback.
- [ ] Inline **critical CSS**; defer the rest. Defer/async all non-critical JS.
- [ ] Put **Cloudflare** in front; full-page edge cache (WP needs a cache plugin; headless
      gets it free).
- [ ] Targets: **LCP < 2.0s · INP < 200ms · CLS < 0.05.** Test on throttled mobile.
- [ ] **Image sitemap** with `<image:image>` per project (fix finding #6).
- [ ] Per-project unique titles + meta descriptions; `ImageObject` schema with caption/EXIF.
- [ ] `hreflang` if DE/EN (you ship German legal pages — you're likely bilingual).

### Phase 5 — Re-platform *(if going for SOTD — Option A)*
- [ ] Stand up **WPGraphQL** on the existing WordPress.
- [ ] Scaffold **Next.js + R3F + GSAP + Lenis**; map CPTs (`nr_project`, gallery, ACF) to
      queries.
- [ ] Migrate Phases 1–3 motion into the framework (where it's *easier*, not harder).
- [ ] Deploy on **Vercel**; point the domain; keep WP admin private as the CMS.

### Phase 6 — Win the award (it's a process, not an accident)
- [ ] Polish the **first 5 seconds** above all — that's what juries score first.
- [ ] **Submit to Awwwards** ($15–30/site). Aim Honorable Mention → Developer/Designer Award →
      Site of the Day. Submitting *is* the marketing; nominees get a profile + backlinks.
- [ ] Also submit to **FWA, CSSDA, GSAP "site of the day", Httpster, Land-book**.
- [ ] **Dribbble is a different game** — it rewards *shots*, not live sites. Post: the loading
      sequence as a video, a transition GIF, a type-system board, the cursor interaction.
      Motion shots outperform statics 5:1. Build a 15–30s **case-study reel**.
- [ ] Write a **process case study** (concept → wireframe → WebGL → result). This is what
      turns "nice site" into "$10K commission" — clients buy the *thinking*, not the pixels.

---

## 7. So… would someone pay $10K for it?

Today: it's a **$1.5–3K premium WordPress portfolio** — clean, fast, well-built, but
templated and DOM-rendered.

The $10K tier is bought for one of two things, and you need at least one:
1. **A bespoke, award-credentialed experience** (Awwwards/FWA badge = instant price anchor), or
2. **A named designer's process and taste** (the case study, the reel, the story).

The roadmap above is the path to *both*. The single highest-leverage move is **Phase 2 (the
WebGL layer)** layered on **Phase 0 (a real concept + curated, world-class images)**.
Everything else is multipliers on those two.

---

### TL;DR
- Your engineering is strong; your **motion and concept layers are missing** — that's the gap.
- The reference sites are **WebGL applications**, yours is an **HTML document**.
- Quick wins now: `srcset` on hero/plates, self-host fonts, kill the `next-level/` duplicate.
- League change: **Lenis + GSAP + WebGL image transitions + a preloader + shared-element nav.**
- Architecture: **headless WordPress + Next.js/R3F** keeps content easy *and* unlocks SOTD.
- The award is a *process*: curate to 8–12 heroes, nail the first 5 seconds, submit, and ship
  a Dribbble motion reel + written case study.
