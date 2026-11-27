# Implementation status — extended V2 staging foundation
Source implementation on design/v2-experience. Not a launch approval. Keep VERDLEAF_PREVIEW=true.
## Implemented in source
- Laravel/PHP/MySQL server-rendered pages, 29 draft families covering 30 historic listings, 11 illustrative draft forms. Catalogue, basic search, filters and pagination; atlas and finder share catalogue.
- Bilingual public landing/catalogue/translated product pages and selected Arabic corporate sections with RTL, conditional reciprocal product/home/catalogue alternates. Buyer/admin UI remains English.
- Approved image upload and attachment to products, variants, entries and homepage hero. Rights and alt text recorded. Actual client photographs and video pending.
- Product form panel transitions, form-specific images and zoom via full image link, basic specs/packaging text, public passport, 2–3 variant comparison, print stylesheet.
- Crop calendar with approved region/year/month/stage; no fabricated schedules.
- Dedicated editorial detail URLs, field notes, interactive six-stage tour, generic company/farm/facility/market/sourcing sections, certificate issuer/number/scope/date/verification and event venue/booth metadata.
- Session Buyer Desk and database-backed quote/sample/document requests; visit/meeting/contact/custom forms. Preview disables real submissions; approved privacy entry required. Expiring revocable shortlist shares contain only variant IDs.
- Admin login, single admin role, product/variant create/edit/publish, crop periods, image library, content create/edit/unpublish, enquiry status, content health and failed notification replay. No production credentials included.
- Staff notification outbox with bounded retry and scheduled command; set VERDLEAF_SALES_EMAIL and real SMTP before testing. Buyer acknowledgments and n8n not integrated.
- Minimal SEO: titles, canonicals, selected hreflang, Organization schema, sitemap, noindex for preview and sensitive/duplicate routes. Content health counts missing fields/expired certifications.
- PHP CI tests; browser test workflow with desktop/mobile screenshots, interaction checks and horizontal overflow assertion added. Inspect actual workflow results separately.
## Still outstanding
Verified company content and media, high-fidelity human-reviewed design, video/360 (360 deferred), full native Arabic buyer flow, typo-tolerant Arabic synonym search, independent atlas design, full technical spec unit schema/comparison, certificate PDF file scopes and private document permissions, complete admin role separation/audit and analytics, filtered print catalogue, full data import/export, buyer email confirmations, CRM/n8n, search reporting, migration redirects, performance/accessibility audit, dependency locks, real MySQL runtime and Hostinger staging. Private batch traceability/customer portal are later phases. 
## Local install
PHP 8.2+, Composer 2, MySQL/MariaDB and Laravel required extensions. Run composer install; copy .env.example to .env and configure DB; php artisan key:generate; php artisan migrate --seed; php artisan verdleaf:admin admin@example.test; php artisan serve. Open localhost:8000. Use vendor/bin/phpunit. For browser test, use a dedicated seeded local database, npm install, npx playwright install chromium, npm run test:browser. Workflow stores visual-evidence screenshots. Generate lockfiles after dependency resolution. Do not use production data in tests. 
For Hostinger, verify exact plan and set document root public/; protect staging with authentication. Schedule php artisan schedule:run each minute when supported. See RELEASE-CHECKLIST.md. No deployment authorized by this branch.
