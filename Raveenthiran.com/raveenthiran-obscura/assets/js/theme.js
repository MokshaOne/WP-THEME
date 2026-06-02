/* ============================================================
   raveenthiran — theme.js · Catalogue Noir
   - custom cursor
   - hero slider (dot constellation)
   - portfolio filter chips + horizontal rail
   - project detail horizontal rail
   - booking modal open/close
   - mobile sidebar open/close
   - clock
   - page-transition amber wipe
   - FAQ accordion
   ============================================================ */
(function () {
  'use strict';

  const $  = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

  /* ---------- clock ---------- */
  function tickClock() {
    const el = $('.nr-clock');
    if (!el) return;
    const d = new Date();
    const h = String(d.getHours()).padStart(2, '0');
    const m = String(d.getMinutes()).padStart(2, '0');
    el.textContent = h + ':' + m + ' ' + (Intl.DateTimeFormat().resolvedOptions().timeZone.split('/').pop() || 'LOCAL');
  }
  tickClock();
  setInterval(tickClock, 30 * 1000);

  /* ---------- custom cursor ---------- */
  const cur = $('.nr-cur');
  const curLbl = cur && cur.querySelector('.nr-cur__lbl');
  if (cur && window.matchMedia('(hover:hover)').matches) {
    // Smooth (lerp) follow instead of snapping to the pointer.
    let mx = window.innerWidth / 2, my = window.innerHeight / 2, cx = mx, cy = my;
    window.addEventListener('mousemove', (e) => { mx = e.clientX; my = e.clientY; }, { passive: true });
    document.addEventListener('mouseenter', () => cur.style.opacity = '1');
    document.addEventListener('mouseleave', () => cur.style.opacity = '0');
    (function follow() {
      cx += (mx - cx) * 0.18; cy += (my - cy) * 0.18;
      cur.style.transform = `translate(${cx}px, ${cy}px) translate(-50%, -50%)`;
      requestAnimationFrame(follow);
    })();

    const linkSelector = 'a, button, .nr-chip, .nr-card, .nr-hero__thumb, [data-modal], [data-hero-prev], [data-hero-next]';
    document.body.addEventListener('mouseover', (e) => {
      const t = e.target.closest(linkSelector);
      if (t) {
        cur.classList.add('is-on-link');
        if (curLbl) {
          const lbl = t.dataset.curLabel ||
            (t.classList.contains('nr-card') ? 'view' :
             t.classList.contains('nr-hero__thumb') ? '' :
             t.tagName === 'BUTTON' && t.type === 'submit' ? 'send' :
             'go');
          curLbl.textContent = lbl;
        }
      }
    });
    document.body.addEventListener('mouseout', (e) => {
      if (e.target.closest(linkSelector)) cur.classList.remove('is-on-link');
    });

    // Magnetic buttons — the element eases toward the pointer while hovered.
    document.querySelectorAll('.nr-btn, .nr-book-trigger, .nr-icon-btn, .nr-hero__arrow, [data-magnetic]').forEach((btn) => {
      btn.addEventListener('mousemove', (e) => {
        const r = btn.getBoundingClientRect();
        btn.style.transform = `translate(${(e.clientX - (r.left + r.width / 2)) * 0.22}px, ${(e.clientY - (r.top + r.height / 2)) * 0.28}px)`;
      });
      btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
    });
  }

  /* ---------- tube-light nav lamp ----------
     Single amber bar with a 3-layer glow halo that slides between
     the active and hovered nav items. Active item is detected via
     aria-current="page" or wp_nav_menu's current-menu-item class. */
  (function tubeLightLamp() {
    const menu = $('.nr-menu');
    if (!menu) return;
    const items = $$('a', menu);
    if (!items.length) return;

    const lamp = document.createElement('span');
    lamp.className = 'nr-menu__lamp';
    lamp.setAttribute('aria-hidden', 'true');
    menu.appendChild(lamp);

    const activeItem = menu.querySelector('a[aria-current="page"]')
      || menu.querySelector('.current-menu-item > a')
      || menu.querySelector('.current_page_item > a')
      || items[0];

    function placeLamp(el) {
      if (!el) return;
      const menuRect = menu.getBoundingClientRect();
      const elRect   = el.getBoundingClientRect();
      const x = (elRect.left - menuRect.left) + (elRect.width / 2);
      const w = Math.min(Math.max(elRect.width * 0.55, 28), 56);
      lamp.style.width = w + 'px';
      lamp.style.transform = `translate3d(${x - (w / 2)}px, 0, 0)`;
    }

    // Place initially (after fonts / layout settle to avoid mis-measurement)
    requestAnimationFrame(function () {
      placeLamp(activeItem);
      // delay the reveal so first paint is not a flash from 0,0
      setTimeout(function () { menu.classList.add('is-ready'); }, 60);
    });
    // Re-place on resize
    window.addEventListener('resize', function () { placeLamp(activeItem); }, { passive: true });
    // Re-place when web fonts finish loading (changes link widths)
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(function () { placeLamp(activeItem); });
    }

    // Slide on hover, return to active on mouseleave
    items.forEach(function (it) {
      it.addEventListener('mouseenter', function () { placeLamp(it); });
    });
    menu.addEventListener('mouseleave', function () { placeLamp(activeItem); });
  })();

  /* ---------- mobile sidebar ---------- */
  const sidebar = $('.nr-sidebar');
  const backdrop = $('.nr-sidebar-backdrop');
  const openSidebar = () => { sidebar && sidebar.classList.add('is-on'); backdrop && backdrop.classList.add('is-on'); document.body.classList.add('is-modal-open'); };
  const closeSidebar = () => { sidebar && sidebar.classList.remove('is-on'); backdrop && backdrop.classList.remove('is-on'); document.body.classList.remove('is-modal-open'); };
  $$('.nr-hamb, [data-sidebar-open]').forEach(b => b.addEventListener('click', openSidebar));
  $$('.nr-sidebar__close, .nr-sidebar-backdrop, [data-sidebar-close]').forEach(b => b.addEventListener('click', closeSidebar));


  /* ---------- modal (booking + inquiry) ---------- */
  const focusableSel = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
  let modalLastFocus = null;
  function openModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    modalLastFocus = document.activeElement;
    m.classList.add('is-on');
    m.setAttribute('aria-hidden', 'false');
    document.body.classList.add('is-modal-open');
    // Focus the first interactive control inside the modal so keyboard
    // users land in the right context — close button as fallback.
    requestAnimationFrame(() => {
      const first = m.querySelector(focusableSel);
      (first || m.querySelector('.nr-modal__close'))?.focus();
    });
  }
  function closeModal(m) {
    m.classList.remove('is-on');
    m.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('is-modal-open');
    if (modalLastFocus && typeof modalLastFocus.focus === 'function') {
      modalLastFocus.focus();
      modalLastFocus = null;
    }
  }
  document.body.addEventListener('click', (e) => {
    const t = e.target.closest('[data-modal]');
    if (t) {
      const id = t.getAttribute('data-modal');
      if (id) { e.preventDefault(); openModal(id); return; }
    }
    if (e.target.matches('[data-modal-close], .nr-modal__close, .nr-modal__backdrop, .nr-modal-backdrop, .nr-modal-close')) {
      const m = e.target.closest('.nr-modal');
      if (m) closeModal(m);
    }
  });
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      $$('.nr-modal.is-on').forEach(closeModal);
      closeSidebar();
    }
    // Focus trap — keep Tab inside the open modal so keyboard users can't
    // tab out into the underlying page (WCAG 2.4.3 focus order).
    if (e.key === 'Tab') {
      const openM = $('.nr-modal.is-on');
      if (!openM) return;
      const focusables = Array.from(openM.querySelectorAll(focusableSel))
        .filter(el => el.offsetParent !== null);
      if (!focusables.length) { e.preventDefault(); return; }
      const first = focusables[0];
      const last  = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault(); last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault(); first.focus();
      } else if (!openM.contains(document.activeElement)) {
        e.preventDefault(); first.focus();
      }
    }
  });

  /* ---------- hero slider (dot constellation) ---------- */
  const hero = $('[data-hero]');
  if (hero) {
    const dataEl = $('#nr-hero-data');
    let slides = [];
    try { slides = JSON.parse(dataEl ? dataEl.textContent : '[]') || []; } catch (e) { slides = []; }

    const plates = $$('.nr-hero__plate', hero);
    const titleEl = $('[data-hero-title]', hero);
    const linkEl  = $('[data-hero-link]', hero);
    const catEl   = $('[data-hero-cat] span:nth-child(2)', hero) || $('[data-meta-cat]', hero);
    const locEl   = $('[data-hero-loc]', hero) || $('[data-meta-loc]', hero);
    const credEl  = $('[data-hero-cred]', hero) || $('[data-meta-client]', hero);
    const yearEl  = $('[data-meta-year]', hero);
    const currEl  = $('[data-hero-curr]', hero);
    const bigEl   = $('[data-hero-curr-big]', hero);
    const fillEl  = $('[data-hero-fill]', hero);
    const thumbs  = $$('[data-hero-thumb]', hero);
    const prevBtn = $('[data-hero-prev]', hero);
    const nextBtn = $('[data-hero-next]', hero);

    let cur = 0, locked = false;
    const N = slides.length || plates.length || 1;

    function renderTitle(i) {
      if (!titleEl || !slides[i]) return;
      const parts = (slides[i].title || '').split(' ');
      titleEl.innerHTML = parts.map((p, k) => {
        const em = k % 2 === 1 ? ' is-em' : '';
        return `<span class="nr-hero__word${em}">${escapeHtml(p)}</span>`;
      }).join(' ');
    }
    function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    function go(i) {
      if (locked) return;
      i = ((i % N) + N) % N;
      if (i === cur) return;
      locked = true;

      // #1 — let an optional WebGL layer drive the visual transition.
      hero.dispatchEvent(new CustomEvent('nr:hero', { detail: { from: cur, to: i } }));

      setTimeout(() => {
        plates.forEach((p, k) => p.classList.toggle('is-active', k === i));
        plates.forEach((p, k) => p.setAttribute('aria-hidden', k === i ? 'false' : 'true'));
      }, 140);

      if (titleEl) {
        titleEl.style.opacity = '0';
        titleEl.style.transform = 'translateY(8px)';
      }

      setTimeout(() => {
        const s = slides[i] || {};
        renderTitle(i);
        if (catEl && s.cat)     catEl.textContent  = s.cat;
        if (locEl && s.loc)     locEl.textContent  = s.loc;
        if (credEl && (s.client || s.yr)) credEl.textContent = (s.client || '') + ' · ' + (s.yr || '');
        if (yearEl && s.yr)     yearEl.textContent = s.yr;
        if (linkEl && s.url)    linkEl.setAttribute('href', s.url);
        if (currEl)             currEl.textContent = String(i + 1).padStart(2, '0');
        if (bigEl)              bigEl.textContent  = String(i + 1).padStart(2, '0');
        if (fillEl)             fillEl.style.width = (((i + 1) / N) * 100) + '%';
        thumbs.forEach((t, k) => {
          t.classList.toggle('is-on', k === i);
          t.setAttribute('aria-selected', k === i ? 'true' : 'false');
        });
        if (titleEl) {
          titleEl.style.opacity = '1';
          titleEl.style.transform = 'translateY(0)';
        }
        cur = i;
        locked = false;
      }, 300);
    }

    if (prevBtn) prevBtn.addEventListener('click', () => go(cur - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => go(cur + 1));
    thumbs.forEach((t, k) => t.addEventListener('click', () => go(k)));
    window.addEventListener('keydown', (e) => {
      if (!hero.contains(document.activeElement) && document.activeElement !== document.body) return;
      if (e.key === 'ArrowLeft')  go(cur - 1);
      if (e.key === 'ArrowRight') go(cur + 1);
    });

    // Touch swipe (mobile) — swipe left/right to change slide.
    let hsx = 0, hsy = 0;
    hero.addEventListener('touchstart', (e) => { hsx = e.changedTouches[0].clientX; hsy = e.changedTouches[0].clientY; }, { passive: true });
    hero.addEventListener('touchend', (e) => {
      const dx = e.changedTouches[0].clientX - hsx, dy = e.changedTouches[0].clientY - hsy;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.4) go(cur + (dx < 0 ? 1 : -1));
    }, { passive: true });

    const heroAuto = (window.NR && window.NR.hero) ? !!window.NR.hero.auto : true;
    const heroInterval = (window.NR && window.NR.hero) ? (window.NR.hero.interval || 9000) : 9000;
    let auto = heroAuto ? setInterval(() => go(cur + 1), heroInterval) : null;
    if (heroAuto) {
      document.addEventListener('mousemove', () => {
        clearInterval(auto);
        auto = setInterval(() => go(cur + 1), heroInterval);
      }, { passive: true });
    }
  }

  /* ---------- portfolio filter chips (#64 multi-filter) ----------
     "main" group = category/year, single-select (radio).
     "tags" group = keywords, multi-select (toggle). A card shows when it
     matches the main filter AND every active tag. */
  (function () {
    var chips = $$('[data-filter]');
    if (!chips.length) return;
    var mainFilter = 'all';
    var tagSet = [];

    function apply() {
      $$('[data-portfolio] .nr-card, [data-cats]').forEach(function (card) {
        var cats = (card.dataset.cats || '').split(/\s+/);
        var okMain = mainFilter === 'all' || cats.indexOf(mainFilter) !== -1;
        var okTags = tagSet.every(function (t) { return cats.indexOf(t) !== -1; });
        card.style.display = (okMain && okTags) ? '' : 'none';
      });
      var rail = $('[data-portfolio]');
      if (rail) rail.scrollTo({ left: 0, behavior: 'smooth' });
    }

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        var groupEl = chip.closest('[data-group]');
        var group = groupEl ? groupEl.getAttribute('data-group') : 'main';

        if (group === 'tags') {
          var f = chip.dataset.filter;
          var on = chip.classList.toggle('is-on');
          chip.setAttribute('aria-pressed', on ? 'true' : 'false');
          tagSet = tagSet.filter(function (t) { return t !== f; });
          if (on) tagSet.push(f);
        } else {
          var siblings = groupEl ? groupEl.querySelectorAll('.nr-chip') : [];
          siblings.forEach(function (c) { c.classList.remove('is-on'); c.setAttribute('aria-selected', 'false'); });
          chip.classList.add('is-on');
          chip.setAttribute('aria-selected', 'true');
          mainFilter = chip.dataset.filter;
        }
        apply();
      });
    });
  })();

  /* ---------- horizontal rails — arrow keys + drag ---------- */
  $$('[data-h-rail]').forEach(rail => {
    const stepFn = () => (rail.querySelector(':scope > *')?.offsetWidth || 320) + 18;
    rail.addEventListener('wheel', (e) => {
      if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
        rail.scrollLeft += e.deltaY;
        e.preventDefault();
      }
    }, { passive: false });
  });

  /* ---------- FAQ accordion ---------- */
  $$('.nr-faq__item').forEach(item => {
    item.addEventListener('click', () => item.classList.toggle('is-open'));
  });

  /* ---------- plate counter ----------
     Tracks which plate is most visible in the project rail and updates the
     `01 / 24` overlay. Uses IntersectionObserver; works for horizontal
     scroll and mobile vertical reflow. */
  (function plateCounter() {
    const counter = document.querySelector('[data-plate-counter]');
    if (!counter || !('IntersectionObserver' in window)) return;
    const cur = counter.querySelector('[data-plate-cur]');
    const plates = $$('.nr-project__plate');
    if (!plates.length || !cur) return;
    const compute = () => {
      let best = 0, bestRatio = 0;
      const vw = window.innerWidth;
      plates.forEach((p, i) => {
        const r = p.getBoundingClientRect();
        const visible = Math.max(0, Math.min(r.right, vw) - Math.max(r.left, 0));
        const ratio = r.width > 0 ? visible / r.width : 0;
        if (ratio > bestRatio) { bestRatio = ratio; best = i; }
      });
      cur.textContent = String(best + 1).padStart(2, '0');
    };
    const io = new IntersectionObserver(() => requestAnimationFrame(compute), { threshold: [0.25, 0.5, 0.75, 1] });
    plates.forEach(p => io.observe(p));
    // Also recompute on rail scroll (the IO doesn't always fire for subtle
    // movement within the same threshold band)
    const rail = document.querySelector('[data-h-rail]');
    if (rail) rail.addEventListener('scroll', () => requestAnimationFrame(compute), { passive: true });
    compute();
  })();


  /* ---------- page-transition amber wipe ---------- */
  if (!document.querySelector('.nr-wipe')) {
    const wipe = document.createElement('div');
    wipe.className = 'nr-wipe';
    document.body.appendChild(wipe);
  }
  document.body.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
    if (a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || a.dataset.noTransition === '1' || a.dataset.modal) return;
    let isSame = false;
    try {
      const u = new URL(href, window.location.href);
      isSame = u.hostname === window.location.hostname;
    } catch (err) { isSame = href.startsWith('/'); }
    if (!isSame) return;
    e.preventDefault();
    document.body.classList.add('is-wiping');
    setTimeout(() => { window.location.href = href; }, 420);
  });

  /* ---------- lazy load offscreen images ---------- */
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(en => {
        if (en.isIntersecting) {
          const img = en.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          io.unobserve(img);
        }
      });
    }, { rootMargin: '200px' });
    $$('img[data-src]').forEach(img => io.observe(img));
  }
})();


