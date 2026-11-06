# Changelog

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
