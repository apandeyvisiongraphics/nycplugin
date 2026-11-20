# Batch 2026-11-19: summary

Branch `6landingpagebuild19-11-2026` (from `5landingpagebuild06-11-2026`). Doc: `inbox/docs/VGNYCLandingPages171811.html` (6 pages).

## Pages (all drafts, nothing public yet)

All six routes already exist (old `service` design). Each one is a **replacement**: the current page stays live, and admins preview the new one with `?vgnyc_preview=1`.

| Route | Title (chars) | Description (chars) | Sections | Image slots |
|---|---|---|---|---|
| `/nyc/interior-directory-signs/` | 57 | 152 | 12 | 5 (0 real-photo) |
| `/nyc/lobby-signs/` | 59 | 155 | 13 | 9 (4 real-photo) |
| `/nyc/office-signs/` | 58 | **161** ⚠️ | 13 | 8 (4 real-photo) |
| `/nyc/exterior-signs/` | 60 | 157 | 13 | 11 (4 real-photo) |
| `/nyc/channel-letter-signs/` | 47 | **161** ⚠️ | 15 | 8 (4 real-photo) |
| `/nyc/trade-show-displays/` | 56 | 159 | 14 | 9 (4 real-photo) |

**Sections used:** the existing library (hero, trust-bar marquee, rich-text, image-text, features-grid, process-steps, gallery, testimonials, faq, inquiry, plus mid-page cta-band). No new section types.

**Links:** 77 links in the doc. All internal routes exist, and every contact link becomes `#inquiry` (the popup form).

## Doc formatting handled by the builder (now automatic for future docs)

- **`[ ]` checklists** (Office Signs §4, Trade Show §5) render as a checklist with check icons, not as numbered steps.
- **Office Signs hero:** "CTA buttons:" was on the same line as the last intro sentence. It's split mechanically, and the copy is unchanged.
- **`features-grid` additions** (`templates/sections/features-grid.php`, `assets/sections.css`):
  - **`groups`:** Exterior §3 (Storefront / Building and Property / Window Graphics) shows each group's image beside its heading, with the sign types as cards.
  - **`images`:** Lobby §3's two images show as a pair beside the intro.

## Images

**50 new slots:**
- 30 AI-prompt images: 6 heroes at 16:9 (1600px) and 24 content images at 3:2 (960px).
- 20 **real-photo-only** project slots: 4 each on lobby, office, exterior, channel letter and trade show.

Alt text is hand-written in `batch-2026-11-19-config.json`. The designer brief is in **WP Admin → Vision Graphics NYC → Image Brief → Download CSV**.

## Decisions (2026-11-19)

1. **Meta descriptions:** kept exactly as in the doc, including Office Signs and Channel Letters at 161 characters.
2. **Bold product names:** linked to their existing pages. Only the bold phrase gets the link (`"bold": true` in `link_additions`).
   - Wayfinding signs; Brushed metal → metal-signs
   - Blade, Cabinet, Building, Monument, Pylon and Post and panel signs, and Window graphics
   - Building signs (channel letters)
   - retractable banners, Floor graphics and banners

   "Standoff signs", "Backlit lobby signs" and "Conference room signs" have no page, so they stay bold.
3. **Lobby "Elevator lobby signs":** plain text, as in the doc.
4. **Channel Letters §10 wording:** kept as written.
5. **Projects sections:** kept with placeholders. You will decide later.
6. **Form:** the same MicroSiteForm_nyc form, design and buttons as the 2026-11-06 pages. Per-page field lists in the doc are not added.

## Open questions

None. Next: Step 6, QA and screenshots, which needs the Local site running.

Page maps: `inbox/reports/{slug}-map.md`. Issues: `inbox/reports/batch-2026-11-19-issues.json`.