/* =========================================================
   Enquire price calculator + form chip state (v4.1.0)
   ========================================================= */
(function () {
  function ready(fn){ if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function () {

    // form chips (project type) — reflect checked state visually
    document.querySelectorAll('.nr-form__chips').forEach(function (fs) {
      fs.addEventListener('change', function () {
        fs.querySelectorAll('.nr-chip').forEach(function (c) {
          var r = c.querySelector('input');
          c.classList.toggle('is-on', !!(r && r.checked));
        });
      });
    });

    var form = document.querySelector('[data-quote-form]');
    if (!form) return;
    var cur = form.getAttribute('data-currency') || '€';
    var enquireUrl = form.getAttribute('data-enquire-url') || '';
    var sumEl = form.querySelector('[data-quote-sum]');
    var noteEl = form.querySelector('[data-quote-breakdown]');

    function fmt(n){ return cur + Math.round(n).toLocaleString(); }

    function compute(){
      var typeInput = form.querySelector('input[name="nr_q_type"]:checked');
      var base = typeInput ? (parseFloat(typeInput.getAttribute('data-base')) || 0) : 0;
      var total = base, parts = [];
      if (typeInput) parts.push((typeInput.getAttribute('data-label') || '') + ' ' + fmt(base));
      var extras = form.querySelectorAll('input[name="nr_q_extra"]:checked');
      extras.forEach(function (x) { total += parseFloat(x.getAttribute('data-price')) || 0; });
      if (extras.length) parts.push(extras.length + (extras.length > 1 ? ' add-ons' : ' add-on'));
      var lic = form.querySelector('input[name="nr_q_license"]');
      if (lic && lic.checked) { total += parseFloat(lic.getAttribute('data-price')) || 0; parts.push('license'); }
      var km = form.querySelector('input[name="nr_q_km"]');
      if (km && parseFloat(km.value) > 0) {
        total += parseFloat(km.value) * (parseFloat(km.getAttribute('data-per-km')) || 0);
        parts.push(Math.round(parseFloat(km.value)) + ' km');
      }
      if (sumEl) sumEl.textContent = fmt(total);
      if (noteEl) noteEl.textContent = parts.join('  ·  ');
      return total;
    }

    form.addEventListener('change', function (e) {
      if (e.target.name === 'nr_q_type') {
        form.querySelectorAll('.nr-quote__type').forEach(function (l) { l.classList.remove('is-on'); });
        var lab = e.target.closest('.nr-quote__type'); if (lab) lab.classList.add('is-on');
      }
      compute();
    });
    form.addEventListener('input', function (e) { if (e.target.name === 'nr_q_km') compute(); });
    compute();

    var applyBtn = form.querySelector('[data-quote-apply]');
    if (applyBtn) applyBtn.addEventListener('click', function () {
      var typeInput = form.querySelector('input[name="nr_q_type"]:checked');
      var slug = typeInput ? typeInput.value : '';
      var total = compute();
      var ef = document.querySelector('[data-enquire-form]');
      if (ef) {
        if (slug) {
          var chip = ef.querySelector('.nr-chip[data-chip="' + slug + '"]');
          if (chip) {
            ef.querySelectorAll('.nr-chip').forEach(function (c) {
              c.classList.remove('is-on');
              var r = c.querySelector('input'); if (r) r.checked = false;
            });
            chip.classList.add('is-on');
            var radio = chip.querySelector('input'); if (radio) radio.checked = true;
          }
        }
        var out = ef.querySelector('[data-enquire-estimate]'); if (out) out.textContent = fmt(total);
        var hidden = ef.querySelector('[data-enquire-estimate-input]'); if (hidden) hidden.value = cur + Math.round(total);
        var modal = form.closest('.nr-modal');
        var close = modal && modal.querySelector('[data-modal-close]'); if (close) close.click();
        ef.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else if (enquireUrl) {
        var sep = enquireUrl.indexOf('?') > -1 ? '&' : '?';
        window.location.href = enquireUrl + sep + 'service=' + encodeURIComponent(slug) + '&est=' + Math.round(total);
      }
    });
  });
})();


/* =========================================================
   Tier 2 motion — intro preloader + scroll reveals (library-free)
   ========================================================= */
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  (function preloader(){
    var pl = document.querySelector('.nr-preloader');
    if (!pl) return;
    function done(){ document.documentElement.classList.add('nr-ready'); }
    if (reduce || sessionStorage.getItem('nr_seen')) {
      if (pl.parentNode) pl.parentNode.removeChild(pl);
      done(); return;
    }
    var countEl = pl.querySelector('.nr-preloader__count');
    var bar = pl.querySelector('.nr-preloader__bar');
    var p = 0;
    var t = setInterval(function(){
      p = Math.min(100, p + Math.random() * 16 + 5);
      if (countEl) countEl.textContent = Math.round(p);
      if (bar) bar.style.width = p + '%';
      if (p >= 100) {
        clearInterval(t);
        sessionStorage.setItem('nr_seen', '1');
        setTimeout(function(){
          pl.classList.add('is-done'); done();
          setTimeout(function(){ if (pl.parentNode) pl.parentNode.removeChild(pl); }, 700);
        }, 220);
      }
    }, 95);
  })();

  if (!reduce && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (ents, ob) {
      ents.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('nr-rise'); ob.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    // #54 — when line-reveal is on, that effect owns .nr-display (avoid double anim).
    var riseSel = document.body.classList.contains('nr-has-lines')
      ? '.nr-card, .nr-faq__item, .nr-steps li, .nr-page__head-text > *'
      : '.nr-card, .nr-faq__item, .nr-steps li, .nr-display, .nr-page__head-text > *';
    document.querySelectorAll(riseSel).forEach(function (el, i) {
      el.style.animationDelay = ((i % 6) * 70) + 'ms';
      io.observe(el);
    });
  }
})();


