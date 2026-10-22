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
  // [data-show-if="name=value"]: shown only while that radio is checked. Without JS every part stays visible.
  var parts = document.querySelectorAll('[data-show-if]');
  var update = function () {
    parts.forEach(function (el) {
      var kv = el.getAttribute('data-show-if').split('='), on = document.querySelector('input[name="' + kv[0] + '"]:checked');
      el.hidden = !on || on.value !== kv[1];
    });
  };
  if (parts.length) { document.addEventListener('change', update); update(); }
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

(function () {
  // product specs: add, move and remove rows. Without JS there is always one empty row to fill in.
  var box = document.querySelector('[data-spec-rows]');
  var tpl = document.querySelector('[data-spec-template]');
  var add = document.querySelector('[data-spec-add]');
  if (!box || !tpl || !add) return;
  var n = 0;
  var tools = function (row) { var t = row.querySelector('[data-spec-tools]'); if (t) t.hidden = false; };
  box.querySelectorAll('[data-spec-row]').forEach(tools);
  add.hidden = false;
  add.addEventListener('click', function () {
    var wrap = document.createElement('div');
    wrap.innerHTML = tpl.innerHTML.replace(/__N__/g, 'n' + (++n));
    var row = wrap.firstElementChild;
    tools(row);
    box.appendChild(row);
    row.querySelector('input').focus();
  });
  box.addEventListener('click', function (e) {
    var b = e.target.closest('[data-spec]');
    if (!b) return;
    var row = b.closest('[data-spec-row]');
    if (b.dataset.spec === 'remove') row.remove();
    if (b.dataset.spec === 'up' && row.previousElementSibling) box.insertBefore(row, row.previousElementSibling);
    if (b.dataset.spec === 'down' && row.nextElementSibling) box.insertBefore(row.nextElementSibling, row);
    if (b.dataset.spec !== 'remove') b.focus();
  });
})();

