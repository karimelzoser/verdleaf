# Implementation status — foundation milestone
This branch preserves the V2 plan and design study and introduces a Laravel 12 / PHP 8.2+ application with MySQL-compatible migrations. Do not deploy to the live domain yet.
## Implemented in source
- Server-rendered homepage, navigation, catalogue/atlas/finder routes, product pages and general public sections.
- 29 draft product families (30 prior listings including two dried lemon forms), 11 draft example forms, no invented dates or lab values. Actual catalogue export still needed.
- Search and filters by name, alias text, form and approved application; pagination. Not yet typo-tolerant normalized Arabic search.
- Product passport view, comparison (2–3 variants), anonymous session shortlist, RFQ/sample/document purposes, quantity validation, database persistence, private receipt, basic staff request status. Preview blocks real submissions; production additionally needs approved privacy entry.
- Admin sign-in and admin-only role, create and edit products and forms, create/edit/unpublish generic content, crop period approval, manage request statuses. Other role separation and audit trail not yet built.
- Approved crop calendar and print view, source-backed dates, empty state. Generic corporate content routes for certificates, visits, exhibitions, farms, facilities, markets, sourcing, industries, downloads and articles.
- Original SVG field/specimen illustrations (not actual company photographs), responsive CSS, accessible menu, motion preferences, print styles. No claim of high-fidelity visual signoff.
- Staging noindex and robots blocking, published-only product sitemap, minimal SEO. Production canonical, hreflang, structured data, redirects and full SEO audit remain.
- Automated test source and CI workflow included; execution must be confirmed separately.
## Not yet implemented / dependencies
Real imagery and media library; per-variant photographic transitions and zoom; full Arabic localization/RTL; comprehensive certificate metadata/PDF scopes, visit and exhibition meeting forms, document permissions, individual event/article URLs, custom sourcing, filtered print/share, advanced packaging/technical specs, full guided video tour, live seasonal content, notification outbox and SMTP/n8n, multiple admin roles/audit/SEO dashboard, analytics/consent, production batch traceability, actual content approval and hosting verification. The generic sections are templates awaiting original content, not complete modules. Further development must follow V2 acceptance criteria. 
## Local install (not deployment)
Requirements PHP 8.2+, Composer 2, MySQL or MariaDB, required Laravel PHP extensions. Run `composer install`, copy .env.example to .env, set database credentials, `php artisan key:generate`, `php artisan migrate --seed`, `php artisan verdleaf:admin admin@example.test`, `php artisan serve`. Open localhost:8000. The draft catalogue appears in preview. Keep preview on and staging behind authentication until release gates pass. Use `vendor/bin/phpunit` for automated tests. No composer.lock yet; generate/commit after a successful dependency resolution, validate supported versions before release. Set public/ as document root, never repository root. No real buyer data in public repository. 
## Validation
Source changes committed; PHP runtime dependencies not installed in this chat environment (Composer absent, network clone unavailable). Browser testing and actual test execution not yet verified. Track results in PR #1; no merge/deployment until reviewed.
