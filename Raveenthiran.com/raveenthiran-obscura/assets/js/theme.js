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
   - plate lightbox
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

    const linkSelector = 'a, button, .nr-chip, .nr-card, .nr-project__plate, .nr-hero__thumb, [data-modal], [data-hero-prev], [data-hero-next]';
    document.body.addEventListener('mouseover', (e) => {
      const t = e.target.closest(linkSelector);
      if (t) {
        cur.classList.add('is-on-link');
        if (curLbl) {
          const lbl = t.dataset.curLabel ||
            (t.classList.contains('nr-card') || t.classList.contains('nr-project__plate') ? 'view' :
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

  /* ---------- portfolio filter chips ---------- */
  $$('[data-filter]').forEach(chip => {
    chip.addEventListener('click', () => {
      const group = chip.parentElement;
      if (!group) return;
      group.querySelectorAll('.nr-chip').forEach(c => { c.classList.remove('is-on'); c.setAttribute('aria-selected', 'false'); });
      chip.classList.add('is-on');
      chip.setAttribute('aria-selected', 'true');
      const f = chip.dataset.filter;
      $$('[data-portfolio] .nr-card, [data-cats]').forEach(card => {
        const cats = (card.dataset.cats || '').split(/\s+/);
        const show = f === 'all' || cats.indexOf(f) !== -1;
        card.style.display = show ? '' : 'none';
      });
      const rail = $('[data-portfolio]');
      if (rail) rail.scrollTo({ left: 0, behavior: 'smooth' });
    });
  });

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

  /* ---------- plate lightbox ----------
     Consumes data-lightbox-src/-w/-h/-caption on .nr-project__plate-btn.
     Builds a grouped list (same data-lightbox-group on the parent rail)
     so left / right arrows step through plates inside one project. */
  (function lightbox() {
    const lb = document.getElementById('nr-lightbox');
    if (!lb) return;

    const img   = lb.querySelector('.nr-lightbox__img');
    const count = lb.querySelector('.nr-lightbox__count');
    const title = lb.querySelector('.nr-lightbox__title');
    const dims  = lb.querySelector('.nr-lightbox__dims');
    const closeBtn = lb.querySelector('.nr-lightbox__close');
    const prevBtn  = lb.querySelector('.nr-lightbox__prev');
    const nextBtn  = lb.querySelector('.nr-lightbox__next');

    let group = [];     // array of {src, w, h, caption}
    let idx = 0;
    let lastFocus = null;

    function render() {
      if (!group.length) return;
      const it = group[idx];
      img.src = it.src;
      img.alt = it.caption || '';
      count.textContent = String(idx + 1).padStart(2, '0') + ' / ' + String(group.length).padStart(2, '0');
      title.textContent = it.caption || '';
      dims.textContent = (it.w && it.h) ? (it.w + ' × ' + it.h) : '';
      prevBtn.disabled = group.length <= 1;
      nextBtn.disabled = group.length <= 1;
    }

    function open(triggerBtn) {
      lastFocus = document.activeElement;
      // Build group from siblings inside the same data-lightbox-group container.
      const groupRoot = triggerBtn.closest('[data-lightbox-group]') || triggerBtn.parentElement;
      const buttons = Array.from(groupRoot.querySelectorAll('[data-lightbox-src]'));
      group = buttons.map(b => ({
        src: b.dataset.lightboxSrc,
        w: parseInt(b.dataset.lightboxW || '0', 10),
        h: parseInt(b.dataset.lightboxH || '0', 10),
        caption: b.dataset.lightboxCaption || '',
      }));
      idx = Math.max(0, buttons.indexOf(triggerBtn));
      render();
      lb.classList.add('is-on');
      lb.setAttribute('aria-hidden', 'false');
      document.body.classList.add('is-modal-open');
      // Focus the close button so Esc + keyboard works immediately
      requestAnimationFrame(() => closeBtn.focus());
    }

    function close() {
      lb.classList.remove('is-on');
      lb.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('is-modal-open');
      img.src = '';
      group = [];
      if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    function step(d) {
      if (group.length <= 1) return;
      idx = (idx + d + group.length) % group.length;
      render();
    }

    // Open when any plate-btn is clicked
    document.body.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-lightbox-src]');
      if (btn) { e.preventDefault(); open(btn); }
    });
    // Close handlers
    closeBtn.addEventListener('click', close);
    prevBtn.addEventListener('click', () => step(-1));
    nextBtn.addEventListener('click', () => step(1));
    lb.addEventListener('click', (e) => { if (e.target === lb) close(); });

    // Touch swipe (mobile) — swipe between plates in the lightbox.
    let lsx = 0, lsy = 0;
    lb.addEventListener('touchstart', (e) => { lsx = e.changedTouches[0].clientX; lsy = e.changedTouches[0].clientY; }, { passive: true });
    lb.addEventListener('touchend', (e) => {
      const dx = e.changedTouches[0].clientX - lsx, dy = e.changedTouches[0].clientY - lsy;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.4) step(dx < 0 ? 1 : -1);
    }, { passive: true });

    // Keyboard — Esc closes, ←/→ navigate, Tab cycles inside the lightbox.
    window.addEventListener('keydown', (e) => {
      if (!lb.classList.contains('is-on')) return;
      if (e.key === 'Escape')     { e.preventDefault(); close(); }
      else if (e.key === 'ArrowLeft')  { e.preventDefault(); step(-1); }
      else if (e.key === 'ArrowRight') { e.preventDefault(); step(1); }
      else if (e.key === 'Tab') {
        // Focus trap — three buttons cycle
        const focusables = [closeBtn, prevBtn, nextBtn];
        const i = focusables.indexOf(document.activeElement);
        if (i === -1) { e.preventDefault(); closeBtn.focus(); return; }
        e.preventDefault();
        const dir = e.shiftKey ? -1 : 1;
        focusables[(i + dir + focusables.length) % focusables.length].focus();
      }
    });
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
    document.querySelectorAll('.nr-card, .nr-faq__item, .nr-steps li').forEach(function (el, i) {
      el.style.animationDelay = ((i % 6) * 70) + 'ms';
      io.observe(el);
    });
  }
})();
