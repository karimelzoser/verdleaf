import {chromium} from 'playwright';
import {spawn} from 'node:child_process';
import fs from 'node:fs';
import assert from 'node:assert/strict';
const server=spawn('python',['-m','http.server','8080','--directory','preview'],{stdio:'ignore'});const browser=await chromium.launch();
fs.mkdirSync('visual-evidence',{recursive:true});
try{
 for(let i=0;i<20;i++){try{if((await fetch('http://localhost:8080')).ok)break}catch{}await new Promise(r=>setTimeout(r,250))}
 for(const [mode,viewport] of [['desktop',{width:1440,height:900}],['mobile',{width:390,height:844}]]){
  const page=await browser.newPage({viewport,deviceScaleFactor:1});const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto('http://localhost:8080/#home',{waitUntil:'domcontentloaded'});
  await page.screenshot({path:'visual-evidence/'+mode+'-loading.png',animations:'allow'});
  await page.waitForFunction(()=>!document.documentElement.classList.contains('booting'));
  assert(await page.locator('#logo').evaluate(i=>i.complete&&i.naturalWidth>0));
  assert((await page.locator('#logo').getAttribute('src')).includes('transparent'));
  const before=await page.locator('#heroIndex').innerText();await page.locator('#heroNextBtn').click();assert.notEqual(await page.locator('#heroIndex').innerText(),before);
  await page.locator('#heroPause').click();
  const logoBg=await page.locator('#logo').evaluate(i=>getComputedStyle(i).backgroundColor);assert.equal(logoBg,'rgba(0, 0, 0, 0)');
  console.log(mode,'heading font',await page.locator('.hero h1').evaluate(e=>getComputedStyle(e).fontFamily),'body font',await page.locator('body').evaluate(e=>getComputedStyle(e).fontFamily));
  await page.emulateMedia({reducedMotion:'reduce'});
  async function snap(view,name){await page.goto('http://localhost:8080/#'+view);await page.waitForTimeout(450);await page.locator('.view:not([hidden]) img').evaluateAll(imgs=>Promise.all(imgs.map(async i=>{i.loading='eager';try{await i.decode()}catch{}})));assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2),'overflow '+mode+' '+view);await page.screenshot({path:'visual-evidence/'+mode+'-'+name+'.png',fullPage:true,animations:'disabled'})}
  await snap('home','homepage');await snap('catalogue','catalogue');
  assert.equal(await page.locator('#cards .product-card,#textCatalogue .text-list a').count(),44);
  assert.deepEqual(await page.locator('#cards img').evaluateAll(imgs=>imgs.filter(i=>!i.naturalWidth).map(i=>i.src)),[]);
  await page.locator('#search').fill('Hibiscus');assert.equal(await page.locator('#cards .product-card').count(),1);
  await page.locator('#cards .image-link').click();await page.locator('#addItem').click();assert.equal(await page.locator('#count').innerText(),'1');await page.locator('.product-info a[href="#desk"]').click();await page.locator('#reviewRequest').click();assert((await page.locator('#requestPreview a').getAttribute('href')).startsWith('mailto:'));
  await snap('company','company');await page.locator('#journey article').nth(2).click();assert((await page.locator('#journeyPanel h3').innerText()).includes('Commercial form'));
  await snap('quality','quality');await snap('contact','contact');
  if(mode==='mobile')await page.locator('#menuToggle').click();await page.locator('#language').click();assert.equal(await page.locator('html').getAttribute('dir'),'rtl');
  await page.evaluate(()=>location.hash='catalogue');await page.locator('#search').fill('بابونج');assert.equal(await page.locator('#cards .product-card').count(),1);
  await page.screenshot({path:'visual-evidence/'+mode+'-arabic.png',fullPage:true,animations:'disabled'});
  assert.deepEqual(errors,[]);await page.close();
 }
 console.log('Brand, transparent logo, motion, slideshow, reduced motion, responsive layout and buyer flow passed');
}finally{await browser.close();server.kill()}
