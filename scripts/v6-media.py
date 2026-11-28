import requests, pathlib, re, json, html, urllib.parse, io, concurrent.futures, hashlib
from bs4 import BeautifulSoup
from PIL import Image, ImageOps
ROOT=pathlib.Path('preview')
MFILE=ROOT/'media-manifest.js'
m=json.loads(MFILE.read_text().strip().removeprefix('window.VERDLEAF_MEDIA=').removesuffix(';'))
s=requests.Session()
s.headers['User-Agent']='VerdleafSiteMigration/1.0 (public website preview; contact info@verdleaf.com)'
sources={
 'landscape': ['Landwirtschaft im Fayyum Becken.jpg','Egyptian countryside full green.jpg','A grass farm near the Nile River in Egypt.jpg'],
 'field':['Sun flower fields at Faiyom, tounis village.jpg','Chamomile field.jpg'],
 'harvest':['حصاد القمح بالفيوم 147.jpg','Tending Young Plants by Hand.jpg'],
 'flowers-field':['Chamomile field.jpg','Chamomile flowers.jpg'],
 'oregano':['Oregano dried.JPG','Dried Oregano.JPG'],
 'echinacea':['Echinacea purpurea-IMG 9025.jpg','Echinacea purpurea - 2024-06-26 - 01.jpg'],
 'stevia':['Stevia rebaudiana leaves dry.JPG','Stevia dried leavesB.jpg'],
 'coriander-leaves':['Coriander Leaves.jpg','Coriander leaves.jpg'],
 'fennel-leaves':['Foeniculum vulgare leaves chopped.jpg','Foeniculum vulgare cut stems and leaves.jpg'],
 'fenugreek-leaves':['Dried leaves of Fenugreek-Kasuri Methi.jpg','Kasoori Methi.jpg'],
 'lemon-balm':['Melissae folium 005179.jpg','Lemon Balm Foliage.JPG'],
 'bay-leaves':['Whole Bay Leaves.jpg','Dried bay leaves.jpg','BayLeaves.JPG'],
 'savory':['Bohnenkraut (Saturea) getrocknetes Gewürzkraut.-Josef Schlaghecken.jpg','Bohnenkraut Satureja, getrocknete Sackware, Josef Schlaghecken.jpg'],
 'tarragon':['Tarragon spice.jpg','Dried Taragon.JPG'],
 'cumin':['Dried cumin seeds.jpg','Cumin seed.jpg'],
 'ginger':['Dry ginger.jpg','Dried Ginger.jpg'],
 'sunflower':['Sunflowers seeds food.jpg','Sunflower seeds.jpg'],
 'pumpkin-seeds':['Pumpkin seed.jpg','Pumpkin seeds.jpg'],
 'watermelon-seeds':['Watermelon seeds.jpg']
}
editorial={'landscape','field','harvest','flowers-field'}
def fetch(key,names):
 for name in names:
  try:
   url='https://commons.wikimedia.org/wiki/File:'+urllib.parse.quote(name.replace(' ','_'),safe='(),.-_')
   # Retrieve exact file page for attribution and license; do not use an unlicensed search thumbnail.
   r=requests.get(url,headers=s.headers,timeout=18);r.raise_for_status()
   soup=BeautifulSoup(r.text,'html.parser')
   author=soup.select_one('#fileinfotpl_aut + td')
   if not author:
    for tr in soup.select('tr'):
     if tr.find('td',id='fileinfotpl_aut'):author=tr.find_all('td')[-1];break
   if not author:raise ValueError('author metadata missing')
   credit=' '.join(author.stripped_strings)[:250]
   # Prefer CC BY or BY-SA; public domain is accepted when clearly declared.
   lic=None
   for a in soup.select('.licensetpl a[href], .licensetpl_short a[href], #mw-content-text .licensetpl_link[href]'):
    u=a.get('href','')
    if re.search(r'creativecommons\.org/licenses/by(?:-sa)?/[0-9.]+',u):lic=u;break
   if not lic:
    for a in soup.select('a[href]'):
     u=a.get('href','')
     if re.search(r'creativecommons\.org/licenses/by(?:-sa)?/[0-9.]+',u) and ('CC BY' in a.get_text(' ',strip=True) or 'Creative Commons Attribution' in a.get_text(' ',strip=True)):lic=u;break
   if not lic and soup.select_one('.licensetpl') and re.search(r'public domain|CC0',soup.select_one('.licensetpl').get_text(' ',strip=True),re.I):lic='Public domain'
   if not lic or not credit:raise ValueError('license or author not verified')
   lic=urllib.parse.urljoin('https://commons.wikimedia.org',lic) if lic.startswith(('//','/')) else lic
   anchor=soup.select_one('.fullImageLink a')
   if not anchor:raise ValueError('original link missing')
   imgurl=urllib.parse.urljoin(url,anchor['href'])
   if urllib.parse.urlparse(imgurl).hostname!='upload.wikimedia.org':raise ValueError('unexpected image host')
   rr=requests.get(imgurl,headers=s.headers,timeout=30);rr.raise_for_status()
   im=ImageOps.exif_transpose(Image.open(io.BytesIO(rr.content))).convert('RGB')
   if min(im.size)<300:raise ValueError('small source')
   im.thumbnail((1900,1500) if key in editorial else (1100,900))
   out='media/reference-'+key+'.webp'
   im.save(ROOT/out,'WEBP',quality=86,method=5)
   return key,{'path':out,'source':url,'credit':credit,'license':lic,'kind':'regional reference' if key in editorial else 'botanical reference','width':im.width,'height':im.height,'modified':'Resized and converted to WebP; no compositional manipulation.'},None
  except Exception as e:print('TRY',key,name,str(e)[:180],flush=True)
 return key,None,'No licensed usable source obtained'
