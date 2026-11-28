import {test,expect} from '@playwright/test';
import fs from 'node:fs';
test('public routes, responsive layout and interactions',async({page},testInfo)=>{
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 fs.mkdirSync('visual-evidence',{recursive:true});
 async function check(path,name){
  const response=await page.goto(path);expect(response.status()).toBe(200);
  await expect(page.locator('h1')).toBeVisible();
  const fits=await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth+2);
  expect(fits,'page should not overflow horizontally: '+path).toBeTruthy();
  await page.screenshot({path:'visual-evidence/'+testInfo.project.name+'-'+name+'.png',fullPage:true,animations:'disabled'});
 }
 await check('/','home');await check('/products','catalogue');await check('/products/hibiscus','product');
 const form=page.getByRole('button',{name:'Crushed',exact:true});await form.click();
 await expect(form).toHaveAttribute('aria-pressed','true');
 await page.locator('.variant-panel:not([hidden])').getByRole('button',{name:'Add to Buyer Desk +'}).click();
 await expect(page.getByRole('heading',{name:"Let's talk sourcing."})).toBeVisible();
 await page.getByRole('button',{name:'Create shareable shortlist'}).click();await expect(page.getByRole('link',{name:'Open shared shortlist'})).toBeVisible();
 await page.screenshot({path:'visual-evidence/'+testInfo.project.name+'-desk.png',fullPage:true,animations:'disabled'});
 await check('/inside-verdleaf','tour');await page.getByRole('button',{name:'03 / Processing'}).click();await expect(page.locator('#tour-3')).toBeVisible();
 await check('/crop-calendar','calendar');await check('/certifications','certifications');await check('/exhibitions','exhibitions');await check('/visits','visits');await check('/ar','arabic');
 expect(errors).toEqual([]);
});
test('reduced motion',async({page})=>{
 await page.emulateMedia({reducedMotion:'reduce'});await page.goto('/');
 await expect(page.locator('h1')).toBeVisible();
 expect(await page.locator('.hero-copy').evaluate(el=>getComputedStyle(el).animationName)).toBe('none');
});
