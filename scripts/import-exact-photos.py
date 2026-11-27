import requests, re, json, io, pathlib, urllib.parse, concurrent.futures, shutil
from bs4 import BeautifulSoup
from PIL import Image,ImageOps
from concurrent.futures import ThreadPoolExecutor
ROOT=pathlib.Path('preview/media');ROOT.mkdir(parents=True,exist_ok=True)
origin='https://verdleaf.com'
ua='Mozilla/5.0 (compatible; Verdleaf website migration)'
s=requests.Session();s.headers['User-Agent']=ua
manifest={'products':{},'photos':{},'source':origin}
def download(url,key,kind='website',source=None,credit='Existing Verdleaf website',license='Company site asset; reuse subject to owner approval'):
 try:
  url=urllib.parse.urljoin(origin,url.strip().strip('"\''))
  if urllib.parse.urlparse(url).hostname not in ('verdleaf.com','www.verdleaf.com','upload.wikimedia.org'):return
  r=requests.get(url,headers={'User-Agent':ua},timeout=20);r.raise_for_status()
  im=ImageOps.exif_transpose(Image.open(io.BytesIO(r.content))).convert('RGB')
  if min(im.size)<130:return
  im.thumbnail((1650,1650));dest=ROOT/(key+'.webp');im.save(dest,'WEBP',quality=87)
  print('SAVED',key,im.size,url,flush=True)
  return {'path':'media/'+dest.name,'source':source or url,'credit':credit,'license':license,'kind':kind,'width':im.width,'height':im.height}
 except Exception as e:print('IMAGE ERROR',key,str(e),flush=True)
def getpage(path):
 r=s.get(origin+path,timeout=25);r.raise_for_status();p=BeautifulSoup(r.text,'html.parser')
 css=''
 for l in p.find_all('link',href=True):
  if 'elementor-post-' in l.get('id',''):
   u=urllib.parse.urljoin(origin,l['href'])
   try:
    rr=s.get(u,timeout=20);rr.raise_for_status();css+=rr.text+'\n'
   except Exception as e:print('CSS ERROR',u,e)
 css+='\n'.join(st.get_text() for st in p.find_all('style'))
 return p,css
def backgrounds(css,ident,front=False):
 out=[]
 for sel,rule in re.findall(r'([^{}]+)\{([^{}]+)\}',css):
  if 'elementor-element-'+ident not in sel:continue
  if front and 'flip-box__back' in sel:continue
  for u in re.findall(r'url\(\s*["\']?([^\)"\']+)',rule):
   u=urllib.parse.urljoin(origin,u.strip())
   if re.search(r'\.(webp|png|jpe?g)(\?|$)',u,re.I) and u not in out:out.append(u)
 return out
names=['Basil','Black Seeds','Calendula','Caraway','Celery','Chamomile','Coriander','Dill','Dried-beets','Dried-garlic','Dried-onion','Fennel','Fenugreek','Frankincense','Hibiscus','Lemongrass','Liquorice','Marjoram','Molokhia','Parsley','Pepper-mint','Rosemary','Sage','Sesame','Spearmint','Thyme','Moringa Oleifera','Dried lemon','Dried lemon (Mechanical)','Anise']
keys=['basil','black-seeds-nigella','calendula','caraway','celery','chamomile','coriander','dill','dried-beetroot','dried-garlic','dried-onion','fennel','fenugreek','frankincense','hibiscus','lemongrass','liquorice','marjoram','molokhia','parsley','peppermint','rosemary','sage','sesame','spearmint','thyme','moringa','dried-lemon','dried-lemon-mechanical','anise']
def norm(x):return re.sub('[^a-z0-9]','',x.lower())
p,css=getpage('/products/')
for label,key in zip(names,keys):
 h=next((h for h in p.find_all(['h2','h3','h4']) if norm(h.get_text(strip=True))==norm(label)),None)
 if not h:continue
 card=h.find_parent(class_='product')
 if not card:continue
 ids=[x.get('data-id') for x in card.select('[data-widget_type="flip-box.default"]') if x.get('data-id')]
 for ident in ids:
  candidates=backgrounds(css,ident,True)
  print('CARD',key,ident,candidates,flush=True)
  for url in candidates:
   r=download(url,key)
   if r:manifest['products'][key]=r;break
  if key in manifest['products']:break
