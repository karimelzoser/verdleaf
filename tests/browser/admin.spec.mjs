import {test,expect} from '@playwright/test';
import {execFileSync} from 'node:child_process';
import fs from 'node:fs';
test('staff MFA and dashboard on an isolated synthetic database',async({page},testInfo)=>{
 const email='browser-'+testInfo.project.name+'@example.test';
 const output=execFileSync('php',['tests/browser/provision-test-admin.php',email],{encoding:'utf8'}).trim();
 const secretCode=output.split('\n').at(-1);
 await page.goto('/admin');await expect(page).toHaveURL(/\/login$/);
 await page.getByLabel('Email',{exact:true}).fill(email);
 await page.getByLabel('Password',{exact:true}).fill('browser-test-only-password');
 await page.getByRole('button',{name:'Continue'}).click();
 await expect(page).toHaveURL(/\/login\/verify$/);
 await page.getByLabel('Verification code').fill(secretCode);
 await page.getByRole('button',{name:'Verify & sign in'}).click();
 await expect(page.getByRole('heading',{name:'Operations overview'})).toBeVisible();
 fs.mkdirSync('visual-evidence',{recursive:true});
 await page.screenshot({path:'visual-evidence/'+testInfo.project.name+'-admin-dashboard.png',fullPage:true,animations:'disabled'});
 const modules=[['Catalogue & content','operations'],['Media library','media'],['Documents','documents'],['Catalogue migration','catalogue-import'],['Content health','seo'],['SEO & redirects','growth'],['Security & access','security']];
 for(const [label,slug] of modules){
  const menu=page.locator('.admin-menu-toggle');if(await menu.isVisible()&&await menu.getAttribute('aria-expanded')!=='true')await menu.click();
  const response=await Promise.all([page.waitForNavigation({waitUntil:'domcontentloaded'}),page.getByRole('link',{name:label,exact:true}).click()]);
  expect(response[0]?.status()).toBe(200);
  await expect(page.locator('#main')).toBeVisible();
  expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2)).toBeTruthy();
  await page.screenshot({path:'visual-evidence/'+testInfo.project.name+'-admin-'+slug+'.png',fullPage:true,animations:'disabled'});
 }

 await expect(page.getByRole('heading',{name:'Security & access'})).toBeVisible();
 await page.screenshot({path:'visual-evidence/'+testInfo.project.name+'-admin-security.png',fullPage:true,animations:'disabled'});
 if(await page.locator('.admin-menu-toggle').isVisible()&&await page.locator('.admin-menu-toggle').getAttribute('aria-expanded')!=='true')await page.locator('.admin-menu-toggle').click();await page.getByRole('button',{name:'Sign out'}).click();await page.goto('/admin');await expect(page).toHaveURL(/\/login$/);
});
