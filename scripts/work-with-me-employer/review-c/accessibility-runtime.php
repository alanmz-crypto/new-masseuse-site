<?php
/**
 * Bundle C additive frontend adapter for the captured Spectra 2.20.5 FAQ and
 * Astra 4.14.1 footer/scroll control. No persisted blocks or plugin files change.
 * FAQ source binds click directly to the existing question DIV and keyup to its
 * item. Keep both original nodes; a native child button sends one bubbling click
 * while preventing Enter/Space key events from reaching the old item handler.
 */
if ( ! defined( 'ABSPATH' ) ) { return; }
add_action( 'wp_footer', static function () {
	if ( is_admin() || is_feed() ) { return; }
	?>
<script id="ts-accessibility-runtime">
(function(){
  'use strict';
  if(window.tsAccessibilityBundleC){window.tsAccessibilityBundleC();return;}
  const observed=new WeakSet();
  let sequence=0;
  const nextId=kind=>{let id;do{id='ts-faq-'+kind+'-'+(++sequence);}while(document.getElementById(id));return id;};
  const activationKey=e=>e.key==='Enter'||e.key===' ';
  function enhance(){
    document.querySelectorAll('.wp-block-uagb-faq.uagb-faq-layout-accordion').forEach(group=>{
      group.removeAttribute('role');
      group.removeAttribute('aria-multiselectable');
      group.querySelectorAll('.uagb-faq-item').forEach(item=>{
        const question=item.querySelector('.uagb-faq-questions-button');
        const answer=item.querySelector('.uagb-faq-content');
        if(!question||!answer)return;
        item.removeAttribute('role');
        item.removeAttribute('tabindex');
        item.removeAttribute('aria-selected');
        let button=question.querySelector('button.ts-faq-disclosure');
        if(!button){
          button=document.createElement('button');
          button.type='button';
          button.className='ts-faq-disclosure';
          button.id=nextId('question');
          while(question.firstChild)button.appendChild(question.firstChild);
          question.appendChild(button);
          // Native activation produces a click. Spectra's old keyup handler on
          // the ancestor must not also toggle the same disclosure a second time.
          ['keydown','keypress','keyup'].forEach(type=>button.addEventListener(type,e=>{if(activationKey(e))e.stopPropagation();}));
        }
        if(!answer.id)answer.id=nextId('answer');
        button.setAttribute('aria-controls',answer.id);
        answer.setAttribute('role','region');
        answer.setAttribute('aria-labelledby',button.id);
        question.removeAttribute('role');
        question.removeAttribute('tabindex');
        question.removeAttribute('aria-selected');
        answer.removeAttribute('aria-selected');
        item.querySelectorAll('.uagb-faq-icon-wrap').forEach(icon=>{
          icon.setAttribute('aria-hidden','true');
          icon.querySelectorAll('svg').forEach(svg=>{svg.setAttribute('aria-hidden','true');svg.setAttribute('focusable','false');});
        });
        const sync=()=>{
          const expanded=item.classList.contains('uagb-faq-item-active');
          button.setAttribute('aria-expanded',String(expanded));
          answer.setAttribute('aria-hidden',String(!expanded));
          // Spectra writes this on its formerly focusable item. The native
          // button now owns the expanded state; remove the redundant attribute.
          if(item.hasAttribute('aria-expanded'))item.removeAttribute('aria-expanded');
        };
        sync();
        if(!observed.has(item)){
          new MutationObserver(sync).observe(item,{attributes:true,attributeFilter:['class','aria-expanded']});
          // Enter on a real answer link must not reach Spectra's old item
          // keyup toggle either. Leave the link's native activation intact.
          answer.addEventListener('keyup',e=>{if(activationKey(e))e.stopPropagation();});
          observed.add(item);
        }
      });
    });
    const menu=document.getElementById('footer-site-navigation');
    if(menu)menu.setAttribute('aria-label','Footer navigation');
    const widget=document.querySelector('aside[data-section="sidebar-widgets-footer-widget-1"]');
    if(widget)widget.setAttribute('aria-label','Footer information');
    document.querySelectorAll('a[target="_blank"]').forEach(link=>{
      if(/\.pdf(?:[?#]|$)/i.test(link.getAttribute('href')||'')&&link.textContent.trim().replace(/\s+/g,' ')==='View résumé (PDF)'){
        link.setAttribute('aria-label','View résumé (PDF), opens in a new tab');
      }
    });
    const scroll=document.getElementById('ast-scroll-top');
    if(scroll){
      scroll.setAttribute('role','button');
      scroll.setAttribute('aria-label','Scroll to top');
      if(!observed.has(scroll)){
        // Captured Astra handles Enter and mouse click already. Add only Space,
        // activating on release and suppressing the browser's page scroll.
        let pressed=false;
        scroll.addEventListener('keydown',e=>{if(e.key===' '){e.preventDefault();if(!e.repeat)pressed=true;}});
        scroll.addEventListener('keyup',e=>{if(e.key===' '){e.preventDefault();if(pressed){pressed=false;scroll.click();}}});
        scroll.addEventListener('blur',()=>{pressed=false;});
        observed.add(scroll);
      }
    }
  }
  window.tsAccessibilityBundleC=enhance;
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',enhance,{once:true});
  else enhance();
  // Spectra initializes its first-expanded state on window load. Running again
  // after that is safe and keeps the adapter correct in both normal/preview use.
  window.addEventListener('load',enhance,{once:true});
})();
</script>
	<?php
}, 100 );
