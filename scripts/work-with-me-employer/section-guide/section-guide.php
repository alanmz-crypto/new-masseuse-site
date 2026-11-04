<?php
// Native, nonmodal section guide. Existing jump links remain the source of truth.
add_action( 'wp_footer', function () {
	static $printed = false;
	if ( $printed || is_admin() || is_feed() || ! is_page( array( 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ) ) ) {
		return;
	}
	$printed = true;
	?>
<script id="ts-section-guide-runtime">
(function () {
  'use strict';
  function init() {
    if (document.getElementById('ts-section-guide-trigger') ||
        !('popover' in HTMLElement.prototype) || !('IntersectionObserver' in window) ||
        typeof HTMLElement.prototype.showPopover !== 'function' ||
        document.body.classList.contains('admin-bar') ||
        document.querySelector('.ts-corp__sticky, .ts-wwm__sticky')) return;

    var content = document.querySelector('.entry-content');
    var original = content && content.querySelector('.ts-jump');
    if (!original) return;
    var entries = [];
    original.querySelectorAll('a[href]').forEach(function (source) {
      var href = source.getAttribute('href');
      if (!href || href.charAt(0) !== '#' || href.length < 2 || !source.textContent.trim()) return;
      var target;
      try { target = document.getElementById(decodeURIComponent(href.slice(1))); } catch (_) { return; }
      if (!target || !content.contains(target) || original.contains(target)) return;
      entries.push({ source: source, href: href, target: target });
    });
    if (!entries.length) return;

    var trigger = document.createElement('button');
    trigger.id = 'ts-section-guide-trigger';
    trigger.type = 'button';
    trigger.hidden = true;
    trigger.textContent = 'On this page';
    trigger.setAttribute('popovertarget', 'ts-section-guide-popover');
    trigger.setAttribute('popovertargetaction', 'toggle');
    trigger.insertAdjacentHTML('beforeend', '<svg aria-hidden="true" focusable="false" viewBox="0 0 20 20"><path d="m5 12 5-5 5 5"/></svg>');

    var panel = document.createElement('div');
    panel.id = 'ts-section-guide-popover';
    panel.setAttribute('popover', 'auto');
    var nav = document.createElement('nav');
    nav.setAttribute('aria-label', 'On this page');
    var list = document.createElement('ul');
    entries.forEach(function (entry) {
      var item = document.createElement('li');
      var link = document.createElement('a');
      link.textContent = entry.source.textContent;
      link.setAttribute('href', entry.href);
      link.addEventListener('click', function (event) {
        // Preserve modified clicks and browser-native fragment/history behavior.
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        panel.hidePopover();
        // Default anchor navigation scrolls first. Focus then follows without a second scroll.
        requestAnimationFrame(function () {
          var target = entry.target;
          var heading = target.matches('h1,h2,h3,h4,h5,h6') ? target : target.querySelector('h1,h2,h3,h4,h5,h6');
          var destination = heading || target;
          if (!destination.isConnected) return;
          if (!destination.hasAttribute('tabindex')) {
            destination.setAttribute('tabindex', '-1');
            destination.addEventListener('blur', function cleanup() {
              if (destination.getAttribute('tabindex') === '-1') destination.removeAttribute('tabindex');
            }, { once: true });
          }
          destination.focus({ preventScroll: true });
          updateVisibility();
        });
      });
      item.appendChild(link);
      list.appendChild(item);
    });
    nav.appendChild(list);
    panel.appendChild(nav);
    document.body.appendChild(trigger);
    document.body.appendChild(panel);

    var footer = document.getElementById('colophon');
    var navAbove = original.getBoundingClientRect().bottom < 0;
    var footerVisible = footer ? footer.getBoundingClientRect().top < window.innerHeight && footer.getBoundingClientRect().bottom > 0 : false;
    function updatePanelPosition() {
      // Enlarged text may wrap the trigger. Keep the panel above its real height.
      var height = trigger.getBoundingClientRect().height;
      if (height > 0) panel.style.setProperty('--ts-guide-trigger-height', height + 'px');
    }
    function updateVisibility() {
      var focused = trigger.contains(document.activeElement) || panel.contains(document.activeElement);
      // Retain the control while it owns focus or its panel is open, even if the
      // original navigation/footer returns into view. Never hide focused controls.
      trigger.hidden = !((navAbove && !footerVisible) || focused || panel.matches(':popover-open'));
      updatePanelPosition();
    }
    var observer = new IntersectionObserver(function (changes) {
      changes.forEach(function (change) {
        if (change.target === original) navAbove = change.boundingClientRect.bottom < 0;
        if (change.target === footer) footerVisible = change.isIntersecting;
      });
      updateVisibility();
    });
    observer.observe(original);
    if (footer) observer.observe(footer);
    if ('ResizeObserver' in window) new ResizeObserver(updatePanelPosition).observe(trigger);
    window.addEventListener('resize', updatePanelPosition);
    panel.addEventListener('beforetoggle', updatePanelPosition);
    panel.addEventListener('toggle', updateVisibility);
    [trigger, panel].forEach(function (element) {
      element.addEventListener('focusin', updateVisibility);
      element.addEventListener('focusout', function () { requestAnimationFrame(updateVisibility); });
    });
    updateVisibility();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
}());
</script>
	<?php
}, 50 );
