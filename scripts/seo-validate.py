"""Validate preview SEO graph and prepare a separate, explicitly approved production candidate."""
import argparse, json, pathlib, re, shutil, urllib.parse, xml.etree.ElementTree as ET
from bs4 import BeautifulSoup
P=argparse.ArgumentParser()
P.add_argument('root',type=pathlib.Path)
P.add_argument('--production-output',type=pathlib.Path)
P.add_argument('--approved-domain')
P.add_argument('--approve-indexing',action='store_true')
args=P.parse_args(); root=args.root.resolve()
m=json.loads((root/'seo-manifest.json').read_text()); base=m['previewBase']; pages=m['pages']; paths={p['path'] for p in pages}; errors=[]; seen=set(); descriptions=set(); titles=set()
def check(ok,msg):
 if not ok:errors.append(msg)
def canonical(path):return base+path
def read(path):return BeautifulSoup((root/path/'index.html').read_text(),'html.parser')
def local_path(href,current):
 u=urllib.parse.urlsplit(urllib.parse.urljoin(base+current,href))
 b=urllib.parse.urlsplit(base)
 if u.netloc!=b.netloc or not u.path.startswith(b.path):return None
 return urllib.parse.unquote(u.path[len(b.path):])
def exists(target):
 if target is None:return True
 p=(root/target).resolve()
 if not p.is_relative_to(root):return False
 return p.is_file() or (p/'index.html').is_file()
graph={}
for p in pages:
 path=p['path']; s=read(path); loc=p['locale']; c=s.select_one('link[rel=canonical]'); desc=s.select_one('meta[name=description]'); robots=s.select_one('meta[name=robots]')
 check(c and c.get('href')==canonical(path),f'{path} canonical')
 check(robots and 'noindex' in robots.get('content',''),f'{path} preview indexing guard')
 check(s.html.get('lang')==loc,f'{path} html language')
 if loc=='ar':check(s.html.get('dir')=='rtl',f'{path} RTL')
 title=s.title.get_text(strip=True) if s.title else ''
 d=desc.get('content','') if desc else ''
 check(bool(title and d),f'{path} missing metadata')
 check(title not in titles,f'{path} duplicate title');titles.add(title)
 check(d not in descriptions,f'{path} duplicate description');descriptions.add(d)
 check(title==p['title'] and d==p['description'],f'{path} manifest mismatch')
 stem=path[3:] if path.startswith('ar/') else path
 alternates={x.get('hreflang'):x.get('href') for x in s.select('link[rel=alternate][hreflang]')}
 check(alternates=={'en':base+stem,'ar':base+'ar/'+stem,'x-default':base+stem},f'{path} hreflang pair')
 check(stem in paths and 'ar/'+stem in paths,f'{path} missing language counterpart')
 for prop in ['og:title','og:description','og:url','og:image']:
  check(bool(s.select_one(f'meta[property="{prop}"]')),f'{path} {prop}')
 for tag in s.select('script[type="application/ld+json"]'):
  try:
   data=json.loads(tag.string); types={x.get('@type') for x in data['@graph']}
   check({'Organization','WebPage','BreadcrumbList'}<=types,f'{path} schema missing')
   if p['group']=='product':
    prod=next(x for x in data['@graph'] if x.get('@type')=='Product')
    check(not any(k in prod for k in ['offers','review','aggregateRating']),f'{path} unsupported offers or reviews')
  except Exception as e:errors.append(f'{path} JSON-LD: {e}')
 if p['group']!='home' or loc=='ar':check(len(s.select('h1'))==1,f'{path} H1 count')
 graph[path]=set()
 for a in s.select('a[href],img[src],link[rel=stylesheet][href]'):
  target=local_path(a.get('href') or a.get('src'),path)
  check(exists(target),f'{path} broken local URL {target}')
  if a.name=='a' and target in paths:graph[path].add(target)
  if a.name=='img':check(bool(a.get('alt')),f'{path} missing image alt')
queue=[''];reachable=set()
while queue:
 n=queue.pop()
 if n in reachable:continue
 reachable.add(n);queue.extend(graph.get(n,set())-reachable)
check(reachable==paths,f'unreachable pages: {sorted(paths-reachable)}')
check(len(pages)==len(paths), 'Duplicate manifest routes')
for p in ['admin-demo.html','review.html','photography.html']:
 s=BeautifulSoup((root/p).read_text(),'html.parser')
 check('noindex' in s.select_one('meta[name=robots]').get('content',''),f'{p} missing noindex')
if errors:
 print('\n'.join(errors));raise SystemExit(f'FAIL {len(errors)} SEO checks')
print(f'PASS {len(pages)} SEO pages, {len(titles)} unique titles, {len(descriptions)} unique descriptions, all reachable, local links, metadata, reciprocal hreflang and preview guards')
if args.production_output:
 if not args.approve_indexing or args.approved_domain!=m['productionBase']:raise SystemExit('Explicit approval and exact production base required')
 out=args.production_output.resolve()
 if out==root or out.is_relative_to(root) or root.is_relative_to(out) or out.exists():raise SystemExit('Choose a new separate output directory')
 shutil.copytree(root,out,ignore=shutil.ignore_patterns('.git','.github'))
 newbase=args.approved_domain
 for p in pages:
  path=out/p['path']/'index.html';html=path.read_text().replace(base,newbase)
  html=re.sub(r'<meta name="robots" content="noindex,follow">','<meta name="robots" content="index,follow,max-image-preview:large">',html,count=1)
  path.write_text(html)
 # Keep review tools non-indexable and omit from sitemap. Never upload this candidate automatically.
 ns='http://www.sitemaps.org/schemas/sitemap/0.9';ET.register_namespace('',ns)
 xml=ET.Element('{'+ns+'}urlset')
 for p in pages:ET.SubElement(ET.SubElement(xml,'{'+ns+'}url'),'{'+ns+'}loc').text=newbase+p['path']
 ET.ElementTree(xml).write(out/'sitemap.xml',encoding='utf-8',xml_declaration=True)
 (out/'robots.txt').write_text('User-agent: *\nAllow: /\nSitemap: '+newbase+'sitemap.xml\n')
 m['indexing']='approved candidate';m['canonicalBase']=newbase;(out/'seo-manifest.json').write_text(json.dumps(m,ensure_ascii=False,indent=2))
 print('Production candidate generated in separate directory; nothing deployed or submitted.')
