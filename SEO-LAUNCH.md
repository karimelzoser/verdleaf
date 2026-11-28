# SEO launch and migration checklist

1. Approve all content and photograph rights. Resolve provisional product identities and forms, verify certificates, facilities and actual export capabilities.
2. Inventory existing URLs using original sitemap, Search Console indexed pages, backlink exports and analytics. Map each valuable old URL to the closest relevant new page; do not redirect unrelated pages to homepage.
3. Choose canonical host and TLS. Verify production Laravel routing and redirects, private admin and documents, noindex on demo pages. Integrate the public semantic pages with actual CMS records; do not publish this static demo as a live admin.
4. Run `python scripts/seo-validate.py preview` (requires BeautifulSoup). After content approval only: `python scripts/seo-validate.py preview --production-output release-candidate --approved-domain https://verdleaf.com/ --approve-indexing`. This builds a separate static candidate and sitemap; never automatically deploys. Production Laravel integration is a separate requirement.
5. Verify DNS ownership in Search Console, submit production sitemap, inspect representative Arabic and English pages, verify reciprocal hreflang, canonical host, HTTP 200/301/404 and real robots.txt at origin.
6. Confirm analytics consent and track meaningful enquiry completion; no fake conversions or search volume. Benchmark visibility and qualified enquiries weekly against baseline. Improve based on actual queries.
7. Monitor indexing, Core Web Vitals, 404 errors, redirects and commercial conversions. Do not claim ranking guarantees or fabricate rich-result offers/reviews.

Keyword intent architecture (not measured keyword volumes): category discovery; individual botanical ingredient names and commercial forms; sourcing and technical-document questions; food manufacturing and tea/beverage applications. Add actual market pages only after export evidence and distinct local buyer needs are validated.

Official references: https://developers.google.com/search/docs/specialty/international/localized-versions ; https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls ; https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap ; https://developers.google.com/search/docs/appearance/structured-data/product-snippet
