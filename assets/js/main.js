// XTK static demo — small progressive enhancements only; every page reads fine without JS.
(function () {
  var doc = document.documentElement;
  doc.classList.add('js');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  // header state + back-to-top
  var hdr = $('.hdr'), top = $('.float .top');
  function onScroll() {
    var y = window.scrollY;
    if (hdr) hdr.classList.toggle('is-scrolled', y > 80);
    if (top) top.classList.toggle('is-on', y > 600);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  if (top) top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }); });

  // mobile drawer
  var drawer = $('.drawer'), burger = $('.burger');
  function setDrawer(open) {
    drawer.classList.toggle('is-open', open);
    burger.setAttribute('aria-expanded', open);
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) $('.drawer__close').focus(); else burger.focus();
  }
  if (drawer && burger) {
    burger.addEventListener('click', function () { setDrawer(true); });
    $('.drawer__close').addEventListener('click', function () { setDrawer(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && drawer.classList.contains('is-open')) setDrawer(false); });
  }

  // close language menu on outside click
  document.addEventListener('click', function (e) {
    $$('details.lang[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });

  // hero slider
  var hero = $('.hero');
  if (hero) {
    var slides = $$('.hero__slide', hero), bar = $('.hero__bar i', hero), count = $('.hero__count', hero);
    var cur = 0, timer = null, MS = 7000;
    hero.style.setProperty('--slide-ms', MS + 'ms');
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    function show(n) {
      cur = (n + slides.length) % slides.length;
      slides.forEach(function (s, i) {
        var on = i === cur;
        s.classList.toggle('is-active', on);
        s.setAttribute('aria-hidden', !on);
        $$('a, button', s).forEach(function (el) { el.tabIndex = on ? 0 : -1; });
        var v = $('video', s);
        if (v) { if (on && !reduce) { var p = v.play(); if (p && p.catch) p.catch(function () {}); } else v.pause(); }
      });
      if (count) count.textContent = pad(cur + 1) + ' / ' + pad(slides.length);
      if (bar) { bar.classList.remove('run'); void bar.offsetWidth; if (!reduce) bar.classList.add('run'); }
      clearTimeout(timer);
      if (!reduce) timer = setTimeout(function () { show(cur + 1); }, MS);
    }
    $('.hero__btn--next', hero).addEventListener('click', function () { show(cur + 1); });
    $('.hero__btn--prev', hero).addEventListener('click', function () { show(cur - 1); });
    show(0);
  }

  // tabs
  $$('.tabs').forEach(function (tabs) {
    var btns = $$('.tabs__btn', tabs), panels = $$('.tabs__panel', tabs);
    function pick(i) {
      btns.forEach(function (b, j) { b.setAttribute('aria-selected', j === i); b.tabIndex = j === i ? 0 : -1; });
      panels.forEach(function (p, j) { p.hidden = j !== i; });
    }
    btns.forEach(function (b, i) {
      b.addEventListener('click', function () { pick(i); });
      b.addEventListener('keydown', function (e) {
        var d = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[e.key];
        if (!d) return;
        e.preventDefault();
        var n = (i + d + btns.length) % btns.length; pick(n); btns[n].focus();
      });
    });
  });

  // product group filter
  $$('.filters[data-filter]').forEach(function (f) {
    var pills = $$('.pill', f), groups = $$('.pgroup');
    pills.forEach(function (p) {
      p.addEventListener('click', function () {
        var g = p.getAttribute('data-g');
        pills.forEach(function (x) { x.setAttribute('aria-pressed', x === p); });
        groups.forEach(function (el) { el.hidden = g !== 'all' && el.id !== g; });
      });
    });
  });

  // demo forms: there is no backend yet, so just confirm on screen
  $$('form[data-demo]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var ok = $('.form__ok', form);
      if (ok) { ok.hidden = false; ok.focus(); }
      form.reset();
    });
  });

  // loading screen (home): hide once the page has loaded
  var loader = $('.loader');
  if (loader) {
    var done = function () { loader.classList.add('is-done'); };
    if (document.readyState === 'complete') setTimeout(done, 500);
    else window.addEventListener('load', function () { setTimeout(done, 500); });
  }

  // numbers count up when they come into view
  function countUp(el) {
    var node = el.firstChild;
    var end = parseInt(node && node.nodeValue, 10);
    if (!node || node.nodeType !== 3 || isNaN(end) || reduce) return;
    var from = end > 1000 ? end - 30 : 0, t0 = null, DUR = 1600;
    node.nodeValue = from;
    requestAnimationFrame(function step(ts) {
      if (!t0) t0 = ts;
      var k = Math.min((ts - t0) / DUR, 1);
      node.nodeValue = Math.round(from + (end - from) * (1 - Math.pow(1 - k, 3)));
      if (k < 1) requestAnimationFrame(step);
    });
    // never leave a half-counted figure on screen if frames are throttled (background tab)
    setTimeout(function () { t0 = -1e9; node.nodeValue = end; }, DUR + 400);
  }

  // gentle parallax on page-head images
  var plx = $$('.plx');
  if (plx.length && !reduce) {
    var onPlx = function () { var y = window.scrollY; plx.forEach(function (el) { el.style.transform = 'translate3d(0,' + (y * 0.18).toFixed(1) + 'px,0)'; }); };
    window.addEventListener('scroll', onPlx, { passive: true });
    onPlx();
  }

  // reveal on scroll
  var rv = $$('.rv');
  if (reduce || !('IntersectionObserver' in window)) {
    rv.forEach(function (el) { el.classList.add('is-in'); });
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        en.target.classList.add('is-in');
        var c = en.target.querySelector('[data-count]');
        if (c) countUp(c);
        io.unobserve(en.target);
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    rv.forEach(function (el) { io.observe(el); });
  }
})();