/* =========================================================
   Horizontal rail navigation — visible prev/next arrows.
   Desktop only (touch keeps swipe). Replaces the old skew "lean"
   so plates no longer tilt while scrolling.
   ========================================================= */
(function () {
  if (!(window.matchMedia && window.matchMedia('(hover:hover)').matches)) return;
  document.querySelectorAll('.nr-portfolio-rail, .nr-project__rail-track').forEach(function (rail) {
    var host = rail.parentElement;
    if (!host) return;

    var prev = document.createElement('button');
    var next = document.createElement('button');
    prev.type = next.type = 'button';
    prev.className = 'nr-rail-arrow nr-rail-arrow--prev';
    next.className = 'nr-rail-arrow nr-rail-arrow--next';
    prev.setAttribute('aria-label', 'Scroll left');
    next.setAttribute('aria-label', 'Scroll right');
    prev.innerHTML = '<span>←</span>';
    next.innerHTML = '<span>→</span>';
    host.appendChild(prev);
    host.appendChild(next);

    function step() { return Math.max(240, Math.round(rail.clientWidth * 0.85)); }
    prev.addEventListener('click', function () { rail.scrollBy({ left: -step(), behavior: 'smooth' }); });
    next.addEventListener('click', function () { rail.scrollBy({ left: step(), behavior: 'smooth' }); });

    function update() {
      var overflow = rail.scrollWidth > rail.clientWidth + 4;
      host.classList.toggle('nr-has-rail-nav', overflow);
      prev.classList.toggle('is-disabled', rail.scrollLeft <= 2);
      next.classList.toggle('is-disabled', rail.scrollLeft >= rail.scrollWidth - rail.clientWidth - 2);
    }
    rail.addEventListener('scroll', function () { requestAnimationFrame(update); }, { passive: true });
    window.addEventListener('resize', function () { requestAnimationFrame(update); }, { passive: true });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(update);
    setTimeout(update, 80);
    update();
  });
})();


