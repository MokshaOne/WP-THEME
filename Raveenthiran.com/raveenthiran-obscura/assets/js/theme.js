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
    const ghostEl = $('[data-hero-ghost]', hero);
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
        if (ghostEl)            ghostEl.textContent = String(i + 1).padStart(2, '0');
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
  var data = null, journalData = [], loading = null;
  function load(){
    if ( data ) return Promise.resolve( data );
    if ( ! loading ) loading = fetch( home + 'projects.json', { credentials:'same-origin' } )
      .then(function(r){ return r.ok ? r.json() : { projects: [] }; })
      .then(function(j){ data = ( j && j.projects ) || []; journalData = ( j && j.journal ) || []; return data; })
      .catch(function(){ data = []; journalData = []; return data; });
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
    (journalData||[]).forEach(function(p){ if(!q||(p.title||'').toLowerCase().indexOf(q)>=0) items.push({ title:p.title, sub:'Journal', url:p.url }); });
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

/* Attribution — record the external referrer host on the enquiry form */
(function () {
  var f = document.querySelector('[data-nr-referrer]');
  if (!f) return;
  try {
    var r = document.referrer || '';
    if (r) { var u = new URL(r); if (u.host !== location.host) f.value = u.host; }
  } catch (e) {}
})();

/* ─────────────────────────────────────────────────────────────
   IDEAS-NEXT v4.41 — front-end behaviours
   ───────────────────────────────────────────────────────────── */

/* #21 — honor Save-Data even when the server header didn't reach us
   (CDN edge, etc.). The class lets CSS skip heavy backgrounds. */
(function () {
  try {
    var c = navigator.connection || navigator.webkitConnection;
    if ((c && c.saveData) && !document.body.classList.contains('nr-savedata')) {
      document.body.classList.add('nr-savedata');
    }
  } catch (e) {}
})();

/* #22 — focus trap for the ⌘K palette and contact-sheet dialogs.
   Keeps Tab inside the open dialog (WCAG 2.4.3); Escape already closes. */
(function () {
  var sel = 'a[href],button:not([disabled]),input:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Tab') return;
    var dlg = document.querySelector('.nr-cmd.is-on, .nr-sheet.is-on');
    if (!dlg) return;
    var f = Array.prototype.slice.call(dlg.querySelectorAll(sel)).filter(function (el) {
      return el.offsetParent !== null;
    });
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    else if (!dlg.contains(document.activeElement)) { e.preventDefault(); first.focus(); }
  });
})();

/* #6 — recently viewed projects (localStorage, capped at 8, no tracking). */
(function () {
  var KEY = 'nr_recent_v1', MAX = 8;
  function read() { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; } }
  function write(a) { try { localStorage.setItem(KEY, JSON.stringify(a)); } catch (e) {} }

  // Record the current project.
  var cur = document.getElementById('nr-recent-current');
  if (cur) {
    try {
      var rec = JSON.parse(cur.textContent);
      if (rec && rec.id) {
        var list = read().filter(function (r) { return r.id !== rec.id; });
        list.unshift(rec);
        write(list.slice(0, MAX));
      }
    } catch (e) {}
  }

  // Render the strip (omits the page you're currently on).
  var box = document.getElementById('nr-recent');
  if (box) {
    var track = box.querySelector('[data-recent-track]');
    var here = location.pathname.replace(/\/+$/, '');
    var items = read().filter(function (r) {
      try { return new URL(r.url).pathname.replace(/\/+$/, '') !== here; } catch (e) { return true; }
    }).slice(0, 6);
    if (items.length && track) {
      track.innerHTML = items.map(function (r) {
        var img = r.thumb ? '<img src="' + encodeURI(r.thumb) + '" alt="" loading="lazy" decoding="async">' : '';
        var t = (r.title || '').replace(/[<>&]/g, '');
        return '<a class="nr-recent__card" href="' + encodeURI(r.url) + '">' + img + '<span>' + t + '</span></a>';
      }).join('');
      box.hidden = false;
    }
  }
})();

/* #8 — testimonials band: gentle auto-rotation, pauses on hover. */
(function () {
  var band = document.querySelector('[data-testi-band]');
  if (!band) return;
  var items = band.querySelectorAll('.nr-testi');
  if (items.length < 2) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var i = 0, paused = false;
  band.addEventListener('mouseenter', function () { paused = true; });
  band.addEventListener('mouseleave', function () { paused = false; });
  setInterval(function () {
    if (paused) return;
    items[i].classList.remove('is-on');
    i = (i + 1) % items.length;
    items[i].classList.add('is-on');
  }, 6500);
})();

/* ─────────────────────────────────────────────────────────────
   Batch 1 (v4.42.0) — cinematic motion layer (opt-in).
   Master toggle adds body.nr-cinematic; a visitor switch sets
   <html data-nr-motion="calm|standard|cinematic">. Effects gate on
   the level, so the live look only changes when both opt in.
   ───────────────────────────────────────────────────────────── */
