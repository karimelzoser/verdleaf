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
  assert.equal(await page.locator('#cards .product-card').count(),12);await page.locator('#showAll').click();
  assert.equal(await page.locator('#cards .product-card,#textCatalogue .text-list a').count(),44);
  await page.locator('#cards img').evaluateAll(imgs=>Promise.all(imgs.map(async i=>{i.loading='eager';try{await i.decode()}catch{}})));assert.deepEqual(await page.locator('#cards img').evaluateAll(imgs=>imgs.filter(i=>!i.naturalWidth).map(i=>i.src)),[]);
  const records=await page.evaluate(()=>window.VERDLEAF_CATALOGUE.map(p=>({id:p.id,name:p.name,category:p.category,forms:p.forms})));
  for(const p of records){
   await page.evaluate(id=>location.hash='product/'+id,p.id);await page.locator('#product:not([hidden]) .product-info h1').waitFor();
   await page.waitForFunction(name=>document.querySelector('#product .product-info h1')?.textContent===name,p.name);
   assert.equal(await page.locator('#product .forms button').count(),p.forms.length);
  }
  await page.goto('http://localhost:8080/#catalogue/herbs');await page.locator('#catalogue:not([hidden])').waitFor();
  assert.equal(await page.locator('#resultCount').innerText(),records.filter(p=>p.category==='herbs').length+' ingredients');
  await page.locator('#categoryTabs [data-category=""]').click();
  await page.locator('#application').selectOption('Tea & infusions');assert((await page.locator('#resultCount').innerText()).startsWith(String((await page.evaluate(()=>window.VERDLEAF_CATALOGUE.filter(p=>p.applications.includes('Tea & infusions')).length)))));
  await page.locator('#application').selectOption('');
  await page.locator('#formFilter').selectOption('Ground');assert((await page.locator('#resultCount').innerText()).startsWith(String(await page.evaluate(()=>window.VERDLEAF_CATALOGUE.filter(p=>p.forms.includes('Ground')).length))));
  await page.locator('#formFilter').selectOption('');
  await page.evaluate(()=>{window.print=()=>{window.printedCount=document.querySelectorAll('#cards .product-card,#textCatalogue .text-list a').length}});
  await page.locator('#printCatalogue').click();assert.equal(await page.evaluate(()=>window.printedCount),44);
  await page.locator('#search').fill('Hibiscus');assert.equal(await page.locator('#cards .product-card').count(),1);
  await page.locator('#cards .image-link').click();await page.locator('#product .forms button').nth(1).click();await page.locator('#addItem').click();assert.equal(await page.locator('#count').innerText(),'1');
  await page.screenshot({path:'visual-evidence/'+mode+'-product.png',fullPage:true,animations:'disabled'});
  await page.evaluate(()=>location.hash='product/14');await page.waitForFunction(()=>document.querySelector('#product .product-info h1')?.textContent==='Chamomile');await page.locator('#addItem').click();await page.locator('.product-info a[href="#desk"]').click();
  assert.equal(await page.locator('#count').innerText(),'2');await page.locator('#items [data-qty]').first().fill('1500');await page.locator('#items [data-purpose="sample"]').first().check();
  await page.locator('#compare').click();assert.equal(await page.locator('#comparePanel table thead th').count(),3);
  await page.locator('#items [data-qty]').first().fill('0');await page.locator('#reviewRequest').click();assert((await page.locator('#requestPreview').innerText()).includes('Enter valid quantities'));
  await page.locator('#items [data-qty]').first().fill('1500');await page.locator('#destination').fill('Hamburg');await page.locator('#terms').selectOption('CIF');await page.locator('#reviewRequest').click();assert((await page.locator('#requestPreview a').getAttribute('href')).startsWith('mailto:info@verdleaf.com'));
  await page.screenshot({path:'visual-evidence/'+mode+'-desk.png',fullPage:true,animations:'disabled'});
  await page.reload();await page.locator('#desk:not([hidden])').waitFor();assert.equal(await page.locator('#count').innerText(),'2');assert.equal(await page.locator('#items [data-qty]').first().inputValue(),'1500');
  await page.evaluate(()=>{Object.defineProperty(navigator,'clipboard',{configurable:true,value:{writeText:async x=>{window.shareTest=x}}})});await page.locator('#share').click();await page.waitForFunction(()=>window.shareTest?.includes('#share/'));const share=await page.evaluate(()=>window.shareTest);assert(!share.includes('1500'));const other=await browser.newPage({viewport});await other.goto(share);await other.locator('#desk:not([hidden])').waitFor();assert.equal(await other.locator('#count').innerText(),'2');await other.close();
  await page.locator('#items [data-remove]').first().click();assert.equal(await page.locator('#count').innerText(),'1');
  await snap('company','company');await page.locator('#journey article').nth(2).click();assert((await page.locator('#journeyPanel h3').innerText()).includes('Commercial form'));
  await snap('calendar','calendar');assert.equal(await page.locator('#seasonTable tbody tr').count(),3);await snap('meet','meeting');await page.locator('#meetingNotes').fill('Synthetic browser test');await page.locator('#meetingForm button').click();assert((await page.locator('#meetingResult a').getAttribute('href')).startsWith('mailto:info@verdleaf.com'));await snap('quality','quality');await snap('contact','contact');
  if(mode==='mobile')await page.locator('#menuToggle').click();await page.locator('#language').click();assert.equal(await page.locator('html').getAttribute('dir'),'rtl');
  await page.goto('http://localhost:8080/?lang=ar#catalogue');await page.locator('#catalogue:not([hidden])').waitFor();assert.equal(await page.locator('html').getAttribute('dir'),'rtl');await page.locator('#search').fill('بابونج');assert.equal(await page.locator('#cards .product-card').count(),1);
  await page.screenshot({path:'visual-evidence/'+mode+'-arabic.png',fullPage:true,animations:'disabled'});
  await page.goto('http://localhost:8080/review.html');assert.equal(await page.locator('.review-list a').count(),44);await page.screenshot({path:'visual-evidence/'+mode+'-review-index.png',fullPage:true,animations:'disabled'});assert.deepEqual(errors,[]);await page.close();
 }
 console.log('Brand, transparent logo, motion, slideshow, reduced motion, responsive layout and buyer flow passed');
}finally{await browser.close();server.kill()}
