# Master implementation plan
Status: approved strategy translated to proposed implementation; individual business claims require client verification. Owner: project team. Repository: karimelzoser/verdleaf.

## 1. Objectives and audience
Turn Verdleaf into a digital visit to the Egyptian company and a B2B sourcing platform. Primary audiences: importers, wholesalers, tea manufacturers, food and beverage companies, ingredient distributors and relevant botanical extraction/cosmetics buyers. Prioritize finding the right product, evaluating evidence and contacting the export team. English first, Arabic RTL from launch. German/French only with validated demand and native editorial capacity.

## 2. Client requirements traceability
| ID | Client request | Deliverable | Acceptance |
|---|---|---|---|
| R01 | مكان للشهادات | Certification centre | Approved PDF, scope, issuer, issue/expiry, verification, linked products |
| R02 | مكان الزيارات | Visits archive and enquiry | Verified stories, dates, photos, consent; request reaches sales |
| R03 | مكان للمعارض | Exhibition archive and calendar | Event details, participation, booth, gallery, meeting request |
| R04 | منتجات أكتر | Searchable catalogue | All 30 legacy listings mapped; expansion only when verified |
| R05 | أشكال مختلفة | Product forms | Variant-specific photographs, SKU, grade, details, RFQ selection |
| R06 | موعد الزراعة والحصاد | Crop calendar | Approved dates by region/crop/year; distinct stock status |
| R07 | أشكال التصدير | Export details | Approved forms, packaging, MOQ, terms, lead time and documents |
| R08 | الظهور على جوجل | SEO program | Indexable pages, redirects, metadata, hreflang, sitemap, GSC |
| R09 | Non-template living design | Original design and motion | Visual review across breakpoints, reduced-motion alternative |
| R10 | Human content | Editorial workflow | Client approval, no unsupported claims, native Arabic review |

## 3. Sitemap and page-level requirements
| Route (English) | Layout/experience | Functionality |
|---|---|---|
| / | Cinematic opening, editorial chapters, crop highlights, company evidence, active events, clear CTA | Product quick search, enquiry links |
| /about | Human company story, real team portraits, dated milestones | Links to facilities, visits and quality |
| /products | Editorial category introduction + procurement catalogue | Search by common/botanical name, filter by category, form, grade, application, harvest; pagination |
| /products/category/{slug} | Category-specific photography and helpful buying copy | Crawlable product links |
| /products/{slug} | Product photography, interactive variants, crop timeline, specs and packaging | Form/grade selection, TDS, RFQ, sample |
| /crop-calendar | Seasonal field story + accessible calendar grid | Filter by crop/region/month; download approved calendar |
| /farms | Field journey, real crop stories, region information | Link to verified crops and visit request |
| /facilities | Production chapter sequence | Document actual drying, cleaning, sorting, milling, testing, warehouse and packing |
| /quality | Evidence-led process | Testing, traceability, batch documentation |
| /quality/certifications | Document library | Search/filter, download approved public PDFs, expiry visibility |
| /export-services | From order to shipment | Packaging, logistics, documentation, terms, enquiry |
| /industries/{slug} | Application-oriented narrative | Relevant verified products; tea, food, beverages, wholesale, extraction and cosmetics where appropriate |
| /markets | Map with verified destinations separate from target markets | Links to substantive market pages |
| /markets/{slug} | Original market sourcing information | Products, documents, enquiry |
| /exhibitions | Upcoming and archived events | Date filter, gallery, meeting CTA |
| /exhibitions/{slug} | Event editorial feature | Participation, hall/booth, dates, original media |
| /visits | Delegation stories and invitation | Filter, visit request |
| /visits/{slug} | Actual visit narrative | Date, purpose, approved names and images |
| /insights | Buyer knowledge centre | Search, topic filters, author/reviewer/date |
| /insights/{slug} | Original technical editorial | Related products, evidence, CTA |
| /downloads | Public resource library | Catalogue, TDS, public certs, company profile |
| /contact | Human contact page | Verified details, departments, form |
| /quote | Multi-item B2B RFQ | Product/form/grade, quantities/units, destination, terms, packaging, dates, contact, private attachment |
| /sample | Sample request | Product/form/grade, use case, destination and contact |
| /visit-request | Business visit request | Company, purpose, dates, size, products |
| /privacy, /terms | Reviewed legal content | Consent and privacy contact |
| /ar/... | Localized counterparts | RTL, native Arabic, correct canonical/hreflang |
| /admin | Restricted operations dashboard | Role-controlled CMS, enquiry and reporting modules |

Navigation: Products, Our Company (story, farms, facilities), Quality, Export, Global Presence (markets, exhibitions, visits), Resources, Contact; fixed high-visibility RFQ entry. Mobile menu and keyboard accessible mega menu. Deep-link from any page, no forced intro.

## 4. Visitor journeys
- Search visitor: Google -> relevant product -> form and grade -> specs -> RFQ/sample -> confirmation.
- New buyer: home -> field/processing story -> certification evidence -> catalogue -> export enquiry.
- Repeat buyer: direct search/filter -> add several variants -> edit RFQ -> submit.
- Exhibition prospect: QR event page -> event details -> meeting request -> sales follow-up.
- Delegation: visits -> facility information -> request date -> staff approval (not instant booking).
- Quality buyer: certificate -> covered products -> approved TDS -> request current lot COA.
- Content visitor: buyer guide -> product/industry page -> enquiry.

## 5. Feature priorities
Launch: all core pages, 30 legacy listings, approved variants, crop calendar, evidence centre, events/visits, media library, downloads, EN/AR, responsive UI, RFQ/sample/visit forms, CMS roles, email notifications, migration, SEO and analytics.
Next: expanded 50–65 verified product families, 150–200 variants as a mature target, comparison, market/industry expansion, CRM/n8n automation, advanced sales reporting.
Later: customer accounts, quotation history, authenticated batch documents, shipment tracking, approved-content AI product assistant. Do not imply these are launch features.

## 6. Work sequence
Week 1 discovery and content audit, assets and hosting check; week 2 creative prototypes and content schema; weeks 3–4 core PHP/MySQL, CMS and site framework; weeks 4–6 pages/catalogue and interactions; weeks 5–7 forms, data population, SEO and analytics; weeks 7–8 cross-browser, accessibility, security, visual and content QA, staging acceptance and approved release. Indicative only; depends on asset availability, hosting and client review. See ENGINEERING.md for gates.

## 7. Metrics
Track indexed pages, non-brand impressions/clicks, country/query/product segments, RFQ starts and completion, sample/visit requests, qualified leads and sales outcomes. Establish GSC/GA4 baseline before migration; set targets after 30–90 days of data. Target 90+ Lighthouse under defined mobile lab conditions and good field CWV (LCP <=2.5s, INP <=200ms, CLS <=0.1 at p75); no ranking or revenue promises.

## 8. Inputs and responsibilities
Client: product list/forms/grades, TDS, sample COAs, crop schedules, packaging, current certs, ownership/capacity, event and visit records, licensed original media, contacts, market evidence, hosting and analytics access. Marketing: interview team and draft/translate real stories. Quality/export staff approve factual product claims. Design: responsive prototypes and motion storyboards. Engineering: build, migration, tests and operations docs. Owner approves launch. Public repo must contain no secrets, private COAs, personal buyer data or unpublished commercial documents.