(function () {
  var body = document.body;
  if (!body.classList.contains('nr-cinematic')) return;     // master toggle off → nothing
  var root = document.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hover = window.matchMedia('(hover:hover)').matches;
  var LEVELS = ['calm', 'standard', 'cinematic'];

  function getLevel() {
    var l = null; try { l = localStorage.getItem('nr_motion'); } catch (e) {}
    if (LEVELS.indexOf(l) < 0) l = reduce ? 'calm' : 'cinematic';
    return l;
  }
  function setLevel(l) { try { localStorage.setItem('nr_motion', l); } catch (e) {} body.setAttribute('data-nr-motion', l); root.setAttribute('data-nr-motion', l); }
  setLevel(getLevel());
  function level() { return body.getAttribute('data-nr-motion'); }
  function cinematic() { return level() === 'cinematic'; }
  function notCalm() { return level() !== 'calm'; }

  /* #40 — the visitor motion switch (desktop; CSS hides it on small screens). */
  var panel = document.createElement('div');
  panel.className = 'nr-motion';
  panel.innerHTML = '<button class="nr-motion__btn" aria-haspopup="true" aria-expanded="false">◐ motion</button>'
    + '<div class="nr-motion__menu" role="menu" hidden>'
    + LEVELS.map(function (l) { return '<button role="menuitemradio" data-level="' + l + '">' + l + '</button>'; }).join('')
    + '</div>';
  body.appendChild(panel);
  var btn = panel.querySelector('.nr-motion__btn'), menu = panel.querySelector('.nr-motion__menu');
  function mark() { panel.querySelectorAll('[data-level]').forEach(function (b) { b.setAttribute('aria-checked', b.dataset.level === level() ? 'true' : 'false'); }); }
  mark();
  btn.addEventListener('click', function () { var open = !menu.hidden; menu.hidden = open; btn.setAttribute('aria-expanded', String(!open)); });
  panel.querySelectorAll('[data-level]').forEach(function (b) {
    b.addEventListener('click', function () { setLevel(b.dataset.level); mark(); menu.hidden = true; btn.setAttribute('aria-expanded', 'false'); });
  });
  document.addEventListener('click', function (e) { if (!panel.contains(e.target)) { menu.hidden = true; btn.setAttribute('aria-expanded', 'false'); } });

  /* #7 — chromatic-aberration SVG filter (CSS applies it on card hover). */
  if (!document.getElementById('nr-rgbsplit')) {
    var ns = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('aria-hidden', 'true'); svg.setAttribute('width', '0'); svg.setAttribute('height', '0');
    svg.style.cssText = 'position:absolute;width:0;height:0';
    svg.innerHTML = '<filter id="nr-rgbsplit" x="-20%" y="-20%" width="140%" height="140%">'
      + '<feColorMatrix type="matrix" values="1 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 1 0" result="r"/>'
      + '<feOffset in="r" dx="2.5" dy="0" result="ro"/>'
      + '<feColorMatrix type="matrix" values="0 0 0 0 0  0 1 0 0 0  0 0 0 0 0  0 0 0 1 0" result="g"/>'
      + '<feColorMatrix type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 1 0 0  0 0 0 1 0" result="b"/>'
      + '<feOffset in="b" dx="-2.5" dy="0" result="bo"/>'
      + '<feBlend in="ro" in2="g" mode="screen" result="rg"/>'
      + '<feBlend in="rg" in2="bo" mode="screen"/></filter>';
    body.appendChild(svg);
  }

  /* #24 — 3D tilt + glare on cards (desktop, cinematic level). */
  if (hover) {
    document.querySelectorAll('.nr-card').forEach(function (card) {
      card.addEventListener('pointermove', function (e) {
        if (!cinematic()) return;
        var r = card.getBoundingClientRect();
        var px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
        card.style.setProperty('--rx', ((py - 0.5) * -6).toFixed(2) + 'deg');
        card.style.setProperty('--ry', ((px - 0.5) * 6).toFixed(2) + 'deg');
        card.style.setProperty('--mx', (px * 100).toFixed(1) + '%');
        card.style.setProperty('--my', (py * 100).toFixed(1) + '%');
        card.classList.add('nr-tilt-on');
      });
      card.addEventListener('pointerleave', function () {
        card.classList.remove('nr-tilt-on');
        card.style.removeProperty('--rx'); card.style.removeProperty('--ry');
      });
    });
  }

  /* #12 — scroll-velocity image shear on the rails (cinematic level). */
  document.querySelectorAll('.nr-portfolio-rail, .nr-project__rail-track').forEach(function (rail) {
    var last = rail.scrollLeft, raf;
    rail.addEventListener('scroll', function () {
      if (!cinematic()) { rail.style.setProperty('--skew', '0deg'); return; }
      var v = rail.scrollLeft - last; last = rail.scrollLeft;
      var sk = Math.max(-7, Math.min(7, v * 0.22));
      rail.style.setProperty('--skew', sk.toFixed(2) + 'deg');
      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(function () { rail.style.setProperty('--skew', '0deg'); });
    }, { passive: true });
  });

  /* #22 — decode/scramble reveal on the mono eyebrow labels (cinematic level). */
  if (!reduce) {
    var glyphs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789·—/';
    function scramble(el) {
      var target = el.textContent, len = target.length, frame = 0, dur = Math.min(30, 10 + len);
      var id = setInterval(function () {
        var out = '', reveal = (frame / dur) * len;
        for (var i = 0; i < len; i++) {
          if (i < reveal || target[i] === ' ') out += target[i];
          else out += glyphs[Math.floor(Math.random() * glyphs.length)];
        }
        el.textContent = out; frame++;
        if (frame > dur) { clearInterval(id); el.textContent = target; }
      }, 28);
    }
    var ioS = new IntersectionObserver(function (es) {
      es.forEach(function (en) {
        if (en.isIntersecting && cinematic() && !en.target.dataset.scrambled) {
          en.target.dataset.scrambled = '1'; scramble(en.target); ioS.unobserve(en.target);
        }
      });
    }, { threshold: 0.6 });
    document.querySelectorAll('.nr-eyebrow').forEach(function (el) { if (el.children.length === 0) ioS.observe(el); });
  }

  /* #36 — dividers draw in on scroll (standard + cinematic). */
  var ioD = new IntersectionObserver(function (es) {
    es.forEach(function (en) { if (en.isIntersecting && notCalm()) { en.target.classList.add('nr-divider--drawn'); ioD.unobserve(en.target); } });
  }, { threshold: 0.4 });
  document.querySelectorAll('.nr-divider').forEach(function (d) { ioD.observe(d); });

  /* #29 — split-flap stat counters (standard + cinematic). */
  if (!reduce) {
    var ioF = new IntersectionObserver(function (es) {
      es.forEach(function (en) {
        if (!en.isIntersecting || en.target.dataset.flapped || !notCalm()) return;
        en.target.dataset.flapped = '1';
        var el = en.target, full = el.textContent.trim(), num = parseInt(full.replace(/\D/g, ''), 10);
        if (isNaN(num)) return;
        var suffix = full.replace(/[0-9]/g, ''), steps = 22, i = 0;
        var id = setInterval(function () {
          i++; el.textContent = Math.round(num * (i / steps)) + suffix;
          if (i >= steps) { clearInterval(id); el.textContent = full; }
        }, 38);
        ioF.unobserve(el);
      });
    }, { threshold: 0.6 });
    document.querySelectorAll('.nr-stats__n').forEach(function (el) { ioF.observe(el); });
  }

  /* #18 — scroll progress as a 35mm film-frame counter (scrolling pages). */
  (function () {
    var ind = document.createElement('div');
    ind.className = 'nr-filmframe'; ind.setAttribute('aria-hidden', 'true');
    ind.innerHTML = '<span class="nr-filmframe__cur">000</span><span class="nr-filmframe__sep">/</span><span>100</span>';
    body.appendChild(ind);
    var cur = ind.querySelector('.nr-filmframe__cur');
    function pad(n) { n = String(n); return n.length >= 3 ? n : ('000' + n).slice(-3); }
    function upd() {
      if (level() === 'calm') { ind.classList.remove('is-on'); return; }
      var h = document.documentElement.scrollHeight - window.innerHeight;
      cur.textContent = pad(h > 0 ? Math.round(window.scrollY / h * 100) : 0);
      ind.classList.toggle('is-on', h > 60);
    }
    window.addEventListener('scroll', upd, { passive: true });
    window.addEventListener('resize', upd); upd();
  })();
})();

/* ─────────────────────────────────────────────────────────────
   Batch 1 (v4.43.0) — cinematic motion layer, part 2 (lightweight).
   Reads the motion level set by part 1 (on <body>). Same opt-in gate.
   ───────────────────────────────────────────────────────────── */
