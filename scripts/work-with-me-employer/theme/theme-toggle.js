(function(){
var KEY='ts-theme',root=document.documentElement;
var MOON='<svg class="ts-moon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
var SUN='<svg class="ts-sun" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>';
function isDark(){return root.getAttribute('data-ts-theme')==='dark';}
function paint(){var b=document.querySelectorAll('.ts-theme-toggle');for(var i=0;i<b.length;i++){b[i].setAttribute('aria-pressed',isDark()?'true':'false');}}
function set(dark,save){if(dark){root.setAttribute('data-ts-theme','dark');}else{root.removeAttribute('data-ts-theme');}
 if(save){try{localStorage.setItem(KEY,dark?'dark':'light');}catch(e){}}paint();}
function make(){var wrap=document.createElement('div');wrap.className='ast-builder-layout-element ast-flex ts-theme-toggle-wrap';
 var b=document.createElement('button');b.type='button';b.className='ts-theme-toggle';b.setAttribute('aria-label','Dark mode');b.setAttribute('title','Dark mode');
 b.setAttribute('aria-pressed',isDark()?'true':'false');b.innerHTML=MOON+SUN;b.addEventListener('click',function(){set(!isDark(),true);});wrap.appendChild(b);return wrap;}
var d=document.querySelector('#ast-desktop-header .site-header-primary-section-right');
if(d&&!d.querySelector('.ts-theme-toggle')){d.appendChild(make());}
var m=document.querySelector('#ast-mobile-header .site-header-primary-section-right');
if(m&&!m.querySelector('.ts-theme-toggle')){m.insertBefore(make(),m.firstChild);}
window.addEventListener('storage',function(e){if(e.key===KEY){set(e.newValue==='dark',false);}});
})();
