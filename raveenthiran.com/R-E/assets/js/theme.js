/**
 * R-E — Theme JavaScript
 * GSAP animations, 3D tilt, magnetic buttons, custom cursor,
 * hero slider, lightbox, modals, page transitions
 */

(function () {
    'use strict';

    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => [...(c || document).querySelectorAll(s)];
    const body = document.body;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ─── Custom Cursor ───────────────────────────────────── */

    if (body.classList.contains('re-cursor-active') && window.innerWidth > 900) {
        const cur = document.createElement('div');
        cur.className = 're-cur';
        cur.innerHTML = '<span class="re-cur__label"></span>';
        body.appendChild(cur);

        const label = cur.querySelector('.re-cur__label');

        document.addEventListener('mousemove', function (e) {
            cur.style.left = e.clientX + 'px';
            cur.style.top = e.clientY + 'px';
        });

        document.addEventListener('mouseover', function (e) {
            const el = e.target.closest('a, button, [data-lightbox-src], .re-card, [role="button"]');
            if (el) {
                cur.classList.add('is-on-link');
                if (el.matches('.re-card, [data-lightbox-src]')) label.textContent = 'view';
                else if (el.matches('button[type="submit"], .re-btn--primary')) label.textContent = 'send';
                else label.textContent = 'go';
            }
        });

        document.addEventListener('mouseout', function (e) {
            const el = e.target.closest('a, button, [data-lightbox-src], .re-card, [role="button"]');
            if (el) {
                cur.classList.remove('is-on-link');
                label.textContent = '';
            }
        });
    }

    /* ─── Tube Light Nav Lamp ─────────────────────────────── */

    const nav = $('.re-nav');
    if (nav) {
        const lamp = document.createElement('div');
        lamp.className = 're-nav-lamp';
        nav.appendChild(lamp);

        function placeLamp(el) {
            if (!el) return;
            lamp.style.width = el.offsetWidth + 'px';
            lamp.style.left = el.offsetLeft + 'px';
        }

        const activeLink = nav.querySelector('[aria-current="page"]') || nav.querySelector('a');
        placeLamp(activeLink);

        $$('a', nav).forEach(function (a) {
            a.addEventListener('mouseenter', function () { placeLamp(a); });
        });
        nav.addEventListener('mouseleave', function () { placeLamp(activeLink); });

        window.addEventListener('resize', function () { placeLamp(activeLink); });
    }

    /* ─── 3D Card Tilt (Vanilla Tilt) ─────────────────────── */

    if (body.classList.contains('re-3d-active') && window.innerWidth > 900 && !reducedMotion) {
        $$('[data-tilt]').forEach(function (el) {
            VanillaTilt.init(el, {
                max: 12,
                speed: 400,
                glare: true,
                'max-glare': 0.15,
                perspective: 1200,
                gyroscope: false,
            });
        });
    }

    /* ─── Magnetic Buttons ────────────────────────────────── */

    if (body.classList.contains('re-3d-active') && window.innerWidth > 900 && !reducedMotion) {
        $$('.re-magnetic').forEach(function (wrap) {
            const inner = wrap.firstElementChild;
            if (!inner) return;

            wrap.addEventListener('mousemove', function (e) {
                const rect = wrap.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                inner.style.transform = 'translate(' + (x * 0.25) + 'px, ' + (y * 0.25) + 'px)';
            });

            wrap.addEventListener('mouseleave', function () {
                inner.style.transform = '';
            });
        });
    }

    /* ─── Spotlight Effect ────────────────────────────────── */

    $$('.re-spotlight').forEach(function (el) {
        el.addEventListener('mousemove', function (e) {
            const rect = el.getBoundingClientRect();
            el.style.setProperty('--spot-x', (e.clientX - rect.left) + 'px');
            el.style.setProperty('--spot-y', (e.clientY - rect.top) + 'px');
        });
    });

    /* ─── GSAP ScrollTrigger Animations ───────────────────── */

    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined' && !reducedMotion) {
        gsap.registerPlugin(ScrollTrigger);

        // Reveal animations
        $$('.re-reveal').forEach(function (el) {
            ScrollTrigger.create({
                trigger: el,
                start: 'top 85%',
                onEnter: function () { el.classList.add('is-visible'); },
                once: true,
            });
        });

        $$('.re-reveal-3d').forEach(function (el) {
            ScrollTrigger.create({
                trigger: el,
                start: 'top 85%',
                onEnter: function () { el.classList.add('is-visible'); },
                once: true,
            });
        });

        $$('.re-stagger').forEach(function (el) {
            ScrollTrigger.create({
                trigger: el,
                start: 'top 85%',
                onEnter: function () { el.classList.add('is-visible'); },
                once: true,
            });
        });

        // GSAP data-attribute animations
        $$('[data-gsap-fade-up]').forEach(function (el) {
            gsap.to(el, {
                y: 0,
                opacity: 1,
                duration: 0.8,
                ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 85%', once: true },
            });
        });

        $$('[data-gsap-scale-in]').forEach(function (el) {
            gsap.to(el, {
                scale: 1,
                opacity: 1,
                duration: 0.8,
                ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 85%', once: true },
            });
        });

        $$('[data-gsap-rotate-in]').forEach(function (el) {
            gsap.to(el, {
                rotateX: 0,
                opacity: 1,
                duration: 1,
                ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 85%', once: true },
            });
        });

        // Parallax layers
        $$('[data-gsap-parallax]').forEach(function (el) {
            const speed = parseFloat(el.dataset.gsapParallax) || 0.3;
            gsap.to(el, {
                y: function () { return -100 * speed; },
                ease: 'none',
                scrollTrigger: {
                    trigger: el.parentElement,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: true,
                },
            });
        });
    }

    /* ─── Hero Slider ─────────────────────────────────────── */

    const heroWrap = $('.re-hero');
    if (heroWrap) {
        const slides = $$('.re-hero__slide', heroWrap);
        const dots = $$('.re-hero__dot', heroWrap);
        const counter = $('.re-hero__counter', heroWrap);
        const prevBtn = $('.re-hero__arrow--prev', heroWrap);
        const nextBtn = $('.re-hero__arrow--next', heroWrap);
        let current = 0;
        let locked = false;
        let autoTimer = null;
        const use3D = body.classList.contains('re-3d-active') && !reducedMotion;
        const autoPlay = heroWrap.dataset.autoplay !== 'false';
        const interval = parseInt(heroWrap.dataset.interval, 10) || 9000;

        function goSlide(idx, direction) {
            if (locked || idx === current || !slides[idx]) return;
            locked = true;
            const prev = slides[current];
            const next = slides[idx];
            const dir = direction || (idx > current ? 'right' : 'left');

            if (use3D) {
                prev.classList.add(dir === 'right' ? 're-3d-exit-left' : 're-3d-exit-right');
                next.classList.add(dir === 'right' ? 're-3d-enter-left' : 're-3d-enter-right');
                next.classList.add('is-active');

                setTimeout(function () {
                    prev.classList.remove('is-active', 're-3d-exit-left', 're-3d-exit-right');
                    next.classList.remove('re-3d-enter-left', 're-3d-enter-right');
                    locked = false;
                }, 700);
            } else {
                prev.classList.remove('is-active');
                next.classList.add('is-active');
                setTimeout(function () { locked = false; }, 400);
            }

            dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); });
            if (counter) counter.textContent = String(idx + 1).padStart(2, '0') + ' of ' + String(slides.length).padStart(2, '0');

            current = idx;
        }

        if (nextBtn) nextBtn.addEventListener('click', function () { goSlide((current + 1) % slides.length, 'right'); resetAuto(); });
        if (prevBtn) prevBtn.addEventListener('click', function () { goSlide((current - 1 + slides.length) % slides.length, 'left'); resetAuto(); });

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goSlide(i); resetAuto(); });
        });

        document.addEventListener('keydown', function (e) {
            if (!heroWrap.closest(':hover') && document.scrollingElement.scrollTop > window.innerHeight) return;
            if (e.key === 'ArrowRight') { goSlide((current + 1) % slides.length, 'right'); resetAuto(); }
            if (e.key === 'ArrowLeft') { goSlide((current - 1 + slides.length) % slides.length, 'left'); resetAuto(); }
        });

        function resetAuto() {
            clearInterval(autoTimer);
            if (autoPlay) autoTimer = setInterval(function () { goSlide((current + 1) % slides.length, 'right'); }, interval);
        }
        resetAuto();
    }

    /* ─── Mobile Sidebar ──────────────────────────────────── */

    const sidebar = $('.re-sidebar');
    const backdrop = $('.re-sidebar-backdrop');
    if (sidebar) {
        $$('[data-sidebar-open]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                sidebar.classList.add('is-open');
                if (backdrop) backdrop.classList.add('is-on');
                body.classList.add('is-modal-open');
            });
        });

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            if (backdrop) backdrop.classList.remove('is-on');
            body.classList.remove('is-modal-open');
        }

        $$('[data-sidebar-close]', sidebar).forEach(function (btn) { btn.addEventListener('click', closeSidebar); });
        if (backdrop) backdrop.addEventListener('click', closeSidebar);
    }

    /* ─── Modal System ────────────────────────────────────── */

    $$('[data-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            var id = trigger.dataset.modal;
            var modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('is-on');
            body.classList.add('is-modal-open');

            var firstInput = modal.querySelector('input, textarea, button, [tabindex]');
            if (firstInput) firstInput.focus();
        });
    });

    $$('.re-modal').forEach(function (modal) {
        var closeBtn = modal.querySelector('[data-modal-close]');
        var backdropEl = modal.querySelector('.re-modal__backdrop');

        function close() {
            modal.classList.remove('is-on');
            body.classList.remove('is-modal-open');
        }

        if (closeBtn) closeBtn.addEventListener('click', close);
        if (backdropEl) backdropEl.addEventListener('click', close);

        modal.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();

            // Focus trap
            if (e.key === 'Tab') {
                var focusable = modal.querySelectorAll('a, button, input, textarea, select, [tabindex]');
                if (!focusable.length) return;
                var first = focusable[0];
                var last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });
    });

    /* ─── Lightbox ────────────────────────────────────────── */

    var lightbox = $('#re-lightbox');
    if (lightbox) {
        var lbImg = lightbox.querySelector('.re-lightbox__image');
        var lbMeta = lightbox.querySelector('.re-lightbox__meta');
        var lbClose = lightbox.querySelector('[data-lightbox-close]');
        var lbPrev = lightbox.querySelector('[data-lightbox-prev]');
        var lbNext = lightbox.querySelector('[data-lightbox-next]');
        var lbItems = [];
        var lbIndex = 0;

        $$('[data-lightbox-src]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var group = btn.closest('[data-lightbox-group]');
                lbItems = group ? $$('[data-lightbox-src]', group) : [btn];
                lbIndex = lbItems.indexOf(btn);
                showLightbox(lbIndex);
            });
        });

        function showLightbox(idx) {
            var item = lbItems[idx];
            if (!item) return;
            lbImg.src = item.dataset.lightboxSrc;
            if (lbMeta) lbMeta.textContent = (idx + 1) + ' / ' + lbItems.length + (item.dataset.caption ? ' — ' + item.dataset.caption : '');
            lightbox.classList.add('is-on');
            body.classList.add('is-modal-open');
            lbIndex = idx;
        }

        function closeLightbox() {
            lightbox.classList.remove('is-on');
            body.classList.remove('is-modal-open');
        }

        if (lbClose) lbClose.addEventListener('click', closeLightbox);
        lightbox.querySelector('.re-lightbox__backdrop')?.addEventListener('click', closeLightbox);
        if (lbPrev) lbPrev.addEventListener('click', function () { showLightbox((lbIndex - 1 + lbItems.length) % lbItems.length); });
        if (lbNext) lbNext.addEventListener('click', function () { showLightbox((lbIndex + 1) % lbItems.length); });

        lightbox.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft' && lbPrev) lbPrev.click();
            if (e.key === 'ArrowRight' && lbNext) lbNext.click();
        });
    }

    /* ─── Horizontal Rail Scroll ──────────────────────────── */

    $$('[data-h-rail]').forEach(function (rail) {
        rail.addEventListener('wheel', function (e) {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                e.preventDefault();
                rail.scrollLeft += e.deltaY;
            }
        }, { passive: false });
    });

    /* ─── Plate Counter ───────────────────────────────────── */

    $$('[data-plate-counter]').forEach(function (counter) {
        var rail = counter.closest('.re-project')?.querySelector('.re-project__rail');
        if (!rail) return;
        var plates = $$('.re-plate', rail);
        if (!plates.length) return;

        var observer = new IntersectionObserver(function (entries) {
            var best = null;
            var bestRatio = 0;
            entries.forEach(function (entry) {
                if (entry.intersectionRatio > bestRatio) {
                    bestRatio = entry.intersectionRatio;
                    best = entry.target;
                }
            });
            if (best) {
                var idx = plates.indexOf(best);
                counter.textContent = String(idx + 1).padStart(2, '0') + ' / ' + String(plates.length).padStart(2, '0');
            }
        }, { root: rail, threshold: [0.3, 0.5, 0.7, 1] });

        plates.forEach(function (p) { observer.observe(p); });
    });

    /* ─── FAQ Accordion ───────────────────────────────────── */

    $$('.re-faq__q').forEach(function (q, i) {
        q.addEventListener('click', function () {
            var item = q.closest('.re-faq__item');
            var wasOpen = item.classList.contains('is-open');
            $$('.re-faq__item.is-open').forEach(function (o) { o.classList.remove('is-open'); });
            if (!wasOpen) item.classList.add('is-open');
        });
        if (i === 0) q.closest('.re-faq__item')?.classList.add('is-open');
    });

    /* ─── Page Transition Wipe ────────────────────────────── */

    if (body.classList.contains('re-wipe-active') && !reducedMotion) {
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[href]');
            if (!link) return;
            var href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
            if (link.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey) return;
            if (link.dataset.noTransition === '1' || link.closest('[data-modal]')) return;
            if (link.hostname !== window.location.hostname) return;

            e.preventDefault();
            body.classList.add('is-wiping');
            setTimeout(function () { window.location.href = href; }, 420);
        });
    }

    /* ─── Cookie Notice ───────────────────────────────────── */

    var cookie = $('.re-cookie');
    if (cookie && !localStorage.getItem('re_cookie_consent')) {
        setTimeout(function () { cookie.classList.add('is-visible'); }, 2000);

        $$('[data-cookie-accept], [data-cookie-decline]', cookie).forEach(function (btn) {
            btn.addEventListener('click', function () {
                localStorage.setItem('re_cookie_consent', btn.dataset.cookieAccept ? 'accepted' : 'declined');
                cookie.classList.remove('is-visible');
            });
        });
    }

    /* ─── Lazy Load ───────────────────────────────────────── */

    if ('IntersectionObserver' in window) {
        var lazyObs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    lazyObs.unobserve(img);
                }
            });
        }, { rootMargin: '200px' });

        $$('img[data-src]').forEach(function (img) { lazyObs.observe(img); });
    }

    /* ─── Morphing Text ───────────────────────────────────── */

    $$('.re-morph[data-words]').forEach(function (el) {
        var words = el.dataset.words.split(',').map(function (w) { return w.trim(); });
        if (words.length < 2) return;
        var idx = 0;

        setInterval(function () {
            el.classList.add('is-morphing');
            setTimeout(function () {
                idx = (idx + 1) % words.length;
                el.textContent = words[idx];
                el.classList.remove('is-morphing');
                el.classList.add('is-entering');
                setTimeout(function () { el.classList.remove('is-entering'); }, 500);
            }, 500);
        }, 3000);
    });

    /* ─── Service Pre-fill Bridge ─────────────────────────── */

    $$('[data-service]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            sessionStorage.setItem('re_service', btn.dataset.service);
        });
    });

    /* ─── AJAX Forms ──────────────────────────────────────── */

    $$('form[data-ajax]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('[type="submit"]');
            var origText = btn ? btn.textContent : '';
            if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }

            var data = new FormData(form);
            data.append('action', form.dataset.ajax);
            data.append('nonce', (typeof reData !== 'undefined' && reData.nonce) || '');

            fetch((typeof reData !== 'undefined' && reData.ajaxUrl) || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: data,
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    form.innerHTML = '<p class="re-prose" style="color:var(--re-amber);">' + (res.data?.message || 'Sent!') + '</p>';
                } else {
                    if (btn) { btn.disabled = false; btn.textContent = origText; }
                    alert(res.data?.message || 'Error');
                }
            })
            .catch(function () {
                if (btn) { btn.disabled = false; btn.textContent = origText; }
            });
        });
    });
})();
