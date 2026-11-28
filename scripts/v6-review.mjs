import {chromium} from 'playwright';
import {spawn} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs';
const server=spawn('python',['-m','http.server','8080','--directory','preview'],{stdio:'ignore'});const browser=await chromium.launch();fs.mkdirSync('visual-evidence',{recursive:true});
try{for(let i=0;i<25;i++){try{if((await fetch('http://localhost:8080')).ok)break}catch{}await new Promise(r=>setTimeout(r,250))}
for(const width of [320,390,768,1024,1440,1920]){
 const page=await browser.newPage({viewport:{width,height:width<=820?844:900},reducedMotion:'reduce'});const errors=[];page.on('pageerror',e=>errors.push(e.message));
 const go=async hash=>{await page.goto('http://localhost:8080/index.html#'+hash);await page.locator('.view:not([hidden])').waitFor();await page.waitForTimeout(150);assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2),'overflow '+width+' '+hash)};
 await go('home');await page.screenshot({path:'visual-evidence/home-'+width+'.png'});
 for(const v of ['catalogue','product/14','company','quality','calendar','meet','solutions','desk','contact']){
  await go(v);const h=await page.locator('.view:not([hidden]) h1').first().evaluate(el=>parseFloat(getComputedStyle(el).fontSize));assert(h<=45,'oversized heading '+v+' '+h);
  if(v==='catalogue'){assert(await page.locator('#search').evaluate(e=>e.getBoundingClientRect().bottom<innerHeight));await page.locator('#search').fill('Hibiscus');assert.equal(await page.locator('#cards .product-card').count(),1);await page.locator('#search').fill('');}
  if(v==='product/14'){assert(await page.locator('#addItem').isVisible());if(width>=1024)assert(await page.locator('#addItem').evaluate(e=>e.getBoundingClientRect().bottom<innerHeight));await page.locator('#addItem').click()}
  if([390,1440].includes(width)){await page.locator('.view:not([hidden]) img').evaluateAll(imgs=>Promise.all(imgs.map(async i=>{i.loading='eager';try{await i.decode()}catch{}})));const broken=await page.locator('.view:not([hidden]) img').evaluateAll(imgs=>imgs.filter(i=>!i.naturalWidth).map(i=>i.src));assert.deepEqual(broken,[]);await page.screenshot({path:'visual-evidence/'+v.replace('/','-')+'-'+width+'.png'});}
 }
 await page.goto('http://localhost:8080/admin-demo.html#products');await page.locator('#records').waitFor();assert.equal(await page.locator('#records tbody tr').count(),12);await page.locator('[data-page="1"]').click();assert.equal(await page.locator('#records tbody tr').count(),12);await page.locator('#search').fill('Chamomile');assert.equal(await page.locator('#records tbody tr').count(),1);await page.locator('[data-edit]').click();await page.locator('#close').click();assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2));if([390,1440].includes(width))await page.screenshot({path:'visual-evidence/admin-products-'+width+'.png'});
 await page.goto('http://localhost:8080/admin-demo.html#overview');if([390,1440].includes(width))await page.screenshot({path:'visual-evidence/admin-overview-'+width+'.png'});
 await page.goto('http://localhost:8080/products/chamomile/');assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2));if([390,1440].includes(width))await page.screenshot({path:'visual-evidence/profile-'+width+'.png'});assert.deepEqual(errors,[]);console.log('PASS compact UX '+width+'px');await page.close()
}
const p=await browser.newPage();await p.goto('http://localhost:8080/');const coverage=await p.evaluate(()=>({total:window.VERDLEAF_CATALOGUE.length,withImage:window.VERDLEAF_CATALOGUE.filter(x=>window.VERDLEAF_MEDIA.products[x.key]).length,references:Object.keys(window.VERDLEAF_MEDIA.references||{}).length}));console.log('MEDIA COVERAGE',JSON.stringify(coverage));await p.close();
}finally{await browser.close();server.kill()}
