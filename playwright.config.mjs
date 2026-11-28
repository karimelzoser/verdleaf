import { defineConfig, devices } from '@playwright/test';
export default defineConfig({
 testDir:'./tests/browser', timeout:30000, retries:0, reporter:'list',
 use:{baseURL:'http://127.0.0.1:8000',trace:'retain-on-failure',screenshot:'only-on-failure'},
 projects:[{name:'desktop',use:{...devices['Desktop Chrome'],viewport:{width:1440,height:900}}},{name:'mobile',use:{...devices['iPhone 13'],defaultBrowserType:'chromium'}}],
 webServer:{command:'php artisan serve --host=127.0.0.1 --port=8000',url:'http://127.0.0.1:8000/up',reuseExistingServer:false,timeout:30000}
});
