# Site op `corp-inspect-snip1237-2`

| | |
|---|---|
| operation | `inspect` on `staging37` |
| query | `snippet` `1237` |
| lane | ops (site-ops.yml) |
| commit | `d556c5816f52f9b7a1172d3dc6f299630d6f6d50` on `feat/2026-10-24-corporate-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40134951540 |
| started | 2026-10-24 21:36:28 UTC |

## inspect staging37 snippet 1237: PASS

```text
# staging37.thaisolitude.com is www/staging37.thaisolitude.com/public_html   (read-only inspect: snippet 1237)
ID 1237  status publish  title ThemeToggle  length 2421  sha256 bc62c1ae979c6f0229c0de4792e57c2cf089c64b9375c53b83f5a7bd25e1dbd0
----- BEGIN code -----
// Dark / light mode button in the header (Ryan 2026-10-18). Remove this snippet and the ts-theme block in Additional CSS to take it away. Default stays light; the choice is kept in localStorage "ts-theme".
add_action( 'wp_head', function () {
	$js = <<<'TSJS'
(function(){try{if(localStorage.getItem('ts-theme')==='dark'){document.documentElement.setAttribute('data-ts-theme','dark');}}catch(e){}})();
TSJS;
	echo '<script id="ts-theme-init">' . $js . '</script>' . "\n";
}, 1 );
add_action( 'astra_header_after', function () {
	$js = <<<'TSJS'
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
TSJS;
	echo '<script id="ts-theme-toggle">' . $js . '</script>' . "\n";
} );

----- END code -----
```

**RESULT: PASS** - read-only inspect done

_finished 2026-10-24 21:36:35 UTC_
