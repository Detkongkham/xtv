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
