# Vision Graphics NYC Website v1.4.0: 5 landing page build

**Date:** 06 November 2026, 20:33 IST
**Branch:** 5landingpagebuild06-11-2026
**Built by:** apandeyvisiongraphics
**Requires:** WordPress 6.4+, PHP 7.4+ (tested on WordPress 7.1)

## New / updated pages
| Page | URL | Type | Sections | Images |
|---|---|---|---|---|
| Home | /nyc/ | REPLACEMENT | hero, trust-bar, rich-text, card-grid, features-grid, cta-band ×2, process-steps, gallery, testimonials, faq, inquiry (15) | 22 |
| About | /nyc/about/ | REPLACEMENT | hero, trust-bar, image-text, features-grid, cta-band ×2, rich-text, gallery, testimonials, faq, inquiry (14) | 8 |
| Sign installation | /nyc/sign-installation/ | NEW | hero, trust-bar, features-grid, rich-text, cta-band ×2, card-grid, image-text, process-steps, testimonials, faq, inquiry (14) | 11 |
| Contact | /nyc/contact/ | REPLACEMENT | hero, trust-bar, inquiry, process-steps, faq, testimonials (6) | 0 |
| Interior signs | /nyc/interior-signs/ | REPLACEMENT | hero, trust-bar, card-grid, image-text, rich-text, features-grid, cta-band ×2, process-steps, gallery, testimonials, faq, inquiry (15) | 21 |

Copy, testimonials and facts are verbatim from the content docs `VGNYCLandingPages0411.html` (home, about, sign-installation) and `VGNYCLandingPages0511.html` (contact, interior-signs). The replaced page content is kept in `legacy/superseded-1.4.0/`.

## New section templates
- `hero`: H1, intro, one "Get a Quote" button, hero image
- `trust-bar`: slim scrolling strip of proof points (pauses on hover, static with reduced motion)
- `rich-text`: headed prose block
- `image-text`: text beside an image
- `features-grid`: icon cards (2/3/4 columns by item count)
- `card-grid`: image cards with text and links
- `process-steps`: numbered steps
- `testimonials`: client quotes
- `gallery`: project photos with captions
- `faq`: accordion + FAQPage schema
- `inquiry`: on-page form card (the page's single Contact Form 7 form)
- `cta-band`: mid-page call-to-action band (2 per long page)

## Header (all /nyc/ pages)
- Sticky bar with shadow on scroll, animated green underline, hover dropdowns, current-page highlight, icon mobile menu. Copy and links unchanged.

## Images
- 62 images processed into 129 WebP files (480/960 for all, plus 1600 for the 5 heroes); total size 8.1 MB
- All 62 slots ✅ (Image Brief)

## Form
- All CTAs lead to Contact Form 7 `MicroSiteForm_nyc` (id 328910e): page buttons open it in a popup, the same form is on the page, and header/footer buttons link to /nyc/contact/

## SEO
- New titles and descriptions on all 5 pages (About description shortened to 153 characters)
- /nyc/sign-installation/ added to /nyc/sitemap.xml (79 URLs)
- JSON-LD valid on all 5 pages: Organization, WebSite, WebPage, BreadcrumbList, FAQPage (+ Service on sign-installation and interior-signs)

## QA
- Pages checked at 1440 / 1024 / 768 / 390px: PASS (16/16 checks on each of the 5 pages, including no horizontal overflow and copy matching the doc)
- Links: 30 internal, 0 external, all 200 except `/nyc/privacy/`, which redirects to the privacy-policy URL in Settings → Vision Graphics NYC (not set on the local site; the existing footer already uses this link)
- scripts/compare: 0 unintended changes on 76 existing pages (checked with the previous header); the header change was reviewed separately; baseline refreshed, 82/82 pass
- php -l: PASS

## Files changed
753 files vs `main` (v1.2.0), +36,043 / −21 lines. This branch also contains the unmerged v1.3.0 refactor.
- Plugin code: `vg-nyc-website.php`, `includes/` (landing, images), `templates/` (12 section templates, landing page, header), `assets/` (sections.css, landing.js, header styles/JS in public.css and website.js, 129 images)
- Content: `content/pages/*.json` (5 pages), `content/images.json`, `content/services/*.json`, `data/manifest.php`
- Updates: `vendor/plugin-update-checker/` (v5.7, MIT), `.github/workflows/release.yml`, `readme.txt`, `CHANGELOG.md`
- Not shipped in the zip: `tests/` (baseline), `scripts/` (tools), `inbox/` (docs and reports), `legacy/`

## How to update the live site
WP Admin → Plugins → "Vision Graphics NYC Website" → **Update now**.
(First time only: upload `vg-nyc-website.zip` from this Release via Plugins → Add New → Upload Plugin → **Replace current with uploaded**.)
Then check: /nyc/, each new page, the form on the contact page, and /nyc/sitemap.xml.

## Rollback
Re-install the previous release zip from GitHub Releases (Plugins → Add New → Upload → Replace current).
