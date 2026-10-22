// XTK admin — mobile menu toggle. Everything else works without JS.
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