(function () {
  var body = document.body;
  if (!body.classList.contains('nr-cinematic')) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hover = window.matchMedia('(hover:hover)').matches;
  function level() { return body.getAttribute('data-nr-motion') || 'standard'; }
  function notCalm() { return level() !== 'calm'; }
  function cinematic() { return level() === 'cinematic'; }

  /* #23 — viewfinder AF-bracket feedback on card/button press. */
  if (!reduce) {
    document.addEventListener('pointerdown', function (e) {
      if (!notCalm()) return;
      if (!e.target.closest('.nr-card, .nr-btn, .nr-hero__thumb')) return;
      var b = document.createElement('span');
      b.className = 'nr-viewfinder'; b.setAttribute('aria-hidden', 'true');
      b.style.left = e.clientX + 'px'; b.style.top = e.clientY + 'px';
      document.body.appendChild(b);
      setTimeout(function () { b.remove(); }, 520);
    }, { passive: true });
  }

  /* #37 — aspect-matched loading skeleton on portfolio/plate images. */
  document.querySelectorAll('.nr-card img, .nr-project__plate img').forEach(function (img) {
    var fig = img.closest('.nr-card, .nr-project__plate');
    if (!fig || (img.complete && img.naturalWidth > 0)) return;
    fig.classList.add('nr-skel');
    var done = function () { fig.classList.remove('nr-skel'); };
    img.addEventListener('load', done, { once: true });
    img.addEventListener('error', done, { once: true });
  });

  /* #15 — elastic overscroll: rubber-band the rails past their ends. */
  if (!reduce) {
    document.querySelectorAll('.nr-portfolio-rail, .nr-project__rail-track').forEach(function (rail) {
      var raf;
      rail.addEventListener('wheel', function (e) {
        if (!cinematic()) return;
        var d = e.deltaX || e.deltaY;
        var atStart = rail.scrollLeft <= 0, atEnd = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 1;
        if ((atStart && d < 0) || (atEnd && d > 0)) {
          var push = Math.max(-26, Math.min(26, -d * 0.16));
          rail.style.transition = 'none';
          rail.style.transform = 'translateX(' + push.toFixed(1) + 'px)';
          cancelAnimationFrame(raf);
          raf = requestAnimationFrame(function () {
            rail.style.transition = 'transform .5s cubic-bezier(.2,.8,.2,1)';
            rail.style.transform = 'translateX(0)';
            setTimeout(function () { rail.style.transition = ''; }, 520);
          });
        }
      }, { passive: true });
    });
  }

  /* #28 — contextual cursor: "drag" over the scrollable rails. */
  if (hover) {
    var cur = document.querySelector('.nr-cur');
    var lbl = cur && cur.querySelector('.nr-cur__lbl');
    if (cur) {
      document.querySelectorAll('.nr-portfolio-rail, .nr-project__rail').forEach(function (r) {
        r.addEventListener('mouseenter', function () { cur.classList.add('is-drag'); if (lbl) lbl.textContent = 'drag'; });
        r.addEventListener('mouseleave', function () { cur.classList.remove('is-drag'); });
      });
    }
  }

  /* #27 — directional draw-on underline for prose links. */
  document.querySelectorAll('.nr-prose a, .nr-jpost a, .nr-static a, .nr-process__body a').forEach(function (a) {
    if (a.classList.contains('nr-ul')) return;
    a.classList.add('nr-ul');
    a.addEventListener('mouseenter', function (e) {
      var r = a.getBoundingClientRect();
      a.style.setProperty('--ul-origin', (e.clientX - r.left) < r.width / 2 ? 'left' : 'right');
    });
  });
})();

/* ─────────────────────────────────────────────────────────────
   IDEAS-200 Batch 2 — conversion behaviours (v4.44.0).
   Reads #nr-conv config. Toggles gate via body classes from PHP.
   ───────────────────────────────────────────────────────────── */
