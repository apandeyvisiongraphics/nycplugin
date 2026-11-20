# Vision Graphics NYC Website v1.5.0: 6 landing page build

**Date:** 20 November 2026
**Branch:** 6landingpagebuild19-11-2026
**Built by:** apandeyvisiongraphics
**Requires:** WordPress 6.4+, PHP 7.4+ (tested on WordPress 7.1)

## Updated pages
| Page | URL | Type | Sections | Images |
|---|---|---|---|---|
| Interior directory signs | /nyc/interior-directory-signs/ | REPLACEMENT | hero, trust-bar, rich-text ×2, features-grid ×2, cta-band, image-text, process-steps, testimonials, faq, inquiry (12) | 5 |
| Lobby signs | /nyc/lobby-signs/ | REPLACEMENT | hero, trust-bar, features-grid ×4, rich-text ×2, cta-band, gallery, testimonials, faq, inquiry (13) | 9 |
| Office signs | /nyc/office-signs/ | REPLACEMENT | hero, trust-bar, features-grid ×4, image-text, rich-text, cta-band, gallery, testimonials, faq, inquiry (13) | 8 |
| Exterior signs | /nyc/exterior-signs/ | REPLACEMENT | hero, trust-bar, features-grid ×4, rich-text, cta-band, process-steps, gallery, testimonials, faq, inquiry (13) | 11 |
| Channel letter signs | /nyc/channel-letter-signs/ | REPLACEMENT | hero, trust-bar, features-grid ×4, rich-text ×3, cta-band, process-steps, gallery, testimonials, faq, inquiry (15) | 8 |
| Trade show displays | /nyc/trade-show-displays/ | REPLACEMENT | hero, trust-bar, rich-text, features-grid ×4, image-text, cta-band, process-steps, gallery, testimonials, faq, inquiry (14) | 9 |

Copy, testimonials and facts are verbatim from the content doc `VGNYCLandingPages171811.html`. Titles and descriptions are as written in the doc. Bold product names link to their existing NYC pages. The replaced page content is kept in `legacy/superseded-1.5.0/`.

## Section template updates
- `features-grid`:
  - grouped cards: a label, intro and image for each group;
  - image pairs;
  - a heading above the list;
  - notes or a table below the cards, which keeps tables full width on phones;
  - 4 columns for 4, 7 or 8 cards.
- Checklists: `[ ]` items in the doc render as green-check lists.
- No changes to the header, footer or other templates.

## Images
- 50 images processed into 106 WebP files: 480 and 960 for every image, plus 1600 for the 6 heroes. Total size: 7.3 MB.
- All 50 slots ✅ in the Image Brief.
- Every hero is under 250 KB at 1600px. Every other image is under 150 KB at 960px.

## Form
- Same as v1.4.0. Every CTA leads to Contact Form 7 `MicroSiteForm_nyc` (id 328910e):
  - page buttons open the form in a popup;
  - the same form is also on the page, and each page has only one form;
  - header and footer buttons link to /nyc/contact/.

## SEO
- New titles and descriptions on all 6 pages. Two descriptions are 161 characters (office-signs, channel-letter-signs); they are kept as written in the doc.
- JSON-LD valid on all 6 pages: Organization, WebSite, WebPage, BreadcrumbList, FAQPage.
- Sitemap unchanged, because these routes already existed.

## QA
- Pages checked at 1440 / 1024 / 768 / 390px: 16/16 checks pass on 4 pages and 15/16 on 2 pages. The only failure is the 161-character description noted above.
- Checks covered: copy matches the doc, no horizontal overflow, internal links resolve, one form per page, and the popup opens and closes.
- `scripts/compare`:
  - The 73 other existing pages are byte-identical to before, apart from the WordPress version tag.
  - The baseline has been refreshed; it also picks up the v1.4.1 and v1.4.2 blog pages.
- php -l: PASS

## Files changed
- Plugin code: `templates/sections/features-grid.php`, `assets/sections.css`, `vg-nyc-website.php`.
- Images: `assets/images/` (106 new WebP files).
- Content:
  - `content/pages/*.json` (6 pages);
  - `content/images.json`;
  - `data/manifest.php`;
  - the old `content/services/*.json` files for these 6 pages, moved to `legacy/superseded-1.5.0/`.
- Not shipped in the zip: `tests/` (baseline), `scripts/` (tools), `inbox/` (docs and reports), `legacy/`.

## How to update the live site
WP Admin → Plugins → "Vision Graphics NYC Website" → **Update now**.
Then check each of the 6 pages, and open the quote popup on one of them.

## Rollback
Re-install the v1.4.2 release zip from GitHub Releases: Plugins → Add New → Upload → Replace current.
