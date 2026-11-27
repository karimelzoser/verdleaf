# Engineering, backlog and release gates — v2.0
## Architecture
Proposed Laravel + supported PHP + MySQL/MariaDB, Blade server-rendered pages, Vite assets, bespoke CSS/Tailwind utilities and selective Alpine/React islands where complexity merits. No Node runtime required for public SSR; build JS locally/CI. Filament or custom admin after dependency/hosting review. Composer lock, migrations, factory/seed for test only, automated tests. On Hostinger verify plan, PHP version/extensions, SSH/Composer, document-root control, DB/storage/upload limits, cron, outbound SMTP, SSL, backups and deployment constraints. If document root cannot safely expose only public/, use approved safe deployment layout or upgrade; never expose .env/vendor/private storage. If hosting cannot meet Laravel requirements, evaluate custom WordPress theme/plugin with same content model before committing. GitHub Pages cannot run PHP/MySQL.

## Proposed code layout
app/Models, app/Http/Controllers, app/Http/Requests, app/Policies, app/Services, database/migrations, database/seeders, resources/views/{layouts,components,pages,products}, resources/js/{motion,catalogue,rfq}, resources/css, lang/{en,ar}, routes/{web,console}, tests/{Feature,Unit}, public/build, docs/. Private uploads outside public document root; public media only after approval.

## Data relationships
categories and translations -> products and translations -> variants (SKU unique; form, grade, status); product_calendar (region/year/month/stage); variant_specs (parameter, unit, limit/method, source); packaging_options; product_applications pivot; product_media/variant_media; documents and document-product/variant relationships; certificates and certificate scopes; facilities/farms/markets; events and visits with galleries; articles/translations; redirects and SEO metadata; company settings; companies/contacts -> enquiries/RFQs -> RFQ items; sample/visit requests; activity log; users/roles; outbox for retryable notifications. Foreign keys, indexes on slug/status/category/SKU and search; transactions for submissions and item persistence; soft archive where appropriate. Store audit trail and data approval status separately from publication. No personal data or production dumps committed.

## RFQ and enquiry
Anonymous visitor selects one or more approved product variants, quantities (positive, unit-aware), destination, port, packaging, terms, delivery date, notes; contact/company/email/phone and consent. Optional private specification attachments with size/type restrictions and malware handling. Server validates IDs, quantities, input length, honeypot/rate limit, CSRF, idempotency token, stores transactionally; shows actual reference number on success. User can correct errors; preserve non-sensitive basket; no fake success. Send confirmation, notify assigned sales team, retry bounded failures through outbox + cron and expose manual replay. Staff statuses new/qualified/quoted/negotiating/won/lost, owner, history, source and follow-up. Sample/visit/exhibition meetings use separate request types, no automatic booking. n8n/CRM optional signed webhook, protected credentials, retry and dead-letter path. WhatsApp link or opt-in messaging only as approved.

## Admin and security
Roles: super-admin, editor, export sales, quality reviewer, analytics viewer. Policies enforce per-record authorization; editor draft -> quality/export approval -> published; revision/history; scheduled publishing/expiry alerts; dashboards and CSV export respecting permissions. Manage categories, products, variants, crop calendar, specs, certs, media, farms/facilities, industries, markets, events, visits, articles, documents, redirects, settings, enquiries. Search/filter/pagination, bulk imports with dry run and error report. Auth with secure session, password hashing, optional MFA, login rate limits, CSRF, output escaping, parameterized queries, validation and least privilege DB. Upload allowlist and server-generated filenames, size caps, private access, no executable upload. TLS, secure cookies, CSP tailored to assets, backups, retention and deletion policies, audit logs without secrets, no production debug. Public repo: .gitignore secrets/env/vendor/node_modules; .env.example with placeholders. No actual private documentation in public GitHub.

## Delivery backlog
| ID | Work package | Depends on | Acceptance |
|---|---|---|---|
| P01 | Inventory, client brief, source ledger, URL crawl/export, hosting audit | client access | verified inputs and gaps recorded |
| P02 | Brand assets, design tokens, copy interviews and storyboard | P01 | original desktop/mobile design approved |
| P03 | Laravel skeleton, CI, DB and roles | P01 | local install, migrations and auth tests pass |
| P04 | SSR shell, navigation, languages, footer | P02/P03 | keyboard/mobile/RTL/no-JS navigation |
| P05 | Homepage and signature motion | P04 | visual sign-off, reduced motion and performance |
| P06 | CMS product/variant schema and import | P03 | all 30 legacy entries reconciled, drafts only |
| P07 | Catalogue, filters, product pages, variants, TDS | P06/P04 | search/form states, SSR and unique metadata |
| P08 | Calendar and agriculture/production pages | P06 | approved schedules; table fallback |
| P09 | Quality/certification and downloads | P03/P04 | evidence and expiry, permission tests |
| P10 | Events, visits, markets, industries, insights | P04 | editable, real data, consent and CTA |
| P11 | RFQ, sample, visit and meeting requests | P07/P03 | end-to-end server validation and notification tests |
| P12 | Admin dashboards, exports, workflow | P03/P06/P11 | role matrix and approval tests |
| P13 | SEO, redirects, analytics, content migration | P01/P07/P10 | crawl, hreflang, canonical, sitemap checks |
| P14 | Media pipeline and performance | P05/P07 | optimized responsive images and video |
| P15 | Security/accessibility/browser/visual QA | P04–P14 | documented regression matrix |
| P16 | Staging, UAT, backup/rollback, release | P15 | client sign-off and hosting proof |
| P17 | Phase 2 expansion/CRM/advanced reporting | release | separate approved scope |
| P18 | Phase 3 portal/AI/traceability | P17 | separate privacy and feasibility approval |