/* =========================================================
   Enquire aside — image-only crossfade (latest projects)
   ========================================================= */
(function () {
  var box = document.querySelector('[data-enquire-slider]');
  if (!box) return;
  var plates = box.querySelectorAll('.nr-enquire__plate');
  if (plates.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var i = 0;
  setInterval(function () {
    plates[i].classList.remove('is-active'); plates[i].setAttribute('aria-hidden', 'true');
    i = (i + 1) % plates.length;
    plates[i].classList.add('is-active'); plates[i].setAttribute('aria-hidden', 'false');
  }, 4500);
})();




/* =========================================================
   Tier 1 — prefetch, click-to-copy, tab-title, keyboard rails, LQIP sweep
   ========================================================= */
(function () {
  /* #2 prefetch a project page on hover / touch */
  var pf = {};
  function prefetch(url){ if(!url||pf[url])return; pf[url]=1; var l=document.createElement('link'); l.rel='prefetch'; l.href=url; document.head.appendChild(l); }
  document.body.addEventListener('pointerover', function(e){ var a=e.target.closest('.nr-card[href]'); if(a&&a.href) prefetch(a.href); }, {passive:true});
  document.body.addEventListener('touchstart', function(e){ var a=e.target.closest('.nr-card[href]'); if(a&&a.href) prefetch(a.href); }, {passive:true});

  /* #24 click-to-copy + toast */
  function toast(msg){ var t=document.createElement('div'); t.className='nr-toast'; t.textContent=msg; document.body.appendChild(t); requestAnimationFrame(function(){ t.classList.add('is-on'); }); setTimeout(function(){ t.classList.remove('is-on'); setTimeout(function(){ t.remove(); },300); },1800); }
  document.body.addEventListener('click', function(e){ var b=e.target.closest('[data-copy]'); if(!b)return; e.preventDefault(); var v=b.getAttribute('data-copy'); if(navigator.clipboard&&navigator.clipboard.writeText){ navigator.clipboard.writeText(v).then(function(){toast('Copied — '+v);}).catch(function(){window.location.href='mailto:'+v;}); } else { window.location.href='mailto:'+v; } });

  /* #31 tab-away title nudge */
  var realTitle=document.title;
  document.addEventListener('visibilitychange', function(){ document.title = document.hidden ? '↩ Come back —' : realTitle; });

  /* #33/#35 keyboard rail control */
  var railSel='[data-h-rail], .nr-project__rail-track, .nr-portfolio-rail';
  document.addEventListener('keydown', function(e){
    if(['ArrowLeft','ArrowRight','Home','End'].indexOf(e.key)<0) return;
    if(/^(INPUT|TEXTAREA|SELECT)$/.test((e.target&&e.target.tagName)||'')) return;
    var rail=document.querySelector(railSel+':hover') || (document.activeElement&&document.activeElement.closest&&document.activeElement.closest(railSel));
    if(!rail||rail.scrollWidth<=rail.clientWidth) return;
    if(e.key==='Home') rail.scrollTo({left:0,behavior:'smooth'});
    else if(e.key==='End') rail.scrollTo({left:rail.scrollWidth,behavior:'smooth'});
    else rail.scrollBy({left:(e.key==='ArrowRight'?1:-1)*Math.round(rail.clientWidth*0.8),behavior:'smooth'});
  });

  /* #1 LQIP — clear the blur once cached images are already complete */
  window.addEventListener('load', function(){ document.querySelectorAll('img.nr-lqip').forEach(function(i){ if(i.complete) i.classList.add('nr-lqip--done'); }); });
})();


/* =========================================================
   Tier 2 — command palette (#51) + contact-sheet index (#52,#65),
   both fed by /projects.json (#14), and hero pointer parallax (#56).
   ========================================================= */
(function () {
  'use strict';
  var home = ( window.NR && NR.home ) ? NR.home : '/';
  var data = null, loading = null;
  function load(){
    if ( data ) return Promise.resolve( data );
    if ( ! loading ) loading = fetch( home + 'projects.json', { credentials:'same-origin' } )
      .then(function(r){ return r.ok ? r.json() : { projects: [] }; })
      .then(function(j){ data = ( j && j.projects ) || []; return data; })
      .catch(function(){ data = []; return data; });
    return loading;
  }
  var pages = [
    { title:'Showcase', url:home },
    { title:'Work', url:home + 'portfolio/' },
    { title:'Studio', url:home + 'about/' },
    { title:'Enquire', url:home + 'enquire/' }
  ];
  function esc(s){ return String(s).replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);}); }

  /* command palette */
  var pal=null, palInput, palList, palItems=[], palIdx=0;
  function buildPalette(){
    pal=document.createElement('div'); pal.className='nr-cmd'; pal.setAttribute('aria-hidden','true');
    pal.innerHTML='<div class="nr-cmd__panel" role="dialog" aria-modal="true" aria-label="Search">'
      +'<input class="nr-cmd__input" type="text" placeholder="Search projects, pages…" aria-label="Search">'
      +'<ul class="nr-cmd__list"></ul>'
      +'<div class="nr-cmd__hint"><span>&uarr;&darr; navigate</span><span>&crarr; open</span><span>esc close</span></div></div>';
    document.body.appendChild(pal);
    palInput=pal.querySelector('.nr-cmd__input'); palList=pal.querySelector('.nr-cmd__list');
    pal.addEventListener('click',function(e){ if(e.target===pal) closePalette(); });
    palInput.addEventListener('input',renderPalette);
    palInput.addEventListener('keydown',function(e){
      if(e.key==='ArrowDown'){ e.preventDefault(); palIdx=Math.min(palIdx+1,palItems.length-1); markPal(); }
      else if(e.key==='ArrowUp'){ e.preventDefault(); palIdx=Math.max(palIdx-1,0); markPal(); }
      else if(e.key==='Enter'){ e.preventDefault(); go(palItems[palIdx]); }
    });
  }
  function go(it){ if(!it) return; if(it.action) it.action(); else if(it.url) window.location.href=it.url; }
  function openPalette(){ if(!pal) buildPalette(); load().then(renderPalette); pal.classList.add('is-on'); pal.setAttribute('aria-hidden','false'); document.body.classList.add('is-modal-open'); setTimeout(function(){ palInput.value=''; palInput.focus(); },20); }
  function closePalette(){ if(!pal) return; pal.classList.remove('is-on'); pal.setAttribute('aria-hidden','true'); document.body.classList.remove('is-modal-open'); }
  function renderPalette(){
    var q=(palInput.value||'').toLowerCase().trim(), items=[];
    items.push({ title:'▦  All projects — contact sheet', action:function(){ closePalette(); openSheet(); } });
    pages.forEach(function(p){ if(!q||p.title.toLowerCase().indexOf(q)>=0) items.push(p); });
    (data||[]).forEach(function(p){ if(!q||(p.title||'').toLowerCase().indexOf(q)>=0) items.push({ title:p.title, sub:p.year, url:p.url }); });
    palItems=items.slice(0,40); palIdx=0;
    palList.innerHTML=palItems.map(function(it,i){ return '<li class="nr-cmd__item'+(i===0?' is-sel':'')+'" data-i="'+i+'">'+esc(it.title)+(it.sub?'<span class="nr-cmd__sub">'+esc(String(it.sub))+'</span>':'')+'</li>'; }).join('');
    Array.prototype.forEach.call(palList.children,function(li){ li.addEventListener('click',function(){ go(palItems[+li.dataset.i]); }); });
  }
  function markPal(){ Array.prototype.forEach.call(palList.children,function(li,i){ li.classList.toggle('is-sel',i===palIdx); if(i===palIdx) li.scrollIntoView({block:'nearest'}); }); }

  /* contact-sheet index */
  var sheet=null, sheetGrid, sheetInput;
  function buildSheet(){
    sheet=document.createElement('div'); sheet.className='nr-sheet'; sheet.setAttribute('aria-hidden','true');
    sheet.innerHTML='<div class="nr-sheet__bar"><span class="nr-eyebrow">Index</span><input class="nr-sheet__input" type="text" placeholder="Filter…" aria-label="Filter projects"><button class="nr-sheet__close" aria-label="Close">✕</button></div><div class="nr-sheet__grid"></div>';
    document.body.appendChild(sheet);
    sheetGrid=sheet.querySelector('.nr-sheet__grid'); sheetInput=sheet.querySelector('.nr-sheet__input');
    sheet.querySelector('.nr-sheet__close').addEventListener('click',closeSheet);
    sheetInput.addEventListener('input',renderSheet);
  }
  function openSheet(){ if(!sheet) buildSheet(); load().then(renderSheet); sheet.classList.add('is-on'); sheet.setAttribute('aria-hidden','false'); document.body.classList.add('is-modal-open'); setTimeout(function(){ sheetInput.focus(); },20); }
  function closeSheet(){ if(!sheet) return; sheet.classList.remove('is-on'); sheet.setAttribute('aria-hidden','true'); document.body.classList.remove('is-modal-open'); }
  function renderSheet(){
    var q=(sheetInput.value||'').toLowerCase().trim();
    var list=(data||[]).filter(function(p){ return !q||(p.title||'').toLowerCase().indexOf(q)>=0; });
    sheetGrid.innerHTML=list.map(function(p){ return '<a class="nr-sheet__cell" href="'+encodeURI(p.url)+'">'+(p.image?'<img src="'+encodeURI(p.image)+'" alt="" loading="lazy" decoding="async">':'')+'<span class="nr-sheet__cap">'+esc(p.title)+'</span></a>'; }).join('') || '<p class="nr-sheet__empty">No projects yet.</p>';
  }

  document.addEventListener('keydown',function(e){
    if((e.metaKey||e.ctrlKey)&&(e.key==='k'||e.key==='K')){ e.preventDefault(); (pal&&pal.classList.contains('is-on'))?closePalette():openPalette(); }
    else if(e.key==='Escape'){ closePalette(); closeSheet(); }
  });

  /* #56 hero pointer parallax (subtle; desktop, motion-on) */
  if ( window.matchMedia('(hover:hover)').matches && !window.matchMedia('(prefers-reduced-motion: reduce)').matches ) {
    var frame=document.querySelector('.nr-hero__frame');
    if (frame) {
      window.addEventListener('mousemove', function(e){
        var dx=(e.clientX/window.innerWidth-0.5), dy=(e.clientY/window.innerHeight-0.5);
        frame.style.transform='translate3d('+(dx*-8).toFixed(1)+'px,'+(dy*-8).toFixed(1)+'px,0)';
      }, { passive:true });
    }
  }
})();


