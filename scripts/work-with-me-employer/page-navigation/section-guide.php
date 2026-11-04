<?php
// Native, nonmodal section guide. Clone the complete server-rendered navigation.
add_action( 'wp_footer', function () {
	static $printed = false;
	if ( $printed || is_admin() || is_feed() || ! is_page( array( 'services', 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ) ) ) {
		return;
	}
	$printed = true;
	?>
<script id="ts-section-guide-runtime">
(function () {
  'use strict';
  function init() {
    if (window.tsPageNavigationInitialized || document.getElementById('ts-section-guide-trigger')) return;

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
      entries.push({ source: source, label: source.textContent, href: href, target: target });
    });
    if (!entries.length) return;

    window.tsPageNavigationInitialized = true;
    function followSection(event, entry, closePanel) {
      // Preserve modified clicks and browser-native fragment/history behavior.
      if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      if (closePanel) closePanel();
      // Default anchor navigation scrolls first. Focus follows without a second scroll.
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
        if (trigger) updateVisibility();
      });
    }
    entries.forEach(function (entry) {
      entry.source.addEventListener('click', function (event) { followSection(event, entry); });
    });
    // Inline focus enhancement also works in browsers without native popovers.
    if (!('popover' in HTMLElement.prototype) || typeof HTMLElement.prototype.showPopover !== 'function' ||
        document.body.classList.contains('admin-bar') || document.querySelector('.ts-corp__sticky, .ts-wwm__sticky')) return;

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
      link.textContent = entry.label;
      link.setAttribute('href', entry.href);
      link.addEventListener('click', function (event) {
        followSection(event, entry, function () { panel.hidePopover(); });
      });
      item.appendChild(link);
      list.appendChild(item);
    });
    nav.appendChild(list);
    panel.appendChild(nav);
    var footer = document.getElementById('colophon');
    // Keep section navigation before the footer in document/tab order. The
    // last popover link then has a real next page destination in every browser.
    var mount = footer ? footer.parentNode : document.body;
    mount.insertBefore(trigger, footer || null);
    mount.insertBefore(panel, footer || null);

    var updatePending = false;
    var lastTriggerHeight = 0;
    function updatePanelPosition() {
      // Enlarged text may wrap the trigger. Keep the panel above its real height.
      var height = trigger.getBoundingClientRect().height;
      if (height > 0 && height !== lastTriggerHeight) {
        lastTriggerHeight = height;
        panel.style.setProperty('--ts-guide-trigger-height', height + 'px');
      }
    }
    function updateVisibility() {
      var navAbove = original.getBoundingClientRect().bottom < 0;
      var footerRect = footer && footer.getBoundingClientRect();
      var footerVisible = footerRect && footerRect.top < window.innerHeight && footerRect.bottom > 0;
      var focused = trigger.contains(document.activeElement) || panel.contains(document.activeElement);
      // Retain the control while it owns focus or its panel is open, even if the
      // original navigation/footer returns into view. Never hide focused controls.
      var hidden = !((navAbove && !footerVisible) || focused || panel.matches(':popover-open'));
      if (trigger.hidden !== hidden) trigger.hidden = hidden;
      updatePanelPosition();
    }
    function scheduleUpdate() {
      if (updatePending) return;
      updatePending = true;
      requestAnimationFrame(function () {
        updatePending = false;
        updateVisibility();
      });
    }
    // A jump can move the original nav from below to above the viewport without
    // ever intersecting it. Read current geometry once per frame, not cached
    // intersection state; also refresh after text/layout changes and history restore.
    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate);
    window.addEventListener('pageshow', scheduleUpdate);
    if ('ResizeObserver' in window) {
      var sizes = new ResizeObserver(scheduleUpdate);
      [document.body, original, trigger].forEach(function (element) { sizes.observe(element); });
    }
    panel.addEventListener('beforetoggle', updatePanelPosition);
    panel.addEventListener('toggle', updateVisibility);
    [trigger, panel].forEach(function (element) {
      element.addEventListener('focusin', updateVisibility);
      element.addEventListener('focusout', scheduleUpdate);
    });
    updateVisibility();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
}());
</script>
	<?php
}, 50 );