## Test matrix and release gates
Unit: data validation, schedule mapping, permissions, expiry, RFQ calculation. Feature: public route/status/SSR, filters/pagination, variant links, EN/AR, drafts inaccessible, auth, RFQ persistence/duplicate protection, upload reject, notifications retry, redirects/sitemap. Browser: Chromium/Firefox/WebKit, mobile touch, keyboard, reduced motion, no JS, slow network. Screenshot baselines at 390/768/1024/1440; compare every page and error/empty state. Accessibility manual keyboard, screen reader and contrast + automated scan. SEO crawl staging, check broken links, no accidental production noindex, canonical/hreflang, structured data; redirect test old URLs. Performance lab target 90+ mobile Lighthouse, LCP <=2.5s, CLS <=0.1, INP <=200ms field p75 after launch; target initial JS <=150KB compressed excluding deferred modules, optimized hero poster <=250KB where practical. No release with high severity security, accessibility, data loss, visual, content or conversion defects. Evidence: screenshots, test logs, approved content ledger and deployment checklist.

## Hosting/release
Keep main stable, use feature branches and PRs after this initial empty-repo planning commit. CI lint, static analysis, unit/feature tests, build assets, browser tests; no deployment on PR. Provision protected staging with separate database, secrets in environment, no indexing. Validate backup/restore, migration dry run and previous version rollback, confirm email deliverability SPF/DKIM/DMARC as appropriate. On approval, snapshot legacy site/DB/media, deploy artifact to protected application path, point public root to Laravel public, migrate with backup, cache config/routes as suitable, verify cron/SMTP, HTTPS, smoke tests and redirects, submit sitemap, monitor logs and conversion. Never overwrite live WordPress without signed release. Document restoration and owner handover.

## V2 implementation dependencies and acceptance
The v1 backlog is retained but P07 now includes typo-tolerant bilingual search, variant zoom and compare; P08 adds seasonal content; P10 includes sourcing evidence and tour; P11 becomes unified Buyer Desk; P13 adds SEO health. Insert V01–V09 before P15/16 (unless marked future). P02 must pass prototype approval before implementation. 
| ID | Work package | Depends | Acceptance |
|---|---|---|---|
| V01 | Botanical Atlas & bilingual synonyms | P06,P07 | Search by common/botanical name, approved Arabic alias; index links canonical product; no duplicate pages |
| V02 | Ingredient Finder | P06,P07 | application/form/grade filters; approved matches, empty state custom request, no false claims |
| V03 | Compare, zoom, packaging explorer | P07 | max 3 variants, units normalized, accessible controls, unknown values explicit |
| V04 | Public passport and filtered print | P06,P09 | current approved facts, scope/expiry, QR canonical, printed media and revision date |
| V05 | Buyer Desk combined requests | V02,P11 | no mandatory account, item-specific purposes, server review/validation, one reference/transaction, no duplicate |
| V06 | Shareable shortlist | V05 | unguessable expiring token, read-only, no personal data, revoke, noindex; print alternative |
| V07 | Field Notes and guided tour | P08,P10 | approved media, dates, accessibility and static fallback; no fake freshness |
| V08 | Responsible sourcing and case studies | P09,P10 | evidence scope/date, approval/consent, no unsupported claims |
| V09 | SEO health and QR campaigns | P13 | actionable reports, test links and privacy-safe metrics |
| V10 | Private batch passport & portal (future) | P18 | verified lot system, tenant authorization, signed time-limited docs, audit and retention |

Extend data model: search_aliases(product_id,locale,normalized_alias), applications and approved application-variant pivot, comparison via approved specs (no private data), sourcing_requests with request_items and per-item request_purposes (quote/sample/document/custom), requested_document_types, share_tokens(token_hash,expiry,revoked_at,non-sensitive item snapshot), packaging_variant relation, field_notes and scheduling, tour_stops and approved media, sourcing_evidence with scope/issuer/date/approval, SEO_issues with owner/status. Public passport is computed view, not duplicated truth; future lot tables and separate permission policies. 
Buyer Desk: maintain local shortlist IDs and non-sensitive quantities only; contacts entered at final step, no localStorage PII; server revalidates all approved SKU/form IDs, rate limit, CSRF, idempotency and transaction; email receipt via outbox; request any/all purposes; custom sourcing permits free text with clear validation; staff route purposes internally. Share never includes identity or attachments. Compare/finder require accessible HTML baseline and progressive enhancement. 
QA additions: search synonym/typo/Arabic test set; invalid/expired/revoked token; compare 0/1/3/4 and incompatible units; missing documents; mixed request atomicity and deduplication; no invented specs; QR path; print layout; tour reduced motion, no media and no JS; accessibility of zoom; stale seasonal content fallback; SEO duplicate and leakage tests. Prototype files are design studies only: no PHP/SQL, authentication, CRM or real request submissions. Browser visual approval must precede production implementation.