/* =========================================================
   Medium batch — #72 testimonials rotation, #73 related hover-preview
   ========================================================= */
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* #72 — rotate testimonials one at a time */
  var vc = document.querySelector('.nr-voices__list');
  if (vc) {
    var items = vc.querySelectorAll('.nr-voices__item');
    if (items.length > 1 && !reduce) {
      var max = 0;
      items.forEach(function (it) { max = Math.max(max, it.offsetHeight); });
      vc.style.minHeight = max + 'px';
      vc.classList.add('nr-voices--rotate');
      var vi = 0; items[0].classList.add('is-active');
      setInterval(function () {
        items[vi].classList.remove('is-active');
        vi = (vi + 1) % items.length;
        items[vi].classList.add('is-active');
      }, 5200);
    }
  }

  /* #73 — floating thumbnail preview on related-project links */
  if (window.matchMedia('(hover:hover)').matches) {
    var prev = null;
    document.body.addEventListener('mouseover', function (e) {
      var a = e.target.closest('.nr-project__related-links a[data-thumb]');
      if (!a || !a.getAttribute('data-thumb')) return;
      if (!prev) { prev = document.createElement('div'); prev.className = 'nr-relprev'; prev.innerHTML = '<img alt="">'; document.body.appendChild(prev); }
      prev.querySelector('img').src = a.getAttribute('data-thumb');
      prev.classList.add('is-on');
    });
    document.body.addEventListener('mousemove', function (e) {
      if (prev && prev.classList.contains('is-on')) prev.style.transform = 'translate(' + (e.clientX + 18) + 'px,' + (e.clientY - 60) + 'px)';
    }, { passive: true });
    document.body.addEventListener('mouseout', function (e) {
      if (prev && e.target.closest('.nr-project__related-links a[data-thumb]')) prev.classList.remove('is-on');
    });
  }
})();

