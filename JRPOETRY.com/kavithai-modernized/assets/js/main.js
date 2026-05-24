document.addEventListener('DOMContentLoaded', function() {

  // Scroll reveal — same as HTML preview
  var els = document.querySelectorAll('.r');
  if ('IntersectionObserver' in window) {
    var obs = new IntersectionObserver(function(entries) {
      entries.forEach(function(e) {
        if (e.isIntersecting) { e.target.classList.add('on'); obs.unobserve(e.target); }
      });
    }, { threshold: 0.07 });
    els.forEach(function(el) { obs.observe(el); });
  } else {
    els.forEach(function(el) { el.classList.add('on'); });
  }

  // Hero reveals immediately
  setTimeout(function() {
    document.querySelectorAll('.hero .r').forEach(function(el) { el.classList.add('on'); });
  }, 60);

  // Mobile nav toggle
  var toggle = document.querySelector('.nav-toggle');
  var menu   = document.querySelector('.nav-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', function() {
      var open = menu.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.textContent = open ? '✕' : '☰';
    });
    menu.querySelectorAll('a').forEach(function(a) {
      a.addEventListener('click', function() {
        menu.classList.remove('open');
        toggle.textContent = '☰';
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  // Book 3D tilt on hover
  document.querySelectorAll('.book').forEach(function(b) {
    b.addEventListener('mousemove', function(e) {
      var r = b.getBoundingClientRect();
      var x = (e.clientX - r.left) / r.width - 0.5;
      var cover = b.querySelector('.book-cover');
      if (cover) cover.style.transform = 'perspective(600px) rotateY(' + (x * 8) + 'deg)';
    });
    b.addEventListener('mouseleave', function() {
      var cover = b.querySelector('.book-cover');
      if (cover) { cover.style.transition = 'transform 0.5s ease'; cover.style.transform = ''; }
    });
  });

  // wi rows clickable
  document.querySelectorAll('.wi[onclick]').forEach(function(wi) {
    wi.style.cursor = 'pointer';
  });
});
