from pathlib import Path
from PIL import Image, ImageDraw
import json,re
root=Path('preview'); media=root/'media'
def crop_lockup(src,dest):
 im=Image.open(src).convert('RGBA')
 alpha=im.getchannel('A'); box=alpha.point(lambda a:255 if a>70 else 0).getbbox()
 if not box:raise RuntimeError('Empty logo')
 im=im.crop(box); w,h=im.size
 a=im.getchannel('A'); rows=[sum(v>85 for v in a.crop((0,y,w,y+1)).getdata()) for y in range(h)]
 # Find the separating whitespace in the original stacked mark. Retain original glyphs and colours.
 candidates=[]; start=None
 for y in range(int(h*.28),int(h*.86)):
  if rows[y]<=max(1,int(w*.006)):
   if start is None:start=y
  elif start is not None:
   candidates.append((y-start,start,y));start=None
 if start is not None:candidates.append((int(h*.86)-start,start,int(h*.86)))
 if not candidates:raise RuntimeError('No clear separating gap in original logo')
 _,lo,hi=max(candidates,key=lambda t:(t[0],-abs((t[1]+t[2])/2-h*.58)))
 split=(lo+hi)//2
 def trim(x):return x.crop(x.getchannel('A').point(lambda a:255 if a>70 else 0).getbbox())
 mark=trim(im.crop((0,0,w,split))); word=trim(im.crop((0,split,w,h)))
 print('LOGO',src.name,'source',im.size,'split',split,'gap',hi-lo,'mark',mark.size,'word',word.size,flush=True)
 if mark.height<10 or word.height<10:raise RuntimeError('Unexpected logo partition')
 # The emblem and original text are aligned optically; no glyphs are redrawn.
 mh=116; wh=min(92,round(word.height*mh/mark.height*1.4))
 wh=max(48,wh)
 mark=mark.resize((round(mark.width*mh/mark.height),mh),Image.Resampling.LANCZOS)
 word=word.resize((round(word.width*wh/word.height),wh),Image.Resampling.LANCZOS)
 gap=16; pad=5
 out=Image.new('RGBA',(mark.width+word.width+gap+2*pad,mh+2*pad))
 out.alpha_composite(mark,(pad,pad));out.alpha_composite(word,(pad+mark.width+gap,pad+(mh-wh)//2))
 out.save(dest)
 return out
light=crop_lockup(media/'logo-transparent.png',media/'logo-horizontal.png')
dark=crop_lockup(media/'logo-light.png',media/'logo-horizontal-light.png')
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
