# Section library

> **Design standard (decided 2026-11-06):** every **new** NYC page uses this landing-page design:
> `"template": "landing"` with `content/pages/{slug}.json`, built from the section types below.
> Don't create new pages with the older `service` (`content/services/*.json`) or `raw` (`pages/*.php`)
> templates. Existing pages keep their current design until a content doc replaces them.
>
> **Adding a new page:**
> 1. Run the doc pipeline: `scripts/parse_doc.py` → `scripts/build_pages.py` → `scripts/page_map.py` → `scripts/qa.mjs`.
>    Without a doc, copy an existing `content/pages/*.json` and edit it.
> 2. Add the manifest entry with `"template": "landing"`, `"content": "content/pages/{slug}.json"` and `"status": "draft"`.
> 3. Register its image slots in `content/images.json`.
> 4. Run `node scripts/compare.mjs`. Every existing route must still pass.

Landing pages (`"template": "landing"` in `data/manifest.php`) are an ordered list of sections in
`content/pages/{slug}.json`. `templates/landing-page.php` renders each one with
`templates/sections/{type}.php`. A section template receives only its JSON object (plus a small
context array) and returns HTML. Styling lives in `assets/sections.css`, loaded after `public.css`
on landing pages only.

Rules for every section:

- **Escaping:**
  - Plain-text fields use `esc_html` / `esc_attr`, and links use `esc_url`.
  - `*_html` fields (and `html`) go through `vgnyc_kses()`, which is `wp_kses_post` plus inline SVG icons and `srcset`/`sizes`.
- **Images:** always reference a slot id from `content/images.json` and render through `vgnyc_image()`, never a hard-coded `<img>`. A missing image shows a labeled placeholder.
- **Optional fields:**
  - `"continues": true` removes the top padding, so the section reads as part of the previous one.
  - `doc_section` records the writer's section number for traceability. Templates ignore it.

| Type | Purpose | Fields |
|---|---|---|
| `hero` | Page heading, intro, buttons, hero image | `eyebrow?`, `h1`, `lead_html?`, `ctas?` [`{label, href, style: primary\|secondary}`], `image?` |
| `trust-bar` | Slim scrolling strip (marquee) of short proof points with icons | `items` [`{title, text, icon?}`] (icon: any `vgnyc_icon()` name, e.g. award, shield-check, clipboard-check, clock, map-pin, camera, user, layers) |
| `rich-text` | Heading + paragraphs, lists or a table | `eyebrow?`, `h2?`, `html`, `cta?` `{label, href}` |
| `image-text` | Heading + text beside one image | `h2`, `html`, `image`, `image_side?` (`left`/`right`) |
| `features-grid` | Heading + intro + cards of title/text | `h2`, `intro_html?`, `image?` (beside the intro), `items_heading?`, `items` [`{title, html?, image?}`] |
| `card-grid` | Image cards, optionally grouped | `h2`, `intro_html?`, `items` [`{title, html?, image}`] **or** `groups` [`{label, intro_html?, items}`] |
| `process-steps` | Numbered steps | `h2`, `intro_html?`, `steps` [html], `after_html?`, `image?` |
| `testimonials` | Client quotes, initials avatars | `h2?`, `items` [`{quote, name, role?, company?}`] |
| `gallery` | Project photos | `h2?`, `intro_html?`, `items` [`{image, caption?}`] |
| `faq` | Questions and answers, plus FAQPage JSON-LD | `h2`, `items` [`{q, a_html}`] |
| `inquiry` | The form (always `id="inquiry"`) | `h2`, `intro_html?`, `note_html?` |
| `cta-band` | Call-to-action band between sections | `h2?` (shown as a styled title, not an H2), `html?`, `cta` `{label, href, style?}`, `variant?` (`compact`) |

## Examples

```json
{ "type": "hero", "eyebrow": "Vision Graphics: 60 Years of Visual Communications",
  "h1": "Sign Installation in New York City",
  "lead_html": "<p>Vision Graphics manages sign installation for NYC offices, lobbies, and storefronts.</p>",
  "ctas": [ { "label": "Get a Quote", "href": "#inquiry", "style": "primary" },
            { "label": "Send a Photo", "href": "#inquiry", "style": "secondary" } ],
  "image": "nyc-sign-installation-hero-01" }

{ "type": "trust-bar", "items": [ { "title": "Certificate of Insurance", "text": "On request", "icon": "clipboard-check" } ] }

{ "type": "card-grid", "h2": "Interior and Exterior Installation", "intro_html": "<p>…</p>",
  "groups": [ { "label": "Interior", "items": [ { "title": "Office Signs", "html": "<p>…</p>", "image": "nyc-sign-installation-interior-exterior-02" } ] } ] }

{ "type": "process-steps", "h2": "How It Works", "steps": [ "<strong>Receive your quote:</strong> We send an itemized quote with material options." ] }

{ "type": "faq", "h2": "Common Questions", "items": [ { "q": "Do you repair the wall?", "a_html": "<p>Yes. …</p>" } ] }

{ "type": "inquiry", "h2": "Ready to Schedule Your Installation?", "intro_html": "<p>Send photos of your wall…</p>" }
```

## The form and the quote popup

Every page uses the one Contact Form 7 form, `[contact-form-7 id="328910e" title="MicroSiteForm_nyc"]`,
set in **Settings → Vision Graphics NYC**. Only the first `inquiry` section on a page gets the
`<!-- VGNYC_FORM -->` placeholder, so the form shows on the page inside a white card.

Every quote button **inside the page content** opens that same form in a popup: any `main a.btn`
linking to `#inquiry` or `/nyc/contact/`. Header and footer buttons ("Request a quote", "Contact us")
are plain links to `/nyc/contact/`. `templates/landing-page.php` adds the
`<dialog id="vg-inquiry-modal">`, and `assets/landing.js` moves the single form into it and back
on close, so a page never has two forms. Without JavaScript the buttons jump to `#inquiry`.

## Calls to action

- **Hero:** shows one button, "Get a Quote". The batch config option `"hero_cta"` picks which of the writer's hero buttons to keep.
- **Long pages (9+ sections):** with `"mid_ctas": true`, `scripts/build_pages.py` adds two bands, never next to a section that already has a button:
  - **About 40% of the way down:** a full `cta-band` with the closing heading, its first line and "Get a Quote".
  - **About 75% of the way down:** a compact band with the hero's "send a photo" line and the dropped hero button ("Send a Photo").
- **Traceability:** both bands reuse the writer's own wording and carry `"generated": "mid-cta"`.

## Motion

- **Scroll reveal:** cards, quotes, gallery images, step items and FAQ rows fade up as they scroll in (`assets/landing.js`).
- **Hover:** cards lift and their images zoom slightly.
- **Marquee:** the trust bar scrolls continuously and pauses on hover.
- **Reduce motion:** everything is switched off when the visitor's system asks for less motion.

## Adding a section type

Add a section type only when no existing one fits:

1. Create `templates/sections/{type}.php`, returning `function (array $s, array $ctx): string`.
2. Add the type to `vgnyc_section_types()` in `includes/landing.php`.
3. Add its CSS to `assets/sections.css`, using the public.css tokens.
4. Document it in this file.
