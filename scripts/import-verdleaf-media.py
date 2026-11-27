import requests, re, json, io, os, pathlib, urllib.parse
from bs4 import BeautifulSoup
from PIL import Image, ImageOps
ROOT=pathlib.Path('media');ROOT.mkdir(exist_ok=True)
s=requests.Session();s.headers['User-Agent']='Mozilla/5.0 (compatible; Verdleaf website migration)'
origin='https://verdleaf.com'
pages={}
for name,path in [('home','/'),('products','/products/'),('about','/about-us/')]:
 try:
  r=s.get(origin+path,timeout=30);r.raise_for_status();pages[name]=BeautifulSoup(r.text,'html.parser');print(name,r.status_code,len(r.content))
 except Exception as e:print(name,str(e))
def urls(el):
 out=[]
 for e in [el]+list(el.find_all(True)):
  for k in ['src','data-src','data-lazy-src','data-bg','data-background-image','style','data-settings','srcset','data-srcset']:
   v=e.get(k,'')
   if not isinstance(v,str):continue
   v=v.replace('\\/','/')
   for x in re.findall(r'https?://[^\s"\'<>),;]+?\.(?:webp|jpe?g|png)(?:\?[^\s"\'<>),;]*)?',v,re.I):
    if urllib.parse.urlparse(x).hostname in ['verdleaf.com','www.verdleaf.com'] and x not in out:out.append(x)
 return out
def save(url,name):
 try:
  r=s.get(url,timeout=25);r.raise_for_status()
  im=ImageOps.exif_transpose(Image.open(io.BytesIO(r.content))).convert('RGB')
  if min(im.size)<120:return None
  im.thumbnail((1600,1600));dest=ROOT/(name+'.webp');im.save(dest,'WEBP',quality=85)
  print('SAVED',name,im.size,url);return {'path':str(dest),'source':url,'width':im.width,'height':im.height}
 except Exception as e:print('image error',url,str(e));return None
names=['Basil','Black Seeds','Calendula','Caraway','Celery','Chamomile','Coriander','Dill','Dried-beets','Dried-garlic','Dried-onion','Fennel','Fenugreek','Frankincense','Hibiscus','Lemongrass','Liquorice','Marjoram','Molokhia','Parsley','Pepper-mint','Rosemary','Sage','Sesame','Spearmint','Thyme','Moringa Oleifera','Dried lemon','Dried lemon (Mechanical)','Anise']
keys=['basil','black-seeds-nigella','calendula','caraway','celery','chamomile','coriander','dill','dried-beetroot','dried-garlic','dried-onion','fennel','fenugreek','frankincense','hibiscus','lemongrass','liquorice','marjoram','molokhia','parsley','peppermint','rosemary','sage','sesame','spearmint','thyme','moringa','dried-lemon','dried-lemon-mechanical','anise']
def norm(x):return re.sub(r'[^a-z0-9]','',x.lower())
manifest={'products':{},'photos':{},'source':'https://verdleaf.com/'}
p=pages.get('products')
if p:
 # Elementor may use a separate stylesheet for background images. Resolve element IDs first.
 css=''
 for l in p.find_all('link',href=True):
  href=urllib.parse.urljoin(origin,l['href'])
  if '/elementor/css/post-' in href and urllib.parse.urlparse(href).hostname=='verdleaf.com':
   try:css+=s.get(href,timeout=15).text
   except Exception:pass
 def bg(el):
  o=urls(el)
  for a in [el]+list(el.select('[data-id]')):
   ident=a.get('data-id','')
   if ident:
    for rule in re.findall(r'[^{}]*elementor-element-'+re.escape(ident)+r'[^{}]*\{([^{}]+)\}',css):
     for u in re.findall(r'url\(["\']?([^)"\']+)',rule):
      u=urllib.parse.urljoin(origin,u)
      if '/uploads/' in u and u not in o:o.append(u)
  return o
 used=set()
 for label,key in zip(names,keys):
  heading=next((h for h in p.find_all(['h2','h3','h4','h5','div','span']) if norm(h.get_text(strip=True))==norm(label) and len(h.find_all(['h2','h3','h4','h5']))==0),None)
  if not heading:print('NO HEADING',label);continue
  options=[]
  for a in [heading]+list(heading.parents)[:7]:
   if a.name in ['html','body']:break
   hits=[x for x in bg(a) if not re.search(r'logo|certific|organic-seal|icon|index-',x,re.I)]
   # refuse multi-product containers
   others={norm(h.get_text(strip=True)) for h in a.find_all(['h2','h3','h4','h5'])}&set(map(norm,names))
   if len(others)>1:break
   if hits:options=hits;break
  # prefer file matching name, then a single card-local unique image.
  options=sorted(options,key=lambda x:0 if norm(label) in norm(urllib.parse.unquote(x).split('/')[-1]) else 1)
  for u in options:
   if u in used:continue
   result=save(u,key)
   if result:manifest['products'][key]=result;used.add(u);break
  if key not in manifest['products']:print('NO PHOTO',key,options[:5])
 print('ALL PRODUCT IMAGE URLS',json.dumps(bg(p)[:150]))
