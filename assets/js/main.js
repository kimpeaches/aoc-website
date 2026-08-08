/* ============================================================
   ASSIST ON CALL - main.js
   Animations, accordion, video controls, nav
   ============================================================ */
(function () {
  'use strict';
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- 1. Sticky nav state ---------- */
  var nav = document.querySelector('.nav');
  function navScroll() { if (nav) nav.classList.toggle('is-stuck', window.scrollY > 40); }
  navScroll();
  window.addEventListener('scroll', navScroll, { passive: true });

  /* ---------- 2. Mobile nav toggle ---------- */
  var tgl = document.querySelector('.navtoggle');
  if (tgl) tgl.addEventListener('click', function () {
    var box = document.querySelector('.nav-in');
    var open = box.classList.toggle('open');
    tgl.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  /* ---------- 3. Scroll reveal ---------- */
  var rv = document.querySelectorAll('.rv');
  if (reduced) {
    rv.forEach(function (el) { el.classList.add('in'); });
  } else if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    rv.forEach(function (el) { io.observe(el); });
  } else {
    rv.forEach(function (el) { el.classList.add('in'); });
  }

  /* ---------- 4. Word-by-word statement reveal ---------- */
  var stmts = document.querySelectorAll('[data-reveal-words]');
  stmts.forEach(function (host) {
    var accent = (host.getAttribute('data-accent') || '').toLowerCase().split(',').map(function (s) { return s.trim(); });
    var words = host.textContent.trim().split(/\s+/);
    host.textContent = '';
    words.forEach(function (w, i) {
      var s = document.createElement('span');
      s.className = 'w' + (accent.indexOf(w.toLowerCase().replace(/[^a-z]/g, '')) > -1 ? ' acc' : '');
      s.textContent = w;
      host.appendChild(s);
      if (i < words.length - 1) host.appendChild(document.createTextNode(' '));
    });
  });
  function litWords() {
    stmts.forEach(function (host) {
      var ws = host.querySelectorAll('.w');
      var r = host.getBoundingClientRect();
      var start = window.innerHeight * 0.85;
      var end = window.innerHeight * 0.22;
      var p = (start - r.top) / (start - end + r.height * 0.55);
      p = Math.max(0, Math.min(1, p));
      var n = Math.round(p * ws.length);
      ws.forEach(function (w, i) { w.classList.toggle('lit', i < n); });
    });
  }
  if (stmts.length) {
    if (reduced) { document.querySelectorAll('.w').forEach(function (w) { w.classList.add('lit'); }); }
    else { litWords(); window.addEventListener('scroll', litWords, { passive: true }); window.addEventListener('resize', litWords); }
  }

  /* ---------- 5. Service accordion ---------- */
  var items = document.querySelectorAll('.acc__item');
  items.forEach(function (item, idx) {
    var head = item.querySelector('.acc__head');
    var body = item.querySelector('.acc__body');
    if (!head || !body) return;
    var id = body.id || ('acc-panel-' + idx);
    body.id = id;
    head.setAttribute('aria-controls', id);
    head.setAttribute('aria-expanded', item.classList.contains('open') ? 'true' : 'false');
    head.addEventListener('click', function () {
      var openNow = item.classList.contains('open');
      // single-open behaviour within the same accordion group
      var group = item.closest('.acc');
      if (group && !openNow) {
        group.querySelectorAll('.acc__item.open').forEach(function (o) {
          o.classList.remove('open');
          var h = o.querySelector('.acc__head');
          if (h) h.setAttribute('aria-expanded', 'false');
        });
      }
      item.classList.toggle('open', !openNow);
      head.setAttribute('aria-expanded', !openNow ? 'true' : 'false');
    });
    // keyboard: arrow navigation between headers
    head.addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
      e.preventDefault();
      var all = Array.prototype.slice.call(items);
      var next = all[all.indexOf(item) + (e.key === 'ArrowDown' ? 1 : -1)];
      if (next) next.querySelector('.acc__head').focus();
    });
  });

  /* ---------- 6. Deep-link to a service (?s=skilled-nursing or #skilled-nursing) ---------- */
  var target = (location.hash || '').replace('#', '') || new URLSearchParams(location.search).get('s');
  if (target) {
    var t = document.getElementById(target);
    if (t && t.classList.contains('acc__item')) {
      t.classList.add('open');
      var th = t.querySelector('.acc__head');
      if (th) th.setAttribute('aria-expanded', 'true');
      setTimeout(function () { t.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'center' }); }, 320);
    }
  }

  /* ---------- 7. Hero cover-video play / pause toggle ---------- */
  document.querySelectorAll('.vid-toggle').forEach(function (btn) {
    var vid = document.querySelector(btn.getAttribute('data-target'));
    if (!vid) return;
    function paint() {
      var paused = vid.paused;
      btn.innerHTML = paused
        ? '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>'
        : '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M6 5h4v14H6zm8 0h4v14h-4z"/></svg>';
      btn.setAttribute('aria-label', paused ? 'Play background video' : 'Pause background video');
    }
    paint();
    btn.addEventListener('click', function () { vid.paused ? vid.play() : vid.pause(); paint(); });
    vid.addEventListener('play', paint);
    vid.addEventListener('pause', paint);
  });

  /* ---------- 8. Respect reduced motion for videos ---------- */
  if (reduced) document.querySelectorAll('video[autoplay]').forEach(function (v) { v.pause(); v.removeAttribute('autoplay'); });

  /* ---------- 9. Pause off-screen videos (saves bandwidth/battery) ---------- */
  if (!reduced && 'IntersectionObserver' in window) {
    var vo = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        var v = e.target;
        if (v.dataset.userPaused === '1') return;
        if (e.isIntersecting) { if (v.paused) v.play().catch(function () {}); }
        else if (!v.paused) v.pause();
      });
    }, { threshold: 0.15 });
    document.querySelectorAll('video[data-auto]').forEach(function (v) { vo.observe(v); });
  }

  /* ---------- 10. Marquee duplication (seamless loop) ---------- */
  document.querySelectorAll('.mstrip__row').forEach(function (row) {
    if (row.dataset.cloned) return;
    row.dataset.cloned = '1';
    row.innerHTML = row.innerHTML + row.innerHTML;
  });

  /* ---------- 11. Mark current page in the nav ---------- */
  var here = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.menu a').forEach(function (a) {
    if (a.getAttribute('href') === here) a.classList.add('on');
  });
})();