/* #67 — before / after compare slider (drag the handle / use the range) */
(function () {
  var wraps = document.querySelectorAll('[data-compare]');
  if (!wraps.length) return;
  wraps.forEach(function (wrap) {
    var clip   = wrap.querySelector('[data-compare-clip]');
    var range  = wrap.querySelector('[data-compare-range]');
    var handle = wrap.querySelector('.nr-compare__handle');
    if (!clip || !range) return;

    function set(pct) {
      pct = Math.max(0, Math.min(100, pct));
      wrap.style.setProperty('--nr-compare-start', pct + '%');
      clip.style.clipPath = 'inset(0 ' + (100 - pct) + '% 0 0)';
      if (handle) handle.style.left = pct + '%';
      range.value = pct;
    }
    set(parseFloat(range.value) || 50);

    range.addEventListener('input', function () { set(parseFloat(range.value)); });

    function fromEvent(clientX) {
      var r = wrap.getBoundingClientRect();
      if (!r.width) return;
      set(((clientX - r.left) / r.width) * 100);
    }
    var dragging = false;
    function down(e) { dragging = true; fromEvent((e.touches ? e.touches[0] : e).clientX); }
    function move(e) { if (dragging) fromEvent((e.touches ? e.touches[0] : e).clientX); }
    function up()   { dragging = false; }
    if (handle) {
      handle.addEventListener('mousedown', down);
      handle.addEventListener('touchstart', down, { passive: true });
    }
    wrap.addEventListener('mousedown', down);
    window.addEventListener('mousemove', move, { passive: true });
    window.addEventListener('touchmove', move, { passive: true });
    window.addEventListener('mouseup', up);
    window.addEventListener('touchend', up);
  });
})();

