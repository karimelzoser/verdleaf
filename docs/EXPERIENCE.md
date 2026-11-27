# Creative direction, editorial system and motion
## North star
A digital visit to Verdleaf. The visitor feels the landscape, the people, the ingredient and the precision behind export. Balance an editorial agricultural story with the fast interaction of a sourcing catalogue. Design original page compositions; do not repeat identical feature cards, giant gradients, stock icons or generic SaaS layouts.

## Design language
Forest #193D2D, ink #25312B, warm ivory #F5F2E9, olive #778B5A, muted gold #B99B5E (accent, not body text). Use brand logo exactly as supplied. Proposed fonts: editorial serif for selected headlines and accessible sans for text/technical UI; native Arabic family (e.g. IBM Plex Sans Arabic) after testing. Host licensed WOFF2 locally with fallback and subset. 12-column desktop, 8 tablet, 4 mobile; 8px spacing system; fluid typography, generous whitespace, dark/ivory chapters, natural photography. Avoid dark text on busy imagery without contrast overlay. Design at 1440, 1024, 768 and 390px; test down to 320px.

## Homepage storyboard
| Chapter | Composition | Motion | Content/CTA |
|---|---|---|---|
| 01 Arrival | Full-bleed actual field film or wide still, modest title left, top nav | 600–800ms headline mask reveal, gentle image scale, no blocking loader | “Egyptian herbs, grown for the world.” / Explore ingredients |
| 02 Origins | Asymmetric field image and handwritten-style crop labels | Slow image reveal on entry; no scroll-jacking | Fayoum, approved heritage and company story |
| 03 Ingredient room | Large product macro photography; category tabs, editorial grid | 220ms crossfade and item focus, touch-friendly | Explore catalogue |
| 04 Seed to shipment | Six-stage process with real operation images and thin botanical line | Scroll-triggered stage highlights on desktop; stacked mobile | Farms, processing, quality, packing and export |
| 05 In season | Crop of the season with timeline and harvest calendar | Calendar active-month marker; user-controlled tabs | Explore crop calendar; confirmed dates only |
| 06 Evidence | Restrained document and certification layout with actual seals and lab media | Small stagger, no animated badges | View quality documents |
| 07 Around the world | Geographic diagram of verified destinations and upcoming/past events | SVG route draw only when appropriate; static alternate | Exhibition/market links |
| 08 People | Real visits, team portraits, dated captions | Editorial gallery, keyboard controls | Arrange visit |
| 09 Buyer desk | Warm ivory simple product search and enquiry section | RFQ drawer and inline validation | Request quote or sample |
| 10 Footer | Clear navigation, contact and language | None | Verified contacts |

## Signature interactions
- Product transformation: click whole, crushed, TBC, powder etc.; switch real photo and technical facts with 180–240ms crossfade. Forms not offered are omitted. Initial variant and data SSR. URL query may preserve selection; canonical main page unless dedicated variant content.
- Crop season explorer: interactive month selection, planting/growth/harvest bars, separate verified availability; table alternative and download. Never infer stock from month.
- Field-to-export story: illustrated path links six verified stages; each stage opens a readable caption and evidence. No mandatory pinned scroll.
- Certificate viewer: accessible dialog with document metadata, actual PDF link, close/escape, focus return; expired documents clearly marked and not promoted as current.
- Exhibition/visit editorial galleries: image captions, dates, previous/next, fullscreen optional; no auto-advance.
- Quotation basket: selected items persist locally without private customer details; edit/remove quantity, units, form and grade; keyboard-accessible; submission confirmed only on server success.
- Product comparison (phase 2): compare up to 3 products/variants, highlight differences, export to RFQ.
- Language switch retains equivalent page; fallback marked when translation unavailable. RTL motion respects reading direction.
- Global navigation: accessible mega menu, skip link, escape to close, focus handling, meaningful link labels.

## Motion specification
Micro hover/focus 120–180ms; component changes 180–260ms; section entrance 350–550ms; hero intro <=800ms. Use opacity/transform rather than layout properties. Easing cubic-bezier(.22,1,.36,1). Native CSS/Web Animations/IntersectionObserver first; use GSAP selectively only after bundle and license review; no persistent animation engine for static content. Never hide content when JS fails. prefers-reduced-motion disables parallax, scroll choreography, autoplay and animated transitions; all information still present. No scroll hijack, cursor replacement, heavy WebGL, forced sound, infinite loops or mandatory loading animation. Video muted with controls/pause, poster first and no autoplay on constrained devices. Lazy-load below fold; stop offscreen animation.

## Human editorial standard
Write like an export specialist explaining a product to a buyer, not an advertising generator. Use named crops, actual places, observable details, dates and approved actions. Vary rhythm; short headlines, specific prose, ordinary language. Avoid “unleash”, “elevate”, “world-class excellence”, “seamless journey”, “unwavering commitment”, “best quality” and generic three-adjective claims. No invented testimonials, health claims, statistics, quotes or harvest schedules. Do not copy competitors. Native Arabic, not literal machine translation.
Draft examples for approval:
- Homepage: “Egyptian herbs, grown for the world.” / “Explore our herbs, spices and botanical ingredients. Choose the form your production needs and speak directly with our export team.”
- Product page: “Need chamomile for tea bags? Ask us about cut sizes and the current crop before placing a bulk order.”
- RFQ: “Tell us the product, quantity and destination. Our export team will review your requirements and get back to you.”
- Quality: “Check the scope and validity of our certificates before requesting a quotation.”
- Arabic: “أعشاب وتوابل مصرية لأسواق العالم.” / “اختر الشكل المناسب لاحتياجات التصنيع، واطلب عرض سعر من فريق التصدير.”
All draft copy subject to factual verification. Interview staff about origin, processing, what buyers ask, common forms, documentation and constraints; collect notes, fact-check, edit for voice, native translation, approval, revision dates.

## Photography and visual assets
Shoot actual fields at dawn and working day, close-up leaves/flowers, harvesting hands, processing, lab and packing, warehouse, staff, export preparation and events. Asset list: 3–5 hero stills, 1 corporate film, 3–6 process clips, 1–2 main images per family, 2–3 per variant, 20–30 farm, 15–25 processing, 8–12 QA, 10–15 packaging, 10–15 logistics, 5–10 team, 5–15/event, 5–10/visit. Consistent neutral product photography with scale reference where useful; use real actual forms. Obtain releases and usage rights. Record asset provenance, photographer, license/consent, alt/captions, crops, focal point, approval status. Staging inspiration only: MartinBauer editorial storytelling, Kräuter Mix technical catalogue, Döhler application pages; never copy layout/assets.

## Visual QA
Review desktop/tablet/mobile screenshots for every route and key state: empty results, invalid filters, loading/errors, long EN/AR copy, RTL, missing photos, expired certificates, no upcoming events, quote validation, no JS and reduced motion. Validate contrast WCAG AA, keyboard/focus, 44px recommended touch targets, text resizing, screen reader semantics, no clipping/overlap, no horizontal overflow, readable content with animations off. Test real low-end mobile performance. Sign off against storyboard and reference screenshots before release.