# known photo from company About page (ingredient stock photo, not facility)
u=origin+'/wp-content/uploads/2024/09/Picture28.webp'
r=save(u,'moringa-editorial')
if r:manifest['photos']['editorial']=r
# existing site imagery only; choose large, distinct home and about photographs by dimensions.
candidates=[]
for pg in ['home','about']:
 if pg not in pages:continue
 for u in urls(pages[pg]):
  if re.search(r'logo|certific|organic|seal|icon|index-|h3-img2|picture28',u,re.I):continue
  if u in {x['source'] for x in manifest['products'].values()}:continue
  if u not in candidates:candidates.append(u)
for u in candidates[:25]:
 r=save(u,'editorial-'+str(len(manifest['photos'])))
 if r and r['width']>=650 and r['height']>=400:
  manifest['photos']['photo'+str(len(manifest['photos']))]=r
 if len(manifest['photos'])>=5:break
# try catalogue PDF as official reference even if image extraction fails
pdf=origin+'/wp-content/uploads/2024/09/VerdLeaf-Product-List.pdf'
try:
 r=s.get(pdf,timeout=50);r.raise_for_status()
 if r.content[:5]==b'%PDF-' and len(r.content)<25_000_000:
  (ROOT/'Verdleaf-Product-List.pdf').write_bytes(r.content);manifest['catalogue']='media/Verdleaf-Product-List.pdf'
  import fitz
  d=fitz.open(stream=r.content,filetype='pdf')
  print('PDF PAGES',len(d))
  # PDF image fallback only when one product name and a clear largest image on same page.
  for page in d:
   txt=norm(page.get_text());matches=[(l,k) for l,k in zip(names,keys) if norm(l) in txt]
   if len(matches)!=1:continue
   l,k=matches[0]
   if k in manifest['products']:continue
   imgs=sorted(page.get_images(full=True),key=lambda v:v[2]*v[3],reverse=True)
   for img in imgs[:1]:
    raw=d.extract_image(img[0])['image'];im=Image.open(io.BytesIO(raw)).convert('RGB')
    if min(im.size)<160:continue
    im.thumbnail((1200,1200));dest=ROOT/(k+'.webp');im.save(dest,'WEBP',quality=85)
    manifest['products'][k]={'path':str(dest),'source':pdf+'#page='+str(page.number+1),'width':im.width,'height':im.height}
except Exception as e:print('PDF unavailable',str(e))
(ROOT/'manifest.json').write_text(json.dumps(manifest,indent=2))
pathlib.Path('media-manifest.js').write_text('window.VERDLEAF_MEDIA='+json.dumps(manifest)+';\n')
print('FINAL',len(manifest['products']),len(manifest['photos']))