/* #54 — line-reveal display headings (opt-in: body.nr-has-lines, motion-on) */
(function () {
  if (!document.body.classList.contains('nr-has-lines')) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (!('IntersectionObserver' in window)) return;

  function collect(node, em, out) {
    Array.prototype.forEach.call(node.childNodes, function (n) {
      if (n.nodeType === 3) {
        n.textContent.split(/\s+/).forEach(function (w) { if (w !== '') out.push({ w: w, em: em }); });
      } else if (n.nodeType === 1) {
        collect(n, em || /^(EM|STRONG|B|I)$/.test(n.tagName), out);
      }
    });
  }

  var io = new IntersectionObserver(function (ents, ob) {
    ents.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-in'); ob.unobserve(en.target); } });
  }, { threshold: 0.2, rootMargin: '0px 0px -6% 0px' });

  document.querySelectorAll('.nr-display').forEach(function (el) {
    var original = el.innerHTML;
    try {
      var words = []; collect(el, false, words);
      if (!words.length) return;
      // 1) lay words out inline to measure line breaks
      el.innerHTML = '';
      var spans = words.map(function (o) {
        var s = document.createElement('span');
        s.className = 'nr-w' + (o.em ? ' is-em' : '');
        s.textContent = o.w;
        el.appendChild(s); el.appendChild(document.createTextNode(' '));
        return s;
      });
      // 2) group by vertical position into lines
      var lines = [], cur = [], top = null;
      spans.forEach(function (s) {
        if (top === null || Math.abs(s.offsetTop - top) > 2) { if (cur.length) lines.push(cur); cur = []; top = s.offsetTop; }
        cur.push(s);
      });
      if (cur.length) lines.push(cur);
      // 3) wrap each line in a clip mask
      el.innerHTML = '';
      lines.forEach(function (ln, li) {
        var L = document.createElement('span'); L.className = 'nr-line';
        var I = document.createElement('span'); I.className = 'nr-line__i'; I.style.transitionDelay = (li * 0.07) + 's';
        ln.forEach(function (s, wi) { I.appendChild(s); if (wi < ln.length - 1) I.appendChild(document.createTextNode(' ')); });
        L.appendChild(I); el.appendChild(L); el.appendChild(document.createTextNode(' '));
      });
      el.classList.add('nr-rl');
      io.observe(el);
    } catch (e) { el.innerHTML = original; }
  });
})();