# hero and supporting original images from home page CSS: capture slides in order.
p,css=getpage('/')
slides=[]
for e in p.select('[data-widget_type]'):
 kind=e.get('data-widget_type','')
 if 'slides' not in kind and 'image' not in kind:continue
 if 'slides' in kind:
  for url in backgrounds(css,e.get('data-id','')):
   if url not in slides:slides.append(url)
for url in slides:
 r=download(url,'hero')
 if r and r['width']>=650:manifest['photos']['hero']=r;break
# Known actual original company logo and editorial image.
r=download(origin+'/wp-content/uploads/2024/09/Picture28.webp','moringa-editorial',source=origin+'/about-us/')
if r:manifest['photos']['editorial']=r
r=download(origin+'/wp-content/uploads/2024/09/imageedit_8_5143658727-e1727429180126.png','logo',source=origin)
if r:manifest['logo']=r['path']
# Remaining catalogue additions: Wikimedia Commons reference photos, with author/license and clear labeling.
queries={'oregano':'Origanum vulgare leaves','echinacea':'Echinacea purpurea flower photograph','stevia':'Stevia rebaudiana leaves','coriander-leaves':'Coriandrum sativum leaves','fennel-leaves':'Foeniculum vulgare leaves','fenugreek-leaves':'Trigonella foenum-graecum leaves','lemon-balm':'Melissa officinalis leaves','bay-leaves':'Laurus nobilis dried leaves','savory':'Satureja hortensis plant','tarragon':'Artemisia dracunculus plant','cumin':'Cuminum cyminum seeds','ginger':'Zingiber officinale rhizome','sunflower':'sunflower seeds closeup','pumpkin-seeds':'pumpkin seeds closeup','watermelon-seeds':'watermelon seeds'}
def commons(item):
 key,q=item
 try:
  api='https://commons.wikimedia.org/w/api.php'
  params={'action':'query','format':'json','generator':'search','gsrsearch':q+' filetype:bitmap','gsrnamespace':6,'gsrlimit':6,'prop':'imageinfo','iiprop':'url|extmetadata','iiurlwidth':1000}
  pages=requests.get(api,params=params,headers={'User-Agent':ua},timeout=25).json().get('query',{}).get('pages',{}).values()
  for p in sorted(pages,key=lambda p:p.get('index',999)):
   title=p.get('title','')
   if re.search(r'illustration|drawing|koehler|Köhler|map|diagram|logo|herbarium',title,re.I):continue
   inf=p.get('imageinfo',[{}])[0];md=inf.get('extmetadata',{})
   val=lambda k:BeautifulSoup(md.get(k,{}).get('value',''),'html.parser').get_text(' ',strip=True)
   lic=val('LicenseShortName')
   if not re.search(r'CC BY|CC0|Public domain',lic,re.I):continue
   r=download(inf.get('thumburl') or inf.get('url',''),key,'botanical reference',inf.get('descriptionurl'),val('Artist') or 'Wikimedia Commons',lic)
   if r:return key,r
 except Exception as e:print('COMMONS ERROR',key,e,flush=True)
 return key,None
with ThreadPoolExecutor(max_workers=3) as pool:
 for k,r in pool.map(commons,queries.items()):
  if r:manifest['products'][k]=r
# Only known website photos used as generic editorial imagery.
if not manifest['photos'].get('hero'):
 manifest['photos']['hero']=manifest['photos'].get('editorial',{})
manifest['photos']['story']=manifest['products'].get('chamomile',manifest['photos'].get('editorial',{}))
(ROOT/'manifest.json').write_text(json.dumps(manifest,indent=2))
pathlib.Path('preview/media-manifest.js').write_text('window.VERDLEAF_MEDIA='+json.dumps(manifest)+';\n')
print('FINAL ORIGINAL',sum(x['kind']=='website' for x in manifest['products'].values()),'REFERENCE',sum(x['kind']=='botanical reference' for x in manifest['products'].values()),'hero',manifest['photos'].get('hero'),flush=True)
