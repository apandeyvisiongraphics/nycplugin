# Section library

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
| `trust-bar` | Row of short proof points with icons | `items` [`{title, text, icon?}`] (icon: any `vgnyc_icon()` name, e.g. award, shield-check, clipboard-check, clock, map-pin, camera, user, layers) |
| `rich-text` | Heading + paragraphs, lists or a table | `eyebrow?`, `h2?`, `html`, `cta?` `{label, href}` |
| `image-text` | Heading + text beside one image | `h2`, `html`, `image`, `image_side?` (`left`/`right`) |
| `features-grid` | Heading + intro + cards of title/text | `h2`, `intro_html?`, `image?` (beside the intro), `items_heading?`, `items` [`{title, html?, image?}`] |
| `card-grid` | Image cards, optionally grouped | `h2`, `intro_html?`, `items` [`{title, html?, image}`] **or** `groups` [`{label, intro_html?, items}`] |
| `process-steps` | Numbered steps | `h2`, `intro_html?`, `steps` [html], `after_html?`, `image?` |
| `testimonials` | Client quotes, initials avatars | `h2?`, `items` [`{quote, name, role?, company?}`] |
| `gallery` | Project photos | `h2?`, `intro_html?`, `items` [`{image, caption?}`] |
| `faq` | Questions and answers, plus FAQPage JSON-LD | `h2`, `items` [`{q, a_html}`] |
| `inquiry` | The form (always `id="inquiry"`) | `h2`, `intro_html?`, `note_html?` |

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

## The form

Every page uses the one Contact Form 7 form, `[contact-form-7 id="328910e" title="MicroSiteForm_nyc"]`,
set in **Settings → Vision Graphics NYC**. Only the first `inquiry` section on a page gets the
`<!-- VGNYC_FORM -->` placeholder. Buttons point to `#inquiry` (or `/nyc/contact/` on pages without a
form section).

## Adding a section type

Add a section type only when no existing one fits:

1. Create `templates/sections/{type}.php`, returning `function (array $s, array $ctx): string`.
2. Add the type to `vgnyc_section_types()` in `includes/landing.php`.
3. Add its CSS to `assets/sections.css`, using the public.css tokens.
4. Document it in this file.
