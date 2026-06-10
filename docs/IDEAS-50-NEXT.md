# Obscura — 50 fresh improvements (post-IDEAS-200)

Written after IDEAS-200 closed (186 shipped / 14 won't-do). These are **new
directions**, not rehashes — and scoped to what actually fits raveenthiran.com:
a Vienna photographer's portfolio, **enquiry-based (no payments / no logins)**,
plugin-light, on easyname + Cloudflare. Convention/tags as before: `Awww · Conv ·
SEO · Perf · UX · A11y · Ops · Content · Sec · Dev`; effort _S/M/L_; visitor-facing
or risky = opt-in.

## 📷 Photography craft (the body of work as data)
1. **EXIF location map per project** — pin every *frame's* GPS, not just one project coord. _Content · M_
2. **"Shot on" facet** — browse/filter by camera body or film stock pulled from EXIF (e.g. "Leica M6"). _UX · M_
3. **Lens & focal-length chart** on Studio — a quiet data-viz of the whole body of work. _Awww · M_
4. **Time-of-day filter** — golden-hour / blue-hour / night, derived from EXIF timestamps. _UX · S_
5. ⏭ _removed v4.58.1 (owner: not needed)_ — **Per-project contact-sheet PDF** — one-page proof of all selects (separate from the series lookbook). _Content · S_
6. **Print-size wall preview** — show a frame at A2/A1 against a room scale (viz only, no sale). _Awww · M_
7. **Aspect-true masonry** — an archive view that respects each frame's real crop instead of forcing 4:5. _UX · M_
8. ✅ _v4.56.0_ — **Site-wide B&W toggle** — a one-tap monochrome view honouring the noir aesthetic. _Awww · S_
9. **Related-by-EXIF** — "other frames from this location / same camera" on a project. _SEO · S_
10. **Burst / sequence viewer** — step through a rapid series frame-by-frame. _Content · M_

## ✍️ Editorial depth
11. **Field notes** — a short-form micro-journal (a sentence + one frame) separate from long essays. _Content · M_
12. **Image hotspots** — annotate a frame with small note pins (behind-the-scenes). _Content · M_
13. ✅ _v4.56.0_ — **Reading-position memory** — resume a long journal article where you left off. _UX · S_
14. **Diptych compare** — view two projects side by side. _UX · S_
15. **Series mood-board intro** — auto-compose a palette/grid intro screen per series. _Awww · M_
16. **Pull-quote rotator** — surface the strongest lines from journal essays on Home/About. _Content · S_
17. **Annotated changelog / "studio log"** — a public "what's new in the studio" feed. _Content · S_
18. **Footnoted credits** — link collaborators to their own sites/socials consistently. _Content · S_

## 🧭 Visitor experience
19. ✅ _v4.56.0_ — **Keyboard gallery mode** — `j`/`k` to move through projects, `f` favourite, `?` help. _A11y · S_
20. ✅ _v4.56.0_ — **"Surprise me"** — a button that deep-links to a random project. _UX · S_
21. ✅ _v4.57.0_ — **Lightbox slideshow** — autoplay with slow Ken Burns + caption, pause on interaction. _UX · S_
22. ✅ _v4.57.0_ — **Shareable plate deep-link** — URL that opens a specific frame (#3 of a project). _UX · S_
23. **Shareable shortlist** — turn the "my selection" into a sendable link, not just an email. _Conv · M_
24. ✅ _v4.57.0_ — **PWA install polish** — proper icon set, splash, an unobtrusive "add to home screen" nudge. _Perf · S_
25. **Offline-readable journal** — cache opened articles so they survive a tunnel/flight. _Perf · M_
26. ✅ _v4.57.0_ — **Cursor "compass"** — a tiny edge indicator showing how much rail is left to scroll. _Awww · S_

## ⚡ Performance & resilience
27. **BlurHash placeholders** — sharper than the current LQIP, ~30 bytes each. _Perf · M_
28. **Video poster auto-generation** — grab frame 0 as a poster so motion plates don't pop. _Perf · S_
29. ✅ _v4.56.0_ — **size-adjust / font metrics** — eliminate the display-font CLS with a metric-matched fallback. _Perf · S_
30. ✅ _v4.56.0_ — **`decode()` before swap** — decode large images off the main thread to avoid jank. _Perf · S_
31. ✅ _v4.57.0_ — **Section-aware speculation rules** — prerender portfolio, only prefetch journal. _Perf · S_
32. ✅ _v4.57.0_ — **Cloudflare HTML edge-cache recipe** — a worker/cache-rule doc for logged-out HTML. _Perf · S_
33. ✅ _v4.58.0_ — **Weekly studio digest email** — new enquiries + top projects + CWV, to the owner. _Ops · M_
34. ✅ _v4.57.0_ — **Synthetic uptime check from a 2nd region** — catch easyname blips the local cron misses. _Ops · S_

## 🔎 SEO & reach (Vienna-specific)
35. ✅ _v4.58.0_ — **Exhibition / show schema** — `Event` markup for gallery shows + a "Shows" section. _SEO · M_
36. **Vienna district landing pages** — "editorial photographer in Neubau/1070" long-tail pages. _SEO · M_
37. ✅ _v4.56.0_ — **Heuristic auto-alt** — generate decent alt from title + EXIF when empty (no AI/subscription). _SEO · S_
38. ✅ _v4.58.0_ — **Press-kit auto-zip** — bundle bio, headshots, logo, selected hi-res into one download. _Conv · M_
39. ✅ _v4.56.0_ — **Image sitemap captions from EXIF** — richer image search entries. _SEO · S_
40. ✅ _v4.57.0_ — **"As featured in" logo wall** — a dedicated, animated press-logo band (beyond the list). _Conv · S_

## 🤝 Booking flow (still no payment)
41. ✅ _v4.58.0_ — **Availability heat-calendar** — a month grid showing busy/open, fed from a simple field or `.ics`. _Conv · M_
42. **Moodboard upload on Enquire** — let a client attach reference images with the brief. _Conv · M_
43. ✅ _v4.57.0_ — **Client onboarding page** — "what to expect / what to bring", linked from the booking confirmation. _Content · S_
44. **Pre-shoot countdown emails** — info-only drip (location, prep) before the date. _Conv · M_
45. ✅ _v4.57.0_ — **Testimonial video embeds** — short client clips alongside the text quotes. _Content · S_

## ♿ Accessibility & inclusivity
46. ✅ _v4.56.0_ — **Dyslexia-friendly reading mode** — font + spacing toggle on journal essays. _A11y · S_
47. ✅ _v4.57.0_ — **Long-descriptions** for complex editorial frames (`aria-describedby` → a hidden detailed alt). _A11y · S_
48. ✅ _v4.56.0_ — **Save-Data hero** — a single static poster instead of the slider when Save-Data is on. _Perf · S_

## 🛠 Dev & quality
49. ✅ _v4.58.0_ — **Unit tests for the pure helpers** — PDF writer, EXIF parse, field flatten, encryption (PHPUnit). _Dev · M_
50. ✅ _v4.58.0_ — **Storybook-style component gallery** — a hidden `/components` page rendering every UI block for visual QA. _Dev · M_

---

**If/when you want to build:** the highest-value, lowest-risk first picks are
**#37 auto-alt** (193 images currently have weak alt), **#8 B&W toggle** (one-tap,
on-brand), **#19 keyboard gallery**, **#33 weekly digest**, and **#41 availability
calendar**. The photography-data ones (#1–#4) are the most distinctive but depend on
clean EXIF in the library.

---

## ⏳ Queued next — Medium tier (remembered, not yet built)
Per the owner: do the small (S) ones first; the Medium (M) items are parked here
as the next run, smallest-impact-risk first within the tier:

**#1** EXIF location map · **#2** "Shot on" facet · **#3** lens/focal chart ·
**#7** aspect-true masonry · **#10** burst viewer · **#11** field notes ·
**#12** image hotspots · **#15** series mood-board · **#23** shareable shortlist ·
**#25** offline-readable journal · **#27** BlurHash · **#33** weekly studio digest ·
**#35** exhibition/Event schema · **#36** Vienna district pages · **#38** press-kit
zip · **#41** availability heat-calendar · **#42** moodboard upload · **#44**
pre-shoot countdown mails · **#49** PHPUnit · **#50** component gallery.

_Status: 22/50 shipped (all S). 28 Medium queued._
