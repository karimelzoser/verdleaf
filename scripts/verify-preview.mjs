import {chromium} from 'playwright';
import {spawn} from 'node:child_process';
import fs from 'node:fs';
import assert from 'node:assert/strict';
const server=spawn('python',['-m','http.server','8080','--directory','preview'],{stdio:'ignore'});
const browser=await chromium.launch({headless:true});
fs.mkdirSync('visual-evidence',{recursive:true});
try{
 for(let i=0;i<20;i++){try{if((await fetch('http://localhost:8080')).ok)break}catch{}await new Promise(r=>setTimeout(r,250))}
 for(const viewport of [{width:1440,height:900},{width:390,height:844}]){
  const page=await browser.newPage({viewport,deviceScaleFactor:1,reducedMotion:'reduce'});
  const errors=[];page.on('pageerror',e=>errors.push(e.message));
  const mode=viewport.width===1440?'desktop':'mobile';
  async function snap(view,name){
   await page.goto('http://localhost:8080/#'+view);
   await page.locator('.view:not([hidden])').waitFor();
   await page.waitForTimeout(250);
   await page.locator('.view:not([hidden]) img').evaluateAll(imgs=>Promise.all(imgs.map(async i=>{i.loading='eager';try{await i.decode()}catch{}})));
   assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2),'overflow '+mode+' '+view);
   await page.screenshot({path:'visual-evidence/'+mode+'-'+name+'.png',fullPage:true,animations:'disabled'});
  }
  await snap('home','homepage');
  await snap('catalogue','catalogue');
  assert.equal(await page.locator('#cards .product-card').count(),44);
  const manifest=await page.evaluate(()=>window.VERDLEAF_MEDIA);
  console.log('media',mode,Object.keys(manifest.products||{}).length);
  const broken=await page.locator('#cards img').evaluateAll(imgs=>imgs.filter(i=>!i.naturalWidth).map(i=>i.src));
  assert.deepEqual(broken,[],'broken product photos');
  await page.locator('#search').fill('Hibiscus');
  assert.equal(await page.locator('#cards .product-card').count(),1);
  await page.locator('#cards .image-link').click();
  await page.locator('#addItem').waitFor();
  await page.locator('#addItem').click();
  assert.equal(await page.locator('#count').innerText(),'1');
  await page.screenshot({path:'visual-evidence/'+mode+'-product.png',fullPage:true,animations:'disabled'});
  await page.locator('.product-info a[href="#desk"]').click();
  await page.locator('#reviewRequest').click();
  assert(await page.locator('#requestPreview a').getAttribute('href').then(s=>s.startsWith('mailto:info@verdleaf.com')));
  await page.screenshot({path:'visual-evidence/'+mode+'-desk.png',fullPage:true,animations:'disabled'});
  await snap('company','company');
  await snap('quality','quality');
  await snap('contact','contact');
  if(mode==='mobile')await page.locator('#menuToggle').click();
  await page.locator('#language').click();
  assert.equal(await page.locator('html').getAttribute('dir'),'rtl');
  await page.evaluate(()=>location.hash='catalogue');
  await page.locator('#cards .product-card').first().waitFor();
  await page.locator('#search').fill('بابونج');
  assert.equal(await page.locator('#cards .product-card').count(),1);
  await page.screenshot({path:'visual-evidence/'+mode+'-arabic.png',fullPage:true,animations:'disabled'});
  assert.deepEqual(errors,[]);
  await page.close();
 }
 console.log('Desktop and mobile interactive preview checks passed');
}finally{await browser.close();server.kill()}
