from pathlib import Path
from PIL import Image, ImageDraw
import json,re
root=Path('preview'); media=root/'media'
def crop_lockup(dest,dark=False):
 # The supplied 355x354 circular company seal contains a green leaf symbol
 # and a two-line wordmark. Extract those exact original components.
 im=Image.open(media/'logo.webp').convert('RGB')
 w,h=im.size
 def extract(bounds,leaf=False):
  x0,y0,x1,y1=bounds
  crop=im.crop((round(x0*w/355),round(y0*h/354),round(x1*w/355),round(y1*h/354)))
  out=Image.new('RGBA',crop.size)
  pixels=[]
  for r,g,b in crop.getdata():
   if leaf:
    # Recover antialiasing from the white original background.
    a=min(255,max(0,round((255-b)*255/196)))
    if a<8:pixels.append((0,0,0,0));continue
    rgb=tuple(max(0,min(255,round((v-255*(1-a/255))/(a/255)))) for v in (r,g,b))
    pixels.append((*rgb,a))
   else:
    a=255-min(r,g,b)
    pixels.append((245,248,235,a) if dark else (24,42,28,a))
  out.putdata(pixels)
  return out.crop(out.getchannel('A').point(lambda x:255 if x>20 else 0).getbbox())
 mark=extract((65,102,291,192),True)
 word=extract((46,196,309,301))
 mh=72;wh=97
 mark=mark.resize((round(mark.width*mh/mark.height),mh),Image.Resampling.LANCZOS)
 word=word.resize((round(word.width*wh/word.height),wh),Image.Resampling.LANCZOS)
 gap=18;pad=5;H=max(mh,wh)+pad*2
 out=Image.new('RGBA',(mark.width+word.width+gap+2*pad,H))
 out.alpha_composite(mark,(pad,(H-mh)//2));out.alpha_composite(word,(pad+mark.width+gap,(H-wh)//2))
 out.save(dest)
 print('LOGO horizontal',out.size,flush=True)
 return out
light=crop_lockup(media/'logo-horizontal.png')
dark=crop_lockup(media/'logo-horizontal-light.png',True)
p=root/'index.html';s=p.read_text()
s=s.replace('id="logo" src="media/logo-transparent.png" alt="Verdleaf Global" width="76" height="76"','id="logo" src="media/logo-horizontal.png" alt="Verdleaf Global" width="%s" height="%s"'%light.size)
s=s.replace('class="menu-toggle">Menu</button>','class="menu-toggle" aria-label="Open navigation"><svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path class="menu-line-top" d="M4 6h16"/><path class="menu-line-mid" d="M4 12h16"/><path class="menu-line-bottom" d="M4 18h16"/></svg></button>')
s=s.replace('</head>','<link rel="stylesheet" href="header-v7.css?v=1"></head>').replace('site.js?v=11','site.js?v=12').replace('media-manifest.js?v=7','media-manifest.js?v=8')
p.write_text(s)
p=root/'site.js';s=p.read_text()
s=s.replace("navLogo.src=media.logoTransparent||media.logo","navLogo.src=media.logoHorizontal||media.logoTransparent||media.logo")
s=s.replace("$('menuToggle').textContent=t('Menu','القائمة');","updateMenuLabel();")
s=s.replace("function translate(){","function updateMenuLabel(){const open=$('menuToggle').getAttribute('aria-expanded')==='true';$('menuToggle').setAttribute('aria-label',open?t('Close navigation','إغلاق القائمة'):t('Open navigation','فتح القائمة'))}function translate(){")
s=s.replace("$('menuToggle').setAttribute('aria-expanded','false')}","$('menuToggle').setAttribute('aria-expanded','false');updateMenuLabel()}")
s=s.replace("$('menuToggle').onclick=()=>{$('menuToggle').setAttribute('aria-expanded',String($('navLinks').classList.toggle('open')))};","$('menuToggle').onclick=()=>{$('menuToggle').setAttribute('aria-expanded',String($('navLinks').classList.toggle('open')));updateMenuLabel()};document.addEventListener('keydown',e=>{if(e.key==='Escape'&&$('navLinks').classList.contains('open')){$('navLinks').classList.remove('open');$('menuToggle').setAttribute('aria-expanded','false');updateMenuLabel();$('menuToggle').focus()}});")
p.write_text(s)
p=root/'media-manifest.js';m=json.loads(p.read_text().strip().removeprefix('window.VERDLEAF_MEDIA=').removesuffix(';'));m['logoHorizontal']='media/logo-horizontal.png';m['logoHorizontalLight']='media/logo-horizontal-light.png';p.write_text('window.VERDLEAF_MEDIA='+json.dumps(m,ensure_ascii=False)+';\n');(media/'manifest.json').write_text(json.dumps(m,ensure_ascii=False,indent=2))
# Update stand-alone product headers as well. Footer and loading mark retain original stacked logo.
for p in list((root/'products').glob('*/index.html'))+list((root/'ar/products').glob('*/index.html')):
 s=p.read_text().replace('media/logo-transparent.png','media/logo-horizontal.png')
 prefix='../../../' if p.parts[1]=='ar' else '../../'
 s=s.replace('</head>',f'<link rel="stylesheet" href="{prefix}header-v7.css?v=1"></head>')
 p.write_text(s)
# Admin uses the matching horizontal white brand asset, with label retained for product context.
p=root/'admin-demo.html';s=p.read_text().replace('media/logo-light.png','media/logo-horizontal-light.png').replace('Modules ☰</button>','<svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>').replace('id="menu" aria-expanded','id="menu" aria-label="Toggle administration navigation" aria-expanded').replace('admin-demo.css?v=7','admin-demo.css?v=8');p.write_text(s)
p=root/'admin-demo.css';p.write_text(p.read_text()+'\n.identity{display:flex;flex-direction:column;align-items:flex-start;gap:4px}.identity img{width:158px;height:40px;object-fit:contain;object-position:left center}.identity span{font-size:13px}.identity small{font-size:8px}@media(max-width:900px){.identity{flex-direction:row;align-items:center;gap:8px}.identity img{width:112px;height:34px}.identity span{font-size:11px}#menu{width:42px;height:42px;padding:8px;display:grid;place-items:center}}')
(root/'header-v7.css').write_text('''/* Compact header, original horizontal brand lockup. */
.topline{font-size:9px;line-height:1.25;letter-spacing:.07em}.topline .shell{padding:3px 0;align-items:center;gap:8px}.topline a{font-size:9px}
header nav.shell{min-height:58px;height:auto;padding:4px 0;gap:14px}
header .brand{display:inline-flex;align-items:center;flex-shrink:0}.brand-text{display:none!important}header .brand img{width:clamp(128px,12vw,170px);height:46px;object-fit:contain;object-position:left center}
header .nav-links{gap:clamp(8px,1.2vw,18px);font-size:13px}header .nav-quote{padding:6px 12px;min-height:36px}
header .menu-toggle{border:0;background:transparent;border-radius:8px;padding:7px;width:42px;height:42px;color:#244b35;flex-shrink:0}
.menu-toggle svg{display:block;margin:auto}.menu-toggle svg path{transition:transform .18s,opacity .18s;transform-origin:12px 12px}
.menu-toggle[aria-expanded=true] .menu-line-top{transform:translateY(6px) rotate(45deg)}.menu-toggle[aria-expanded=true] .menu-line-mid{opacity:0}.menu-toggle[aria-expanded=true] .menu-line-bottom{transform:translateY(-6px) rotate(-45deg)}
header .static-head img{width:150px;height:46px;object-fit:contain}.static-head{padding-block:4px}
@media(max-width:1150px){header nav.shell{min-height:54px;padding:4px 0;gap:8px}header .brand img{width:140px;height:42px}header .nav-links.open{padding:6px 0;gap:1px}header .nav-links a,header .nav-links .language{padding:8px}header .menu-toggle{display:grid;place-items:center}}
@media(max-width:600px){.topline .shell{justify-content:center;padding:3px 0;white-space:nowrap}.topline a{display:none}.topline{font-size:8px;letter-spacing:.04em}header .brand img{width:132px;height:39px}header nav.shell{min-height:51px}.living-hero{min-height:clamp(415px,64svh,560px)}}
html[dir=rtl] header .brand img{object-position:right center}
''')
Path('visual-evidence').mkdir(exist_ok=True)
sheet=Image.new('RGB',(640,300),'#f6f5ed');draw=ImageDraw.Draw(sheet);draw.text((12,12),'Original / horizontal lockup',fill='#22382b')
orig=Image.open(media/'logo-transparent.png').convert('RGBA');orig.thumbnail((180,210));sheet.paste(orig,(20,50),orig)
sample=light.copy();sample.thumbnail((380,120));sheet.paste(sample,(235,85),sample);sheet.save('visual-evidence/logo-lockup.png')
