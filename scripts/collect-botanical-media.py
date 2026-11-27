import requests, re, json, io, pathlib, html, urllib.parse, concurrent.futures, time
from bs4 import BeautifulSoup
from PIL import Image, ImageOps
ROOT=pathlib.Path('media');ROOT.mkdir(exist_ok=True)
ua='VerdleafWebsiteRedesign/1.0 (contact: info@verdleaf.com)'
s=requests.Session();s.headers['User-Agent']=ua
origin='https://verdleaf.com'
manifest={'products':{},'photos':{},'source':origin}
def save(url,key,source,credit,license,kind):
 try:
  u=urllib.parse.urlparse(url)
  if u.scheme!='https' or u.hostname not in ['verdleaf.com','www.verdleaf.com','upload.wikimedia.org']:return None
  r=requests.get(url,headers={'User-Agent':ua},timeout=25);r.raise_for_status()
  im=ImageOps.exif_transpose(Image.open(io.BytesIO(r.content))).convert('RGB')
  if min(im.size)<150:return None
  im.thumbnail((1450,1450));dest=ROOT/(key+'.webp');im.save(dest,'WEBP',quality=85)
  return {'path':str(dest),'source':source,'credit':credit,'license':license,'kind':kind,'width':im.width,'height':im.height}
 except Exception as e:print('FAIL',key,str(e));return None
def norm(x):return re.sub('[^a-z0-9]','',x.lower())
# Retrieve original HTML and CSS for diagnostics.
try:
 r=s.get(origin+'/products/',timeout=20);soup=BeautifulSoup(r.text,'html.parser')
 pathlib.Path('source-products.html').write_text(r.text)
 for l in soup.find_all('link',href=True):
  u=urllib.parse.urljoin(origin,l['href'])
  if '/elementor/css/' in u:
   print('CSS',u)
   try:
    v=s.get(u,timeout=15);pathlib.Path('source-'+u.split('/')[-1].split('?')[0]).write_text(v.text)
   except Exception:pass
 for h in soup.find_all(['h2','h3','h4']):
  if h.get_text(strip=True) in ['Basil','Chamomile','Hibiscus']:
   print('CARD',h.get_text(strip=True),str(h.parent.parent)[:4500])
except Exception as e:print(e)
# existing company logo: official header image, verify visually before use.
try:
 p=BeautifulSoup(s.get(origin+'/',timeout=20).text,'html.parser')
 for im in p.find_all('img')[:8]:
  print('HEADER IMG',im.get('src'),im.get('alt'))
except Exception:pass
p=save(origin+'/wp-content/uploads/2024/09/Picture28.webp','moringa-editorial',origin+'/about-us/','Existing Verdleaf website','Company website asset; reuse subject to owner approval','website')
if p:manifest['photos']['editorial']=p
# Wikipedia page images, retrieved as clearly identified botanical reference images, not company inventory.
articles={
'basil':'Basil','black-seeds-nigella':'Nigella sativa','calendula':'Calendula officinalis','caraway':'Caraway','celery':'Celery','chamomile':'Matricaria chamomilla','coriander':'Coriander','dill':'Dill','dried-beetroot':'Beetroot','dried-garlic':'Garlic','dried-onion':'Onion','fennel':'Fennel','fenugreek':'Fenugreek','frankincense':'Frankincense','hibiscus':'Roselle (plant)','lemongrass':'Cymbopogon citratus','liquorice':'Liquorice','marjoram':'Marjoram','molokhia':'Corchorus olitorius','parsley':'Parsley','peppermint':'Peppermint','rosemary':'Rosemary','sage':'Salvia officinalis','sesame':'Sesame','spearmint':'Spearmint','thyme':'Thyme','moringa':'Moringa oleifera','dried-lemon':'Dried lime','anise':'Anise','oregano':'Oregano','echinacea':'Echinacea','stevia':'Stevia rebaudiana','bay-leaves':'Laurus nobilis','lemon-balm':'Melissa officinalis','savory':'Satureja hortensis','tarragon':'Tarragon','cumin':'Cumin','ginger':'Ginger','sunflower':'Sunflower seed','pumpkin-seeds':'Pumpkin seed','watermelon-seeds':'Watermelon','fennel-leaves':'Fennel','fenugreek-leaves':'Fenugreek','coriander-leaves':'Coriander'}
def wiki(item):
 key,title=item
 try:
  api='https://en.wikipedia.org/w/api.php'
  q={'action':'query','format':'json','redirects':1,'titles':title,'prop':'pageimages','pithumbsize':1200,'pilicense':'free'}
  d=requests.get(api,params=q,headers={'User-Agent':ua},timeout=25).json()
  page=next(iter(d['query']['pages'].values()));file=page.get('pageimage')
  if not file:print('NO WIKI IMAGE',key);return key,None
  commons='https://commons.wikimedia.org/w/api.php'
  d=requests.get(commons,params={'action':'query','format':'json','titles':'File:'+file,'prop':'imageinfo','iiprop':'url|extmetadata','iiurlwidth':1200},headers={'User-Agent':ua},timeout=25).json()
  inf=next(iter(d['query']['pages'].values()))['imageinfo'][0]
  md=inf.get('extmetadata',{})
  val=lambda k:BeautifulSoup(md.get(k,{}).get('value',''),'html.parser').get_text(' ',strip=True)
  lic=val('LicenseShortName');artist=val('Artist')
  if not re.search(r'CC BY|CC0|Public domain|GFDL',lic,re.I):print('NO FREE LICENSE',key,lic);return key,None
  if 'GFDL' in lic and 'CC' not in lic:return key,None
  r=save(inf.get('thumburl') or inf['url'],key,inf['descriptionurl'],artist or 'Wikimedia Commons',lic,'botanical reference')
  if r:print('WIKI',key,lic,r['source'])
  return key,r
 except Exception as e:print('WIKI ERROR',key,str(e));return key,None
with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
 for k,r in pool.map(wiki,articles.items()):
  if r:manifest['products'][k]=r
# Reuse actual company original mark from downloaded PDF only (if accessible).
try:
 import fitz
 r=s.get(origin+'/wp-content/uploads/2024/09/VerdLeaf-Product-List.pdf',timeout=40);r.raise_for_status()
 d=fitz.open(stream=r.content,filetype='pdf')
 page=d[0];pix=page.get_pixmap(matrix=fitz.Matrix(3,3),clip=fitz.Rect(386,10,477,103),alpha=False)
 im=Image.open(io.BytesIO(pix.tobytes('png')));im.save(ROOT/'logo.png')
 manifest['logo']='media/logo.png'
except Exception as e:print('LOGO',e)
(ROOT/'manifest.json').write_text(json.dumps(manifest,indent=2))
pathlib.Path('media-manifest.js').write_text('window.VERDLEAF_MEDIA='+json.dumps(manifest)+';\n')
print('FINAL',len(manifest['products']),len(manifest['photos']))
