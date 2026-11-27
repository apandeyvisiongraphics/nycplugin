# Changelog

## 1.6.0 — 27 November 2026 (6 landing pages)

- **Replaced pages (landing design):** `/nyc/storefront-signs/`, `/nyc/window-film/`, `/nyc/window-graphics/`, `/nyc/wayfinding-signs/`, `/nyc/school-signs/`, `/nyc/manhattan/`. Copy, testimonials and facts verbatim from `VGNYCLandingPages242511.html`; titles and descriptions as written in the doc. Bold product names link to their existing NYC pages. Old content moved to `legacy/superseded-1.6.0/`.
- **Wayfinding signs:** the Projects section is left out (no real project photos), via `drop_sections` in the batch config.
- **process-steps:** numbered H3 steps in the doc render as steps with an H3 title (`.vg-steps__list li>h3`).
- **Images:** 44 new slots, 94 WebP files (5.9 MB).
- **Pipeline:** `parse_doc.py` keeps numbered H3 steps inside their section; `build_pages.py` supports `drop_sections`; `qa.mjs` skips dropped sections; the Image Brief CSV exports the filtered rows.
- Other routes render byte-identical to 1.5.0, apart from image `?v=` cache-busting.

## 1.5.0 — 20 November 2026 (6 landing pages)

- **Replaced pages (landing design):** `/nyc/interior-directory-signs/`, `/nyc/lobby-signs/`, `/nyc/office-signs/`, `/nyc/exterior-signs/`, `/nyc/channel-letter-signs/`, `/nyc/trade-show-displays/`. Copy, testimonials and facts verbatim from `VGNYCLandingPages171811.html`; titles and descriptions as written in the doc. Bold product names link to their existing NYC pages. Old content moved to `legacy/superseded-1.5.0/`.
- **features-grid:** grouped cards (`groups` with a label, intro and image per group), image pairs (`images`), a list heading (`items_heading`), notes or tables below the cards (`after_html`), 4 columns for 4, 8 or 7 cards, card titles as H4 under a group H3.
- **Checklists:** doc lists written as `[ ]` items render as green-check lists (`ul.vg-checklist`).
- **Images:** 50 new slots, 106 WebP files (7.3 MB).
- **Pipeline:** `build_pages.py` splits run-on labels, detects checklists, moves tables and section-wide closing lines below the cards (`after_cards` in the batch config) and links bold names only (`"bold": true`).
- Other routes render byte-identical to 1.4.2.

## 1.4.2 — 13 November 2026 (blog)

- **New post:** `/nyc/resources/lobby-sign-cost-nyc/` "How Much Does a Lobby Sign Cost in NYC?" — copy verbatim from the doc, 9 images (19 WebP files), two price tables. Card added to `/nyc/resources/`; fixes the "lobby sign cost guide" links from the checklist post.
- **Blog template:** styled price tables (scroll sideways on phones) and images placed under H3s (`[[image:slot]]`). `build_blog.py` uses a doc H1 as the title.
- No other page changes (existing routes render byte-identical to 1.4.1).

## 1.4.1 — 13 November 2026 (blog)

- **Blog posts:** new `"template": "blog"` (`templates/blog-post.php`, `assets/blog.css`, `content/blog/`). Same light design as the landing pages, laid out as an article: breadcrumb, title, intro, author/date/reading time, featured image, sticky "On this page" contents with a quote button, reading progress bar, checklist cards, quote box, FAQ and the closing form section with the popup. `BlogPosting` + `FAQPage` JSON-LD, `og:type=article`.
- **Doc pipeline:** `scripts/build_blog.py` turns a Google Docs export into a post (copy verbatim, images registered and converted to WebP).
- **Page:** `/nyc/resources/project-brief/` replaced by "What to Send for an Accurate Sign Quote in NYC: A Checklist" (9 images, 19 WebP files). Resources hub card updated. Old content in `legacy/superseded-1.4.1/`.

## 1.4.0 — 06 November 2026 (5 landing page build)

- **Pages:** new landing design for `/nyc/`, `/nyc/about/`, `/nyc/contact/`, `/nyc/interior-signs/` (replacements) and the new `/nyc/sign-installation/`. Copy, testimonials and facts come verbatim from the content docs (VGNYCLandingPages0411 / 0511).
- **Section library** (`templates/sections/`): hero, trust-bar (marquee), rich-text, image-text, features-grid, card-grid, process-steps, testimonials, gallery, faq, inquiry, cta-band.
- **Form:** every page button opens Contact Form 7 `MicroSiteForm_nyc` (id 328910e) in a popup; the same single form is also on the page. Header/footer buttons link to `/nyc/contact/`.
- **Images:** 62 designer images, 129 WebP files (480/960/1600), 8.1 MB. Image registry in `content/images.json`, Image Brief in WP Admin.
- **Header:** sticky bar, animated nav underline, hover dropdowns, current-page highlight, icon mobile menu. Copy and links unchanged.
- **SEO:** new titles/descriptions, FAQ schema, `/nyc/sign-installation/` added to `/nyc/sitemap.xml`.
- **Updates:** Plugin Update Checker 5.7 — the live site sees new GitHub Releases as "Update now".
- Replaced page content kept in `legacy/superseded-1.4.0/`.

## 1.3.0

- Content separated from templates (`content/`, `templates/`, `data/manifest.php`), zero visual change.

## 1.2.0

- Initial release.