(function () {
  var cfg = {};
  try { cfg = JSON.parse((document.getElementById('nr-conv') || {}).textContent || '{}'); } catch (e) {}
  var body = document.body;

  /* #68 — referral capture: ?via=CODE → localStorage, appended to the brief. */
  try {
    var via = new URLSearchParams(location.search).get('via');
    if (via) localStorage.setItem('nr_via', via.slice(0, 40));
  } catch (e) {}

  /* #47 trust line + #49 availability + #74 WhatsApp ref + #68 brief tag — on Enquire. */
  var form = document.querySelector('.nr-enquire__form');
  if (form) {
    if (cfg.trust) {
      var t = document.createElement('p'); t.className = 'nr-trust'; t.textContent = cfg.trust;
      (form.querySelector('.nr-form__actions') || form).insertAdjacentElement('afterend', t);
    }
    if (cfg.avail) {
      var d = form.querySelector('input[type="date"]');
      if (d && d.parentElement) {
        var a = document.createElement('span'); a.className = 'nr-form__avail'; a.textContent = cfg.avail;
        d.parentElement.appendChild(a);
      }
    }
    // append referral code to notes on submit so it reaches the email
    form.addEventListener('submit', function () {
      try {
        var v = localStorage.getItem('nr_via');
        var notes = form.querySelector('[name="notes"]');
        if (v && notes && notes.value.indexOf('[ref:') < 0) notes.value += '\n\n[ref: ' + v + ']';
      } catch (e) {}
    });
    // enrich the WhatsApp link with the project ref if present
    try {
      var ref = new URLSearchParams(location.search).get('ref');
      if (ref) {
        var wa = form.parentElement && form.parentElement.querySelector('a[href*="wa.me"]');
        if (wa) { var u = new URL(wa.href); u.searchParams.set('text', 'Re: ' + ref); wa.href = u.toString(); }
      }
    } catch (e) {}
  }

  /* #77 — share button on single projects (Web Share API → clipboard fallback). */
  if (body.classList.contains('single-nr_project') || document.querySelector('.nr-project')) {
    var actions = document.querySelector('.nr-project__actions');
    if (actions && !actions.querySelector('.nr-share')) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'nr-btn nr-share'; b.innerHTML = '<span>Share</span>';
      b.addEventListener('click', function () {
        var data = { title: document.title, url: location.href };
        if (navigator.share) { navigator.share(data).catch(function () {}); }
        else if (navigator.clipboard) { navigator.clipboard.writeText(location.href); b.querySelector('span').textContent = 'Copied'; setTimeout(function () { b.querySelector('span').textContent = 'Share'; }, 1600); }
      });
      actions.appendChild(b);
    }
  }

  /* #73 — seasonal banner dismiss (persisted). */
  var promo = document.querySelector('[data-promo]');
  if (promo) {
    var key = 'nr_promo_seen';
    try { if (localStorage.getItem(key) === promo.textContent.trim()) promo.remove(); } catch (e) {}
    var x = promo.querySelector('.nr-promo__x');
    if (x) x.addEventListener('click', function () { try { localStorage.setItem(key, promo.textContent.trim()); } catch (e) {} promo.remove(); });
  }

  /* #44 — exit-intent soft offer (desktop, once per session). */
  if (body.classList.contains('nr-exit') && window.matchMedia('(hover:hover)').matches) {
    var shown = false;
    try { shown = sessionStorage.getItem('nr_exit') === '1'; } catch (e) {}
    document.addEventListener('mouseout', function (e) {
      if (shown || e.relatedTarget || e.clientY > 12) return;
      shown = true; try { sessionStorage.setItem('nr_exit', '1'); } catch (e2) {}
      var o = document.createElement('div'); o.className = 'nr-exitp'; o.setAttribute('role', 'dialog');
      o.innerHTML = '<div class="nr-exitp__box"><button class="nr-exitp__x" aria-label="Close">✕</button>'
        + '<p>' + (cfg.exit || 'Before you go — tell me about your project.') + '</p>'
        + '<a class="nr-btn nr-btn--primary" href="' + (cfg.enquire || '/enquire') + '"><span>Start a brief</span> <span>→</span></a></div>';
      document.body.appendChild(o);
      var close = function () { o.remove(); };
      o.addEventListener('click', function (e3) { if (e3.target === o || e3.target.closest('.nr-exitp__x')) close(); });
    });
  }

  /* #41 — visitor shortlist: ♥ on cards → a "selection" tray → send as brief. */
  if (body.classList.contains('nr-shortlist')) {
    var SK = 'nr_shortlist_v1';
    var read = function () { try { return JSON.parse(localStorage.getItem(SK) || '[]'); } catch (e) { return []; } };
    var write = function (a) { try { localStorage.setItem(SK, JSON.stringify(a)); } catch (e) {} };
    var titleOf = function (card) {
      var el = card.querySelector('.nr-card__title, .nr-card__t');
      return (el ? el.textContent : (card.getAttribute('aria-label') || '')).trim();
    };
    var list = read();
    // inject hearts
    document.querySelectorAll('.nr-card').forEach(function (card) {
      if (card.querySelector('.nr-heart')) return;
      var href = card.getAttribute('href') || (card.querySelector('a') && card.querySelector('a').getAttribute('href')) || '';
      var t = titleOf(card); if (!t) return;
      var h = document.createElement('button');
      h.type = 'button'; h.className = 'nr-heart'; h.setAttribute('aria-label', 'Add to selection');
      if (list.some(function (x) { return x.t === t; })) h.classList.add('is-on');
      h.addEventListener('click', function (e) {
        e.preventDefault(); e.stopPropagation();
        var l = read(), i = l.findIndex(function (x) { return x.t === t; });
        if (i >= 0) { l.splice(i, 1); h.classList.remove('is-on'); } else { l.push({ t: t, u: href }); h.classList.add('is-on'); }
        write(l); render();
      });
      card.appendChild(h);
    });
    // tray
    var tray = document.createElement('div'); tray.className = 'nr-tray'; tray.hidden = true;
    tray.innerHTML = '<button class="nr-tray__btn" aria-expanded="false">♥ <span data-tray-n>0</span></button>'
      + '<div class="nr-tray__panel" hidden><strong>Your selection</strong><ul data-tray-list></ul>'
      + '<a class="nr-btn nr-btn--primary" data-tray-send><span>Send as brief</span> <span>→</span></a></div>';
    document.body.appendChild(tray);
    var btn = tray.querySelector('.nr-tray__btn'), panel = tray.querySelector('.nr-tray__panel');
    btn.addEventListener('click', function () { var o = panel.hidden; panel.hidden = !o; btn.setAttribute('aria-expanded', String(o)); });
    function render() {
      var l = read();
      tray.hidden = l.length === 0;
      tray.querySelector('[data-tray-n]').textContent = l.length;
      tray.querySelector('[data-tray-list]').innerHTML = l.map(function (x) { return '<li>' + (x.t || '').replace(/[<>&]/g, '') + '</li>'; }).join('');
      var send = tray.querySelector('[data-tray-send]');
      var titles = l.map(function (x) { return x.t; }).join(', ');
      var base = cfg.enquire || '/enquire';
      send.href = base + (base.indexOf('?') < 0 ? '?' : '&') + 'ref=' + encodeURIComponent(titles.slice(0, 180));
    }
    render();
  }

  /* #110 — search-term capture: beacon ⌘K palette queries (debounced). */
  (function () {
    if (!(window.NR && NR.home)) return;
    var input = document.querySelector('.nr-cmd__input'); // exists once the palette is built
    var bind = function (el) {
      var tmr;
      el.addEventListener('input', function () {
        clearTimeout(tmr);
        tmr = setTimeout(function () {
          var q = el.value.trim();
          if (q.length < 3) return;
          try { navigator.sendBeacon(NR.home + '?rest_route=/nr/v1/search-log', new Blob([JSON.stringify({ q: q })], { type: 'application/json' })); } catch (e) {}
        }, 1200);
      });
    };
    if (input) bind(input);
    else {
      // the palette is built lazily; observe for it
      var mo = new MutationObserver(function () {
        var i = document.querySelector('.nr-cmd__input');
        if (i) { bind(i); mo.disconnect(); }
      });
      mo.observe(document.body, { childList: true });
    }
  })();
})();

/* ─────────────────────────────────────────────────────────────
   IDEAS-200 quick wins (v4.45.0) — journal reading aids.
   #141 reading time + scroll progress · #142 auto table of contents.
   Pure progressive enhancement on the journal single (.nr-jpost).
   ───────────────────────────────────────────────────────────── */
