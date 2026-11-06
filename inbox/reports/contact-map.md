# Page map: /nyc/contact/

| | |
|---|---|
| Route | `/nyc/contact/` |
| Status | **REPLACEMENT** (current page stays live; draft previews at `?vgnyc_preview=1`) |
| Source | `inbox/docs/VGNYCLandingPages0511.html` |
| Meta title | Request a Sign Quote in NYC | Vision Graphics NYC (**49** chars) |
| Meta description | Send photos and a few details about your NYC sign project. A Vision Graphics project lead replies within one business day with next steps and a quote. (**150** chars) |
| H1 | Request a Custom Sign Quote for Your New York City Project |

## Sections in order

### 1. Hero → `hero` (existing markup/CSS)

- `p` <strong>H1:</strong> Request a Custom Sign Quote for Your New York City Project
- `p` <strong>Intro:</strong> Send photos of your wall or storefront, rough sizes, and the building address, and we will start your NYC sign quote.
- `p` A Vision Graphics project lead replies within one business day with questions, a site survey request, or a quote. You do not need final artwork.

### 2. Trust Strip → `trust-bar` (new section template)

- `p` <strong>60 Years</strong> | Vision Graphics legacy
- `p` <strong>Certificate of Insurance</strong> | On request
- `p` <strong>One Business Day</strong> | Our standard reply time
- `p` (Icon row. No review counts or star ratings until real reviews exist.)

### 3. (no label) → `inquiry` (existing markup/CSS)

- `p` <strong>Form heading:</strong> Tell us about your sign
- `h3` About you
- `li` <strong>Name</strong> (required)
- `li` <strong>Business Name</strong> (required)
- `li` <strong>Email</strong> (required). Helper text: We reply to this address.
- `li` <strong>Phone</strong> (optional). Helper text: Add it if you prefer a call.
- `h3` About your sign
- `li` <strong>Product of interest</strong> (required, grouped dropdown). Helper text: Pick the closest match, or choose Not sure yet or I have a question.
- `li` <strong>Exterior:</strong> Storefront sign / Channel letters / Dimensional letters / Building sign / Blade sign / Window graphics
- `li` <strong>Interior:</strong> Lobby or reception sign / Office signs / Elevator lobby or directional signs / ADA signs / Door signs
- `li` <strong>Services:</strong> Installation only / Sign removal and replacement / Sign repair
- `li` <strong>Other:</strong> Multiple signs or a full program / Not sure yet or I have a question
- `li` <strong>Project timing</strong> (required dropdown): As soon as possible / Within 1 month / 1 to 3 months / More than 3 months / Planning or budgeting only. Helper text: Lit exterior signs usually need three to five weeks plus permit review.
- `li` <strong>Borough</strong> (required dropdown): Manhattan / Brooklyn / Queens / The Bronx / Staten Island
- `li` <strong>Photos and artwork</strong> (optional upload). Helper text: Add photos of the wall, the old sign, and your logo; up to 5 files, 10 MB each. We accept HEIC, JPG, PNG, PDF, AI, and EPS.
- `li` <strong>Project details</strong> (optional, short text)
- `h3` Add building details (optional, collapsed by default)
- `li` <strong>Install address.</strong> Helper text: A street address helps us plan permits and access.
- `li` <strong>Building type</strong> (dropdown): Office building / Retail storefront / Restaurant or cafe / Medical or dental office / Mixed-use or residential building / Property management portfolio / Other
- `li` <strong>Building requirements</strong> (checkboxes): Old sign to remove / Certificate of insurance required / After-hours or weekend install / Landlord or board approval needed / Landmark or historic building / Limits on drilling or adhesives
- `li` <strong>Number of locations</strong> (dropdown, shown only when Multiple signs or a full program is chosen): 2 to 5 / 6 to 10 / More than 10
- `p` <strong>Button:</strong> Get My Quote
- `p` <strong>Microcopy under button:</strong> We reply within one business day. See our <strong>privacy policy</strong>.

### 4. Confirmation Message → `form-config` (not a page section)

- `p` <strong>On-page message (shown after submit, replacing the form):</strong>
- `p` Thanks, [First Name]. We have your project. A Vision Graphics project lead will review your photos and details and reply within one business day with questions, a site survey request, or a quote.
- `p` To add anything, reply to the email we just sent you.
- `p` <strong><em>Auto-reply email:</em></strong>
- `p` <strong><em>Subject:</em></strong> <em>We received your sign quote request</em>
- `p` <em>Hi [First Name],</em>
- `p` <em>Thanks for sending your project. We have your request for [Product of interest] in [Borough].</em>
- `p` <em>A Vision Graphics project lead will review your photos and details and reply within one business day with questions, a site survey request, or a quote. If you need to add photos or details, reply to this email.</em>
- `p` <em>The Vision Graphics team</em>
- `p` <strong><em>Developer note:</em></strong> <em>send the confirmation to a thank-you URL so the conversion can be tracked.</em>

### 5. H2: What Happens Next → `process-steps` (new section template)

- `li` <strong>We review your photos and details:</strong> A project lead checks your photos, sizes, and building information.
- `li` <strong>We come back with questions or a site survey:</strong> If we need more detail, we ask. For lit and exterior signs, we may arrange a survey.
- `li` <strong>You receive a quote with options:</strong> Your quote shows material options so that you can compare finish and cost.
- `p` Our sign installation page covers building rules, certificates of insurance, and after-hours installs.

### 6. H2: Quick Answers → `faq` (existing markup/CSS)

- `p` <strong>Do I need final artwork?</strong>
- `p` No. Send your logo if you have one, or a photo of your old sign, and we will prepare a drawing for you to approve before anything is made. We accept HEIC, JPG, PNG, PDF, AI, and EPS files.
- `p` <strong>Can you quote from photos?</strong>
- `p` Yes. Photos of the wall or storefront plus rough sizes are enough for a first quote. For lit and exterior signs, we may ask for a site survey before the final price.
- `p` <strong>How fast will you reply?</strong>
- `p` We reply within one business day. If your project has a firm deadline, choose your timing in the form or tell us in the details box, and we will confirm what is possible.

### 7. Testimonial → `testimonials` (new section template)

- `p` I sent two photos and an address, and had questions and a quote the next day. Natalie Greene, Operations Manager, Greene Dental Partners.

## Image slots

| Slot | File name (designer) | Section | Ratio | Min width | Sizes | Alt (proposed, needs OK) | Writer prompt |
|---|---|---|---|---|---|---|---|
| – | – | – | – | – | – | – | No images requested |

## Links

| Anchor text | URL in doc | Will render as | Type | Status |
|---|---|---|---|---|
| – | – | – | – | No links in doc |

## Issues

- **Replacement** of the current contact page, including the Ed Kerins sales-contact block. The email and phone still show as the automatic fallback if the form can't load. Preview it at `/nyc/contact/?vgnyc_preview=1`.
- **Form fields, button, confirmation message and auto-reply (sections 3–4)** are Contact Form 7 settings for `MicroSiteForm_nyc`, not page content. Conditional and collapsed fields need a CF7 add-on.
- **Links added (as agreed):** "privacy policy" → `/nyc/privacy/`, and "sign installation page" → `/nyc/sign-installation/` (publish both pages together).
- **Testimonial:** Natalie Greene. Confirm this is a real client who agreed to be quoted.
- **Thank-you redirect (developer note):** recommended instead: a GTM trigger on CF7's `wpcf7mailsent` event, with no page change.
