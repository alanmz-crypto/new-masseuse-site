(function () {
  'use strict';
  function onInitialArrival(callback) {
    function run() {
      var marker = document.getElementById('ts-peek-arrival');
      if (marker && marker.dataset.hash && marker.dataset.hash === location.hash) callback();
    }
    if (document.readyState === 'complete') run();
    else window.addEventListener('load', run, { once: true });
  }
  var revealingQuestion = null, closingHeaderToggle = null;
  function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
  function attr(t) { return esc(t).replace(/"/g, '&quot;'); }
  function byHref(h) { var id = h.slice(1); try { id = decodeURIComponent(id); } catch (e) {} return document.getElementById(id); }
  function rowSections(row) {
    var out = row ? [].slice.call(row.querySelectorAll('a[href^="#"]')).map(function (a) { return { href: a.getAttribute('href'), label: a.textContent.trim() }; }) : [];
    out = out.filter(function (s) { return !!byHref(s.href); });
    out.sort(function (a, b) { var x = byHref(a.href), y = byHref(b.href); return x === y ? 0 : (x.compareDocumentPosition(y) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1); });
    return out;
  }
  // Mobile text overflow can enlarge innerWidth/innerHeight while the visible
  // viewport remains unchanged. Keep fixed controls in the visible rectangle,
  // including its offsets during pinch zoom, without clipping page content.
  function viewportBounds() {
    var v = window.visualViewport, root = document.documentElement;
    var left = v ? v.offsetLeft : 0, top = v ? v.offsetTop : 0;
    var width = v ? v.width : root.clientWidth, height = v ? v.height : root.clientHeight;
    return { left: left, top: top, width: width, height: height, right: left + width, bottom: top + height };
  }
  function measureViewport() {
    var v = viewportBounds(), root = document.documentElement;
    var values = { '--pk-vx': v.left, '--pk-vy': v.top, '--pk-vw': v.width, '--pk-vh': v.height };
    Object.keys(values).forEach(function (key) { var value = values[key] + 'px'; if (root.style.getPropertyValue(key) !== value) root.style.setProperty(key, value); });
    return v;
  }
  function pageIdentity() {
    try { var config = JSON.parse(document.getElementById('ts-peek-nav-config').textContent); if (typeof config.title === 'string' && config.title.trim()) return config.title.trim(); } catch (e) {}
    return document.title;
  }
  function dismissMobileMenu(head) {
    function current() {
      var mobile = head && head.querySelector('#ast-mobile-header[data-type="dropdown"]');
      var toggle = mobile && mobile.querySelector('.menu-toggle.main-header-menu-toggle');
      var panel = mobile && mobile.querySelector('.ast-mobile-header-content');
      return toggle && panel && toggle.getAttribute('aria-expanded') === 'true' && toggle.isConnected && panel.isConnected && toggle.getBoundingClientRect().height > 0 && panel.getBoundingClientRect().height > 0 ? { toggle: toggle, panel: panel } : null;
    }
    function close(active) {
      active = active || current(); if (!active) return;
      // Only this exact native toggle bypasses Peek's one-click guard; the
      // original preview-owned tap stays consumed.
      closingHeaderToggle = active.toggle;
      try { active.toggle.click(); } finally { closingHeaderToggle = null; }
    }
    if (head) document.addEventListener('click', function (event) {
      var active = current();
      if (event.button > 0 || !active || active.toggle.contains(event.target) || active.panel.contains(event.target)) return;
      // The final click already owns its target. Close before ordinary page
      // links measure the flow header; Peek's earlier activations also call close.
      close(active);
    }, true);
    return close;
  }
  // All modes share the same strip, pinned-header measurement and reading line.
  function titleStrip(content, sections, resolve, row, titleOnly) {
    var root = document.documentElement, strip = document.createElement('div');
    strip.id = 'ts-peek-title-strip'; strip.className = 'ts-pk ts-pk-top' + (titleOnly ? ' is-title-only' : ''); strip.setAttribute('aria-hidden', 'true');
    strip.innerHTML = '<div class="in"><span class="before">' + esc(pageIdentity()) + '</span><div class="tr"></div></div>';
    if (row) row.parentNode.insertBefore(strip, row.nextSibling); else content.insertBefore(strip, content.firstChild);
    var topIn = strip.querySelector('.in'), topTr = strip.querySelector('.tr'), items = [], head = document.getElementById('masthead');
    var closeMenu = dismissMobileMenu(head);
    root.classList.add('ts-pk-title-ready');
    if (content !== document.body) content.classList.add('ts-pk-title-content');
    function measureHead() {
      measureViewport();
      var pinned = head && getComputedStyle(head).position === 'sticky';
      var menu = head ? head.querySelector('.ast-mobile-header-content') : null;
      var bar = pinned ? head.offsetHeight - (menu ? menu.offsetHeight : 0) : 0;
      root.style.setProperty('--pk-head', Math.max(0, bar) + 'px');
      root.style.setProperty('--pk-strip', strip.offsetHeight + 'px');
      root.style.setProperty('--pk-title-end', ((parseFloat(getComputedStyle(strip).top) || 0) + strip.offsetHeight) + 'px');
    }
    function layout() {
      var height = 0;
      if (titleOnly) {
        var width = Math.max(1, topIn.clientWidth * (items.length === 1 ? 1 : 0.68));
        items.forEach(function (item) { item.style.maxWidth = width + 'px'; });
        // Reserve the active weight's full wrapped height before painting, so
        // becoming current cannot cut a long title's last line off.
        height = items.reduce(function (h, item) { item.style.fontWeight = '800'; var next = Math.ceil(item.getBoundingClientRect().height); item.style.removeProperty('font-weight'); return Math.max(h, next); }, 0);
      }
      height = Math.max(height, Math.ceil(strip.querySelector('.before').getBoundingClientRect().height));
      topIn.style.height = Math.max(parseFloat(getComputedStyle(root).fontSize) * 1.55, height) + 'px';
      measureHead();
    }
    function setSections(next) {
      sections = next;
      topTr.innerHTML = sections.map(function (s) { return '<span>' + esc(s.label) + '</span>'; }).join('');
      items = [].slice.call(topTr.children); strip.classList.toggle('is-single', sections.length === 1); layout();
    }
    function line() { var v = viewportBounds(); return Math.max(v.top + v.height * 0.25, (parseFloat(getComputedStyle(strip).top) || 0) + strip.offsetHeight + 24); }
    function bounds(i) { var h = resolve(i), nx = resolve(i + 1); return [h ? h.getBoundingClientRect().top : 0, nx ? nx.getBoundingClientRect().top : content.getBoundingClientRect().bottom]; }
    function read() {
      var y = line(), cur = -1, tops = sections.map(function (s, k) { var h = resolve(k); return h ? h.getBoundingClientRect().top : null; }), end = content.getBoundingClientRect().bottom;
      tops.forEach(function (t, k) { if (t !== null && t <= y) cur = k; });
      var fills = sections.map(function (s, k) { if (k < cur) return 1; if (k !== cur || tops[k] === null) return 0; var b = tops[k + 1] !== undefined && tops[k + 1] !== null ? tops[k + 1] : end; return Math.max(0, Math.min(1, (y - tops[k]) / Math.max(1, b - tops[k]))); });
      var a0 = tops[0] !== null ? tops[0] : 0, whole = Math.max(0, Math.min(1, (y - a0) / Math.max(1, end - a0)));
      var ci = items.map(function (t) { return t.offsetLeft + t.offsetWidth / 2; }), at = Math.max(cur, 0), nxt = Math.min(at + 1, sections.length - 1);
      var x = cur < 0 ? ci[0] : ci[at] + (fills[at] || 0) * (ci[nxt] - ci[at]), near = 0;
      ci.forEach(function (c, k) { if (Math.abs(c - x) < Math.abs(ci[near] - x)) near = k; });
      return { cur: cur, fills: fills, whole: whole, shift: topIn.clientWidth / 2 - x, near: near };
    }
    function paint(state) {
      strip.classList.toggle('is-before', state.cur < 0);
      topTr.style.transform = 'translateX(' + state.shift.toFixed(1) + 'px)';
      items.forEach(function (t, k) { t.classList.toggle('is-now', k === state.near); });
    }
    function watch(changed) {
      window.addEventListener('resize', function () { layout(); changed(); });
      if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function () { layout(); changed(); });
        window.visualViewport.addEventListener('scroll', function () { measureViewport(); changed(); });
      }
      if ('ResizeObserver' in window) {
        // layout() can resize the observed strip when root text grows. Apply
        // that correction in the next frame, outside observer delivery, while
        // coalescing a header and strip notification into the same update.
        var sizeFrame = 0;
        var sizes = new ResizeObserver(function () {
          if (!sizeFrame) sizeFrame = requestAnimationFrame(function () { sizeFrame = 0; layout(); changed(); });
        });
        if (head) sizes.observe(head);
        // Rem-based padding changes with text enlargement even while the
        // measured title window still has its previous pixel height.
        sizes.observe(strip, { box: 'border-box' });
      }
      window.addEventListener('load', function () { layout(); changed(); });
      if (document.fonts && document.fonts.addEventListener) document.fonts.addEventListener('loadingdone', function () { layout(); changed(); });
    }
    setSections(sections);
    return { strip: strip, head: head, line: line, bounds: bounds, read: read, paint: paint, layout: layout, watch: watch, setSections: setSections, closeMenu: closeMenu };
  }
  function practiceNavigation(content, title) {
    var labs = [].slice.call(content.querySelectorAll('.tsx-lab,.tsp-lab,.tsr-lab'));
    function barHeight(lab) {
      var bar = lab.querySelector('.tsx-pills.is-on,.tsx-seg.is-on,.tsp-bar.is-on,.tsr-top');
      if (!bar || !bar.getClientRects().length || getComputedStyle(bar).display === 'none') return 0;
      // Keep the actual height while Now/Next closes: the section's document
      // position includes that same shrinking list, so the two offsets cancel.
      return Math.max(0, bar.getBoundingClientRect().height);
    }
    function measure() {
      labs.forEach(function (lab) { var value = barHeight(lab) + 'px'; if (lab.style.getPropertyValue('--pk-demo-bar') !== value) lab.style.setProperty('--pk-demo-bar', value); });
    }
    if (labs.length && 'MutationObserver' in window) {
      // These two owned demos mark a section is-flash only inside go(), after
      // the visitor chooses a destination. Observe only those section classes:
      // previews, index presses and ordinary scrolling never request a jump.
      // Their old scrollTo offsets know only the old bar, so correct that one
      // navigation immediately. No global API replacement or settling timer.
      var jumps = new MutationObserver(function (changes) {
        var section = null;
        changes.forEach(function (change) { if (change.target.classList.contains('is-flash')) section = change.target; });
        if (!section) return;
        var lab = section.closest('.tsp-lab,.tsr-lab'), strip = title.strip;
        if (!lab || !content.contains(lab)) return;
        var offset = (parseFloat(getComputedStyle(strip).top) || 0) + strip.offsetHeight + barHeight(lab) + 12;
        window.scrollTo({ top: section.getBoundingClientRect().top + window.scrollY - offset, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
      });
      [].forEach.call(content.querySelectorAll('.tsp-lab .tsp-sec,.tsr-lab .tsr-sec'), function (section) { jumps.observe(section, { attributes: true, attributeFilter: ['class'] }); });
    }
    measure(); return measure;
  }
  function titleNavigation(content, title, refresh) {
    var calm = window.matchMedia('(prefers-reduced-motion: reduce)'), stopPending = function () {}, mayArrive = true;
    function below(target) {
      // The owned practice pages add their old bar to scroll-margin. Ordinary
      // content uses the same shared header/strip boundary as the four rails.
      return Math.max((parseFloat(getComputedStyle(title.strip).top) || 0) + title.strip.offsetHeight + 12, parseFloat(getComputedStyle(target).scrollMarginTop) || 0);
    }
    function reveal(target) {
      for (var node = target; node && content.contains(node); node = node.parentElement) { if (node.tagName === 'DETAILS') node.open = true; }
      var faq = target.closest('.uagb-faq-item');
      if (faq && !faq.classList.contains('uagb-faq-item-active')) {
        var question = faq.querySelector('.uagb-faq-questions-button');
        if (question) {
          // A title-card touch has armed its compatibility-click guard. Allow
          // only this owned disclosure activation through that guard; the real
          // click following the finger lift must still be consumed.
          revealingQuestion = question;
          try { question.click(); } finally { revealingQuestion = null; }
        }
      }
      // A second activation may arrive while an already-active answer is
      // still animating and moving the destination.
      return !!faq;
    }
    function align(target, behavior) {
      if (!target.isConnected) return;
      refresh();
      window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - below(target), behavior: behavior });
    }
    function settle(target) {
      // Reuse the existing rail arrival contract only for load-time fragments
      // and an animated FAQ jump. Stop after four seconds or any visitor input.
      stopPending();
      var timer = 0, expiry = 0, done = false, sizes = null, inputs = ['wheel', 'touchstart', 'pointerdown', 'keydown', 'hashchange', 'popstate'];
      // Pointer Events already report a new mouse press. The compatibility
      // mousedown after this handled touch is not new visitor input.
      if (!('PointerEvent' in window)) inputs.push('mousedown');
      function stop() {
        if (done) return; done = true; clearTimeout(timer); clearTimeout(expiry);
        window.removeEventListener('scroll', idle);
        if (sizes) sizes.disconnect();
        inputs.forEach(function (event) { window.removeEventListener(event, stop, true); });
      }
      function idle() {
        clearTimeout(timer); timer = setTimeout(function () {
          if (done || !target.isConnected) { stop(); return; }
          refresh(); if (Math.abs(target.getBoundingClientRect().top - below(target)) > 6) align(target, 'instant');
        }, 180);
      }
      stopPending = stop; expiry = setTimeout(stop, 4000);
      inputs.forEach(function (event) { window.addEventListener(event, stop, { once: true, passive: true, capture: true }); });
      var faq = target.closest('.uagb-faq-item'), group = faq && faq.closest('.uagb-faq__outer-wrap');
      if (faq && 'ResizeObserver' in window) {
        // Closing the preceding answer can move this target without scrolling.
        // Observe the items, since one closing/one opening may keep group size
        // constant. These observers share the same expiry and cancellation.
        sizes = new ResizeObserver(idle);
        [].forEach.call(group ? group.querySelectorAll('.uagb-faq-item') : [faq], function (item) { sizes.observe(item); });
      }
      window.addEventListener('scroll', idle, { passive: true }); idle();
    }
    function go(target, href) {
      if (!target || !content.contains(target)) return;
      stopPending(); mayArrive = false;
      title.closeMenu();   // a preview may activate on pointerup or keydown, before click
      var animated = reveal(target); align(target, calm.matches ? 'auto' : 'smooth');
      var focus = /^H[1-6]$/.test(target.tagName) ? target : target.querySelector('h1,h2,h3,h4,h5,h6,.ts-faq-disclosure') || target;
      if (!focus.hasAttribute('tabindex') && !focus.matches('a[href],button,input,select,textarea,summary')) {
        focus.setAttribute('tabindex', '-1');
        focus.addEventListener('blur', function () { if (focus.getAttribute('tabindex') === '-1') focus.removeAttribute('tabindex'); }, { once: true });
      }
      focus.focus({ preventScroll: true });
      if (href) { try { history.replaceState(null, '', href); } catch (e) {} }
      if (animated) settle(target);
    }
    content.addEventListener('click', function (event) {
      var link = event.target.closest ? event.target.closest('a[href^="#"]') : null;
      if (!link || link.closest('.ts-pk,.tsx-lab,.tsp-lab,.tsr-lab,.tsi-lab') || event.defaultPrevented || event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
      var href = link.getAttribute('href'), target = href.length > 1 ? byHref(href) : null;
      if (!target || !content.contains(target)) return;
      // Astra's anchor handler ignores scroll-margin. Capture the existing link
      // before it reaches the theme; retain its exact encoded fragment.
      event.preventDefault(); event.stopPropagation(); go(target, href);
    }, true);
    function arrive() {
      if (!mayArrive) return;
      var target = location.hash.length > 1 ? byHref(location.hash) : null;
      if (!target || !content.contains(target) || target.closest('.ts-pk')) return;
      reveal(target); refresh(); settle(target);
    }
    onInitialArrival(arrive);
    return { go: go };
  }
  function sectionSource(content, row, keepRowTargets) {
    var config = null;
    try { config = JSON.parse(document.getElementById('ts-peek-nav-config').textContent); } catch (e) {}
    var fallback = pageIdentity();
    function visible(node) {
      if (!node || !content.contains(node) || node.closest('.ts-pk,.ts-jump,nav,header,footer,dialog,[role="navigation"],[role="banner"],[role="contentinfo"],[role="dialog"],[hidden],[inert],[aria-hidden="true"],.screen-reader-text,.sr-only')) return false;
      var r = node.getBoundingClientRect(); if (r.width < 2 || r.height < 2) return false;
      for (var a = node; a && content.contains(a); a = a.parentElement) {
        var cs = getComputedStyle(a);
        if (cs.display === 'none' || cs.visibility === 'hidden' || cs.visibility === 'collapse' || cs.contentVisibility === 'hidden') return false;
        if (a.tagName === 'DETAILS' && !a.open) { var summary = a.querySelector('summary'); if (!summary || !summary.contains(node)) return false; }
        if (a !== node && /^(hidden|clip)$/.test(cs.overflowY)) { var ar = a.getBoundingClientRect(); if (a.clientHeight < 2 || r.top >= ar.bottom || r.bottom <= ar.top) return false; }
      }
      return true;
    }
    function readingStart() {
      // The strip is inserted inside content. A page-title destination belongs
      // to the first original reading block, not that navigation's own parent.
      // Keep the actual node and address, including on fixed-title search pages.
      return [].slice.call(content.children).filter(visible)[0] || content;
    }
    function discover() {
      // Search cards can share a row; they are parallel results, not sequential
      // page sections. The WordPress query supplies their fixed page identity.
      if (config && config.mode === 'fixed') return [{ label: fallback, target: readingStart(), href: null }];
      var list = row ? rowSections(row).map(function (s) { return { label: s.label, target: byHref(s.href), href: s.href }; }) : [].slice.call(content.querySelectorAll('h2')).map(function (h) { return { label: h.innerText.replace(/\s+/g, ' ').trim(), target: h, href: h.id ? '#' + encodeURIComponent(h.id) : null }; });
      // An explicit jump row may intentionally address a closed disclosure;
      // discovered headings belong to the rail only while they are visible.
      list = list.filter(function (s) { return s.label && (keepRowTargets && row ? content.contains(s.target) : visible(s.target)); });
      return list.length ? list : [{ label: fallback, target: readingStart(), href: null }];
    }
    var cached;
    function collect() { return cached || (cached = discover()); }
    collect.watch = function (redraw) {
      function invalidate() { cached = null; redraw(); }
      window.addEventListener('resize', invalidate);
      content.addEventListener('toggle', invalidate, true);
      if ('ResizeObserver' in window) new ResizeObserver(invalidate).observe(content);
      if ('MutationObserver' in window) new MutationObserver(function (changes) {
        if (changes.some(function (change) {
          var node = change.target.nodeType === 1 ? change.target : change.target.parentElement;
          return node && !node.closest('.ts-pk');
        })) invalidate();
      }).observe(content, { subtree: true, childList: true, characterData: true, attributes: true,
        attributeFilter: ['class', 'style', 'hidden', 'inert', 'aria-hidden', 'open', 'id', 'href', 'role'] });
    };
    return collect;
  }
  function titleOnly(content, row) {
    var collect = sectionSource(content, row, false), sections = collect(), title = titleStrip(content, sections, function (i) { return sections[i] && sections[i].target; }, row, true), measurePractice = practiceNavigation(content, title), queued = false;
    function drawTitle() {
      queued = false;
      var next = collect();
      if (next !== sections && (next.length !== sections.length || next.some(function (s, i) { return s.target !== sections[i].target || s.label !== sections[i].label; }))) { sections = next; title.setSections(sections); }
      measurePractice();
      title.paint(title.read());
    }
    function askTitle() { if (!queued) { queued = true; requestAnimationFrame(drawTitle); } }
    title.watch(askTitle); window.addEventListener('scroll', askTitle, { passive: true });
    collect.watch(askTitle);
    drawTitle();
    titleNavigation(content, title, drawTitle);
  }
  var tries = 0;
  function init() {
    if (document.getElementById('ts-peek-title-strip')) return;
    var main = document.querySelector('#main,main,[role="main"]') || document.querySelector('#primary,#content') || document.body;
    var entries = main.querySelectorAll('.entry-content'), content = entries.length === 1 ? entries[0] : main, row = content.querySelector('.ts-jump');
    var root = document.documentElement, calm = window.matchMedia('(prefers-reduced-motion: reduce)'), data = null;
    var dataNode = document.getElementById('ts-peek-nav-data');
    try { data = JSON.parse(dataNode.textContent); } catch (e) {}
    var hasData = data && data.schema === 1 && Array.isArray(data.items);
    var links = row ? [].slice.call(row.querySelectorAll('a[href^="#"]')) : [];
    if (hasData && links.some(function (a) { return !byHref(a.getAttribute('href')); }) && tries++ < 10) { setTimeout(init, 100); return; }
    hasData = hasData && rowSections(row).length >= 2;
    var collect = hasData ? null : sectionSource(content, row, true), S = collect ? collect() : rowSections(row);
    // These two dedicated practice pages already have their own rail. Progress
    // mode E is handled dynamically below because its other modes have none.
    var practiceRail = content.querySelector('.tsr-lab .tsr-nav,.tsi-lab .tsi-nav');
    if (!S.length || !('PointerEvent' in window) || practiceRail || document.querySelector('.ts-pk-nav')) { titleOnly(content, row); return; }
    if (collect) content.classList.add('ts-pk-title-rail');
    function el(i) { return S[i] ? S[i].target || byHref(S[i].href) : null; }
    function nameOf(h, fallback) {   // Kiro, template review: an FAQ panel or a plain block as a target read its whole text (2,173 characters) aloud
      if (!h) return fallback;
      var q = /^H[1-6]$/.test(h.tagName) ? h : h.querySelector('h1,h2,h3,h4,h5,h6,.uagb-question,summary');
      var t = q ? q.textContent.replace(/\s+/g, ' ').trim() : ''; return t && t.length <= 140 ? t : fallback;
    }
    function buzz() { if (navigator.vibrate) { try { navigator.vibrate(6); } catch (e) {} } }

    // what each section is: its heading, its first paragraph, its length (read from the page itself)
    // Data contains wording, approval and provenance only; it cannot introduce
    // destinations. Unapproved or mismatched-page data uses whole paragraphs.
    var PAGE = (location.pathname.replace(/\/+$/, '').split('/').pop() || '').toLowerCase(), MINE = Object.create(null);
    if (hasData && data.slug === PAGE && data.approval && data.approval.status === 'approved') {
      data.items.forEach(function (item) {
        if (!Array.isArray(item) || item.length !== 2 || typeof item[0] !== 'string' || typeof item[1] !== 'string') return;
        if (!Object.prototype.hasOwnProperty.call(MINE, item[1])) MINE[item[1]] = [];
        MINE[item[1]].push(item[0]);
      });
    }
    function describe() {
      if (!hasData) { S.forEach(function (s) { s.heading = s.label; }); return; }
      var ps = [].slice.call(content.querySelectorAll('p'));
      S.forEach(function (s, i) {
        var h = el(i), nx = el(i + 1) || null; s.heading = nameOf(h, s.label); s.excerpt = ''; s.desc = ''; s.paragraph = ''; s.mins = 1;
        if (!h) return;
        var r = document.createRange(); r.setStartBefore(h); if (nx) r.setEndBefore(nx); else r.setEndAfter(content.lastChild);
        var frag = r.cloneContents(); [].forEach.call(frag.querySelectorAll('script,style,noscript'), function (x) { x.remove(); });
        var words = (frag.textContent.match(/\S+/g) || []).length; s.mins = Math.max(1, Math.round(words / 200));
        for (var k = 0; k < ps.length; k++) {
          var p = ps[k], after = h.compareDocumentPosition(p) & Node.DOCUMENT_POSITION_FOLLOWING, before = !nx || (p.compareDocumentPosition(nx) & Node.DOCUMENT_POSITION_FOLLOWING);
          var pt = p.textContent.trim();
          if (after && before && pt.length > 30 && !/\?$/.test(pt) && !p.closest('.ts-jump') && !p.closest('.ts-pk-top') && !p.closest('.ts-pk') && !p.closest('figcaption')) {
            var t = p.textContent.trim().replace(/\s+/g, ' '); s.paragraph = t; s.excerpt = t.length > 120 ? t.slice(0, 117).replace(/\s+\S*$/, '') + '…' : t;
            var safe = t.replace(/\b(Dr|Mr|Mrs|Ms|St|vs|etc|e\.g|i\.e)\./gi, function (m) { return m.replace(/\./g, '\u2024'); });   // abbreviations do not end the sentence
            var first = ((safe.match(/^.*?[.!?](\s|$)/) || [safe])[0].trim()).replace(/\u2024/g, '.'); s.desc = first.length > 90 ? first.slice(0, 87).replace(/\s+\S*$/, '') + '…' : first; break;
          }
        }
      });
      S.forEach(function (s, i) { var id = el(i).id; s.gist = Object.prototype.hasOwnProperty.call(MINE, id) ? MINE[id] : null; var sm = s.gist ? [] : summarize(i), alt = s.paragraph; s.summary = sm.length ? sm : (alt ? [alt] : []); });
      if (segs) segs.forEach(function (li, i) { li.querySelector('a').setAttribute('aria-label', S[i].label + (S[i].desc ? ': ' + S[i].desc : '')); });   // the rail's names for screen readers include the description
    }

    // a summary of a section made only of its own words: its opening paragraphs, whole and in order (the copy is regulated, so nothing is reworded or
    // cut mid-sentence; Kiro, sixth review: splitting into sentences broke at "1.5", "U.S." and closing quotes). It stops at a paragraph boundary once
    // there is about 320 characters of text, at most three paragraphs; the box scrolls if it is longer.
    function summarize(i) {
      var h = el(i), nx = el(i + 1), out = [], n = 0; if (!h) return out;
      [].forEach.call(content.querySelectorAll('p, li'), function (p) {
        if (n >= 320 || out.length >= 3) return;
        if (!(h.compareDocumentPosition(p) & Node.DOCUMENT_POSITION_FOLLOWING) || (nx && !(p.compareDocumentPosition(nx) & Node.DOCUMENT_POSITION_FOLLOWING))) return;
        if (p.closest('.ts-pk') || p.closest('.ts-jump') || p.closest('nav') || p.closest('figcaption') || p.querySelector('p, li')) return;
        var t = p.textContent.trim().replace(/\s+/g, ' '); if (t.length < 25 || /\?$/.test(t)) return;   // no headings-in-disguise, no questions
        out.push(t); n += t.length;
      });
      return out;
    }

    // the parts: top strip (display only), rail with its index icon, card
    var segs = null;
    function readingTarget(i) {
      var target = el(i), reading = target;
      if (!hasData) for (var node = target; node && content.contains(node); node = node.parentElement) {
        if (node.tagName === 'DETAILS' && !node.open) reading = node.querySelector(':scope > summary') || node;
        if (node.classList.contains('uagb-faq-item') && !node.classList.contains('uagb-faq-item-active')) reading = node.querySelector('.uagb-faq-questions-button') || node;
      }
      return reading;
    }
    var title = titleStrip(content, S, readingTarget, row, !hasData), strip = title.strip;
    var measurePractice = hasData ? function () {} : practiceNavigation(content, title), plainNavigation = null;
    function railHTML() {
      return S.map(function (s) { var tag = s.href ? 'a' : 'button'; return '<li><' + tag + (s.href ? ' href="' + attr(s.href) + '" draggable="false"' : ' type="button"') + ' data-ts-pk-section aria-label="' + attr(s.label) + '"><i></i></' + tag + '></li>'; }).join('');
    }
    function fanHTML() { return S.map(function (s, i) { return '<li><button type="button" class="ts-pk-topic" disabled><b aria-hidden="true">' + (i + 1) + '</b><span>' + esc(s.label) + '</span></button></li>'; }).join(''); }
    var box = document.createElement('div'); box.className = 'ts-pk' + (hasData ? '' : ' ts-pk-titles') + (S.length === 1 ? ' is-single' : '');
    box.innerHTML = '<nav class="ts-pk-nav" data-ts-pk-ui="quiet-v1" aria-label="Sections on this page"><button type="button" class="ts-pk-ix" aria-expanded="false" aria-controls="ts-pk-fan" aria-label="Show all sections (press and hold)"><svg viewBox="0 0 24 24" aria-hidden="true"><path class="ts-pk-ix-list" d="M6 7h12M6 12h9M6 17h6"/><path class="ts-pk-ix-close" d="M7 7l10 10M17 7L7 17"/></svg></button>' +
      '<ol class="ts-pk-fan" id="ts-pk-fan" aria-label="All sections" aria-hidden="true">' + fanHTML() + '</ol><ol class="ts-pk-rail">' + railHTML() + '</ol><span class="ts-pk-you" aria-hidden="true"></span></nav>' +
      '<div class="ts-pk-card" role="group" aria-label="Section preview" tabindex="-1"></div>' +
      '<div class="ts-pk-tip" role="note" hidden><p>Slide your thumb along this bar to preview each section. Lift, then tap the preview to go there. Press the icon above it to see them all, and slide onto a name to open it.</p><div class="row"><button type="button" class="ok">Got it</button><button type="button" class="flip"></button></div></div>';
    var host = strip.parentNode, okHost = true;
    for (var an = host; an && an !== document.documentElement; an = an.parentElement) { var cs = getComputedStyle(an); if (cs.transform !== 'none' || cs.filter !== 'none' || cs.perspective !== 'none' || (cs.backdropFilter || cs.webkitBackdropFilter || 'none') !== 'none' || /transform|filter|perspective/.test(cs.willChange || '') || (cs.containerType && cs.containerType !== 'normal') || /paint|layout|strict|content/.test(cs.contain || '')) { okHost = false; break; } }   // any of these would make position:fixed relative to that ancestor
    if (okHost) host.insertBefore(box, strip.nextSibling); else document.body.appendChild(box);   // keyboard order: row, strip, rail, then the sections
    var top = strip, nav = box.querySelector('.ts-pk-nav'), rail = nav.querySelector('.ts-pk-rail'),
      you = nav.querySelector('.ts-pk-you'), card = box.querySelector('.ts-pk-card');
    segs = [].slice.call(rail.children);
    var tip = box.querySelector('.ts-pk-tip'), ixBtn = nav.querySelector('.ts-pk-ix'), fan = nav.querySelector('.ts-pk-fan'), fanItems = [].slice.call(fan.children);
    var head = title.head; title.watch(ask);
    // the text column stops 6px before the rail and its icon, whatever margin the theme gives the content at this width (measured, not guessed);
    // the theme's own padding stays whenever it is already enough
    function clearRail() {
      var left = root.classList.contains('ts-pk-left'), need = 0, pageX = window.scrollX, pageY = window.scrollY;
      root.classList.remove('ts-pk-pad');   // start from the theme's own spacing (no paint happens in between)
      if (box.hidden) return;
      // measure where the text really ends on the rail's side, and give the text blocks only what is missing (the column itself is never padded)
      for (var round = 0; round < 3; round++) {
        var edge = left ? Infinity : -Infinity;
        [].forEach.call(content.querySelectorAll((collect ? 'form,' : '') + '.uagb-faq-item,p,li,h1,h2,h3,h4,h5,h6,blockquote,figcaption,dt,dd,td,th'), function (el) {
          if (el.closest('.ts-pk,.ts-jump,nav,[role="navigation"],[role="tablist"],.tsx-picker,.tsp-picker,.tsr-picker,.tsi-picker') || el.parentElement.closest('.uagb-faq-item') || (collect && el.parentElement.closest('form'))) return;
          var r = el.getBoundingClientRect(); if (!r.width) return;
          var cs = getComputedStyle(el), boxed = el.classList.contains('uagb-faq-item'), e2 = left ? r.left + (boxed ? 0 : parseFloat(cs.paddingLeft) || 0) : r.right - (boxed ? 0 : parseFloat(cs.paddingRight) || 0);
          edge = left ? Math.min(edge, e2) : Math.max(edge, e2);
        });
        if (!isFinite(edge)) break;
        var nr = nav.getBoundingClientRect(), over = left ? (nr.right + 6) - edge : edge - (nr.left - 6);
        if (!(over > 0.5) || need >= 80) break;
        need = Math.min(80, need + Math.ceil(over)); root.style.setProperty('--pk-clear', need + 'px'); root.classList.add('ts-pk-pad');   // capped
      }
      // Measuring without padding can briefly shorten a near-bottom page and
      // clamp its scroll position, even though final text wrapping is unchanged.
      // Restore the title-rail reader's position after the final layout flush.
      if (collect) { void content.offsetHeight; if (window.scrollX !== pageX || window.scrollY !== pageY) window.scrollTo({ left: pageX, top: pageY, behavior: 'instant' }); }
    }
    // at most once per frame, and on resize only when the width really changed (phone toolbars resize the page while scrolling)
    var clearQ = 0, lastW = -1;
    function scheduleClear(force) { if (!force && document.documentElement.clientWidth === lastW) return; lastW = document.documentElement.clientWidth; if (clearQ) return; clearQ = requestAnimationFrame(function () { clearQ = 0; clearRail(); }); }
    root.classList.toggle('ts-pk-left', store('ts-pk-side') === 'left');   // the saved side first, so the first measurement is for the right side (Kiro, third review)
    lastW = document.documentElement.clientWidth; clearRail(); window.addEventListener('resize', function () { scheduleClear(false); });

    // left-hand option (remembered on this device)
    function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } return null; }
    function sideName() { return root.classList.contains('ts-pk-left') ? 'right' : 'left'; }   // the side a flip would move to
    function setSide(left) { root.classList.toggle('ts-pk-left', left); store('ts-pk-side', left ? 'left' : 'right'); clearRail(); tip.querySelector('.flip').textContent = 'Use it on the ' + sideName(); lengths(); ask(); }
    tip.querySelector('.flip').textContent = 'Use it on the ' + sideName();

    // Sticky height alone misses the still-visible header and booking notice.
    // Keep the whole index/rail below their actual visible boundary. Reclaim
    // empty bottom space, then only rail gaps/padding, before deferring an idle
    // rail that physically cannot fit. Touch targets keep their original size.
    var bookingBand = document.querySelector('.ts-corp-band'), actionBar = document.querySelector('.ts-wwm__sticky,.ts-corp__sticky');
    function layoutRail() {
      // Captured navigation keeps the geometry under the current finger until
      // release. The summary card owns its separate fitting and reading state.
      if (g || ixPress !== null || fdn) return;
      var viewport = viewportBounds(), ceiling = Math.max(viewport.top, (parseFloat(getComputedStyle(strip).top) || 0) + strip.offsetHeight);
      [head, bookingBand, strip].forEach(function (node) {
        if (!node || !node.getClientRects().length || getComputedStyle(node).visibility === 'hidden') return;
        var r = node.getBoundingClientRect();
        if (r.top < viewport.bottom && r.bottom > viewport.top) ceiling = Math.max(ceiling, r.bottom);
      });
      var inset = parseFloat(getComputedStyle(nav).getPropertyValue('--pk-safe-bottom')) || 0;
      var limit = viewport.bottom - Math.max(12, inset + 8), first = ceiling + 64;
      // Existing fixed contact actions own the lower screen edge. Only
      // reclaim genuinely empty space; their buttons must remain unobstructed.
      if (actionBar && actionBar.getClientRects().length) {
        var actionStyle = getComputedStyle(actionBar), actionRect = actionBar.getBoundingClientRect();
        if (actionStyle.position === 'fixed' && actionStyle.visibility !== 'hidden' && actionRect.top < viewport.bottom && actionRect.bottom > viewport.top) limit = Math.min(limit, actionRect.top - 8);
      }
      var targets = segs.reduce(function (height, li) { return height + Math.max(22, parseFloat(getComputedStyle(li).minHeight) || 0); }, 0);
      var plan = null;
      [{ gap: 5, pad: 8, compact: false }, { gap: 2, pad: 4, compact: true }].some(function (space) {
        var minimum = targets + Math.max(0, segs.length - 1) * space.gap + 2 * space.pad;
        var start = Math.max(first, viewport.top + viewport.height * .3);
        if (start + minimum > limit) start = first;
        if (start + minimum > limit) return false;
        plan = { top: start, bottom: Math.min(limit, Math.max(start + minimum, viewport.bottom - Math.max(viewport.height * .14, 76))), compact: space.compact };
        return true;
      });
      // A focused or kept-open index remains owned by its current interaction.
      // Defer hiding it until blur/close, without moving focus or cancelling it.
      if (!plan && (nav.contains(document.activeElement) || tip.contains(document.activeElement) || ixOpen)) return;
      nav.classList.toggle('is-waiting', !plan); nav.inert = !plan;
      if (!plan) { nav.setAttribute('aria-hidden', 'true'); hideTip(); return; }
      nav.removeAttribute('aria-hidden'); nav.classList.toggle('is-compact', plan.compact);
      function style(key, value) { if (nav.style[key] !== value) nav.style[key] = value; }
      style('top', plan.top + 'px');
      if (S.length === 1) {
        style('bottom', 'auto'); style('height', Math.min(120, viewport.height * .24, limit - plan.top) + 'px');
      } else {
        style('height', ''); style('bottom', 'calc(100% - ' + plan.bottom + 'px)');
      }
    }
    nav.addEventListener('focusout', ask);
    tip.addEventListener('focusout', ask);
    nav.addEventListener('pointerup', ask);
    nav.addEventListener('pointercancel', ask);
    if (bookingBand && 'ResizeObserver' in window) new ResizeObserver(ask).observe(bookingBand);
    if (actionBar && 'ResizeObserver' in window) new ResizeObserver(ask).observe(actionBar);

    // the rail is one keyboard stop: Tab reaches the current section's piece, arrow keys move between pieces
    function rove(i) { segs.forEach(function (li, k) { li.firstChild.setAttribute('tabindex', k === i ? '0' : '-1'); }); }
    rove(0);
    rail.addEventListener('keydown', function (e) {
      var li = e.target.closest('li'), i = segs.indexOf(li), n = -1;
      if (e.key === 'ArrowDown' || e.key === 'ArrowRight') n = Math.min(segs.length - 1, i + 1); else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') n = Math.max(0, i - 1); else if (e.key === 'Home') n = 0; else if (e.key === 'End') n = segs.length - 1;
      if (n < 0) return; e.preventDefault(); rove(n); segs[n].firstChild.focus();
    });

    // where the reader is
    var cur = -1;
    function line() { return title.line(); }
    function bounds(i) { return title.bounds(i); }
    function part(i) { var b = bounds(i); return Math.max(0, Math.min(1, (line() - b[0]) / Math.max(1, b[1] - b[0]))); }
    function fill(i) { return i < cur ? 1 : (i === cur ? part(i) : 0); }
    function whole() { var a = bounds(0)[0], b = bounds(S.length - 1)[1]; return Math.max(0, Math.min(1, (line() - a) / Math.max(1, b - a))); }
    function lengths() { var hs = S.map(function (s, i) { var b = bounds(i); return Math.max(1, b[1] - b[0]); }), avg = hs.reduce(function (x, y) { return x + y; }, 0) / S.length; S.forEach(function (s, i) { s.len = hs[i] / avg; });
      if (segs) { segs.forEach(function (li, k) { li.style.setProperty('--len', S[k].len.toFixed(2)); }); } }

    // a jump: the heading lands just below the strip (also under the admin bar)
    var glow = { el: null, t: 0 };
    function below() { return (parseFloat(getComputedStyle(top).top) || 0) + top.offsetHeight + 12; }   // where the stuck strip ends
    function openDetails(h) {
      // Native details may itself be the target, or contain a target several
      // levels down. Open outer and inner panels before measuring the jump.
      for (var d = h; d && content.contains(d); d = d.parentElement) {
        if (d.tagName === 'DETAILS') d.open = true;
      }
    }
    function jumpTo(i) {   // Ryan, 2026-11-08: no "Back to ..." button
      var h = el(i); if (!h || !h.isConnected) return;
      if (plainNavigation) { plainNavigation.go(h, S[i].href); return; }
      title.closeMenu();   // close before the pointerup/keydown jump measures its destination
      openDetails(h);
      // a closed FAQ panel as the target opens, as the old row's own script did (after the swallowed click of the tap, so it is not swallowed itself)
      var fq = h.classList.contains('uagb-faq-item') ? h : h.closest('.uagb-faq-item');
      if (fq && !fq.classList.contains('uagb-faq-item-active')) { var qb = fq.querySelector('.uagb-faq-questions-button'); if (qb) setTimeout(function () { qb.click(); }, 600); }
      window.scrollTo({ top: h.getBoundingClientRect().top + window.scrollY - below(), behavior: calm.matches ? 'auto' : 'smooth' });
      if (glow.el) { glow.el.classList.remove('ts-pk-flash'); clearTimeout(glow.t); }
      glow.el = h; h.classList.remove('ts-pk-unflash'); h.classList.add('ts-pk-flash'); glow.t = setTimeout(function () { h.classList.add('ts-pk-unflash'); h.classList.remove('ts-pk-flash'); glow.el = null; }, 1200);
      if (!h.hasAttribute('tabindex')) h.setAttribute('tabindex', '-1');
      h.focus({ preventScroll: true });
      try { history.replaceState(null, '', S[i].href); } catch (e) {}   // no history entry per jump (the browser's Back would only change the address); the in-page Back returns
    }
    // the row at the top of the page uses the same jump (otherwise the theme's own smooth scroll hides the heading under the strip)
    if (hasData && row) row.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[href^="#"]') : null; if (!a || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var i = S.findIndex(function (s) { return s.href === a.getAttribute('href'); }); if (i < 0 || !el(i)) return; e.preventDefault(); e.stopPropagation(); jumpTo(i);
    }, true);

    // any other in-page link in the content (a "see the questions below" link): the theme's own smooth scroll would hide the heading under the pinned header and strip
    if (hasData) content.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[href^="#"]') : null;
      if (!a || a.closest('.ts-pk') || a.closest('.ts-jump') || e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var id = a.getAttribute('href').slice(1); try { id = decodeURIComponent(id); } catch (err) {}
      var t = id ? document.getElementById(id) : null; if (!t || !content.contains(t)) return;
      openDetails(t);
      e.preventDefault(); e.stopPropagation();
      window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - below(), behavior: calm.matches ? 'auto' : 'smooth' });
      try { history.replaceState(null, '', '#' + id); } catch (err) {}
    }, true);

    // the peek card
    function rowHTML(k, near) { return '<button type="button" class="row' + (near ? ' near' : '') + '" data-k="' + k + '" aria-label="Section ' + (k + 1) + ': ' + attr(S[k].heading) + ' (show its preview)"><b>' + (k + 1) + '</b><span>' + esc(S[k].heading) + '</span></button>'; }
    function cardHTML(i, pinned) {
      var above = '', below = '';
      if (hasData) {
        if (i > 0) above = rowHTML(i - 1, true);
        if (i + 1 < S.length) below = rowHTML(i + 1, true);
      } else {
        for (var k = 0; k < S.length; k++) { if (k < i) above += rowHTML(k, k === i - 1); else if (k > i) below += rowHTML(k, k === i + 1); }
      }
      var upN = hasData ? 0 : Math.max(0, i - 1), downN = hasData ? 0 : Math.max(0, S.length - i - 2);
      return (upN ? '<span class="more" aria-hidden="true">+' + upN + ' more above</span>' : '') + above + '<div class="open" tabindex="-1" aria-label="Section ' + (i + 1) + ' preview: ' + attr(S[i].label) + '">' + openHTML(i, pinned) + '</div>' + below + (downN ? '<span class="more" aria-hidden="true">+' + downN + ' more below</span>' : '');
    }
    function openHTML(i, pinned) {
      var s = S[i];   // no "You are in ...": the strip at the top already says where you are (Ryan, 2026-11-08)
      return '<button type="button" class="x" aria-label="Close the preview">×</button><small>Section ' + (i + 1) + ' of ' + S.length + (hasData ? ' · ' + s.mins + ' min' : '') + '</small><b>' + esc(s.label) + '</b>' +
        (!hasData ? '' : s.gist ? '<div class="sum is-gist" tabindex="0" role="region" aria-label="Summary of ' + attr(s.label) + '"><ul>' + s.gist.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('') + '</ul></div>'
         : s.summary.length ? '<div class="sum" tabindex="0" role="region" aria-label="Summary of ' + attr(s.label) + '">' + s.summary.map(function (t) { return '<p>' + esc(t) + '</p>'; }).join('') + '</div>' : '') +
        '<span class="hint"><span>' + (pinned ? 'Tap to go there' : 'Lift to keep this preview') + '</span>' + (pinned ? '<button type="button" class="side" aria-label="Move the section bar to the ' + sideName() + ' side">' + (sideName() === 'left' ? 'Left' : 'Right') + '</button>' : '') + '</span>';
    }
    function hover(i) { segs.forEach(function (li, k) { li.classList.toggle('is-hover', k === i); }); }
    function cardSpaces() {
      var r = top.getBoundingClientRect(), stickyTop = parseFloat(getComputedStyle(top).top) || 0, viewport = viewportBounds();
      var visible = r.top < viewport.bottom && r.bottom > viewport.top, hr = head && head.getBoundingClientRect();
      var headBottom = Math.max(viewport.top, stickyTop, hr && hr.top < viewport.bottom && hr.bottom > viewport.top ? hr.bottom : 0);
      // At page top the availability banner still separates the strip from its
      // sticky position. Clear the visible strip, including the card's focus ring.
      // A strip entirely outside the viewport must not consume preview space.
      var lower = { top: Math.max(headBottom, stickyTop + top.offsetHeight, visible ? r.bottom : 0) + 8, bottom: viewport.bottom - 12 };
      var upper = visible && r.top - 8 > headBottom + 8 ? { top: headBottom + 8, bottom: r.top - 8 } : null;
      var expanded = { top: headBottom + 8, bottom: viewport.bottom - 12 };
      return { lower: lower, upper: upper, expanded: expanded, key: [lower.top, lower.bottom, upper ? upper.top : 0, upper ? upper.bottom : 0, viewport.left, viewport.width].join('|') };
    }
    function moreHint() {
      var m = card.querySelector('.sum');
      // Set pan permission after fitting, before the next gesture. Containment
      // alone cannot reserve a swipe for a box with no internal scroll range.
      card.classList.toggle('can-scroll', card.scrollHeight - card.clientHeight > 1);
      if (m) {
        m.classList.toggle('can-scroll', m.scrollHeight - m.clientHeight > 1);
        m.classList.toggle('has-more', m.scrollHeight - m.scrollTop - m.clientHeight > 2);
      }
    }
    var fitted = null, reading = null;
    function rememberReading(preserveIntent) {
      var m = card.querySelector('.sum'), saved = preserveIntent && reading && reading.node === m ? reading : null;
      // A smaller scroll range may clamp the displayed offset without changing
      // where the reader chose to be. Keep that intent separate from the offset
      // applied by fitting, including when all the text temporarily fits.
      reading = m ? { node: m, top: saved ? saved.top : m.scrollTop, atEnd: saved ? saved.atEnd : m.scrollTop > 0 && m.scrollHeight - m.scrollTop - m.clientHeight <= 2, rendered: m.scrollTop, width: m.clientWidth, height: m.clientHeight, total: m.scrollHeight } : null;
    }
    // Firefox can round scrollHeight one pixel differently after positioning the
    // same fractional-height content; its viewport and client dimensions agree.
    function sameReadingSize(m) { return m && reading && reading.node === m && reading.width === m.clientWidth && reading.height === m.clientHeight && Math.abs(reading.total - m.scrollHeight) <= 1; }
    function fit() {   // use the same visible boundary for fitting and placement
      card.style.maxHeight = '';
      var spaces = cardSpaces(), bounds = spaces.lower, mh = parseFloat(getComputedStyle(card).maxHeight), summary = card.querySelector('.sum');
      function fitIn(space) {
        card.classList.remove('fit1', 'fit2', 'fit3', 'fit4', 'fit5');
        if (summary) summary.style.removeProperty('max-height');
        var cap = Math.max(1, space.bottom - space.top);
        if (mh > 0) cap = Math.min(cap, mh);
        // Keep the existing scrollable-card fallback inside the selected space.
        card.style.maxHeight = cap + 'px';
        var avail = Math.max(0, cap - (card.offsetHeight - card.clientHeight));
        // Summary cards contain only their immediate neighbours. Compact the
        // controls and summary within the space without removing those links;
        // title-only cards retain their existing folding sequence.
        ['fit1', 'fit2', 'fit3', 'fit4', 'fit5'].some(function (c) { if (card.scrollHeight <= avail + 0.5) return true; card.classList.add(c); return false; });
        if (summary && (card.classList.contains('fit5') || (summary.scrollHeight > summary.clientHeight + 1 && summary.clientHeight < 120))) {
          // Use the space actually left after the neighboring links, compact
          // title and jump controls. The summary keeps its own native scroller.
          card.classList.add('fit5');
          var chrome = card.scrollHeight - summary.clientHeight;
          summary.style.maxHeight = Math.max(1, avail - chrome) + 'px';
        }
        return card.scrollHeight <= avail + 0.5;
      }
      // A short landscape viewport may leave less room below the unpinned strip
      // than above it. Use that larger free interval only after compact fitting
      // below fails, without scrolling the page or changing the selected section.
      if (!fitIn(bounds) && spaces.upper && spaces.upper.bottom - spaces.upper.top > bounds.bottom - bounds.top) {
        bounds = spaces.upper; fitIn(bounds);
      }
      // On short screens an open reading card may cover the strip, but never
      // the visible site header. The page and strip return unchanged on close.
      if (summary && viewportBounds().height <= 500 && summary.scrollHeight > summary.clientHeight + 1 && summary.clientHeight < 120 && spaces.expanded.bottom - spaces.expanded.top > bounds.bottom - bounds.top) {
        bounds = spaces.expanded; fitIn(bounds);
      }
      var o = card.querySelector('.open'); if (o && card.scrollHeight > card.clientHeight + 1) card.scrollTop = Math.max(0, o.offsetTop - 4);   // never hidden
      var viewport = viewportBounds();
      fitted = { bounds: bounds, key: spaces.key, width: viewport.width, height: viewport.height };
    }
    function clampCard(y) {
      var bounds = fitted.bounds;
      card.style.top = Math.max(bounds.top, Math.min(bounds.bottom - card.offsetHeight, y)) + 'px';
    }
    function place(y) {   // the open box beside the thumb, within the chosen free interval
      refitCard(false);
      var h = card.offsetHeight, o = card.querySelector('.open'), mid = o ? o.offsetTop + o.offsetHeight / 2 : h / 2;
      clampCard(y - mid);
    }
    function refitCard(force) {
      if (!card.classList.contains('is-on')) return;
      var spaces = cardSpaces(), sum = card.querySelector('.sum'), viewport = viewportBounds();
      if (!force && fitted && spaces.key === fitted.key && fitted.width === viewport.width && fitted.height === viewport.height && (!sum || sameReadingSize(sum))) return;
      var y = parseFloat(card.style.top), scroll = card.scrollTop;
      // Responsive styles have already reflowed by the time resize fires. Keep
      // the reading intent recorded before that reflow, especially an endpoint.
      var saved = reading && reading.node === sum ? reading : null;
      var sumScroll = saved ? saved.top : (sum ? sum.scrollTop : 0), atEnd = saved ? saved.atEnd : sum && sumScroll > 0 && sum.scrollHeight - sumScroll - sum.clientHeight <= 2;
      if (!isFinite(y)) y = card.getBoundingClientRect().top;
      fit();
      if (card.scrollTop !== scroll) card.scrollTop = scroll;
      if (sum) {
        var restored = Math.min(Math.max(0, sum.scrollHeight - sum.clientHeight), atEnd ? sum.scrollHeight : sumScroll);
        if (sum.scrollTop !== restored) sum.scrollTop = restored;
      }
      // Clamp the existing position, rather than recentering or rebuilding the
      // card: its chosen section, focus and reading position stay with the reader.
      clampCard(y);
      var focused = document.activeElement;
      if (focused && focused !== card && card.contains(focused)) {
        var fr = focused.getBoundingClientRect(), cr = card.getBoundingClientRect(), edge = cr.top + card.clientTop;
        // Rotation can clip a still-focused summary inside the outer scroller.
        // Reveal it there only; never scroll the page or reset its own scrollbar.
        if (fr.width > 0 && fr.height > 0) {
          if (fr.height > card.clientHeight || fr.top < edge) card.scrollTop += fr.top - edge;
          else if (fr.bottom > edge + card.clientHeight) card.scrollTop += fr.bottom - edge - card.clientHeight;
        }
      }
      moreHint(); rememberReading(true);
    }
    var shown = '', clear = 0;
    function hideCard() { card.className = 'ts-pk-card'; shown = ''; reading = null; hover(-1); nav.classList.remove('is-active'); clearTimeout(clear); clear = setTimeout(function () { if (!shown) card.innerHTML = ''; }, 220); }   // empty it after the fade
    function show(i, y, pinned) {
      var key = i + '|' + (pinned ? 1 : 0) + '|' + cur;
      if (key !== shown) { clearTimeout(clear); card.className = 'ts-pk-card ts-pk-stack is-on' + (pinned ? ' is-pinned' : ''); card.innerHTML = cardHTML(i, pinned); card.dataset.i = i; nav.classList.add('is-active'); hover(i); shown = key; fit(); moreHint(); rememberReading(); }
      place(y);
    }
    // "this tap is already handled": swallow the click the browser may still send (to the card, or to whatever is under it once the card is gone)
    function swallowNextClick(lifted, keepTouchFocus) {
      var on = true, t = 0;
      function sw(c) { if (!c.isTrusted && ((revealingQuestion && c.target === revealingQuestion) || (closingHeaderToggle && c.target === closingHeaderToggle))) return; c.preventDefault(); c.stopPropagation(); off(); }
      // A navigation card can disappear on pointerup before the compatibility
      // mousedown. Its default action would blur the destination just focused
      // by the jump. Reserve that same touch's mouse events; a fresh pointer
      // press is a new interaction and disarms this additional reservation.
      function mouse(c) { c.preventDefault(); c.stopPropagation(); }
      function nextPointer() { off(); }
      function up() { clearTimeout(t); t = setTimeout(off, 450); }   // timed from the finger lifting, however long the press
      function off() { if (!on) return; on = false; clearTimeout(t); document.removeEventListener('click', sw, true); document.removeEventListener('pointerup', up, true); document.removeEventListener('pointercancel', off, true); document.removeEventListener('mousedown', mouse, true); document.removeEventListener('mouseup', mouse, true); document.removeEventListener('pointerdown', nextPointer, true); }
      document.addEventListener('click', sw, true); document.addEventListener('pointercancel', off, true);   // a touch that becomes a scroll sends no click: disarm
      if (keepTouchFocus) { document.addEventListener('mousedown', mouse, true); document.addEventListener('mouseup', mouse, true); document.addEventListener('pointerdown', nextPointer, true); }
      if (lifted) up(); else document.addEventListener('pointerup', up, true);
      setTimeout(off, 5000);
    }
    function focusSeg(i) {
      if (nav.inert) {
        // Dismissal must not focus a hidden control or reveal an overlapping rail.
        // Return to the reading region without moving the page; the strip is visual only.
        var readingRegion = document.querySelector('#main,main,[role="main"]') || content;
        if (!readingRegion.hasAttribute('tabindex')) {
          readingRegion.setAttribute('tabindex', '-1');
          readingRegion.addEventListener('blur', function () { readingRegion.removeAttribute('tabindex'); }, { once: true });
        }
        readingRegion.focus({ preventScroll: true }); return;
      }
      var a = segs[i] && segs[i].firstChild; if (a) { rove(i); a.focus({ preventScroll: true }); }
    }
    // a tap on the pinned card: acted on when the finger lifts (a short, still touch), so it never depends on the browser turning the touch into a click;
    // mouse and keyboard use the click
    function activate(target) {
      if (!card.classList.contains('is-pinned')) return;
      var row = target.closest('.row');
      if (row) {   // a closed box: open it in the stack (no jump), where it was
        var k = +row.getAttribute('data-k'), rr = row.getBoundingClientRect(), f = card.contains(document.activeElement);
        show(k, rr.top + rr.height / 2, true); hover(k); if (f) { var no = card.querySelector('.open'); (no || card).focus({ preventScroll: true }); } return;   // screen readers hear the new section
      }
      if (target.closest('.sum')) return;   // reading the summary (tapping or scrolling it) never jumps
      var i = +card.dataset.i, hadFocus = card.contains(document.activeElement); hideCard();
      if (target.closest('.side')) { setSide(sideName() === 'left'); if (hadFocus) focusSeg(i); return; }
      if (target.closest('.x')) { if (hadFocus) focusSeg(i); return; }   // closing keeps keyboard users on the rail
      jumpTo(i);
    }
    var cdn = null, cardTapAt = -1e9;
    card.addEventListener('scroll', function (e) {
      var m = e.target;
      if (!m.classList || !m.classList.contains('sum')) return;
      moreHint();
      // Only a changed position within the fitted geometry replaces the reading
      // choice. A queued event from fitting's own clamp/restoration matches the
      // recorded rendered offset and must not turn an interior bookmark into End.
      var viewport = viewportBounds();
      if (fitted && fitted.width === viewport.width && fitted.height === viewport.height && sameReadingSize(m)) {
        // Firefox may briefly report a clamped offset beyond its integer scroll
        // range. Compare within that range; real one-pixel reading moves remain.
        var limit = Math.max(0, m.scrollHeight - m.clientHeight);
        if (Math.max(0, Math.min(m.scrollTop, limit)) !== Math.max(0, Math.min(reading.rendered, limit))) rememberReading();
      }
    }, true);
    card.addEventListener('pointerdown', function (e) {
      if (e.pointerType === 'mouse' || !e.isPrimary) return;
      cdn = card.classList.contains('is-pinned') ? { id: e.pointerId, x: e.clientX, y: e.clientY, target: e.target, dragged: false } : null;
      if (cdn && e.target.closest('.row,.x,.side')) { try { card.setPointerCapture(e.pointerId); } catch (err) {} }
    });
    card.addEventListener('pointermove', function (e) { if (cdn && e.pointerId === cdn.id && (Math.abs(e.clientX - cdn.x) > 10 || Math.abs(e.clientY - cdn.y) > 10)) cdn.dragged = true; });
    card.addEventListener('pointerup', function (e) {
      var d = cdn; if (!d || e.pointerId !== d.id) return; cdn = null;
      // A control owns a touch from its start. Sliding across it neither pans the
      // page nor turns the compatibility click into an accidental box activation.
      // Navigation owns its focus change; a summary tap keeps native reading focus.
      cardTapAt = performance.now(); swallowNextClick(true, !d.target.closest('.sum'));
      if (d.dragged || Math.abs(e.clientX - d.x) > 10 || Math.abs(e.clientY - d.y) > 10) return;
      activate(d.target);   // the click that follows would land on whatever is under the card once it closes
    });
    function cancelCardTouch(e) { if (cdn && e.pointerId === cdn.id) { cdn = null; if (collect) ask(); } }
    card.addEventListener('pointercancel', cancelCardTouch);
    card.addEventListener('lostpointercapture', function (e) { if (e.target === card) cancelCardTouch(e); });
    card.addEventListener('click', function (e) { if (performance.now() - cardTapAt < 700) { e.preventDefault(); return; } activate(e.target); });
    card.addEventListener('keydown', function (e) { if (e.key !== 'Enter' || !card.classList.contains('is-pinned') || e.target.closest('button')) return; e.preventDefault(); var i = +card.dataset.i; hideCard(); jumpTo(i); });   // Enter on × or the side button is that button's own click
    document.addEventListener('pointerdown', function (e) {
      if (!card.classList.contains('is-pinned') || card.contains(e.target) || rail.contains(e.target)) return;
      hideCard(); swallowNextClick(false);   // this tap only closes the preview
    }, true);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && card.classList.contains('is-on')) { var i = +card.dataset.i; hideCard(); focusSeg(i); } });

    // the rail gesture: slide to browse (the page does not move), lift to keep the preview
    var g = null;
    function pickAt(y) { var best = 0; segs.forEach(function (li, i) { if (y >= li.getBoundingClientRect().top - 3) best = i; }); return best; }
    var pressedAt = -1e9;   // when a finger or mouse last lifted off the rail; the click that may follow it is not an activation
    rail.addEventListener('contextmenu', function (e) { e.preventDefault(); });   // a long press must not open the link menu (it would cancel the preview)
    rail.addEventListener('dragstart', function (e) { e.preventDefault(); });
    function tipSeen() { if (!tipDone) { tipDone = true; store('ts-pk-tip', 'seen'); } hideTip(); }   // someone who already uses the rail or the icon needs no hint
    rail.addEventListener('pointerdown', function (e) { if (e.button > 0 || !e.isPrimary || g) return; tipSeen(); try { rail.setPointerCapture(e.pointerId); } catch (err) {} g = { id: e.pointerId, i: pickAt(e.clientY), y: e.clientY }; show(g.i, g.y, false); e.preventDefault(); });
    rail.addEventListener('pointermove', function (e) { if (!g || e.pointerId !== g.id) return; var i = pickAt(e.clientY); g.y = e.clientY; if (i !== g.i) { g.i = i; buzz(); } show(g.i, g.y, false); });
    rail.addEventListener('pointerup', function (e) { if (!g || e.pointerId !== g.id) return; var x = g; g = null; pressedAt = performance.now(); show(x.i, x.y, true); });
    function cancelPress(e) { if (!g || e.pointerId !== g.id) return; g = null; pressedAt = performance.now(); hideCard(); ask(); }
    rail.addEventListener('pointercancel', cancelPress);
    rail.addEventListener('lostpointercapture', function (e) { if (g && e.pointerId === g.id) setTimeout(function () { cancelPress(e); }, 0); });   // after pointerup g is already cleared, so only a real loss cancels
    rail.addEventListener('click', function (e) {   // a finger or mouse press is handled above; a click without one (keyboard Enter, VoiceOver/TalkBack double-tap) opens the preview and focuses it
      e.preventDefault(); e.stopPropagation();
      if (performance.now() - pressedAt < 600) { pressedAt = -1e9; return; }
      var li = e.target.closest('li'), i = segs.indexOf(li); if (i < 0) return; var r = li.getBoundingClientRect(); show(i, r.top + r.height / 2, true); card.focus();
    }, true);

    // draw on scroll, once per frame
    var ticking = false;
    var lastRove = -2, tipWait = 0;
    function refreshSections() {
      // A captured gesture owns its original target until release. Reconcile
      // afterward by DOM identity, never by an index that may now name another
      // section. The stable rail/fan containers retain their event handlers.
      if (!collect || g || ixPress !== null || cdn || fdn) return;
      var next = collect();
      if (next === S) return;
      if (next.length === S.length && next.every(function (s, i) { return s.target === el(i) && s.label === S[i].label && s.href === S[i].href; })) return;
      var active = document.activeElement, selected = card.classList.contains('is-on') ? el(+card.dataset.i) : null;
      var wasPinned = card.classList.contains('is-pinned'), wasCardFocus = card.contains(active), focusedPiece = segs.findIndex(function (li) { return li.contains(active); });
      var focusedTarget = focusedPiece >= 0 ? el(focusedPiece) : null, currentTarget = el(cur), oldIndex = Math.max(0, cur), oldY = card.getBoundingClientRect().top + card.offsetHeight / 2;
      var focusedRow = wasCardFocus && active.closest('.row'), rowTarget = focusedRow ? el(+focusedRow.dataset.k) : null, oldSelectedIndex = +card.dataset.i;
      var focusPart = active && active.closest('.x,.side'), focusClass = focusPart && (focusPart.classList.contains('x') ? '.x' : '.side');
      hideCard(); setIndex(false);
      S = next; box.classList.toggle('is-single', S.length === 1); describe(); rail.innerHTML = railHTML(); fan.innerHTML = fanHTML();
      segs = [].slice.call(rail.children); fanItems = [].slice.call(fan.children);
      title.setSections(S); lengths(); lastRove = -2;
      var selectedIndex = S.findIndex(function (s) { return s.target === selected; }), focusedIndex = S.findIndex(function (s) { return s.target === focusedTarget; });
      var currentIndex = S.findIndex(function (s) { return s.target === currentTarget; });
      rove(focusedIndex >= 0 ? focusedIndex : currentIndex >= 0 ? currentIndex : Math.min(oldIndex, S.length - 1));
      if (selectedIndex >= 0) {
        show(selectedIndex, oldY, wasPinned);
        if (wasCardFocus) {
          var rowIndex = S.findIndex(function (s) { return s.target === rowTarget; });
          var focusNext = focusClass ? card.querySelector(focusClass) : rowIndex >= 0 ? card.querySelector('.row[data-k="' + rowIndex + '"]') : null;
          if (!focusNext || !focusNext.getClientRects().length) focusNext = card.querySelector('.open') || card;
          focusNext.focus({ preventScroll: true });
        }
      } else if (wasCardFocus) focusSeg(Math.min(isFinite(oldSelectedIndex) ? oldSelectedIndex : oldIndex, S.length - 1));
      if (focusedPiece >= 0) focusSeg(focusedIndex >= 0 ? focusedIndex : Math.min(focusedPiece, S.length - 1));
      scheduleClear(true);
    }
    function practiceRailMode() {
      if (hasData) return;
      var demo = content.querySelector('.tsp-lab .tspE.is-on'), hidden = !!(demo && demo.getClientRects().length && getComputedStyle(demo).display !== 'none');
      if (hidden === box.hidden) return;
      var hadFocus = hidden && box.contains(document.activeElement);
      if (hadFocus) {
        var chooser = content.querySelector('.tsp-picker [aria-pressed="true"]') || demo.querySelector('a,button');
        if (chooser) chooser.focus({ preventScroll: true });
      }
      box.hidden = hidden; root.classList.toggle('ts-pk-ready', !hidden);
      hideCard(); setIndex(false); hideTip();
      clearRail(); title.layout(); lengths();
    }
    function draw() {
      ticking = false;
      refreshSections(); measurePractice(); practiceRailMode();
      var state = title.read(), fills = state.fills, w = state.whole; cur = state.cur;
      if (box.hidden) { title.paint(state); return; }
      layoutRail();
      var rr = rail.getBoundingClientRect(), nr = nav.getBoundingClientRect(), railStyle = getComputedStyle(rail), railTop = parseFloat(railStyle.paddingTop) || 0, railBottom = parseFloat(railStyle.paddingBottom) || 0;
      title.paint(state);
      segs.forEach(function (li, k) { li.firstChild.firstChild.style.setProperty('--p', fills[k].toFixed(3)); var a = li.firstChild; if (k === cur) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current'); });
      you.style.top = (rr.top - nr.top + railTop + w * (rr.height - railTop - railBottom)) + 'px';
      if (cur !== lastRove && cur > -1 && !rail.contains(document.activeElement)) { rove(cur); lastRove = cur; }   // only when the section changes
      if (ixOpen) fanLayout();   // the fan stays current if the page moves while it is open (keyboard toggle)
      refitCard(false);
      if (!nav.inert && !tipDone && !tipWait && cur > -1) tipWait = setTimeout(function () { tipWait = 0; if (!tipDone && !card.classList.contains('is-on') && !ixOpen && !g && ixPress === null) maybeTip(); }, 1500);   // after 1.5 s of reading, never over a preview or the fan
    }

    // the index icon: hold it and every section name fans out beside its piece of the rail; lift and they slide back (a click without a press toggles)
    var ixOpen = false, ixPress = null, ixPressedAt = -1e9, keepT = 0, kept = false;   // Ryan, 2026-11-08: held for 2 s, the fan stays open after lifting
    var fanPitch = 99;
    function fanLayout() {
      var nr = nav.getBoundingClientRect(), n = S.length, c = segs.map(function (li) { var r = li.getBoundingClientRect(); return r.top + r.height / 2; });
      fanPitch = 99; for (var j = 1; j < c.length; j++) fanPitch = Math.min(fanPitch, c[j] - c[j - 1]);
      var fh = Math.max(18, Math.min(34, Math.floor(fanPitch - 3)));   // a name is never taller than the spacing between rail pieces
      nav.style.setProperty('--pk-fan-h', fh + 'px'); nav.classList.toggle('is-tight', fh < 34);
      segs.forEach(function (li, k) {
        var r = li.getBoundingClientRect(), it = fanItems[k];
        it.style.top = (r.top + r.height / 2 - nr.top) + 'px';
        it.style.setProperty('--d-open', (k * 35) + 'ms'); it.style.setProperty('--d-close', ((n - 1 - k) * 25) + 'ms');   // out top to bottom, back bottom to top
        it.classList.toggle('is-now', k === cur); it.classList.toggle('is-done', k < cur); it.querySelector('b').textContent = k < cur ? '✓' : String(k + 1);
      });
    }
    function setIndex(on) {
      if (!on) { clearTimeout(keepT); keep(false); setOver(-1); }
      if (on === ixOpen) return; ixOpen = on;
      if (on) { hideCard(); hideTip(); fanLayout(); buzz(); }
      nav.classList.toggle('is-index', on); ixBtn.setAttribute('aria-expanded', String(on)); fan.setAttribute('aria-hidden', String(!on)); ask();
      ixBtn.setAttribute('aria-label', on ? 'Close section index' : 'Show all sections (press and hold)');
    }
    function keep(on) {
      kept = on; nav.classList.toggle('is-kept', on);
      fanItems.forEach(function (item) { item.firstChild.disabled = !on; });
    }
    ixBtn.addEventListener('contextmenu', function (e) { e.preventDefault(); });
    // Ryan, 2026-11-08: press the icon and the names fan out. Then, without lifting:
    //   slide onto a name (it turns green) and lift: that name's box opens with its summary (tap it to go there);
    //   slide straight down onto the rail: the names fold away and the rail's preview follows the thumb (the page does not move); lift to keep it;
    //   hold still for 2 s: the names stay open after lifting (tap one for its box).
    // (A page scroll from the icon was tried and dropped the same day: "Get rid of the scroll idea.")
    var handed = null, ixStart = null, lean = false, over = -1;   // the rail preview the icon press turned into: { i, y }; where the press started; whether it headed for the names
    function onLeft() { return document.documentElement.classList.contains('ts-pk-left'); }
    function inRailBand(x) { var rr = rail.getBoundingClientRect(); return onLeft() ? x <= rr.right + 10 : x >= rr.left - 10; }
    function hitFan(x, y) {   // the name whose middle is nearest the finger, within half the spacing (no overlaps, no dead gaps between names)
      var best = -1, bd = 1e9, reach = Math.max(fanPitch / 2, 22);
      for (var k = 0; k < fanItems.length; k++) { var r = fanItems[k].getBoundingClientRect(), d = Math.abs(y - (r.top + r.height / 2)); if (x >= r.left - 6 && x <= r.right + 6 && d <= reach && d < bd) { best = k; bd = d; } }
      return best;
    }
    function setOver(k) { if (k === over) return; over = k; fanItems.forEach(function (it, j) { it.classList.toggle('is-over', j === k); }); if (k > -1) buzz(); }
    ixBtn.addEventListener('pointerdown', function (e) { if (e.button > 0 || !e.isPrimary) return; tipSeen(); ixPress = e.pointerId; handed = null; ixStart = { x: e.clientX, y: e.clientY, t: performance.now(), moved: 0, open: ixOpen }; lean = false; try { ixBtn.setPointerCapture(e.pointerId); } catch (err) {} setIndex(true); keep(false); clearTimeout(keepT); keepT = setTimeout(function () { if (ixPress !== null && !handed && over < 0) { keep(true); buzz(); } }, 2000); e.preventDefault(); });
    ixBtn.addEventListener('pointermove', function (e) {
      if (ixPress === null || e.pointerId !== ixPress) return;
      var x = e.clientX, y = e.clientY, rr = rail.getBoundingClientRect();
      ixStart.moved = Math.max(ixStart.moved, Math.abs(x - ixStart.x) + Math.abs(y - ixStart.y));
      if (!handed) {
        var k = ixOpen ? hitFan(x, y) : -1; setOver(k);
        if (!inRailBand(x)) lean = true;   // left the rail sideways: this press picks a name (a slide toward a far name crosses the wide rail first)
        if (k > -1 || lean || y < rr.top + 24 || Math.abs(x - ixStart.x) > (y - ixStart.y) * 0.12) return;   // the preview takes over only on a clearly straight slide down
        clearTimeout(keepT); setOver(-1); setIndex(false); handed = { i: pickAt(y), y: y }; buzz(); show(handed.i, handed.y, false); return;
      }
      var i = pickAt(y); handed.y = y; if (i !== handed.i) { handed.i = i; buzz(); } show(handed.i, handed.y, false);
    });
    function ixRelease(e) {
      if (ixPress === null || e.pointerId !== ixPress) return;
      ixPress = null; ixPressedAt = performance.now(); clearTimeout(keepT);
      ask();
      var h = handed, up = e.type === 'pointerup'; handed = null;
      if (h && up) { show(h.i, h.y, true); return; }   // lifted on the rail: the preview stays, tap it to go there
      if (h) { hideCard(); return; }                   // cancelled while on the rail
      if (over > -1 && up) { var k = over, r = fanItems[k].getBoundingClientRect(); setIndex(false); swallowNextClick(true); show(k, r.top + r.height / 2, true); return; }   // lifted on a name: its box opens
      setOver(-1);
      if (up && !kept && ixStart && performance.now() - ixStart.t < 250 && ixStart.moved < 8) { if (ixStart.open) setIndex(false); else keep(true); return; }   // tap opens the names; tap the X closes them
      if (kept && up) return;                          // held 2 s: the names stay open (tap one, the rail, or anywhere else)
      setIndex(false);
    }
    ixBtn.addEventListener('pointerup', ixRelease); ixBtn.addEventListener('pointercancel', ixRelease); ixBtn.addEventListener('lostpointercapture', function (e) { if (ixPress !== null && e.pointerId === ixPress) setTimeout(function () { ixRelease({ pointerId: e.pointerId, type: 'pointercancel' }); }, 0); });
    ixBtn.addEventListener('click', function (e) { e.preventDefault(); if (performance.now() - ixPressedAt < 600) { ixPressedAt = -1e9; return; } setIndex(!ixOpen); if (ixOpen) keep(true); });   // opened without a press (keyboard, screen reader): kept open
    document.addEventListener('pointerdown', function (e) {
      if (!ixOpen || ixPress !== null || ixBtn.contains(e.target) || (kept && fan.contains(e.target))) return;
      setIndex(false); if (!rail.contains(e.target)) swallowNextClick(false);   // on the rail the touch carries on as a rail touch (its preview); elsewhere it only closes the fan
    }, true);
    // kept open: a tap on a name opens its box with the summary (tap that to go there)
    var fdn = null, fanTapAt = -1e9;
    function fanGo(target, focusPreview) {
      var item = target.closest && target.closest('.ts-pk-fan li');
      if (!item || !kept) return;
      var index = fanItems.indexOf(item), rect = item.getBoundingClientRect();
      setIndex(false);
      show(index, rect.top + rect.height / 2, true);
      if (focusPreview) card.focus({ preventScroll: true });
    }
    fan.addEventListener('pointerdown', function (e) { fdn = kept && e.isPrimary ? { id: e.pointerId, x: e.clientX, y: e.clientY } : null; });
    fan.addEventListener('pointerup', function (e) { var d = fdn; fdn = null; if (!d || e.pointerId !== d.id || Math.abs(e.clientX - d.x) > 10 || Math.abs(e.clientY - d.y) > 10) return; fanTapAt = performance.now(); swallowNextClick(true); fanGo(e.target); });
    fan.addEventListener('pointercancel', function () { fdn = null; });
    function releaseFanPress(e) { if (fdn && e.pointerId === fdn.id) { fdn = null; ask(); } }
    document.addEventListener('pointerup', releaseFanPress);
    document.addEventListener('pointercancel', releaseFanPress);
    fan.addEventListener('click', function (e) { e.preventDefault(); if (e.detail && performance.now() - fanTapAt < 700) return; fanGo(e.target, true); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && ixOpen) { setIndex(false); ixBtn.focus(); } });

    // first-time hint: once per visitor, when they start reading; it goes away by itself, on "Got it", or when they touch the rail
    var tipDone = store('ts-pk-tip') === 'seen' || /^#./.test(location.hash), tipT = 0;   // not for visitors who arrived at a section by link
    function maybeTip() {
      if (nav.inert) return;
      tipDone = true; store('ts-pk-tip', 'seen');
      var rr = rail.getBoundingClientRect(); tip.hidden = false; tip.style.top = Math.max(12, rr.top + rr.height / 2 - tip.offsetHeight / 2) + 'px';
      requestAnimationFrame(function () { tip.classList.add('is-on'); }); nav.classList.add('is-hinting');
      tipT = setTimeout(autoHide, 9000);
    }
    function autoHide() { if (tip.contains(document.activeElement)) { tipT = setTimeout(autoHide, 3000); return; } hideTip(); }   // never take the hint away from someone using it with the keyboard
    function hideTip() { clearTimeout(tipT); clearTimeout(tipWait); tipWait = 0; if (tip.hidden) return; var had = tip.contains(document.activeElement); tip.classList.remove('is-on'); nav.classList.remove('is-hinting'); setTimeout(function () { tip.hidden = true; }, 260); if (had) focusSeg(Math.max(cur, 0)); }
    tip.querySelector('.ok').addEventListener('click', hideTip);
    tip.querySelector('.flip').addEventListener('click', function () { setSide(sideName() === 'left'); var rr = rail.getBoundingClientRect(); tip.style.top = Math.max(12, rr.top + rr.height / 2 - tip.offsetHeight / 2) + 'px'; nav.classList.remove('is-hinting'); void nav.offsetWidth; nav.classList.add('is-hinting'); clearTimeout(tipT); tipT = setTimeout(autoHide, 6000); });
    function ask() { if (!ticking) { ticking = true; window.requestAnimationFrame(draw); } }
    window.addEventListener('scroll', ask, { passive: true });   // reading position stays in the strip and rail, without a floating label
    window.addEventListener('resize', function () { lengths(); ask(); refitCard(true); });   // rotation or zoom: keep the open card inside the current visible bounds
    var lastCW = -1;
    if ('ResizeObserver' in window) new ResizeObserver(function (en) { lengths(); ask(); var w = Math.round(en[0].contentRect.width); if (w !== lastCW) { lastCW = w; scheduleClear(true); } }).observe(content);   // re-measure the rail clearance only when the width changes (an FAQ opening changes the height every frame)   // FAQ answers or late fonts move block edges   // FAQ answers opening change the lengths
    if (collect) {
      collect.watch(ask);
      document.addEventListener('pointerup', ask); document.addEventListener('pointercancel', ask);
    }

    // arriving with #section in the address: the theme's own smooth scroll runs after load and ignores the strip, so line the heading up once the page stops moving
    // (only for 4 s, and never once the visitor touches, scrolls or types)
    function arrive() {
      var hash = location.hash, i = -1; S.forEach(function (s, k) { if (s.href === hash) i = k; }); if (i < 0) return;
      openDetails(el(i));
      var arrivalEvents = ['wheel', 'touchstart', 'pointerdown', 'keydown', 'mousedown', 'hashchange', 'popstate'];
      var timer = 0, expiry = setTimeout(stop, 4000);
      function stop() {
        clearTimeout(timer); clearTimeout(expiry);
        window.removeEventListener('scroll', idle);
        arrivalEvents.forEach(function (event) { window.removeEventListener(event, stop, true); });
      }
      function fix() {
        var h = el(i); if (!h || !h.isConnected) return;
        var top = h.getBoundingClientRect().top, want = below();
        if (Math.abs(top - want) > 6) window.scrollTo({ top: top + window.scrollY - want, behavior: 'instant' });
      }
      function idle() { clearTimeout(timer); timer = setTimeout(fix, 180); }
      arrivalEvents.forEach(function (event) { window.addEventListener(event, stop, { passive: true, capture: true }); });
      window.addEventListener('scroll', idle, { passive: true }); idle();
    }
    root.classList.add('ts-pk-ready'); clearRail(); title.layout();
    if (collect) plainNavigation = titleNavigation(content, title, function () { refreshSections(); measurePractice(); lengths(); ask(); });
    describe(); lengths(); draw();
    if (document.readyState !== 'complete') window.addEventListener('load', function () { describe(); lengths(); ask(); }, { once: true });
    if (hasData) onInitialArrival(arrive);
  }
  // after the section guide has given the Questions and Research headings their ids
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { setTimeout(init, 0); }); else setTimeout(init, 0);
})();
