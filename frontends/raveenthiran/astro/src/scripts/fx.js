/* =============================================================================
   Raveenthiran v5.2 "Vogue × Dazed" — motion engine (vanilla, no deps).
   Restraint by design: a soft opacity fade on load, a 20px rise on scroll,
   the header that tucks away on scroll-down — and nothing else.
   Lightbox and cookie notice are functional, not decorative.
   All visuals live in theme.css — this file only toggles styles/classes.
   initFX() returns a cleanup fn; ui.js re-runs it on every Astro page-load.
   ============================================================================= */
export function initFX() {
        const cleanups = [];
        const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
        const on = (t, ev, fn, o) => { t.addEventListener(ev, fn, o); cleanups.push(() => t.removeEventListener(ev, fn, o)); };
        const ease = 'cubic-bezier(0,0,.2,1)'; // ease-out

        /* load: opacity 0 → 1, 0.6s ease-out, lightly staggered */
        document.querySelectorAll('[data-intro]').forEach((el, i) => {
                if (reduce) return;
                el.style.opacity = '0';
                setTimeout(() => {
                        el.style.transition = 'opacity .6s ' + ease;
                        el.style.opacity = '';
                }, 60 + i * 90);
        });

        /* scroll reveal: translateY(20px) → 0 with opacity, for text and images alike */
        const io = new IntersectionObserver((es) => es.forEach((e) => {
                if (!e.isIntersecting) return;
                const el = e.target;
                el.style.transition = 'opacity .9s ' + ease + ', transform .9s ' + ease;
                el.style.opacity = ''; el.style.transform = '';
                io.unobserve(el);
        }), { threshold: 0.08, rootMargin: '0px 0px -4% 0px' });
        const below = (el) => el.getBoundingClientRect().top > innerHeight * 0.96;
        if (!reduce) {
                document.querySelectorAll('[data-reveal], [data-reveal-img]').forEach((el) => {
                        if (!below(el)) return;
                        el.style.opacity = '0'; el.style.transform = 'translateY(20px)';
                        io.observe(el);
                });
        }
        cleanups.push(() => io.disconnect());

        /* header: tucks away on scroll-down, returns on scroll-up */
        const header = document.querySelector('[data-header]');
        if (header) {
                let lastY = scrollY;
                const onHdr = () => {
                        const y = scrollY;
                        const menuOpen = document.querySelector('[data-mobile-ovl].open');
                        header.classList.toggle('is-scrolled', y > 8);
                        if (menuOpen || y < 200 || y < lastY - 2) header.classList.remove('is-hidden');
                        else if (y > lastY + 2) header.classList.add('is-hidden');
                        lastY = y;
                };
                on(window, 'scroll', onHdr, { passive: true }); onHdr();
                // keyboard users must never lose the header
                on(header, 'focusin', () => header.classList.remove('is-hidden'));
                cleanups.push(() => header.classList.remove('is-hidden', 'is-scrolled'));
        }

        /* lightbox — every [data-zoom] in the same rail/grid is one navigable set
           (arrows, ←/→, swipe, counter, neighbour preload). role=dialog, focus trap,
           focus returned to the trigger on close. */
        const onZoom = (e) => {
                const img = e.target.closest && e.target.closest('[data-zoom]');
                if (!img) return;
                e.preventDefault(); e.stopPropagation();
                const scope = img.closest('[data-work-grid], .proj-gallery') || document;
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

        return () => cleanups.forEach((f) => { try { f(); } catch (e) {} });
}