(function () {
  var art = document.querySelector('.nr-jpost__article, .nr-jpost article, article.nr-jpost');
  if (!art) return;

  /* #141 — reading time, injected once near the top of the article. */
  var words = (art.textContent || '').trim().split(/\s+/).length;
  var mins = Math.max(1, Math.round(words / 200));
  if (!art.querySelector('.nr-readtime')) {
    var rt = document.createElement('p');
    rt.className = 'nr-readtime';
    rt.textContent = mins + ' min read';
    art.insertBefore(rt, art.firstChild);
  }

  /* #141 — top scroll-progress bar tracking the article. */
  var bar = document.createElement('div');
  bar.className = 'nr-readbar'; bar.setAttribute('aria-hidden', 'true');
  document.body.appendChild(bar);
  var upd = function () {
    var r = art.getBoundingClientRect();
    var total = art.offsetHeight - window.innerHeight;
    var done = Math.min(1, Math.max(0, (-r.top) / (total > 0 ? total : 1)));
    bar.style.transform = 'scaleX(' + done.toFixed(3) + ')';
  };
  window.addEventListener('scroll', upd, { passive: true });
  window.addEventListener('resize', upd); upd();

  /* #142 — auto table of contents from h2/h3 (only if ≥3 headings). */
  var heads = art.querySelectorAll('h2, h3');
  if (heads.length >= 3) {
    var toc = document.createElement('nav');
    toc.className = 'nr-toc'; toc.setAttribute('aria-label', 'Contents');
    var ul = document.createElement('ul');
    heads.forEach(function (h, i) {
      if (!h.id) h.id = 'sec-' + i + '-' + (h.textContent || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').slice(0, 32);
      var li = document.createElement('li');
      li.className = 'nr-toc__' + h.tagName.toLowerCase();
      var a = document.createElement('a');
      a.href = '#' + h.id; a.textContent = h.textContent;
      li.appendChild(a); ul.appendChild(li);
    });
    toc.appendChild(ul);
    var rt2 = art.querySelector('.nr-readtime');
    if (rt2) rt2.insertAdjacentElement('afterend', toc); else art.insertBefore(toc, art.firstChild);
  }
})();

/* ─────────────────────────────────────────────────────────────
   IDEAS-200 "Small" tier (v4.46.0) — front-end behaviours.
   ───────────────────────────────────────────────────────────── */
(function () {
  var body = document.body;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hover = window.matchMedia('(hover:hover)').matches;
  var cine = body.classList.contains('nr-cinematic');
  function level() { return body.getAttribute('data-nr-motion') || 'standard'; }

  /* #161 — a polite live-region for screen readers. */
  var live = document.createElement('div');
  live.className = 'nr-sr-only'; live.setAttribute('aria-live', 'polite'); live.setAttribute('aria-atomic', 'true');
  body.appendChild(live);
  window.nrAnnounce = function (msg) { live.textContent = ''; setTimeout(function () { live.textContent = msg; }, 60); };

  /* #162 — landmark hygiene: label main + primary nav if unlabelled. */
  var main = document.querySelector('main'); if (main && !main.getAttribute('role')) main.setAttribute('role', 'main');
  var nav = document.querySelector('.nr-menu'); if (nav && !nav.getAttribute('aria-label')) nav.setAttribute('aria-label', 'Primary');

  /* #163 — keyboard shortcuts help overlay ("?"). */
  document.addEventListener('keydown', function (e) {
    if (e.key !== '?' || e.target.matches('input,textarea,select,[contenteditable]')) return;
    if (document.querySelector('.nr-keys')) return;
    var o = document.createElement('div'); o.className = 'nr-keys'; o.setAttribute('role', 'dialog');
    o.innerHTML = '<div class="nr-keys__box"><button class="nr-keys__x" aria-label="Close">✕</button><h3>Keyboard</h3><ul>'
      + '<li><kbd>⌘</kbd>/<kbd>Ctrl</kbd>+<kbd>K</kbd> — search</li><li><kbd>←</kbd><kbd>→</kbd> — hero / plates</li>'
      + '<li><kbd>Esc</kbd> — close</li><li><kbd>?</kbd> — this help</li></ul></div>';
    body.appendChild(o);
    var close = function () { o.remove(); };
    o.addEventListener('click', function (e2) { if (e2.target === o || e2.target.closest('.nr-keys__x')) close(); });
    document.addEventListener('keydown', function k(e3) { if (e3.key === 'Escape') { close(); document.removeEventListener('keydown', k); } });
  });

  /* #181 — consent beacon when the cookie notice is accepted. */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-consent-accept], .nr-cookie__accept, .nr-cookie button');
    if (!b || !(window.NR && NR.home)) return;
    try { navigator.sendBeacon(NR.home + '?rest_route=/nr/v1/consent', new Blob([''], { type: 'text/plain' })); } catch (x) {}
  });

  /* #95 — idle prefetch of a few internal links (saves a hop on first nav). */
  var pf = function () {
    if (body.classList.contains('nr-savedata')) return;
    var seen = {}, n = 0;
    document.querySelectorAll('a.nr-card[href], .nr-hero__thumb[href], a.nr-recent__card[href]').forEach(function (a) {
      var h = a.getAttribute('href'); if (!h || seen[h] || n >= 5) return;
      try { if (new URL(h, location.href).host !== location.host) return; } catch (e) { return; }
      seen[h] = 1; n++;
      var l = document.createElement('link'); l.rel = 'prefetch'; l.href = h; document.head.appendChild(l);
    });
  };
  if ('requestIdleCallback' in window) requestIdleCallback(pf, { timeout: 4000 }); else setTimeout(pf, 3000);

  /* #38 — compare-slider keyboard + labels. */
  document.querySelectorAll('[data-compare] input[type="range"], .nr-compare__range').forEach(function (r) {
    r.setAttribute('aria-label', r.getAttribute('aria-label') || 'Compare before and after');
  });

  /* #126 — contact-sheet toggle on a project page. */
  var rail = document.querySelector('.nr-project__rail');
  if (rail && !document.querySelector('.nr-sheet-toggle')) {
    var crumbs = document.querySelector('.nr-project__crumbs');
    if (crumbs) {
      var t = document.createElement('button');
      t.type = 'button'; t.className = 'nr-sheet-toggle'; t.textContent = '▦ contact sheet';
      t.setAttribute('aria-pressed', 'false');
      t.addEventListener('click', function () {
        var on = rail.classList.toggle('is-sheet');
        t.setAttribute('aria-pressed', String(on));
        t.textContent = on ? '▭ single' : '▦ contact sheet';
      });
      crumbs.appendChild(t);
    }
  }

  /* #157 — captions toggle (EXIF plate captions). */
  if (document.querySelector('.nr-plate-cap')) {
    var ct = document.createElement('button');
    ct.type = 'button'; ct.className = 'nr-cap-toggle'; ct.textContent = 'ƒ captions';
    var capOn = false; try { capOn = localStorage.getItem('nr_caps') === '1'; } catch (e) {}
    if (capOn) body.classList.add('nr-caps-on');
    ct.addEventListener('click', function () {
      capOn = !capOn; body.classList.toggle('nr-caps-on', capOn);
      try { localStorage.setItem('nr_caps', capOn ? '1' : '0'); } catch (e) {}
    });
    var cr = document.querySelector('.nr-project__crumbs'); if (cr) cr.appendChild(ct);
  }

  /* ── Enquire-page helpers ── */
  var form = document.querySelector('.nr-enquire__form');
  if (form) {
    /* #80 — autosave/restore the brief (localStorage). */
    var FK = 'nr_brief_v1';
    var fields = form.querySelectorAll('input[name="name"],input[name="email"],textarea[name="notes"]');
    try {
      var saved = JSON.parse(localStorage.getItem(FK) || '{}');
      fields.forEach(function (f) { if (saved[f.name] && !f.value) f.value = saved[f.name]; });
    } catch (e) {}
    form.addEventListener('input', function () {
      var o = {}; fields.forEach(function (f) { o[f.name] = f.value; });
      try { localStorage.setItem(FK, JSON.stringify(o)); } catch (e) {}
    });
    form.addEventListener('submit', function () { try { localStorage.removeItem(FK); } catch (e) {} });

    /* #75 — "hold two dates": add an alternative date, folded into notes. */
    var d = form.querySelector('input[type="date"][name="preferred_date"]');
    if (d && !form.querySelector('[name="preferred_date_2"]')) {
      var wrap = document.createElement('label');
      wrap.innerHTML = '<span class="nr-eyebrow nr-eyebrow--plain">' + 'Alternative date' + '</span>';
      var d2 = document.createElement('input'); d2.type = 'date'; d2.name = 'preferred_date_2';
      wrap.appendChild(d2);
      if (d.parentElement && d.parentElement.parentElement) d.parentElement.insertAdjacentElement('afterend', wrap);
      form.addEventListener('submit', function () {
        var notes = form.querySelector('[name="notes"]');
        if (d2.value && notes && notes.value.indexOf(d2.value) < 0) notes.value += '\n\nAlternative date: ' + d2.value;
      });
      /* #54 — "add to calendar" link once a date is chosen. */
      var cal = document.createElement('a'); cal.className = 'nr-cal-link'; cal.target = '_blank'; cal.rel = 'noopener'; cal.hidden = true; cal.textContent = '+ add to calendar';
      d.insertAdjacentElement('afterend', cal);
      d.addEventListener('change', function () {
        if (!d.value) { cal.hidden = true; return; }
        var day = d.value.replace(/-/g, '');
        cal.href = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' + encodeURIComponent('Shoot — ' + (document.title)) + '&dates=' + day + '/' + day;
        cal.hidden = false;
      });
    }

    /* #45 — "similar work" for the chosen service (from /projects.json). */
    try {
      var svc = new URLSearchParams(location.search).get('service');
      if (svc && window.NR && NR.home) {
        fetch(NR.home + 'projects.json').then(function (r) { return r.json(); }).then(function (data) {
          var list = (data.projects || data || []).filter(function (p) {
            return (p.cat || p.category || '').toLowerCase().indexOf(svc.replace(/-/g, ' ')) >= 0;
          }).slice(0, 3);
          if (!list.length) return;
          var box = document.createElement('div'); box.className = 'nr-similar';
          box.innerHTML = '<span class="nr-eyebrow nr-eyebrow--xs">Similar work</span>'
            + list.map(function (p) { return '<a href="' + encodeURI(p.url) + '">' + (p.title || '').replace(/[<>&]/g, '') + '</a>'; }).join('');
          form.insertAdjacentElement('afterend', box);
        }).catch(function () {});
      }
    } catch (e) {}
  }

  /* ── Cinematic-gated motion (only when master toggle + level agree) ── */
  if (cine && !reduce) {
    /* #6 — click shockwave. */
    document.addEventListener('pointerdown', function (e) {
      if (level() !== 'cinematic') return;
      if (e.target.closest('input,textarea,select')) return;
      var s = document.createElement('span'); s.className = 'nr-shock';
      s.style.left = e.clientX + 'px'; s.style.top = e.clientY + 'px';
      body.appendChild(s); setTimeout(function () { s.remove(); }, 650);
    }, { passive: true });

    /* #21 — spring release on magnetic buttons (overshoot back to rest). */
    document.querySelectorAll('.nr-btn, [data-magnetic]').forEach(function (b) {
      b.addEventListener('mouseleave', function () {
        if (level() === 'calm') return;
        b.classList.add('nr-spring'); setTimeout(function () { b.classList.remove('nr-spring'); }, 420);
      });
    });

    /* #19 — idle "screensaver" on the home hero. */
    if (body.classList.contains('nr-page-home')) {
      var idle; var arm = function () { clearTimeout(idle); body.classList.remove('nr-idle'); idle = setTimeout(function () { if (level() !== 'calm') body.classList.add('nr-idle'); }, 60000); };
      ['mousemove', 'keydown', 'touchstart', 'scroll', 'click'].forEach(function (ev) { window.addEventListener(ev, arm, { passive: true }); });
      arm();
    }
  }
})();

