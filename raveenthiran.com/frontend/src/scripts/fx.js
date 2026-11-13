/* =============================================================================
   Raveenthiran v5 "Editorial" — motion engine (vanilla, no deps).
   Deliberately small: a few well-placed effects instead of many at once.
   Preloader, intro stagger, scroll + image reveals, header state, Vienna clock,
   lightbox, hover-peek, drag rails, inertial scroll, cookie notice.
   All visuals live in theme.css — this file only toggles classes.
   initFX() returns a cleanup fn; ui.js re-runs it on every Astro page-load.
   ============================================================================= */
export function initFX(opts = {}) {
        const cleanups = [];
        const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
        const fine = matchMedia('(pointer: fine)').matches;
        const on = (t, ev, fn, o) => { t.addEventListener(ev, fn, o); cleanups.push(() => t.removeEventListener(ev, fn, o)); };

        /* preloader — once per session, a quiet wordmark wipe */
        let introDelay = 80;
        if (opts.preloader && !reduce) {
                introDelay = 1300;
                const pre = document.createElement('div');
                pre.className = 'pre';
                pre.setAttribute('aria-hidden', 'true');
                pre.innerHTML = '<div class="pre__word"><span></span></div><div class="pre__line"></div>';
                pre.querySelector('span').textContent = opts.preloader;
                document.body.append(pre);
                // timers, not rAF: throttled tabs may produce no frames
                setTimeout(() => pre.classList.add('in'), 40);
                const t1 = setTimeout(() => pre.classList.add('out'), 1100);
                const t2 = setTimeout(() => pre.remove(), 2200);
                cleanups.push(() => { clearTimeout(t1); clearTimeout(t2); pre.remove(); });
        }

        /* intro stagger — small, soft rise */
        const ease = 'cubic-bezier(.16,1,.3,1)';
        document.querySelectorAll('[data-intro]').forEach((el, i) => {
                if (reduce) return;
                el.style.opacity = '0'; el.style.transform = 'translateY(24px)';
                setTimeout(() => {
                        el.style.transition = 'opacity 1s ' + ease + ', transform 1.2s ' + ease;
                        el.style.opacity = ''; el.style.transform = '';
                }, introDelay + i * 110);
        });

        /* scroll reveals (text blocks) + clip reveals (images) */
        const io = new IntersectionObserver((es) => es.forEach((e) => {
                if (!e.isIntersecting) return;
                const el = e.target;
                const img = rvOf.get(el);
                if (img) img.classList.add('is-in');
                if (el.style.opacity === '0') { el.style.transition = 'opacity 1s ' + ease + ', transform 1.2s ' + ease; el.style.opacity = ''; el.style.transform = ''; }
                io.unobserve(el);
        }), { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
        // Chrome intersects against the target's own clip-path, so a fully clipped
        // image would never "enter" — observe its parent and map back instead.
        const rvOf = new WeakMap();
        const below = (el) => el.getBoundingClientRect().top > innerHeight * 0.94;
        if (!reduce) {
                document.querySelectorAll('[data-reveal]').forEach((el) => {
                        if (!below(el)) return;
                        el.style.opacity = '0'; el.style.transform = 'translateY(32px)';
                        io.observe(el);
                });
                document.querySelectorAll('[data-reveal-img]').forEach((el) => {
                        if (!below(el)) return;
                        const host = el.parentElement || el;
                        el.classList.add('rv');
                        rvOf.set(host, el);
                        io.observe(host);
                });
        }
        cleanups.push(() => io.disconnect());

        /* optional gentle parallax ([data-parallax="0.08"]) */
        const pEls = [...document.querySelectorAll('[data-parallax]')];
        if (pEls.length && !reduce) {
                const onPar = () => pEls.forEach((el) => {
                        const sp = parseFloat(el.dataset.parallax) || 0.08;
                        const host = el.parentElement.getBoundingClientRect();
                        const d = (host.top + host.height / 2 - innerHeight / 2) * sp;
                        el.style.transform = 'translate3d(0,' + d.toFixed(1) + 'px,0) scale(1.08)';
                });
                on(window, 'scroll', onPar, { passive: true }); onPar();
        }

        /* header: solid once scrolled, tucks away on scroll-down, returns on scroll-up */
        const header = document.querySelector('[data-header]');
        if (header) {
                let lastY = scrollY;
                const onHdr = () => {
                        const y = scrollY;
                        const menuOpen = document.querySelector('[data-mobile-ovl].open');
                        header.classList.toggle('is-scrolled', y > 8);
                        if (menuOpen || y < 400 || y < lastY - 2) header.classList.remove('is-hidden');
                        else if (y > lastY + 2) header.classList.add('is-hidden');
                        lastY = y;
                };
                on(window, 'scroll', onHdr, { passive: true }); onHdr();
                // keyboard users must never lose the header
                on(header, 'focusin', () => header.classList.remove('is-hidden'));
                cleanups.push(() => header.classList.remove('is-hidden', 'is-scrolled'));
        }

        /* live Vienna clock */
        const clocks = [...document.querySelectorAll('[data-clock]')];
        if (clocks.length) {
                const setT = () => {
                        const t = new Date().toLocaleTimeString('de-AT', { timeZone: 'Europe/Vienna', hour: '2-digit', minute: '2-digit', hour12: false });
                        clocks.forEach((el) => { el.textContent = t + ' Vienna'; });
                };
                setT(); const ct = setInterval(setT, 15000);
                cleanups.push(() => clearInterval(ct));
        }

        /* lightbox — every [data-zoom] in the same rail/grid is one navigable set
           (arrows, ←/→, swipe, counter, neighbour preload). role=dialog, focus trap,
           focus returned to the trigger on close. */
        const onZoom = (e) => {
                const img = e.target.closest && e.target.closest('[data-zoom]');
                if (!img) return;
                e.preventDefault(); e.stopPropagation();
                const scope = img.closest('.rail, [data-work-grid], .proj-gallery') || document;
                const set = [...scope.querySelectorAll('[data-zoom]')];
                let idx = Math.max(0, set.indexOf(img));
                const prevFocus = document.activeElement;
                const pad2 = (n) => String(n).padStart(2, '0');

                const box = document.createElement('div');
                box.className = 'lb';
                box.setAttribute('role', 'dialog'); box.setAttribute('aria-modal', 'true'); box.setAttribute('aria-label', 'Image gallery');
                box.innerHTML =
                        '<img class="lb__img" alt="">' +
                        '<p class="lb__cap"></p><p class="lb__count label"></p>' +
                        '<button class="rbtn lb__prev" aria-label="Previous image">←</button>' +
                        '<button class="rbtn lb__next" aria-label="Next image">→</button>' +
                        '<button class="btn btn--ghost lb__close" aria-label="Close">Close ✕</button>';
                const big = box.querySelector('.lb__img');
                const cap = box.querySelector('.lb__cap');
                const count = box.querySelector('.lb__count');
                const prev = box.querySelector('.lb__prev');
                const next = box.querySelector('.lb__next');
                const x = box.querySelector('.lb__close');
                [prev, next, x].forEach((b) => b.addEventListener('click', (ev) => ev.stopPropagation()));
                if (set.length < 2) { prev.hidden = true; next.hidden = true; }

                const show = (i) => {
                        idx = (i + set.length) % set.length;
                        const im = set[idx];
                        const src = im.currentSrc || im.src;
                        big.style.opacity = '0';
                        const swap = () => { big.src = src; big.alt = im.alt || ''; big.style.opacity = ''; };
                        const p = new Image(); p.onload = swap; p.onerror = swap; p.src = src;
                        cap.textContent = im.alt || '';
                        count.textContent = pad2(idx + 1) + ' / ' + pad2(set.length);
                        [set[(idx + 1) % set.length], set[(idx - 1 + set.length) % set.length]].forEach((n) => {
                                if (n && n !== im) { const w = new Image(); w.src = n.currentSrc || n.src; }
                        });
                };
                prev.addEventListener('click', () => show(idx - 1));
                next.addEventListener('click', () => show(idx + 1));
                document.body.append(box);
                document.documentElement.style.overflow = 'hidden';
                show(idx);
                requestAnimationFrame(() => box.classList.add('on'));

                const close = () => {
                        box.classList.remove('on'); setTimeout(() => box.remove(), 400);
                        document.documentElement.style.overflow = '';
                        removeEventListener('keydown', onKey);
                        if (prevFocus && prevFocus.focus) prevFocus.focus();
                };
                const onKey = (ev) => {
                        if (ev.key === 'Escape') { close(); return; }
                        if (ev.key === 'ArrowLeft') { show(idx - 1); return; }
                        if (ev.key === 'ArrowRight') { show(idx + 1); return; }
                        if (ev.key === 'Tab') {
                                const f = [prev, next, x].filter((b) => !b.hidden);
                                const at = f.indexOf(document.activeElement);
                                ev.preventDefault();
                                f[(at + (ev.shiftKey ? -1 : 1) + f.length) % f.length].focus();
                        }
                };
                let swiped = false;
                box.addEventListener('click', () => { if (swiped) { swiped = false; return; } close(); });
                x.addEventListener('click', close);
                addEventListener('keydown', onKey);
                x.focus();

                let tx = null, ty = null;
                box.addEventListener('pointerdown', (ev) => { tx = ev.clientX; ty = ev.clientY; });
                box.addEventListener('pointerup', (ev) => {
                        if (tx === null) return;
                        const dx = ev.clientX - tx, dy = ev.clientY - ty; tx = ty = null;
                        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.5) { swiped = true; show(idx + (dx < 0 ? 1 : -1)); }
                });
        };
        on(document, 'click', onZoom, true);

        /* EU cookie notice (dismissal remembered) */
        let hasCookieOk = true;
        try { hasCookieOk = !!localStorage.getItem('rvn-cookie-ok'); } catch (e) {}
        if (!hasCookieOk) {
                const cb = document.createElement('div');
                cb.className = 'cookie';
                cb.setAttribute('role', 'region'); cb.setAttribute('aria-label', 'Cookie notice');
                cb.innerHTML =
                        '<p><strong>Cookies.</strong> This site uses only technically necessary cookies. Analytics and embeds load after your consent — see <a href="/legal/datenschutz/">Datenschutz</a>.</p>' +
                        '<div class="cookie__row"><button class="btn btn--ghost" data-c="necessary">Necessary only</button><button class="btn btn--solid" data-c="all">Accept</button></div>';
                cb.querySelectorAll('button').forEach((b) => b.addEventListener('click', () => {
                        try { localStorage.setItem('rvn-cookie-ok', b.dataset.c); } catch (e) {}
                        cb.classList.remove('on'); setTimeout(() => cb.remove(), 800);
                }));
                document.body.append(cb);
                const ct = setTimeout(() => cb.classList.add('on'), 1600);
                cleanups.push(() => { clearTimeout(ct); cb.remove(); });
        }

        /* hover-peek: a floating colour preview that trails the pointer over index rows */
        const rows = [...document.querySelectorAll('[data-peek]')];
        if (rows.length && fine && !reduce) {
                const peek = document.createElement('img');
                peek.className = 'peek'; peek.alt = ''; peek.decoding = 'async';
                document.body.append(peek);
                let mx = 0, my = 0, px = 0, py = 0, active = false, raf = 0;
                const loop = () => {
                        px += (mx - px) * 0.16; py += (my - py) * 0.16;
                        peek.style.transform = 'translate3d(' + (px + 28).toFixed(1) + 'px,' + (py - peek.offsetHeight / 2).toFixed(1) + 'px,0)';
                        raf = active || Math.abs(mx - px) > 0.5 ? requestAnimationFrame(loop) : 0;
                };
                const kick = () => { if (!raf) raf = requestAnimationFrame(loop); };
                on(window, 'mousemove', (e) => { mx = e.clientX; my = e.clientY; if (active) kick(); }, { passive: true });
                rows.forEach((row) => {
                        on(row, 'mouseenter', (e) => {
                                const im = row.dataset.img; if (!im) return;
                                if (!active) { px = mx = e.clientX; py = my = e.clientY; }
                                peek.src = im; active = true; peek.classList.add('on'); kick();
                        });
                        on(row, 'mouseleave', () => { active = false; peek.classList.remove('on'); });
                });
                cleanups.push(() => { cancelAnimationFrame(raf); peek.remove(); });
        }

        /* drag-to-scroll rails */
        document.querySelectorAll('[data-drag]').forEach((rail) => {
                let down = false, sx = 0, sl = 0, moved = 0;
                on(rail, 'pointerdown', (e) => { if (e.pointerType !== 'mouse') return; down = true; sx = e.clientX; sl = rail.scrollLeft; moved = 0; rail.classList.add('is-drag'); });
                on(rail, 'pointermove', (e) => { if (!down) return; const dx = e.clientX - sx; moved = Math.max(moved, Math.abs(dx)); rail.scrollLeft = sl - dx; });
                const up = () => { down = false; rail.classList.remove('is-drag'); };
                on(rail, 'pointerup', up); on(rail, 'pointerleave', up);
                on(rail, 'click', (e) => { if (moved > 8) { e.preventDefault(); e.stopPropagation(); } }, true);
                on(rail, 'dragstart', (e) => e.preventDefault());
        });

        cleanups.push(smoothScroll(reduce, fine));
        return () => cleanups.forEach((f) => { try { f(); } catch (e) {} });
}

/* ── inertial smooth scroll (mini-Lenis). Desktop wheel only; touch, reduced
   motion and keyboard scrolling stay native. Horizontal rails under the cursor
   own the wheel until they reach their end. ──────────────────────────────── */
function smoothScroll(reduce, fine) {
        if (reduce || !fine || matchMedia('(pointer: coarse)').matches || 'ontouchstart' in window) return () => {};
        let target = scrollY, current = scrollY, raf = 0, running = false, programmatic = false;
        const max = () => document.documentElement.scrollHeight - innerHeight;
        const clamp = (v) => Math.max(0, Math.min(max(), v));
        const loop = () => {
                current += (target - current) * 0.12;
                if (Math.abs(target - current) < 0.4) { current = target; running = false; }
                programmatic = true; window.scrollTo(0, current); programmatic = false;
                if (running) raf = requestAnimationFrame(loop);
        };
        const start = () => { if (!running) { running = true; raf = requestAnimationFrame(loop); } };
        const onWheel = (e) => {
                if (e.ctrlKey || document.documentElement.style.overflow === 'hidden' || document.body.style.overflow === 'hidden') return;
                const unit = e.deltaMode === 1 ? 16 : e.deltaMode === 2 ? innerHeight : 1;
                const rail = e.target.closest && e.target.closest('[data-drag]');
                if (rail && rail.scrollWidth > rail.clientWidth + 1) {
                        const d = (Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY) * unit;
                        const maxL = rail.scrollWidth - rail.clientWidth;
                        if ((d > 0 && rail.scrollLeft < maxL - 1) || (d < 0 && rail.scrollLeft > 1)) {
                                e.preventDefault(); rail.scrollLeft += d; return;
                        }
                }
                e.preventDefault();
                target = clamp(target + e.deltaY * unit); start();
        };
        const resync = () => { if (!programmatic && !running) { target = current = scrollY; } };
        addEventListener('wheel', onWheel, { passive: false });
        addEventListener('scroll', resync, { passive: true });
        addEventListener('resize', resync);
        return () => { cancelAnimationFrame(raf); removeEventListener('wheel', onWheel); removeEventListener('scroll', resync); removeEventListener('resize', resync); };
}
