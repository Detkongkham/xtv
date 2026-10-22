// XTK site — small progressive enhancements only; every page reads fine without JS.
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
  $$('.filters[data-filter=""]').forEach(function (f) {
    var pills = $$('.pill', f), groups = $$('.pgroup');
    pills.forEach(function (p) {
      p.addEventListener('click', function () {
        var g = p.getAttribute('data-g');
        pills.forEach(function (x) { x.setAttribute('aria-pressed', x === p); });
        groups.forEach(function (el) { el.hidden = g !== 'all' && el.id !== g; });
      });
    });
  });

  // supplier filter: each tile lists its divisions in data-divs; the result count is announced
  $$('.filters[data-filter="items"]').forEach(function (f) {
    var pills = $$('.pill', f), tiles = $$('[data-divs]'), out = $('[data-result]');
    pills.forEach(function (p) {
      p.addEventListener('click', function () {
        var g = p.getAttribute('data-g'), n = 0;
        pills.forEach(function (x) { x.setAttribute('aria-pressed', x === p); });
        tiles.forEach(function (el) {
          var on = g === 'all' || el.getAttribute('data-divs').split(' ').indexOf(g) > -1;
          el.hidden = !on;
          if (on) n++;
        });
        if (out) out.textContent = out.getAttribute('data-result').replace('{}', n);
      });
    });
  });

  // contact form: without JS it posts normally; with JS it gets a time token, sends with fetch
  // and shows errors under each field (docs/plan/02-frontend.md §5)
  $$('form[data-contact]').forEach(function (form) {
    if (!window.fetch || !window.FormData) return;
    var msgs = JSON.parse(form.getAttribute('data-msgs') || '{}');
    var btn = $('button[type="submit"]', form), btnText = btn.textContent;
    var ok = $('.form__ok', form), fail = $('.form__err', form);
    var ts = form.elements.ts, asked = false;
    form.noValidate = true;

    var getToken = function () {
      if (asked) return;
      asked = true;
      fetch(form.action + '?token=1', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) { if (d && d.ts) ts.value = d.ts; })
        .catch(function () {});
    };
    form.addEventListener('focusin', getToken);

    var clearErrors = function () {
      $$('.field__err', form).forEach(function (s) { s.hidden = true; s.textContent = ''; });
      $$('[aria-invalid]', form).forEach(function (el) { el.removeAttribute('aria-invalid'); el.removeAttribute('aria-describedby'); });
      fail.hidden = true;
    };
    var showErrors = function (errors) {
      var first = null;
      Object.keys(errors).forEach(function (name) {
        var el = form.elements[name], span = $('#f-' + name + '-err', form);
        if (!el || !span) return;
        var code = errors[name];
        span.textContent = name === 'consent' ? msgs.err_consent : (msgs['err_' + code] || msgs.err_invalid);
        span.hidden = false;
        el.setAttribute('aria-invalid', 'true');
        el.setAttribute('aria-describedby', span.id);
        if (!first) first = el;
      });
      if (first) first.focus();
    };
    var showFail = function (text) {
      $('span', fail).textContent = text;
      fail.hidden = false;
      fail.focus();
    };
    var busy = function (on) {
      btn.disabled = on;
      btn.textContent = on ? msgs.sending : btnText;
    };

    // light checks before sending; the server checks everything again
    var localErrors = function () {
      var e = {}, v = function (n) { return (form.elements[n].value || '').trim(); };
      if (!v('name')) e.name = 'required';
      if (!v('tel')) e.tel = 'required';
      if (!v('email')) e.email = 'required';
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v('email'))) e.email = 'invalid';
      if (!v('message')) e.message = 'required';
      else if (v('message').length < 10) e.message = 'too_short';
      if (!form.elements.consent.checked) e.consent = 'required';
      return e;
    };

    var send = function (retried) {
      busy(true);
      fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (r) {
          return r.json().catch(function () { return {}; }).then(function (d) { return { status: r.status, d: d }; });
        })
        .then(function (res) {
          busy(false);
          if (res.status === 200 && res.d.ok) {
            form.reset();
            ts.value = '';
            form.classList.add('form--sent');
            ok.hidden = false;
            ok.focus();
          } else if (res.status === 422 && res.d.errors) {
            showErrors(res.d.errors);
          } else if (res.status === 422 && !retried) {
            // token too old or rejected: send once more without it (server applies the stricter no-JS limit)
            ts.value = '';
            send(true);
          } else {
            showFail(res.status === 429 ? msgs.err_rate : msgs.err_server);
          }
        })
        .catch(function () { busy(false); showFail(msgs.err_server); });
    };

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (btn.disabled) return;
      clearErrors();
      var errs = localErrors();
      if (Object.keys(errs).length) { showErrors(errs); return; }
      send(false);
    });
  });

  // Chinese font (zh pages, see templates/site/layout.php): fetched after the page has loaded
  var zhFont = $('meta[name="font-zh"]');
  if (zhFont && window.FontFace) {
    var loadZh = function () {
      new FontFace('Noto Sans SC', 'url(' + zhFont.content + ') format("woff2")', { weight: '400 600', display: 'swap' })
        .load().then(function (f) { document.fonts.add(f); }, function () {});
    };
    if (document.readyState === 'complete') loadZh();
    else window.addEventListener('load', loadZh);
  }

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