(function () {
  // textarea[data-editor]: a small editor (headings, bold, italic, lists, quote, link, picture). The textarea stays
  // the value that is sent; the server cleans the HTML with an allow-list. Without JS the textarea takes raw HTML.
  var areas = document.querySelectorAll('textarea[data-editor]');
  if (!areas.length || !document.execCommand) return;
  var holder = document.querySelector('[data-rte-images]');
  var images = holder ? JSON.parse(holder.getAttribute('data-rte-images') || '[]') : [];
  document.execCommand('defaultParagraphSeparator', false, 'p');
  var tools = [
    ['H2', 'ຫົວຂໍ້ຍ່ອຍ', function () { document.execCommand('formatBlock', false, 'h2'); }],
    ['H3', 'ຫົວຂໍ້ນ້ອຍ', function () { document.execCommand('formatBlock', false, 'h3'); }],
    ['¶', 'ຫຍໍ້ໜ້າທຳມະດາ', function () { document.execCommand('formatBlock', false, 'p'); }],
    null,
    ['B', 'ຕົວໜາ', function () { document.execCommand('bold'); }],
    ['I', 'ຕົວອຽງ', function () { document.execCommand('italic'); }],
    null,
    ['• ລາຍການ', 'ລາຍການ', function () { document.execCommand('insertUnorderedList'); }],
    ['1. ລາຍການ', 'ລາຍການມີເລກ', function () { document.execCommand('insertOrderedList'); }],
    ['“ ”', 'ຄຳອ້າງອີງ', function () { document.execCommand('formatBlock', false, 'blockquote'); }],
    null,
    ['ລິ້ງ', 'ໃສ່ລິ້ງ', function () {
      var url = window.prompt('ລິ້ງ: ຊື່ໜ້າໃນເວັບ (ເຊັ່ນ healthcare.html) ຫຼື https://…', 'https://');
      if (url && url !== 'https://') document.execCommand('createLink', false, url.trim());
    }],
    ['ລຶບລິ້ງ', 'ລຶບລິ້ງ', function () { document.execCommand('unlink'); }],
    ['ຮູບ', 'ໃສ່ຮູບຈາກຄັງສື່', null],
    ['ລ້າງຮູບແບບ', 'ລ້າງຮູບແບບ', function () { document.execCommand('removeFormat'); document.execCommand('formatBlock', false, 'p'); }]
  ];
  var pickImage = function (done) {
    var dlg = document.createElement('dialog');
    dlg.className = 'rte-dialog';
    dlg.innerHTML = '<h2>ເລືອກຮູບ</h2><p class="hint">ອັບຮູບໃໝ່ທີ່ໜ້າ ຄັງສື່ ກ່ອນ.</p><div class="media-choices"></div><div class="btns" style="margin-top:16px"><button class="btn btn--ghost" type="button">ຍົກເລີກ</button></div>';
    var grid = dlg.querySelector('.media-choices');
    images.forEach(function (img) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'media-choice';
      b.innerHTML = '<img alt="" loading="lazy"><span></span>';
      b.querySelector('img').src = img.thumb;
      b.querySelector('span').textContent = img.name;
      b.addEventListener('click', function () { dlg.close(); dlg.remove(); done(img); });
      grid.appendChild(b);
    });
    if (!images.length) grid.textContent = 'ຍັງບໍ່ມີຮູບໃນຄັງສື່.';
    dlg.querySelector('.btn--ghost').addEventListener('click', function () { dlg.close(); dlg.remove(); done(null); });
    document.body.appendChild(dlg);
    dlg.showModal();
  };
  areas.forEach(function (ta) {
    var box = document.createElement('div');
    box.className = 'rte';
    var bar = document.createElement('div');
    bar.className = 'rte__bar';
    bar.setAttribute('role', 'toolbar');
    bar.setAttribute('aria-label', 'ຮູບແບບຂໍ້ຄວາມ');
    var area = document.createElement('div');
    area.className = 'rte__area';
    area.contentEditable = 'true';
    area.setAttribute('role', 'textbox');
    area.setAttribute('aria-multiline', 'true');
    area.lang = ta.lang;
    var label = document.querySelector('label[for="' + ta.id + '"]');
    if (label) { label.id = label.id || ta.id + '-label'; area.setAttribute('aria-labelledby', label.id); label.addEventListener('click', function () { area.focus(); }); }
    area.innerHTML = ta.value;
    var sync = function () { ta.value = area.innerHTML.trim() === '<br>' ? '' : area.innerHTML; };
    var saved = null;
    tools.forEach(function (t) {
      if (!t) { var s = document.createElement('span'); s.className = 'sep'; bar.appendChild(s); return; }
      var b = document.createElement('button');
      b.type = 'button';
      b.textContent = t[0];
      b.title = t[1];
      b.setAttribute('aria-label', t[1]);
      b.addEventListener('mousedown', function (e) { e.preventDefault(); }); // keep the selection in the text
      b.addEventListener('click', function () {
        area.focus();
        if (t[2]) { t[2](); sync(); return; }
        var sel = window.getSelection();
        saved = sel.rangeCount ? sel.getRangeAt(0).cloneRange() : null;
        pickImage(function (img) {
          area.focus();
          if (saved) { sel.removeAllRanges(); sel.addRange(saved); }
          if (img) {
            // a picture is its own block: it goes after the paragraph or heading the cursor is in, never inside it
            var block = saved ? saved.startContainer : null;
            while (block && block.parentNode !== area) block = block.parentNode;
            var fig = document.createElement('figure');
            var el = document.createElement('img');
            el.src = img.src; el.alt = ''; el.width = img.w; el.height = img.h;
            fig.appendChild(el);
            area.insertBefore(fig, block ? block.nextSibling : null);
            var after = document.createElement('p');
            after.appendChild(document.createElement('br'));
            area.insertBefore(after, fig.nextSibling);
            var r = document.createRange();
            r.setStart(after, 0);
            sel.removeAllRanges();
            sel.addRange(r);
            sync();
          }
        });
      });
      bar.appendChild(b);
    });
    area.addEventListener('input', sync);
    box.appendChild(bar);
    box.appendChild(area);
    ta.hidden = true;
    ta.parentNode.insertBefore(box, ta);
    if (ta.getAttribute('aria-invalid')) area.setAttribute('aria-invalid', 'true');
  });
})();
