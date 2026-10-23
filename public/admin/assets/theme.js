// XTK admin — /admin/theme: RGB editor for each color, live preview, readability checks, themes, import/export.
// The HEX fields in the form stay the source of truth (empty = follows its base color); without JS they are edited by hand.
(function () {
  var form = document.querySelector('[data-theme-form]'), dataEl = document.getElementById('theme-data');
  if (!form || !dataEl) return;
  var D = JSON.parse(dataEl.textContent);
  var HEX = /^#[0-9a-f]{6}([0-9a-f]{2})?$/;
  var scope = 'site', current = null, dirty = false;
  var $ = function (sel, box) { return (box || document).querySelector(sel); };
  var $$ = function (sel, box) { return Array.prototype.slice.call((box || document).querySelectorAll(sel)); };

  // ---------------------------------------------------------------- colors
  var parse = function (hex) {
    hex = hex.slice(1);
    return [parseInt(hex.slice(0, 2), 16), parseInt(hex.slice(2, 4), 16), parseInt(hex.slice(4, 6), 16),
      hex.length === 8 ? Math.round(parseInt(hex.slice(6, 8), 16) / 2.55) / 100 : 1];
  };
  var two = function (n) { return ('0' + Math.max(0, Math.min(255, Math.round(n))).toString(16)).slice(-2); };
  var toHex = function (c) { return '#' + two(c[0]) + two(c[1]) + two(c[2]) + (c[3] < 1 ? two(c[3] * 255) : ''); };
  var css = function (c) { return 'rgb(' + c[0] + ' ' + c[1] + ' ' + c[2] + (c[3] < 1 ? ' / ' + Math.round(c[3] * 100) + '%' : '') + ')'; };
  var over = function (c, u) { var a = c[3]; return [c[0] * a + u[0] * (1 - a), c[1] * a + u[1] * (1 - a), c[2] * a + u[2] * (1 - a), 1]; };
  var lum = function (c) {
    var ch = function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
    return 0.2126 * ch(c[0]) + 0.7152 * ch(c[1]) + 0.0722 * ch(c[2]);
  };
  var contrast = function (fg, bg) {
    bg = over(bg, [255, 255, 255]); fg = over(fg, bg);
    var a = lum(fg), b = lum(bg);
    return Math.round((Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05) * 100) / 100;
  };
  var mix = function (c, w, t) { return [c[0] + (w[0] - c[0]) * t, c[1] + (w[1] - c[1]) * t, c[2] + (w[2] - c[2]) * t, 1]; };

  // ---------------------------------------------------------------- form fields
  var tokens = function (s) { return D.scopes[s].tokens; };
  var input = function (s, t) { return form.elements[s + '[' + t + ']']; };
  var value = function (s, t) { var v = input(s, t).value.trim().toLowerCase(); return HEX.test(v) ? v : ''; };
  var values = function (s) {
    var out = {};
    Object.keys(tokens(s)).forEach(function (t) { var v = value(s, t); if (v) out[t] = v; });
    return out;
  };
  var resolve = function (s) {
    var tk = tokens(s), out = {};
    var get = function (t) {
      if (out[t]) return out[t];
      var d = value(s, t) || tk[t].default, c;
      if (Array.isArray(d)) { c = get(d[0]).slice(); c[3] = Math.round(c[3] * d[1]) / 100; }
      else c = d.charAt(0) === '#' ? parse(d) : get(d).slice();
      return (out[t] = c);
    };
    Object.keys(tk).forEach(get);
    return out;
  };
  var followName = function (s, d) {
    if (!Array.isArray(d) && d.charAt(0) === '#') return 'ຄ່າເດີມ ' + d;
    var t = Array.isArray(d) ? d[0] : d, tk = tokens(s)[t];
    return (tk ? tk.label : t) + (Array.isArray(d) ? ' (' + d[1] + '%)' : '');
  };

  // ---------------------------------------------------------------- preview
  var frame = $('[data-prev-frame]');
  var frameRoot = function () {
    try { return frame && frame.contentDocument && frame.contentDocument.documentElement; } catch (e) { return null; }
  };
  var paint = function (s, root) {
    if (!root) return;
    var tk = tokens(s);
    Object.keys(tk).forEach(function (t) { root.style.setProperty('--' + t, value(s, t) || tk[t].css); });
  };

  // ---------------------------------------------------------------- list rows, counts, warnings
  var render = function (s) {
    var c = resolve(s), tk = tokens(s), n = 0;
    $$('[data-panel="' + s + '"] .th-row').forEach(function (row) {
      var t = row.getAttribute('data-token'), v = value(s, t);
      row.querySelector('.th-sw i').style.background = css(c[t]);
      row.querySelector('.th-row__val').textContent = v ? v : 'ຕາມ ' + followName(s, tk[t].default);
      row.toggleAttribute('data-set', !!v);
      if (v) n++;
    });
    $('[data-count="' + s + '"]').textContent = n;
    if (s === 'admin') paint('admin', document.documentElement);
    else paint('site', frameRoot());
    if (s === scope) warnings();
  };
  var warnings = function () {
    var c = resolve(scope), box = $('[data-warn]'), bad = [];
    D.scopes[scope].pairs.forEach(function (p) {
      var r = contrast(c[p[0]], c[p[1]]);
      if (r < 4.5) bad.push([p[2], r]);
    });
    box.textContent = '';
    if (!bad.length) {
      var ok = document.createElement('p');
      ok.className = 'th-ok';
      ok.textContent = '✓ ທຸກຄູ່ສີອ່ານງ່າຍ (4.5:1 ຂຶ້ນໄປ)';
      box.appendChild(ok);
      return;
    }
    var ul = document.createElement('ul');
    ul.className = 'th-warn';
    bad.forEach(function (b) {
      var li = document.createElement('li');
      li.textContent = b[0] + ' — ' + b[1] + ':1 (ຄວນ 4.5:1 ຂຶ້ນໄປ)';
      ul.appendChild(li);
    });
    box.appendChild(ul);
  };

  // ---------------------------------------------------------------- editor
  var ed = $('[data-ed]');
  var ranges = {}, nums = {};
  ['r', 'g', 'b', 'a'].forEach(function (ch) { ranges[ch] = $('[data-ed-range="' + ch + '"]'); nums[ch] = $('[data-ed-num="' + ch + '"]'); });
  var hexIn = $('[data-ed-hex]'), rgbIn = $('[data-ed-rgb]'), follow = $('[data-ed-follow]');

  var editorShow = function () {
    if (!current) return;
    var s = current[0], t = current[1], tk = tokens(s)[t], c = resolve(s)[t], v = value(s, t);
    $('[data-ed-label]').textContent = tk.label;
    $('[data-ed-token]').textContent = '--' + t;
    $('[data-ed-sw]').style.background = css(c);
    follow.checked = !v;
    $('[data-ed-follow-text]').textContent = 'ຕາມ ' + followName(s, tk.default);
    ['r', 'g', 'b'].forEach(function (ch, i) { ranges[ch].value = nums[ch].value = c[i]; });
    ranges.a.value = nums.a.value = Math.round(c[3] * 100);
    if (document.activeElement !== hexIn) hexIn.value = toHex(c);
    if (document.activeElement !== rgbIn) rgbIn.value = css(c);
    // each track shows where it goes: R from 0 to 255 with G and B as they are now
    var g = function (a, b) { return 'linear-gradient(to right, ' + css(a) + ', ' + css(b) + ')'; };
    ranges.r.style.setProperty('--track', g([0, c[1], c[2], 1], [255, c[1], c[2], 1]));
    ranges.g.style.setProperty('--track', g([c[0], 0, c[2], 1], [c[0], 255, c[2], 1]));
    ranges.b.style.setProperty('--track', g([c[0], c[1], 0, 1], [c[0], c[1], 255, 1]));
    ranges.a.style.setProperty('--track', g([c[0], c[1], c[2], 0], [c[0], c[1], c[2], 1]));
    $('[data-ed-scale]').hidden = !(t === 'brand-600');
    pairs(s, t);
  };
  var pairs = function (s, t) {
    var c = resolve(s), ul = $('[data-ed-pairs]');
    ul.textContent = '';
    D.scopes[s].pairs.forEach(function (p) {
      if (p[0] !== t && p[1] !== t) return;
      var r = contrast(c[p[0]], c[p[1]]), li = document.createElement('li');
      li.className = r < 4.5 ? 'is-bad' : 'is-ok';
      li.textContent = (r < 4.5 ? '⚠ ອ່ານຍາກ ' : '✓ ອ່ານງ່າຍ ') + r + ':1 — ' + p[2];
      ul.appendChild(li);
    });
  };
  var set = function (s, t, hex) {
    input(s, t).value = hex;
    dirty = true;
    render(s);
    if (current && current[0] === s) editorShow();
  };
  var setFromEditor = function (c) { if (current) set(current[0], current[1], toHex(c)); };
  var narrow = window.matchMedia('(max-width: 1100px)');
  var pick = function (s, t, byHand) {
    current = [s, t];
    ed.hidden = false;
    // on narrow screens the editor sits above the list
    if (byHand && narrow.matches) ed.scrollIntoView({ block: 'start', behavior: 'smooth' });
    $$('.th-row.is-on').forEach(function (r) { r.classList.remove('is-on'); });
    var row = $('[data-panel="' + s + '"] .th-row[data-token="' + t + '"]');
    if (row) row.classList.add('is-on');
    editorShow();
  };
  var channels = function () {
    return [+nums.r.value, +nums.g.value, +nums.b.value, Math.max(0, Math.min(100, +nums.a.value)) / 100];
  };
  ['r', 'g', 'b', 'a'].forEach(function (ch) {
    ranges[ch].addEventListener('input', function () { nums[ch].value = ranges[ch].value; setFromEditor(channels()); });
    ranges[ch].addEventListener('change', function () { remember(); });
    nums[ch].addEventListener('change', function () {
      var max = ch === 'a' ? 100 : 255, n = Math.max(0, Math.min(max, Math.round(+nums[ch].value || 0)));
      nums[ch].value = n; setFromEditor(channels()); remember();
    });
  });
  hexIn.addEventListener('input', function () {
    var v = hexIn.value.trim().toLowerCase();
    if (v.charAt(0) !== '#') v = '#' + v;
    if (/^#[0-9a-f]{3}$/.test(v)) v = '#' + v[1] + v[1] + v[2] + v[2] + v[3] + v[3];
    if (HEX.test(v)) setFromEditor(parse(v));
  });
  hexIn.addEventListener('change', remember);
  rgbIn.addEventListener('change', function () {
    var m = rgbIn.value.match(/(\d{1,3})[\s,]+(\d{1,3})[\s,]+(\d{1,3})(?:[\s,/]+([\d.]+)(%?))?/);
    if (!m) { editorShow(); return; }
    var a = m[4] === undefined ? 1 : (m[5] || +m[4] > 1 ? +m[4] / 100 : +m[4]);
    setFromEditor([+m[1], +m[2], +m[3], Math.max(0, Math.min(1, a))]);
    remember();
  });
  follow.addEventListener('change', function () {
    if (!current) return;
    if (follow.checked) set(current[0], current[1], '');
    else setFromEditor(resolve(current[0])[current[1]]);
  });
  var drop = $('[data-ed-drop]');
  if ('EyeDropper' in window) {
    drop.hidden = false;
    drop.addEventListener('click', function () {
      new window.EyeDropper().open().then(function (r) { setFromEditor(parse(r.sRGBHex.toLowerCase())); remember(); }).catch(function () {});
    });
  }
  $('[data-ed-scale]').addEventListener('click', function () {
    if (!current) return;
    var s = current[0], c = resolve(s)['brand-600'];
    var scale = { 'brand-900': mix(c, [0, 0, 0], 0.42), 'brand-500': mix(c, [255, 255, 255], 0.15),
      'brand-100': mix(c, [255, 255, 255], 0.88), 'brand-50': mix(c, [255, 255, 255], 0.94) };
    Object.keys(scale).forEach(function (t) { input(s, t).value = toHex(scale[t]); });
    dirty = true; render(s); editorShow();
  });

  // recent colors, per browser
  var KEY = 'xtk-theme-recent';
  var recent = function () { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; } };
  var drawRecent = function () {
    var list = recent(), box = $('[data-ed-recent]');
    $('[data-ed-recent-box]').hidden = !list.length;
    box.textContent = '';
    list.forEach(function (h) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'th-chip'; b.title = h;
      b.setAttribute('aria-label', 'ໃຊ້ສີ ' + h);
      b.style.background = css(parse(h));
      b.addEventListener('click', function () { setFromEditor(parse(h)); });
      box.appendChild(b);
    });
  };
  function remember() {
    if (!current) return;
    var h = value(current[0], current[1]);
    if (!h) return;
    var list = recent().filter(function (x) { return x !== h; });
    list.unshift(h);
    try { localStorage.setItem(KEY, JSON.stringify(list.slice(0, 12))); } catch (e) {}
    drawRecent();
  }

  // ---------------------------------------------------------------- rows, tabs, search
  $$('.th-row').forEach(function (row) {
    var s = row.closest('[data-panel]').getAttribute('data-panel'), t = row.getAttribute('data-token');
    row.querySelector('[data-pick]').addEventListener('click', function () { pick(s, t, true); });
    row.querySelector('.th-row__hex').addEventListener('input', function () { dirty = true; render(s); if (current && current[1] === t) editorShow(); });
  });
  var showScope = function (s) {
    scope = s;
    $$('[data-scope-tab]').forEach(function (b) { b.setAttribute('aria-selected', b.getAttribute('data-scope-tab') === s ? 'true' : 'false'); });
    $$('[data-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== s; });
    $('[data-prev]').hidden = s !== 'site';
    $('[data-admin-note]').hidden = s !== 'admin';
    if (s === 'site' && !frame.getAttribute('src')) loadFrame();
    pick(s, 'brand-600');
    warnings();
  };
  $$('[data-scope-tab]').forEach(function (b, i, all) {
    b.addEventListener('click', function () { showScope(b.getAttribute('data-scope-tab')); });
    b.addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
      var next = all[(i + (e.key === 'ArrowRight' ? 1 : all.length - 1)) % all.length];
      next.focus(); next.click();
    });
  });
  var q = $('[data-search-colors]');
  q.addEventListener('input', function () {
    var v = q.value.trim().toLowerCase();
    $$('.th-row').forEach(function (r) { r.hidden = v !== '' && r.getAttribute('data-text').indexOf(v) === -1; });
    $$('.th-group').forEach(function (g) { g.hidden = !g.querySelector('.th-row:not([hidden])'); });
  });

  // ---------------------------------------------------------------- preview frame
  // the page is drawn at its real width (1280 px, phone 390 px) and scaled down to the box
  var box = $('[data-prev-box]');
  var fit = function () {
    var w = box.classList.contains('is-mobile') ? 390 : 1280;
    box.style.setProperty('--s', Math.min(1, box.clientWidth / w).toFixed(4));
  };
  if ('ResizeObserver' in window) new ResizeObserver(fit).observe(box);
  fit();
  var loadFrame = function () { frame.src = '/admin/preview?page=' + encodeURIComponent($('[data-prev-page]').value) + '&lang=lo'; };
  frame.addEventListener('load', function () { paint('site', frameRoot()); });
  $('[data-prev-page]').addEventListener('change', loadFrame);
  $$('[data-prev-size]').forEach(function (b) {
    b.addEventListener('click', function () {
      $$('[data-prev-size]').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
      box.classList.toggle('is-mobile', b.getAttribute('data-prev-size') === 'mobile');
      fit();
    });
  });

  // ---------------------------------------------------------------- whole themes: ready-made, saved, history, files
  var fill = function (theme) {
    ['site', 'admin'].forEach(function (s) {
      var t = theme[s] || {};
      Object.keys(tokens(s)).forEach(function (k) {
        var v = typeof t[k] === 'string' ? t[k].toLowerCase() : '';
        input(s, k).value = HEX.test(v) ? v : '';
      });
      render(s);
    });
    dirty = true;
    editorShow();
  };
  var load = $('[data-load]');
  var group = function (label, items) {
    if (!items.length) return;
    var g = document.createElement('optgroup');
    g.label = label;
    items.forEach(function (it) { var o = document.createElement('option'); o.value = it[0]; o.textContent = it[1]; g.appendChild(o); });
    load.appendChild(g);
  };
  group('ສຳເລັດຮູບ', D.presets.map(function (p, i) { return ['p' + i, p.name]; }));
  group('ທີ່ບັນທຶກໄວ້', D.saved.map(function (p, i) { return ['s' + i, p.name + ' (' + p.at + ')']; }));
  group('ສະບັບກ່ອນໜ້າ', D.history.map(function (p, i) { return ['h' + i, p.at + (p.by ? ' · ' + p.by : '')]; }));
  load.addEventListener('change', function () {
    var v = load.value, k = v.charAt(0), i = +v.slice(1);
    load.value = '';
    if (!v || !window.confirm('ແທນສີທີ່ກຳລັງປັບຢູ່ທັງໝົດ (ທັງໜ້າເວັບ ແລະ admin)?')) return;
    if (k === 'p') fill({ site: D.presets[i].brand, admin: D.presets[i].brand });
    else fill((k === 's' ? D.saved : D.history)[i]);
  });
  $('[data-export]').addEventListener('click', function () {
    var blob = new Blob([JSON.stringify({ site: values('site'), admin: values('admin') }, null, 2)], { type: 'application/json' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'xtk-theme-' + new Date().toISOString().slice(0, 10) + '.json';
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
  });
  $('[data-import]').addEventListener('change', function (e) {
    var file = e.target.files[0];
    e.target.value = '';
    if (!file) return;
    file.text().then(function (text) {
      var t = JSON.parse(text);
      if (!t || typeof t !== 'object' || (!t.site && !t.admin)) throw new Error('shape');
      fill(t);
    }).catch(function () { window.alert('ໄຟລ໌ນີ້ບໍ່ແມ່ນໄຟລ໌ຕີມ (JSON ທີ່ມີ site / admin).'); });
  });
  $('[data-reset-scope]').addEventListener('click', function () {
    if (!window.confirm('ໃຫ້ທຸກສີໃນແທັບນີ້ກັບໄປຕາມຄ່າເດີມ?')) return;
    Object.keys(tokens(scope)).forEach(function (t) { input(scope, t).value = ''; });
    dirty = true; render(scope); editorShow();
  });

  form.addEventListener('submit', function (e) {
    var bad = $$('.th-row__hex', form).filter(function (i) { return i.value.trim() !== '' && !HEX.test(i.value.trim().toLowerCase()); });
    if (bad.length) { e.preventDefault(); bad[0].focus(); window.alert('ລະຫັດສີບໍ່ຖືກຕ້ອງ: ໃຊ້ #rrggbb ຫຼື #rrggbbaa.'); return; }
    dirty = false;
  });
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  $$('[data-js]').forEach(function (el) { el.hidden = false; });
  drawRecent();
  render('admin');
  render('site');
  showScope('site');
})();
