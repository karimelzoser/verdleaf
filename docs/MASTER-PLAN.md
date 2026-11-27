# Master implementation plan — v2.0
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

## 3.1 V2 routes and dual navigation
Explore Verdleaf: /inside-verdleaf (tour), /responsible-sourcing (evidence-led practices), /insights/field-notes (dated updates), /about (team and history). Source Ingredients: /atlas (editorial taxonomy, canonical product links), /ingredient-finder (guided application/form selection), /compare (up to 3 approved variants; noindex), /buyer-desk (shortlist, combined RFQ/sample/document/custom request; noindex), /how-to-order (commercial buying guide), /products/{slug}#passport (public product facts; avoid duplicate indexable page), /catalogue/print (filtered print layout, noindex), /shortlist/{token} (optional expiring anonymous read-only share, noindex). Keep /quote, /sample, /visit-request and exhibition meetings as direct entry paths using shared request logic; do not require login. All routes have localized equivalents when approved. 
Home presents both paths above the fold; global search and Buyer Desk accessible from all public pages. A single canonical product identity connects atlas, catalogue, finder, passport and buyer tools.

## 4. Visitor journeys
- Search visitor: Google -> relevant product -> form and grade -> specs -> RFQ/sample -> confirmation.
- New buyer: home -> field/processing story -> certification evidence -> catalogue -> export enquiry.
- Repeat buyer: direct search/filter -> add several variants -> edit RFQ -> submit.
- Exhibition prospect: QR event page -> event details -> meeting request -> sales follow-up.
- Delegation: visits -> facility information -> request date -> staff approval (not instant booking).
- Quality buyer: certificate -> covered products -> approved TDS -> request current lot COA.
- Content visitor: buyer guide -> product/industry page -> enquiry.

## 5. Feature priorities (v2 supersedes v1)
Launch: all original client requirements, 30 legacy listings reconciled, approved variants, agriculture/quality/events/visits, EN/AR, original design, searchable catalogue with synonyms and typo tolerance, Botanical Atlas, ingredient finder, variant transform, public product passports, comparison of up to 3 variants, anonymous Buyer Desk (RFQ + samples + document requests), custom sourcing, shareable non-sensitive shortlist, filtered printable catalogue, packaging explorer, documented responsible sourcing, editorial field notes, guided virtual tour using approved stills and clips, CMS, SEO health centre, analytics and email.
Phase 2: expand approved catalogue towards 50–65 families and 150–200 variants, CRM integrations, substantive market pages, advanced sales reporting and richer tour if suitable media is available.
Phase 3: authenticated buyer accounts, private batch passport/COA access, shipment tracking, 360 tour and approved-content AI assistant only after feasibility and privacy review.
No features are represented as live before implementation and acceptance.

## 6. Revised gated delivery
G0: inventory, hosting, media and evidence audit; G1: three responsive prototype flows and motion/accessibility approval; G2: Laravel foundation and content models; G3: immersive corporate site and buyer catalogue; G4: Buyer Desk, passports, finder, comparison and requests; G5: content, multilingual SEO, QA and staging; G6: client acceptance and migration. The previous 8-week estimate is retired; schedule and staffing must be re-estimated after G0/G1. No unsupported target dates.

## 7. Metrics
Track indexed pages, non-brand impressions/clicks, country/query/product segments, RFQ starts and completion, sample/visit requests, qualified leads and sales outcomes. Establish GSC/GA4 baseline before migration; set targets after 30–90 days of data. Target 90+ Lighthouse under defined mobile lab conditions and good field CWV (LCP <=2.5s, INP <=200ms, CLS <=0.1 at p75); no ranking or revenue promises.

## 7.1 Task-based validation
Test with procurement, quality and new-partner personas. Proposed UAT gates: locate a known ingredient within 3 actions from homepage; compare two forms and add to shortlist without losing state; request quote + sample + TDS together without account; navigate to a verified certification from product; find next confirmed event or honest empty state. Measure completion, time, errors and qualitative feedback; no conversion lift promises. See PROTOTYPES.md.
 
## 8. Inputs and responsibilities
Client: product list/forms/grades, TDS, sample COAs, crop schedules, packaging, current certs, ownership/capacity, event and visit records, licensed original media, contacts, market evidence, hosting and analytics access. Marketing: interview team and draft/translate real stories. Quality/export staff approve factual product claims. Design: responsive prototypes and motion storyboards. Engineering: build, migration, tests and operations docs. Owner approves launch. Public repo must contain no secrets, private COAs, personal buyer data or unpublished commercial documents.
