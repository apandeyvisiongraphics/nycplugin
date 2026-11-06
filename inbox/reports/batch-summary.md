# Batch 2026-11-06: summary

Branch `content/batch-2026-11-06`. Docs: `inbox/docs/VGNYCLandingPages0411.html` (home, about, sign-installation) and `inbox/docs/VGNYCLandingPages0511.html` (contact, interior-signs). The superseded first exports are in `inbox/done/superseded/`.

## Pages (all drafts, nothing public yet)

| Route | Status | Title (chars) | Description (chars) | Sections | Image slots | Admin preview |
|---|---|---|---|---|---|---|
| `/nyc/` | REPLACEMENT | 59 | 155 | 13 | 22 (4 real-photo) | `/nyc/?vgnyc_preview=1` |
| `/nyc/about/` | REPLACEMENT | 49 | **163** ⚠️ | 12 | 8 (4 real-photo) | `/nyc/about/?vgnyc_preview=1` |
| `/nyc/sign-installation/` | **NEW** | 60 | 158 | 12 | 11 | `/nyc/sign-installation/` (admins only) |
| `/nyc/contact/` | REPLACEMENT | 49 | 150 | 6 | 0 | `/nyc/contact/?vgnyc_preview=1` |
| `/nyc/interior-signs/` | REPLACEMENT | 57 | 153 | 13 | 21 (4 real-photo) | `/nyc/interior-signs/?vgnyc_preview=1` |

Public visitors still see the current pages, and the new page returns 404 and is left out of the sitemap. `scripts/compare` confirms that all 81 existing targets are unchanged, down to the pixel.

## Section templates (`templates/sections/`, documented in `templates/sections/README.md`)

Built on existing markup and CSS: **hero** (`.product-hero`), **faq** (`.faqs`, plus `FAQPage` JSON-LD), **inquiry** (`.landing-conversion`, `id="inquiry"`, the single `<!-- VGNYC_FORM -->`).

New, styled in `assets/sections.css`, which loads only on landing pages:

| Template | Purpose |
|---|---|
| `trust-bar` | Row of 3–4 proof items (icon, bold title, one line) |
| `rich-text` | H2 + paragraphs / lists / tables, optional button |
| `image-text` | H2 + text beside one image |
| `features-grid` | H2 + intro (optional image beside it) + grid of title/text cards |
| `card-grid` | H2 + intro + image cards, optionally in labeled groups |
| `process-steps` | H2 + numbered steps, optional image and closing line |
| `testimonials` | Quotes with initials avatars (no photos) |
| `gallery` | Project photos with captions |

## Images

**62 slots**:
- 50 AI-prompt images: 4 heroes at 16:9 with a 1600px minimum, and 46 content images at 3:2 with a 960px minimum.
- 12 project slots that the writer says must be **real photos only**.

Each slot shows a labeled placeholder until the image arrives, and the prompts are visible to admins only. The designer brief is in **WP Admin → Vision Graphics NYC → Image Brief → Download CSV**.

## Open questions (see each `*-map.md` for details)

1. **Testimonials:** are these 10 people real clients who agreed to be quoted?
   - Jordan Feldman, Aisha Rahman, Daniela Ortiz (home)
   - Samuel Levy, Elena Cruz (about)
   - Hannah Brooks, Victor Romano (sign installation)
   - Natalie Greene (contact)
   - Leah Whitman, Dominic Ferraro (interior signs)

   Testimonials that aren't real can't be published.
2. **Claims and facts:** "60 Years", "$300 and $20,000 installed", the 1966 founding by Robert and Diane Shupe, Banner Capital Management in 2024, and the family brands list.
3. **About:** the meta description is 163 characters (over 160). Also confirm the fix for the typo "references,s".
4. **Missing links:**
   - "lobby sign cost guide" (sign installation and interior signs): no URL and no page exists.
   - Bold, unlinked product names on home and interior signs: should they link to the existing pages?
5. **Home:** the project caption format is cut off in the doc ("[PROJECT TYPE]" and nothing after it).
6. **Form:** fields, button labels, confirmation message and auto-reply are all Contact Form 7 settings for `MicroSiteForm_nyc`, which doesn't exist on the local site. Should I draft the CF7 form markup from the docs?
7. **Publish together:** home, about, contact and interior signs link to `/nyc/sign-installation/`.
