import requests, re, json, io, pathlib, urllib.parse
from PIL import Image
from bs4 import BeautifulSoup
from collections import Counter
root=pathlib.Path('preview/media')
origin='https://verdleaf.com'
s=requests.Session();s.headers['User-Agent']='Mozilla/5.0 Verdleaf website migration'
url=origin+'/wp-content/uploads/2024/09/imageedit_8_5143658727-e1727429180126.png'
r=s.get(url,timeout=25);r.raise_for_status()
im=Image.open(io.BytesIO(r.content)).convert('RGBA')
print('ORIGINAL LOGO',im.size,im.getextrema())
# Remove only white paper and its antialiasing matte. Preserve existing alpha and the original artwork.
dark=Image.new('RGBA',im.size);light=Image.new('RGBA',im.size)
for y in range(im.height):
 for x in range(im.width):
  rr,g,b,a=im.getpixel((x,y))
  if not a:continue
  # Estimate foreground coverage of black lettering / original green leaf ink.
  strength=255-min(rr,g,b)
  if strength<9:continue
  if max(rr,g,b)-min(rr,g,b)<17:
   # Neutral letter and border pixels: remove white matte and preserve black contours.
   coverage=255-round((rr+g+b)/3)
   fg=(18,20,17);fg_light=(244,244,231)
  else:
   # Saturated original green. Keep the original shade in opaque pixels.
   coverage=min(255,round(strength/0.76))
   fg=tuple(max(0,min(255,round(255-(255-c)*255/max(1,coverage)))) for c in (rr,g,b))
   fg_light=fg
  alpha=round(a*coverage/255)
  dark.putpixel((x,y),(*fg,alpha));light.putpixel((x,y),(*fg_light,alpha))
dark.save(root/'logo-transparent.png',optimize=True);light.save(root/'logo-light.png',optimize=True)
print('TRANSPARENCY',dark.getchannel('A').getextrema(),Counter(dark.getchannel('A').getdata())[0])
mfile=pathlib.Path('preview/media-manifest.js')
m=json.loads(mfile.read_text().strip().removeprefix('window.VERDLEAF_MEDIA=').removesuffix(';'))
m['logoTransparent']='media/logo-transparent.png';m['logoLight']='media/logo-light.png'
# Recover further original homepage slider photos and record original website font evidence.
try:
 p=BeautifulSoup(s.get(origin+'/',timeout=25).text,'html.parser');css=''
 families=[]
 for l in p.find_all('link',href=True):
  ident=l.get('id','')
  if 'elementor-gf-' in ident:families.append(ident)
  if 'elementor-post-' in ident:
   rr=s.get(urllib.parse.urljoin(origin,l['href']),timeout=20);rr.raise_for_status();css+=rr.text+'\n'
 print('FONT ASSET IDS',families)
 print('FONT FAMILY DECLARATIONS',Counter(re.findall(r'font-family\s*:\s*([^;}]+)',css)).most_common(16))
 ids=[e.get('data-id') for e in p.select('[data-widget_type]') if 'slides' in e.get('data-widget_type','')]
 sources=[]
 for sel,rule in re.findall(r'([^{}]+)\{([^{}]+)\}',css):
  if not any('elementor-element-'+str(i) in sel for i in ids):continue
  for u in re.findall(r'url\(\s*["\']?([^\)"\']+)',rule):
   u=urllib.parse.urljoin(origin,u.strip())
   if urllib.parse.urlparse(u).hostname not in ('verdleaf.com','www.verdleaf.com'):continue
   if re.search(r'\.(webp|png|jpe?g)(\?|$)',u,re.I) and u not in sources:sources.append(u)
 slides=[]
 for u in sources[:6]:
  if u==m['photos']['hero']['source']:continue
  rr=s.get(u,timeout=25);rr.raise_for_status()
  img=Image.open(io.BytesIO(rr.content)).convert('RGB')
  if img.width<650 or img.height<350:continue
  img.thumbnail((1650,1650));name='hero-slide-'+str(len(slides)+1)+'.webp';img.save(root/name,'WEBP',quality=85)
  slides.append({'path':'media/'+name,'source':u,'credit':'Existing Verdleaf website','kind':'website','width':img.width,'height':img.height})
 m['photos']['slides']=slides;print('ADDITIONAL ORIGINAL SLIDES',len(slides))
except Exception as e:print('SOURCE ASSET WARNING',e)
mfile.write_text('window.VERDLEAF_MEDIA='+json.dumps(m)+';\n');(root/'manifest.json').write_text(json.dumps(m,indent=2))