/* #83 — card hover distortion (opt-in: body.nr-has-distort; SVG displacement) */
(function () {
  if (!document.body.classList.contains('nr-has-distort')) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (!(window.matchMedia && window.matchMedia('(hover:hover)').matches)) return;

  var ns = 'http://www.w3.org/2000/svg';
  var svg = document.createElementNS(ns, 'svg');
  svg.setAttribute('width', '0'); svg.setAttribute('height', '0');
  svg.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden';
  svg.innerHTML =
    '<filter id="nr-distort" x="-20%" y="-20%" width="140%" height="140%">' +
    '<feTurbulence id="nr-distort-turb" type="fractalNoise" baseFrequency="0.008 0.012" numOctaves="2" seed="3" result="n"/>' +
    '<feDisplacementMap id="nr-distort-map" in="SourceGraphic" in2="n" scale="0" xChannelSelector="R" yChannelSelector="G"/>' +
    '</filter>';
  document.body.appendChild(svg);
  var map = document.getElementById('nr-distort-map');
  if (!map) return;

  var active = null, scale = 0, target = 0, raf = null, seed = 3;
  function loop() {
    scale += (target - scale) * 0.18;
    if (Math.abs(target - scale) < 0.15 && target === 0) { scale = 0; map.setAttribute('scale', '0'); raf = null; return; }
    map.setAttribute('scale', scale.toFixed(2));
    raf = requestAnimationFrame(loop);
  }
  function start(t) { target = t; if (!raf) raf = requestAnimationFrame(loop); }

  document.querySelectorAll('.nr-card img').forEach(function (img) {
    var card = img.closest('.nr-card');
    if (!card) return;
    card.addEventListener('mouseenter', function () {
      if (active && active !== img) active.classList.remove('is-distort');
      active = img; img.classList.add('is-distort'); start(16);
    });
    card.addEventListener('mouseleave', function () { start(0); setTimeout(function () { img.classList.remove('is-distort'); }, 260); });
  });
})();

/* #58 — opt-in interface sound with a mute toggle (body.nr-has-sound) */
(function () {
  if (!document.body.classList.contains('nr-has-sound')) return;
  var muted = localStorage.getItem('nr_sound') !== 'on'; // starts muted
  var ctx = null;
  function tone(freq, vol, dur) {
    if (muted) return;
    try {
      ctx = ctx || new (window.AudioContext || window.webkitAudioContext)();
      var o = ctx.createOscillator(), g = ctx.createGain(), t = ctx.currentTime;
      o.type = 'sine'; o.frequency.value = freq; o.connect(g); g.connect(ctx.destination);
      g.gain.setValueAtTime(0.0001, t);
      g.gain.exponentialRampToValueAtTime(vol, t + 0.006);
      g.gain.exponentialRampToValueAtTime(0.0001, t + (dur || 0.09));
      o.start(t); o.stop(t + (dur || 0.1));
    } catch (e) {}
  }
  var btn = document.createElement('button');
  btn.type = 'button'; btn.className = 'nr-sound'; btn.setAttribute('aria-label', 'Toggle interface sound');
  function paint() { btn.classList.toggle('is-on', !muted); btn.textContent = muted ? '🔇' : '🔊'; btn.setAttribute('aria-pressed', String(!muted)); }
  paint();
  btn.addEventListener('click', function () {
    muted = !muted; localStorage.setItem('nr_sound', muted ? 'off' : 'on');
    if (!muted) { try { ctx = ctx || new (window.AudioContext || window.webkitAudioContext)(); } catch (e) {} tone(880, 0.05, 0.12); }
    paint();
  });
  document.body.appendChild(btn);
  document.body.addEventListener('pointerover', function (e) { if (e.target.closest('a,button,.nr-card,.nr-chip,.nr-hero__thumb')) tone(620, 0.012, 0.06); }, { passive: true });
  document.body.addEventListener('click', function (e) { if (e.target.closest('a,button')) tone(880, 0.03, 0.1); }, true);
})();

/* #59 — generative monogram favicon (opt-in: body.nr-has-favicon) */
(function () {
  if (!document.body.classList.contains('nr-has-favicon')) return;
  try {
    var cs = getComputedStyle(document.documentElement);
    var accent = (cs.getPropertyValue('--amber') || '#F2A03D').trim();
    var bg = (cs.getPropertyValue('--bg') || '#0B0C10').trim();
    var logo = document.querySelector('.nr-logo');
    var mark = ((logo && logo.textContent) || 'R').trim().charAt(0).toUpperCase();
    var c = document.createElement('canvas'); c.width = c.height = 64;
    var x = c.getContext('2d');
    x.fillStyle = bg; x.fillRect(0, 0, 64, 64);
    x.fillStyle = accent; x.beginPath(); x.arc(32, 32, 27, 0, Math.PI * 2); x.fill();
    x.fillStyle = bg; x.font = '700 36px "Inter Tight", system-ui, sans-serif';
    x.textAlign = 'center'; x.textBaseline = 'middle'; x.fillText(mark, 32, 36);
    var href = c.toDataURL('image/png');
    var link = document.querySelector("link[rel~='icon']");
    if (!link) { link = document.createElement('link'); link.rel = 'icon'; document.head.appendChild(link); }
    link.type = 'image/png'; link.href = href;
  } catch (e) {}
})();
