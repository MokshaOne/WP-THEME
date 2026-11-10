/**
 * m1o-hub — main.js
 * All interactive behaviour. Loaded deferred via wp_enqueue_script.
 */
(function () {
    'use strict';

    /* ── Page transition ──────────────────────────────────────────────────── */
    function initPageTransition() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        var overlay = document.getElementById('m1o-page-transition');
        if (!overlay) return;

        document.querySelectorAll(
            'a[href]:not([href^="#"]):not([href^="mailto"]):not([href^="tel"]):not([target="_blank"])'
        ).forEach(function (a) {
            a.addEventListener('click', function (e) {
                var h = a.getAttribute('href');
                if (!h || h.startsWith('javascript')) return;
                e.preventDefault();
                overlay.classList.add('is-leaving');
                setTimeout(function () { window.location.href = h; }, 420);
            });
        });

        window.addEventListener('pageshow', function () {
            overlay.classList.remove('is-leaving');
        });
    }

    /* ── Scroll-triggered reveals ─────────────────────────────────────────── */
    function initReveals() {
        if (!window.IntersectionObserver) {
            document.querySelectorAll('.m1o-reveal').forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.querySelectorAll('.m1o-reveal').forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.06 });
        document.querySelectorAll('.m1o-reveal').forEach(function (el) { io.observe(el); });
    }

    /* ── Sticky nav scroll state ──────────────────────────────────────────── */
    function initNav() {
        var nav = document.getElementById('m1o-main-nav') ||
                  document.querySelector('.m1o-nav, nav[role="navigation"]');
        if (!nav) return;
        window.addEventListener('scroll', function () {
            nav.classList.toggle('is-scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    /* ── Genre filter pills ───────────────────────────────────────────────── */
    function initGenreFilter() {
        var pills = document.querySelectorAll('.m1o-genre-pill');
        if (!pills.length) return;
        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pills.forEach(function (p) { p.classList.remove('is-active'); });
                pill.classList.add('is-active');
                var g = pill.getAttribute('data-genre');
                document.querySelectorAll('[data-genre]').forEach(function (card) {
                    if (card.classList.contains('m1o-genre-pill')) return;
                    card.classList.toggle('is-hidden', g !== 'all' && card.getAttribute('data-genre') !== g);
                });
            });
        });
    }

    /* ── Press quote rotator ──────────────────────────────────────────────── */
    function initQuotes() {
        var quotes = document.querySelectorAll('.m1o-quote');
        if (quotes.length < 2) return;
        var i = 0;
        setInterval(function () {
            quotes[i].classList.remove('is-active');
            i = (i + 1) % quotes.length;
            quotes[i].classList.add('is-active');
        }, 4000);
    }

    /* ── Cursor glow (desktop only) ───────────────────────────────────────── */
    function initCursorGlow() {
        var g = document.getElementById('m1o-glow');
        if (!g || window.matchMedia('(pointer: coarse)').matches) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        document.addEventListener('mousemove', function (e) {
            g.style.left = e.clientX + 'px';
            g.style.top  = e.clientY + 'px';
        });
    }

    /* ── QR code (share section) ──────────────────────────────────────────── */
    function initQR() {
        var img = document.getElementById('m1o-qr-img');
        if (!img) return;
        var url = encodeURIComponent(window.location.href);
        img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' + url +
                  '&bgcolor=0a0a0a&color=b8f5ec&format=png';
    }

    /* ── Boot ─────────────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        initPageTransition();
        initReveals();
        initNav();
        initGenreFilter();
        initQuotes();
        initCursorGlow();
        initQR();
    });

}());
