#!/usr/bin/env python3
"""Export the approved design FROM LOCALHOST (the source of truth) into a WordPress-native release JSON.
usage: build-release.py <target-base-url> <out.json>
  target examples: https://staging14.thaisolitude.com   |   http://localhost:8092 (rehearsal)
Contents = Additional CSS (astra), ihaf_insert_header, astra footer-copyright-editor, and post_content of all 8 pages.
Nothing here is a file that gets installed on a host (no mu-plugins, no plugin/theme files)."""
import sys,json,hashlib,subprocess,os
target=sys.argv[1].rstrip('/'); out=sys.argv[2]
ROOT=os.path.abspath(os.path.join(os.path.dirname(__file__),'..','..','..'))
PHP=r'''
$s=get_option("astra-settings",[]); $pages=[];
foreach(get_posts(["post_type"=>"page","numberposts"=>-1,"post_status"=>"publish","orderby"=>"ID"]) as $p){$pages[(string)$p->ID]=["slug"=>$p->post_name,"content"=>$p->post_content];}
echo json_encode(["css"=>wp_get_custom_css("astra"),"header"=>(string)get_option("ihaf_insert_header",""),"footer"=>$s["footer-copyright-editor"]??"","pages"=>$pages,"front"=>(int)get_option("page_on_front")]);'''
raw=subprocess.run(['docker','compose','run','--rm','-T','wpcli','wp','--path=/var/www/html','--skip-plugins','--skip-themes','eval',PHP],cwd=ROOT,capture_output=True,text=True).stdout
d=json.loads(raw[raw.index('{'):])
LOCAL='http://localhost:8090'; esc=lambda u:u.replace('/','\\/')
def rw(c): return c.replace(LOCAL,target).replace(esc(LOCAL),esc(target))
for k,v in d['pages'].items():
    v['content']=rw(v['content']); assert 'localhost:8090' not in v['content'], v['slug']; v['content_sha256']=hashlib.sha256(v['content'].encode()).hexdigest(); v['accept_before_sha256']='FILL'
css=d['css']; assert '@import' not in css and 'TS RESTYLE START' in css and 'localhost' not in css
rel={'scope':'wordpress-native-sitewide','target':target,'css':css,'css_sha256':hashlib.sha256(css.encode()).hexdigest(),'header':d['header'],'header_sha256':hashlib.sha256(d['header'].encode()).hexdigest(),
 'footer':d['footer'],'footer_accept_before':'FILL','accept_before_css_sha256':'FILL','accept_before_header_sha256':'FILL','pages':d['pages'],'front_page_id':d['front'],'backup_basename':'FILL','backup_sha256':'FILL'}
json.dump(rel,open(out,'w'),indent=1,ensure_ascii=False)
print('wrote',out,'| target',target,'| pages',{k:v['slug'] for k,v in rel['pages'].items()},'| css',len(css),'bytes | header',len(d['header']),'bytes')
