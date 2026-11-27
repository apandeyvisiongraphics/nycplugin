# Vision Graphics NYC Website v1.6.0: 6 landing page build

**Date:** 27 November 2026
**Branch:** 6landingpagebuild26-11-2026
**Built by:** apandeyvisiongraphics
**Requires:** WordPress 6.4+, PHP 7.4+ (tested on WordPress 7.1)

## Updated pages
| Page | URL | Type | Sections | Images |
|---|---|---|---|---|
| Storefront signs | /nyc/storefront-signs/ | REPLACEMENT | hero, trust-bar, features-grid ×3, rich-text ×2, cta-band, process-steps, gallery, testimonials, faq, inquiry (13) | 8 |
| Window film | /nyc/window-film/ | REPLACEMENT | hero, trust-bar, rich-text ×2, features-grid ×5, cta-band, gallery, testimonials, faq, inquiry (14) | 8 |
| Window graphics | /nyc/window-graphics/ | REPLACEMENT | hero, trust-bar, rich-text ×2, features-grid ×6, cta-band, gallery, testimonials, faq, inquiry (15) | 8 |
| Wayfinding signs | /nyc/wayfinding-signs/ | REPLACEMENT | hero, trust-bar, rich-text ×2, features-grid ×6, cta-band, testimonials, faq, inquiry (14) | 4 |
| School signs | /nyc/school-signs/ | REPLACEMENT | hero, trust-bar, image-text, features-grid ×6, rich-text, cta-band, gallery, testimonials, faq, inquiry (15) | 8 |
| Manhattan | /nyc/manhattan/ | REPLACEMENT | hero, trust-bar, features-grid ×4, cta-band, process-steps, gallery, testimonials, faq, inquiry (12) | 8 |

Copy, testimonials and facts are verbatim from the content doc `VGNYCLandingPages242511.html`. Titles and descriptions are as written in the doc. Bold product names link to their existing NYC pages. The replaced page content is kept in `legacy/superseded-1.6.0/`.

The Wayfinding signs page has no Projects section, because there are no real project photos for it (approved 27 November 2026).

## Section template updates
- `process-steps`: numbered H3 steps in the doc ("1. Send Photos and Measurements") render with an H3 title and text under the step number. This is one new CSS rule, which applies only to steps that have an H3, so existing pages don't change.
- No changes to the header, footer or other templates.

## Images
- 44 images processed into 94 WebP files: 480 and 960 for every image, plus 1600 for the 6 heroes. Total size: 5.9 MB.
- All 44 slots ✅ in the Image Brief.
- Every hero is under 250 KB at 1600px. Every other image is under 150 KB at 960px. Three images were re-encoded at a lower quality to meet this.

## Form
- Same as v1.4.0. Every CTA leads to Contact Form 7 `MicroSiteForm_nyc` (id 328910e):
  - page buttons open the form in a popup;
  - the same form is also on the page, and each page has only one form;
  - header and footer buttons link to /nyc/contact/.

## SEO
- New titles and descriptions on all 6 pages. The School signs description is 161 characters; it is kept as written in the doc.
- JSON-LD valid on all 6 pages: Organization, WebSite, WebPage, BreadcrumbList, FAQPage.
- Sitemap unchanged, because these routes already existed.
- The header link "Midtown & Chelsea" goes to `/nyc/manhattan/#midtown`. The new Manhattan page has no `#midtown` anchor, so the link opens the top of the page.

## QA
- Pages checked at 1440 / 1024 / 768 / 390px: 16/16 checks pass on 5 pages and 15/16 on School signs. The only failure is the 161-character description noted above.
- Checks covered: copy matches the doc, no horizontal overflow, internal links resolve, one form per page, and the popup opens and closes.
- Reverse copy check: all text on the 6 pages comes from the doc (apart from testimonial initials and the popup close button).
- `scripts/compare`: 83/83 pass.
  - The other existing pages are byte-identical to before, apart from the WordPress version tag and the image `?v=` cache-busting.
  - The baseline has been refreshed for the 6 published routes and the 12 landing and blog pages whose image `?v=` changed.
- php -l: PASS

## Files changed
- Plugin code: `assets/sections.css`, `includes/images.php` (Image Brief CSV exports the filtered rows), `vg-nyc-website.php`.
- Images: `assets/images/` (94 new WebP files).
- Content:
  - `content/pages/*.json` (6 pages);
  - `content/images.json`;
  - `data/manifest.php`;
  - the old `content/services/*.json` files for these 6 pages, moved to `legacy/superseded-1.6.0/`.
- Not shipped in the zip: `tests/` (baseline), `scripts/` (tools), `inbox/` (docs and reports), `legacy/`.

## How to update the live site
WP Admin → Plugins → "Vision Graphics NYC Website" → **Update now**.
Then check each of the 6 pages, and open the quote popup on one of them.

v1.5.0 was never published as a GitHub Release. A site on v1.4.2 that updates to v1.6.0 also gets the six v1.5.0 pages: `/nyc/interior-directory-signs/`, `/nyc/lobby-signs/`, `/nyc/office-signs/`, `/nyc/exterior-signs/`, `/nyc/channel-letter-signs/` and `/nyc/trade-show-displays/`.

## Rollback
Re-install the v1.4.2 release zip from GitHub Releases: Plugins → Add New → Upload → Replace current. This also removes the v1.5.0 pages.
