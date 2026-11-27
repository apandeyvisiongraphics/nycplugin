# Batch 2026-11-26: summary

Branch `6landingpagebuild26-11-2026`, made from `main` after v1.5.0 was merged. Doc: `inbox/docs/VGNYCLandingPages242511.html` (6 pages).

## Pages (all drafts, nothing public yet)

All six routes already exist in the old `service` design, so each one is a **replacement**. The current page stays live, and admins preview the new one with `?vgnyc_preview=1`.

| Route | Title (chars) | Description (chars) | Sections | Image slots |
|---|---|---|---|---|
| `/nyc/storefront-signs/` | 60 | 154 | 13 | 8 (4 real-photo) |
| `/nyc/window-film/` | 57 | 155 | 14 | 8 (4 real-photo) |
| `/nyc/window-graphics/` | 57 | 158 | 15 | 8 (4 real-photo) |
| `/nyc/wayfinding-signs/` | 54 | 158 | 15 | 8 (4 real-photo) |
| `/nyc/school-signs/` | 56 | **161** ⚠️ (kept as written) | 15 | 8 (4 real-photo) |
| `/nyc/manhattan/` | 56 | 159 | 12 | 8 (4 real-photo) |

**Sections used:** the existing library:
- hero
- trust-bar marquee
- rich-text
- image-text
- features-grid
- process-steps
- gallery
- testimonials
- faq
- inquiry
- the mid-page cta-band

There are no new section types. The form, buttons and design are the same as on the 2026-11-06 and 2026-11-19 pages.

**Links:** 120 links in the doc. Every internal route exists and returns 200 on the live site. Contact links become `#inquiry`, which opens the popup form.

## Doc formatting handled by the builder (now automatic for future docs)

- **Numbered H3 steps** ("1. Send Photos and Measurements" …):
  - Where: Storefront §8 (5 steps) and Manhattan §7 (4 steps).
  - Problem: the parser used to read each step as a new page section, and that also put the step images in the wrong place.
  - Fix:
    - A numbered H3 now starts a section only when it continues the section numbering.
    - The steps use the numbered steps layout, each with an H3 title and its text. The circle shows the number.
  - Check: parsing and building the earlier docs gives byte-identical output.
- **"How It Works" with unnumbered H3s** (Production, Installation, After-Hours …):
  - Where: Wayfinding §10 and School §10.
  - These are cards, the same as the matching "Installation and Removal" sections on Window Film and Window Graphics. Numbering "Building Approval" as a step would be wrong.
- **Closing lines below the cards:**
  - Where: Storefront §3 and §5, Window Film §5, Window Graphics §7 and §9, Wayfinding §9, School §8.
  - These lines apply to the whole section, so they sit below the cards, as on the 2026-11-19 pages. The wording is unchanged.
- **Step title style:** one new CSS rule (`.vg-steps__list li>h3`). It applies only to steps that have an H3, so existing pages don't change.

## Links added (decided 2026-11-19: bold product names link to existing pages)

- **Storefront:**
  - "cabinet signs" and "metal signs" (§3 "Learn more about …");
  - "Cabinet signs" and "Metal signs" (§4 table rows; the other rows were already linked).
- **Wayfinding:** "school signs" (§9 "Learn more about school signs").

## Images

**48 new slots:**
- 24 AI-prompt images: 6 heroes at 16:9 (1600px) and 18 content images at 3:2 (960px).
- 24 **real-photo-only** project slots: 4 on each page.

Alt text is hand-written in `batch-2026-11-26-config.json`. The designer brief is in **WP Admin → Vision Graphics NYC → Image Brief → Download CSV**.

## Kept as written (earlier decisions, not re-asked)

- **School Signs meta description:** 161 characters.
- **Window Film FAQ:** "$8 to $ 20+ per square foot" has an extra space.
- **Manhattan proof strip:** "Landmark Planning" replaces "5 Boroughs".
- **Testimonials, prices, rules and lead times:** exactly as in the doc.
- **Form-field lists and "Get My Quote":** ignored. Every page uses the one Contact Form 7 form.

## Open questions

None. Next step: design QA and screenshots (Step 6), then your design approval.

## Update 2026-11-27: images and Wayfinding Projects

- **Images:** 44 slots converted to WebP at 480/960/1600. QA re-run: 0 placeholders and 0 broken images on all six pages.
- **Wayfinding Signs Projects section (doc §11) removed:** there are no real project photos for it.
  - Set in the config as `"drop_sections": {"wayfinding-signs": [11]}`, so it stays out when the page is rebuilt.
  - The 4 `nyc-wayfinding-signs-projects-*` slots are out of the image registry and the designer brief.
  - QA no longer expects the removed copy.
  - The page now has 14 sections. The other five pages are byte-identical.