/* ─────────────────────────────────────────────────────────────
   IDEAS-200 "Medium" r1 (v4.47.0) — motion + field metrics.
   ───────────────────────────────────────────────────────────── */
(function () {
  var body = document.body;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hover = window.matchMedia('(hover:hover)').matches;
  var cine = body.classList.contains('nr-cinematic');
  function level() { return body.getAttribute('data-nr-motion') || 'standard'; }

  /* #107 — Core Web Vitals beacon (LCP/CLS/INP/FCP/TTFB), sampled. */
  if (window.NR && NR.home && Math.random() < 0.5) {
    var send = function (m, v) { try { navigator.sendBeacon(NR.home + '?rest_route=/nr/v1/cwv', new Blob([JSON.stringify({ m: m, v: Math.round(v) })], { type: 'application/json' })); } catch (e) {} };
    try {
      var po = function (type, cb) { try { new PerformanceObserver(cb).observe({ type: type, buffered: true }); } catch (e) {} };
      var cls = 0;
      po('largest-contentful-paint', function (l) { var e = l.getEntries(); send('LCP', e[e.length - 1].startTime); });
      po('layout-shift', function (l) { l.getEntries().forEach(function (e) { if (!e.hadRecentInput) cls += e.value; }); });
      po('paint', function (l) { l.getEntries().forEach(function (e) { if (e.name === 'first-contentful-paint') send('FCP', e.startTime); }); });
      po('event', function (l) { l.getEntries().forEach(function (e) { if (e.interactionId) send('INP', e.duration); }); });
      addEventListener('visibilitychange', function () { if (document.visibilityState === 'hidden') send('CLS', cls * 1000); }, { once: true });
      var nav = performance.getEntriesByType('navigation')[0]; if (nav) send('TTFB', nav.responseStart);
    } catch (e) {}
  }

  /* #108 — funnel step beacon. */
  if (window.NR && NR.home) {
    var step = body.classList.contains('nr-page-home') ? 'home'
      : (/\/portfolio|nr-portfolio-archive/.test(location.pathname + ' ' + body.className) ? 'portfolio'
        : (document.querySelector('.nr-enquire__form') ? 'enquire' : ''));
    if (step) { try { navigator.sendBeacon(NR.home + '?rest_route=/nr/v1/funnel', new Blob([JSON.stringify({ s: step })], { type: 'application/json' })); } catch (e) {} }
  }

  /* #31 — pointer-lock-ish immersive lightbox for project plates. */
  (function () {
    var figs = Array.prototype.slice.call(document.querySelectorAll('.nr-project__plate'));
    if (!figs.length) return;
    var imgs = figs.map(function (f) { var i = f.querySelector('img'); return i ? (i.currentSrc || i.src) : ''; }).filter(Boolean);
    if (!imgs.length) return;
    var lb, idx = 0;
    function open(i) {
      idx = i;
      lb = document.createElement('div'); lb.className = 'nr-lightbox'; lb.setAttribute('role', 'dialog'); lb.setAttribute('aria-modal', 'true');
      lb.innerHTML = '<button class="nr-lightbox__x" aria-label="Close">✕</button><button class="nr-lightbox__p" aria-label="Previous">←</button><img alt=""><button class="nr-lightbox__n" aria-label="Next">→</button><span class="nr-lightbox__c"></span>';
      body.appendChild(lb); body.classList.add('is-modal-open');
      render();
      lb.querySelector('.nr-lightbox__x').onclick = close;
      lb.querySelector('.nr-lightbox__p').onclick = function () { go(-1); };
      lb.querySelector('.nr-lightbox__n').onclick = function () { go(1); };
      lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
      var im = lb.querySelector('img');
      im.addEventListener('click', function () { im.classList.toggle('is-zoom'); });
      document.addEventListener('keydown', key);
      lb.querySelector('.nr-lightbox__x').focus();
    }
    function render() { var im = lb.querySelector('img'); im.classList.remove('is-zoom'); im.src = imgs[idx]; lb.querySelector('.nr-lightbox__c').textContent = (idx + 1) + ' / ' + imgs.length; }
    function go(d) { idx = (idx + d + imgs.length) % imgs.length; render(); }
    function close() { if (!lb) return; lb.remove(); lb = null; body.classList.remove('is-modal-open'); document.removeEventListener('keydown', key); }
    function key(e) { if (e.key === 'Escape') close(); else if (e.key === 'ArrowRight') go(1); else if (e.key === 'ArrowLeft') go(-1); }
    figs.forEach(function (f, i) {
      var im = f.querySelector('img'); if (!im) return;
      im.style.cursor = 'zoom-in';
      f.addEventListener('click', function (e) { if (e.target.closest('a')) return; e.preventDefault(); open(i); });
    });
  })();

  /* #32 — video plate scrubber (seek bar over motion plates). */
  document.querySelectorAll('.nr-plate-video video').forEach(function (v) {
    if (v.closest('.nr-plate-video').querySelector('.nr-vbar')) return;
    var bar = document.createElement('div'); bar.className = 'nr-vbar'; var fill = document.createElement('i'); bar.appendChild(fill);
    v.closest('.nr-plate-video').appendChild(bar);
    v.addEventListener('timeupdate', function () { if (v.duration) fill.style.width = (v.currentTime / v.duration * 100) + '%'; });
    bar.addEventListener('click', function (e) { var r = bar.getBoundingClientRect(); if (v.duration) v.currentTime = ((e.clientX - r.left) / r.width) * v.duration; });
  });

  /* #33 — film-strip thumbnail nav for the project rail. */
  (function () {
    var track = document.querySelector('.nr-project__rail-track');
    if (!track) return;
    var plates = track.querySelectorAll('.nr-project__plate'); if (plates.length < 3) return;
    var strip = document.createElement('div'); strip.className = 'nr-filmstrip';
    plates.forEach(function (p, i) {
      var im = p.querySelector('img'); var b = document.createElement('button'); b.setAttribute('aria-label', 'Plate ' + (i + 1));
      if (im) b.style.backgroundImage = 'url(' + (im.currentSrc || im.src) + ')';
      b.addEventListener('click', function () { p.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', inline: 'center', block: 'nearest' }); });
      strip.appendChild(b);
    });
    var rail = document.querySelector('.nr-project__rail'); if (rail) rail.appendChild(strip);
  })();

  if (!cine) return;

  /* #17 — layered hero parallax (gradient + meta move at different rates). */
  if (hover && !reduce) {
    var frame = document.querySelector('.nr-hero__frame');
    if (frame) {
      var layers = [
        [frame.querySelector('.nr-hero__gradient'), 8],
        [document.querySelector('.nr-hero__meta--tl'), 16],
        [document.querySelector('.nr-hero__meta--br'), 16],
        [document.querySelector('.nr-hero__center'), 5]
      ].filter(function (l) { return l[0]; });
      frame.addEventListener('pointermove', function (e) {
        if (level() !== 'cinematic') return;
        var r = frame.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        layers.forEach(function (l) { l[0].style.transform = 'translate(' + (-x * l[1]).toFixed(1) + 'px,' + (-y * l[1]).toFixed(1) + 'px)'; });
      });
      frame.addEventListener('pointerleave', function () { layers.forEach(function (l) { l[0].style.transform = ''; }); });
    }
  }

  /* #26 — spotlight cursor over the hero (brightness reveal). */
  if (hover && !reduce) {
    var hero = document.querySelector('.nr-hero__frame');
    if (hero) {
      hero.addEventListener('pointermove', function (e) {
        if (level() !== 'cinematic') { hero.style.removeProperty('--spot'); return; }
        var r = hero.getBoundingClientRect();
        hero.style.setProperty('--spot-x', ((e.clientX - r.left) / r.width * 100) + '%');
        hero.style.setProperty('--spot-y', ((e.clientY - r.top) / r.height * 100) + '%');
        hero.classList.add('nr-spot');
      });
      hero.addEventListener('pointerleave', function () { hero.classList.remove('nr-spot'); });
    }
  }

  /* #11 — conservative inertial (Lenis-style) smooth scroll, desktop + cinematic. */
  if (hover && !reduce && level() === 'cinematic' && !document.querySelector('.nr-fullscreen')) {
    var target = window.scrollY, current = window.scrollY, ticking = false;
    var ease = function () {
      current += (target - current) * 0.12;
      if (Math.abs(target - current) < 0.5) { current = target; ticking = false; window.scrollTo(0, current); return; }
      window.scrollTo(0, current); requestAnimationFrame(ease);
    };
    window.addEventListener('wheel', function (e) {
      if (e.ctrlKey || Math.abs(e.deltaY) < 2) return;
      e.preventDefault();
      target = Math.max(0, Math.min(document.documentElement.scrollHeight - window.innerHeight, target + e.deltaY));
      if (!ticking) { ticking = true; requestAnimationFrame(ease); }
    }, { passive: false });
    window.addEventListener('scroll', function () { if (!ticking) { target = current = window.scrollY; } }, { passive: true });
  }
})();

/* ─────────────────────────────────────────────────────────────
   IDEAS-200 "Medium" r2 (v4.49.0) — wizard, A/B, palette filter.
   ───────────────────────────────────────────────────────────── */
(function () {
  var body = document.body;

  /* #42 — multi-step enquire wizard (opt-in, progressive over the real form). */
  var form = document.querySelector('.nr-enquire__form');
  if (form && body.classList.contains('nr-wizard') && !form.dataset.wizard) {
    form.dataset.wizard = '1';
    var labels = Array.prototype.slice.call(form.querySelectorAll(':scope > label, :scope > fieldset'));
    if (labels.length >= 4) {
      var steps = [[], [], []];
      labels.forEach(function (el) {
        var t = (el.textContent || '').toLowerCase();
        var i = /name|email/.test(t) ? 0 : (/type|date|budget|estimate/.test(t) ? 1 : 2);
        steps[i].push(el);
      });
      var bar = document.createElement('div'); bar.className = 'nr-wiz';
      bar.innerHTML = '<span class="nr-wiz__t">1 / 3</span><span class="nr-wiz__bar"><i></i></span>'
        + '<span class="nr-wiz__nav"><button type="button" data-wb hidden>← Back</button><button type="button" data-wn>Next →</button></span>';
      form.insertBefore(bar, form.firstChild);
      var cur = 0, fill = bar.querySelector('.nr-wiz__bar i'), txt = bar.querySelector('.nr-wiz__t');
      var bb = bar.querySelector('[data-wb]'), bn = bar.querySelector('[data-wn]');
      var actions = form.querySelector('.nr-form__actions') || form.querySelector('[type="submit"]');
      function show() {
        steps.forEach(function (els, i) { els.forEach(function (el) { el.style.display = i === cur ? '' : 'none'; }); });
        if (actions) actions.style.display = cur === 2 ? '' : 'none';
        bb.hidden = cur === 0; bn.hidden = cur === 2;
        txt.textContent = (cur + 1) + ' / 3';
        fill.style.width = ((cur + 1) / 3 * 100) + '%';
      }
      bn.addEventListener('click', function () {
        // native-validate the visible required fields before advancing
        var ok = steps[cur].every(function (el) {
          return Array.prototype.every.call(el.querySelectorAll('input,textarea,select'), function (f) { return f.reportValidity ? f.reportValidity() : true; });
        });
        if (ok && cur < 2) { cur++; show(); }
      });
      bb.addEventListener('click', function () { if (cur > 0) { cur--; show(); } });
      show();
    }
  }

  /* #48 — A/B hero CTA: stable per-visitor variant, log view + click. */
  var ab = document.getElementById('nr-ab');
  if (ab && window.NR && NR.home) {
    try {
      var cfg = JSON.parse(ab.textContent || '{}');
      var v = localStorage.getItem('nr_ab_v');
      if (v !== 'a' && v !== 'b') { v = Math.random() < 0.5 ? 'a' : 'b'; localStorage.setItem('nr_ab_v', v); }
      var btn = document.querySelector('[data-hero-link] span');
      if (v === 'b' && cfg.b && btn) btn.textContent = cfg.b;
      var ping = function (k) { try { navigator.sendBeacon(NR.home + '?rest_route=/nr/v1/ab', new Blob([JSON.stringify({ k: k })], { type: 'application/json' })); } catch (e) {} };
      ping(v + '_view');
      var link = document.querySelector('[data-hero-link]');
      if (link) link.addEventListener('click', function () { ping(v + '_click'); });
    } catch (e) {}
  }

  /* #135 — colour-mood filter chips (Warm/Cool/Mono) on archive rails, opt-in.
     Dominant colour is computed client-side from each thumbnail (same-origin). */
  if (body.classList.contains('nr-palette')) {
    var rail = document.querySelector('.nr-portfolio-rail');
    var cards = rail ? Array.prototype.slice.call(rail.querySelectorAll('.nr-card')) : [];
    if (cards.length >= 4) {
      var done = 0;
      cards.forEach(function (card) {
        var img = card.querySelector('img'); if (!img) { done++; return; }
        var probe = new Image();
        probe.crossOrigin = 'anonymous';
        probe.onload = function () {
          try {
            var c = document.createElement('canvas'); c.width = c.height = 1;
            var x = c.getContext('2d'); x.drawImage(probe, 0, 0, 1, 1);
            var d = x.getImageData(0, 0, 1, 1).data, r = d[0], g = d[1], b = d[2];
            var max = Math.max(r, g, b), min = Math.min(r, g, b), sat = max - min;
            card.dataset.mood = sat < 18 ? 'mono' : (r >= b ? 'warm' : 'cool');
          } catch (e) { card.dataset.mood = ''; }
          if (++done === cards.length) chips();
        };
        probe.onerror = function () { if (++done === cards.length) chips(); };
        probe.src = img.currentSrc || img.src;
      });
      function chips() {
        if (document.querySelector('.nr-moods')) return;
        var moods = { warm: 0, cool: 0, mono: 0 };
        cards.forEach(function (c) { if (moods[c.dataset.mood] !== undefined) moods[c.dataset.mood]++; });
        if (!moods.warm && !moods.cool && !moods.mono) return;
        var box = document.createElement('div'); box.className = 'nr-moods nr-chips nr-chips--center';
        box.innerHTML = '<button class="nr-chip is-on" data-mood="">All</button>'
          + ['warm', 'cool', 'mono'].filter(function (m) { return moods[m]; })
            .map(function (m) { return '<button class="nr-chip" data-mood="' + m + '">' + m + '</button>'; }).join('');
        rail.parentElement.insertBefore(box, rail);
        box.addEventListener('click', function (e) {
          var b = e.target.closest('[data-mood]'); if (!b) return;
          box.querySelectorAll('.nr-chip').forEach(function (c) { c.classList.toggle('is-on', c === b); });
          var m = b.dataset.mood;
          cards.forEach(function (c) { c.style.display = (!m || c.dataset.mood === m) ? '' : 'none'; });
        });
      }
    }
  }
})();

/* ─────────────────────────────────────────────────────────────
   Batch 6 r2 (v4.51.0) — infra: service worker, analytics beacon,
   offline enquiry queue (#94), virtualised rails (#88).
   ───────────────────────────────────────────────────────────── */
(function () {
  var X = window.NRX || {};

  /* #93 — register the root-scoped service worker. */
  if (X.sw && 'serviceWorker' in navigator) {
    addEventListener('load', function () { navigator.serviceWorker.register(X.sw_url, { scope: '/' }).catch(function () {}); });
  }

  /* #106 — pageview beacon (logged-out only; flag set server-side). */
  if (X.analytics && X.home) {
    try { navigator.sendBeacon(X.home + '?rest_route=/nr/v1/pv', new Blob([JSON.stringify({ p: location.pathname })], { type: 'application/json' })); } catch (e) {}
  }

  /* #94 — offline enquiry queue: if the brief is submitted while offline,
     stash it and replay (fetch) when the connection returns. */
  var form = document.querySelector('.nr-enquire__form');
  if (form) {
    var QK = 'nr_enq_queue';
    form.addEventListener('submit', function (e) {
      if (navigator.onLine) return; // online → let the normal POST happen
      e.preventDefault();
      try {
        var fd = new FormData(form), o = {}; fd.forEach(function (v, k) { o[k] = v; });
        localStorage.setItem(QK, JSON.stringify({ action: form.action, data: o, t: Date.now() }));
        var s = document.createElement('div'); s.className = 'nr-status nr-status--ok'; s.setAttribute('role', 'status');
        s.textContent = 'Saved offline — it will send automatically when you’re back online.';
        document.body.appendChild(s);
      } catch (x) { form.submit(); }
    });
    var flush = function () {
      var raw; try { raw = localStorage.getItem(QK); } catch (e) { return; }
      if (!raw || !navigator.onLine) return;
      var q; try { q = JSON.parse(raw); } catch (e) { localStorage.removeItem(QK); return; }
      var body = new URLSearchParams(q.data);
      fetch(q.action, { method: 'POST', body: body, credentials: 'same-origin' }).then(function () {
        try { localStorage.removeItem(QK); } catch (e) {}
      }).catch(function () {});
    };
    addEventListener('online', flush); flush();
  }

  /* #88 — virtualised rails: let the browser skip off-screen card layout/paint.
     content-visibility:auto + an intrinsic size keeps scroll position stable. */
  if (X.vrails) {
    document.querySelectorAll('.nr-portfolio-rail, .nr-project__rail-track').forEach(function (rail) {
      var cards = rail.children;
      if (cards.length < 24) return; // only worth it on big archives
      rail.classList.add('nr-vrail');
    });
  }
})();

/* v4.51.6 — enquire FAQ popover */
(function () {
  var open = document.querySelector('.nr-faq-open'), pop = document.querySelector('.nr-faq-pop');
  if (!open || !pop) return;
  var x = pop.querySelector('.nr-faq-pop__x');
  function show() { pop.hidden = false; document.body.classList.add('is-modal-open'); (x || pop).focus && (x || pop).focus(); }
  function hide() { pop.hidden = true; document.body.classList.remove('is-modal-open'); open.focus(); }
  open.addEventListener('click', show);
  if (x) x.addEventListener('click', hide);
  pop.addEventListener('click', function (e) { if (e.target === pop) hide(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !pop.hidden) hide(); });
})();
