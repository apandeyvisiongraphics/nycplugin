# Live form setup: MicroSiteForm_nyc

The 1.4.0 pages show your existing Contact Form 7 form (`[contact-form-7 id="328910e" title="MicroSiteForm_nyc"]`)
on the page and in the quote popup. The two-column field layout in the design comes from the form's own markup,
so paste the markup below into the live form to make it look exactly like the local preview.

## 1. Form tab

WP Admin → Contact → Contact Forms → **MicroSiteForm_nyc** → **Form** tab. Replace the content with:

```
<div class="vgf-grid">
<p class="vgf-field"><label>Name <abbr title="required">*</abbr>[text* your-name autocomplete:name]</label></p>
<p class="vgf-field"><label>Business Name <abbr title="required">*</abbr>[text* business-name autocomplete:organization]</label></p>
<p class="vgf-field"><label>Email <abbr title="required">*</abbr>[email* your-email autocomplete:email]</label><small>We reply to this address.</small></p>
<p class="vgf-field"><label>Phone [tel your-phone autocomplete:tel]</label><small>Add it if you prefer a call.</small></p>
<p class="vgf-field"><label>Product of interest <abbr title="required">*</abbr>[select* product-interest first_as_label "Select a product" "Storefront sign" "Channel letters" "Dimensional letters" "Building sign" "Blade sign" "Window graphics" "Lobby or reception sign" "Office signs" "Elevator lobby or directional signs" "ADA signs" "Door signs" "Installation only" "Sign removal and replacement" "Sign repair" "Multiple signs or a full program" "Not sure yet or I have a question"]</label><small>Pick the closest match, or choose Not sure yet or I have a question.</small></p>
<p class="vgf-field"><label>Borough <abbr title="required">*</abbr>[select* borough first_as_label "Select a borough" "Manhattan" "Brooklyn" "Queens" "The Bronx" "Staten Island"]</label></p>
<p class="vgf-field vgf-wide"><label>Project timing <abbr title="required">*</abbr>[select* project-timing first_as_label "Select timing" "As soon as possible" "Within 1 month" "1 to 3 months" "More than 3 months" "Planning or budgeting only"]</label><small>Lit exterior signs usually need three to five weeks plus permit review.</small></p>
<p class="vgf-field vgf-wide"><label>Project details [textarea project-details x3]</label></p>
<p class="vgf-field vgf-wide"><label>Photos and artwork [file project-photos limit:10mb filetypes:heic|jpg|jpeg|png|pdf|ai|eps]</label><small>Add photos of the wall, the old sign, and your logo. HEIC, JPG, PNG, PDF, AI and EPS, up to 10 MB.</small></p>
<p class="vgf-submit vgf-wide">[submit "Get My Quote"]</p>
</div>
```

## 2. Mail tab (check, don't skip)

The fields are named `your-name`, `business-name`, `your-email`, `your-phone`, `product-interest`, `borough`,
`project-timing`, `project-details`, `project-photos`. If the Mail tab uses other names, the email will show the
raw tags. Keep your existing **To** and **From** addresses, and use or adapt:

- **Subject:** `NYC sign quote request: [product-interest] in [borough]`
- **Additional headers:** `Reply-To: [your-name] <[your-email]>`
- **File attachments:** `[project-photos]`
- **Message body:**

```
Name: [your-name]
Business: [business-name]
Email: [your-email]
Phone: [your-phone]
Product of interest: [product-interest]
Borough: [borough]
Timing: [project-timing]

Project details:
[project-details]

Sent from: [_url]
```

## 3. Messages tab (optional)

"Sender's message was sent successfully":

```
Thanks. We have your project. A Vision Graphics project lead will review your photos and details and reply within one business day with questions, a site survey request, or a quote.
```

## 4. Settings → Vision Graphics NYC

- **Form shortcode:** `[contact-form-7 id="328910e" title="MicroSiteForm_nyc"]`
- **Privacy policy:** your HTTPS privacy-policy URL (outside /nyc/), used by `/nyc/privacy/` in the footer and form note.

After saving, send one test from /nyc/contact/ and one from a popup button on /nyc/, with a photo attached,
and confirm the email arrives with every field filled.
