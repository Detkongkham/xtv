// XTK admin — mobile menu, confirm before risky forms, busy text on slow forms. Everything works without JS.
(function () {
  var btn = document.querySelector('[data-menu]'), side = document.getElementById('side');
  if (!btn || !side) return;
  var set = function (open) {
    side.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  };
  btn.addEventListener('click', function () { set(!side.classList.contains('is-open')); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
  document.addEventListener('click', function (e) {
    if (side.classList.contains('is-open') && !side.contains(e.target) && !btn.contains(e.target)) set(false);
  });
})();

(function () {
  // <form data-confirm="question?">: ask first. <form data-busy="text">: disable the button while the server works.
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (f.dataset.confirm && !window.confirm(f.dataset.confirm)) { e.preventDefault(); return; }
    var b = f.querySelector('button[type="submit"]');
    if (f.dataset.busy && b) { b.disabled = true; b.textContent = f.dataset.busy; }
  });
})();

(function () {
  // <input data-filter-for="id">: hide the labels inside #id whose data-text does not contain what was typed
  document.querySelectorAll('[data-filter-for]').forEach(function (input) {
    var box = document.getElementById(input.getAttribute('data-filter-for'));
    if (!box) return;
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      box.querySelectorAll('[data-text]').forEach(function (el) { el.hidden = q !== '' && el.getAttribute('data-text').indexOf(q) === -1; });
      box.querySelectorAll('fieldset').forEach(function (fs) { fs.hidden = !fs.querySelector('[data-text]:not([hidden])'); });
    });
  });
})();

(function () {
  // [data-lang-tabs]: one tab per language panel. A dot marks a tab with an error (red) or empty Thai/Chinese (amber).
  document.querySelectorAll('[data-lang-tabs]').forEach(function (box, n) {
    var panels = Array.prototype.slice.call(box.querySelectorAll('.lang-panel'));
    var list = document.createElement('div');
    list.className = 'lang-tablist';
    list.setAttribute('role', 'tablist');
    var first = panels.findIndex(function (p) { return p.dataset.state === 'error'; });
    var tabs = panels.map(function (p, i) {
      var t = document.createElement('button');
      t.type = 'button';
      t.className = 'lang-tab';
      t.id = 'lt' + n + '-' + i;
      t.textContent = p.dataset.label;
      t.dataset.state = p.dataset.state;
      t.setAttribute('role', 'tab');
      p.setAttribute('role', 'tabpanel');
      p.setAttribute('aria-labelledby', t.id);
      list.appendChild(t);
      return t;
    });
    var show = function (i, focus) {
      tabs.forEach(function (t, k) {
        t.setAttribute('aria-selected', k === i ? 'true' : 'false');
        t.tabIndex = k === i ? 0 : -1;
        panels[k].hidden = k !== i;
      });
      if (focus) tabs[i].focus();
    };
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { show(i); });
      t.addEventListener('keydown', function (e) {
        var d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
        if (d) { e.preventDefault(); show((i + d + tabs.length) % tabs.length, true); }
      });
    });
    // a required field in a hidden tab: show that tab so the browser can point at it
    box.addEventListener('invalid', function (e) {
      var i = panels.indexOf(e.target.closest('.lang-panel'));
      if (i >= 0) show(i);
    }, true);
    box.insertBefore(list, box.firstChild);
    box.classList.add('is-tabbed');
    show(first >= 0 ? first : 0);
  });
})();