results={}
with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
 for key,photo,error in pool.map(lambda pair:fetch(*pair),sources.items()):
  if photo:results[key]=photo;print('IMPORTED',key,photo['width'],photo['height'],flush=True)
  else:print('UNAVAILABLE',key,error,flush=True)
# Preserve the existing catalogue evidence; use new references for previously unillustrated products.
for k,v in results.items():
 if k not in editorial:m['products'][k]=v
if 'landscape' in results:
 old=m['photos']['hero']
 m['photos']['hero']=results['landscape']
 m['photos']['slides']=[results[k] for k in ['field','flowers-field'] if k in results]+[old]+m['photos'].get('slides',[])
if 'harvest' in results:m['photos']['story']=results['harvest']
if 'field' in results:m['photos']['editorial']=results['field']
m['references']=results
MFILE.write_text('window.VERDLEAF_MEDIA='+json.dumps(m,ensure_ascii=False)+';\n')
(ROOT/'media/manifest.json').write_text(json.dumps(m,ensure_ascii=False,indent=2))
# Build attribution page, with every source explicitly linked and the modified version license recorded.
def e(v):return html.escape(str(v),quote=True)
rows=[]
for key,photo in results.items():
 rows.append(f'<article><img src="{e(photo["path"])}" alt="{e(key.replace("-"," "))}" loading="lazy"><h2>{e(key.replace("-"," ").title())}</h2><p>{e(photo["credit"])}</p><p><a href="{e(photo["source"])}">Original source</a> · <a href="{e(photo["license"])}">License</a></p><p>{e(photo["modified"])}</p></article>')
(ROOT/'photography.html').write_text('<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Verdleaf | Photography credits</title><link rel="stylesheet" href="site.css"><link rel="stylesheet" href="compact.css"><style>main{padding:24px 0}h1{font:700 26px system-ui}h2{font:650 17px system-ui}.credits{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px}.credits img{width:100%;height:150px;object-fit:cover;border-radius:8px}.credits article{padding:12px;border:1px solid #d8dfd0;border-radius:10px}.credits p{font-size:12px}</style></head><body><main class="shell"><a href="index.html">← Return to Verdleaf</a><h1>Photography and reference images</h1><p>Regional agricultural photographs are editorial context, not evidence of Verdleaf-owned fields or facilities. Botanical reference photographs are not commercial lot photographs. Original company assets remain subject to owner approval.</p><div class="credits">'+''.join(rows)+'</div></main></body></html>')
# Synchronize all independent product pages and make their content compact.
for path in list((ROOT/'products').glob('*/index.html'))+list((ROOT/'ar/products').glob('*/index.html')):
 key=path.parent.name;ar=path.parts[1]=='ar';prefix='../../../' if ar else '../../'
 txt=path.read_text();txt=txt.replace('</head>',f'<link rel="stylesheet" href="{prefix}compact.css"></head>')
 p=m['products'].get(key)
 if p and p.get('kind')=='botanical reference':
  figure=f'<figure><img src="{prefix}{e(p["path"])}" alt="{e(key)}" width="{p["width"]}" height="{p["height"]}"><figcaption>'+('صورة نباتية مرجعية وليست صورة شحنة' if ar else 'Botanical reference, not a commercial lot')+f' · <a href="{e(p["source"])}">{e(p["credit"])}</a> · <a href="{prefix}photography.html">Credits</a></figcaption></figure>'
  txt=re.sub(r'<figure>.*?</figure>',lambda _:figure,txt,count=1,flags=re.S)
 path.write_text(txt)
# Adjust source labels and static image references on the public site.
p=ROOT/'index.html';txt=p.read_text().replace('Original ingredient photography · Artistic motion layer','Regional and ingredient photography · Artistic motion layer').replace('صور المنتجات الأصلية · طبقة حركة فنية','صور زراعية ومنتجات مرجعية · طبقة حركة فنية')
txt=txt.replace('<a href="review.html">Review centre ↗</a>','<a href="photography.html">Photography credits ↗</a> · <a href="review.html">Review centre ↗</a>').replace('src="media-manifest.js"','src="media-manifest.js?v=6"')
if 'flowers-field' in results:txt=txt.replace('src="media/chamomile.webp" alt="Chamomile from the Verdleaf catalogue"','src="'+results['flowers-field']['path']+'" alt="Chamomile growing in a field (reference photograph)"').replace('CHAMOMILE / MATRICARIA CHAMOMILLA','CHAMOMILE / BOTANICAL REFERENCE')
p.write_text(txt)
p=ROOT/'site.js';txt=p.read_text()
txt=txt.replace("const credit", "const credit")
# Credit all new editorial sources via accessible link rather than misleading ownership claims.
txt=txt.replace("setPhoto('companyPhoto',editor);","setPhoto('companyPhoto',editor);")
p.write_text(txt)
# Generate contact sheet for visual verification.
from PIL import ImageDraw
items=list(results.items());w=800;cellw=200;cellh=145
sheet=Image.new('RGB',(w,max(1,(len(items)+3)//4)*cellh),'white');draw=ImageDraw.Draw(sheet)
for i,(k,v) in enumerate(items):
 im=Image.open(ROOT/v['path']);im.thumbnail((190,115))
 x=i%4*cellw;y=i//4*cellh;sheet.paste(im,(x+(cellw-im.width)//2,y));draw.text((x+5,y+118),k,fill='black')
pathlib.Path('visual-evidence').mkdir(exist_ok=True);sheet.save('visual-evidence/new-media-contact-sheet.jpg',quality=90)
print('MEDIA_SUMMARY',json.dumps({'imported':list(results),'missing':[k for k in sources if k not in results],'catalogue_with_image':len(m['products'])}))
