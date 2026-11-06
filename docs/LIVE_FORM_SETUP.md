# Live form setup: MicroSiteForm_nyc

The 1.4.0 pages show your existing Contact Form 7 form (`[contact-form-7 id="328910e" title="MicroSiteForm_nyc"]`)
on the page and in the quote popup. This is your live form with the same field names, laid out in the
two-column design. Nothing else about the form changes.

## 1. Form tab

WP Admin → Contact → Contact Forms → **MicroSiteForm_nyc** → **Form** tab. Replace the content with:

```
<div class="vgf-grid">
<p class="vgf-field vgf-wide vgf-note"><small>Fields marked with <abbr title="required">*</abbr> are required.</small></p>
<p class="vgf-field"><label>Your name <abbr title="required">*</abbr>[text* your-name autocomplete:name placeholder "Your name"]</label></p>
<p class="vgf-field"><label>Your email <abbr title="required">*</abbr>[email* your-email autocomplete:email placeholder "Your email"]</label></p>
<p class="vgf-field"><label>Company [text your-company autocomplete:organization placeholder "Company"]</label></p>
<p class="vgf-field"><label>Phone [tel your-phone autocomplete:tel placeholder "Phone"]</label></p>
<p class="vgf-field"><label>Product of interest [text your-product placeholder "Product of interest"]</label></p>
<p class="vgf-field"><label>Project timing [text your-timing placeholder "Project timing"]</label></p>
<p class="vgf-field vgf-wide"><label>Tell us about your project <abbr title="required">*</abbr>[textarea* your-project x4 placeholder "Tell us about your project"]</label></p>
<p class="vgf-field"><label>ZIP code [text your-zip autocomplete:postal-code placeholder "ZIP code"]</label></p>
<p class="vgf-field"><label>Attach a photo [file your-photo limit:5mb filetypes:jpg|jpeg|png|webp|pdf]</label><small>jpg, png, webp, or pdf, max 5MB</small></p>
<p class="vgf-submit vgf-wide">[submit "Send inquiry"]</p>
</div>
[hidden nyc_page]
[hidden nyc_source]
[hidden nyc_product]
[hidden utm_source default:get]
[hidden utm_medium default:get]
[hidden utm_campaign default:get]
[hidden utm_content default:get]
[hidden utm_term default:get]
```

What changed compared with the previous form:
- Same fields, same names, same required fields, same placeholders, same submit text ("Send inquiry").
- Fields are wrapped in labels and laid out in two columns (one column on phones).
- The project details box moved below the short fields; ZIP code sits next to the photo upload.
- `utm_*` hidden fields now fill themselves from the page link (`default:get`), e.g. `?utm_source=google`.
  `nyc_page`, `nyc_source` and `nyc_product` are unchanged (nothing on the site fills them).

## 2. Mail tab → Message body

Same as before, plus **Page URL: [_url]** (the page the form was sent from):

```
New inquiry from the Vision Graphics NYC site.

Name: [your-name]
Email: [your-email]
Company: [your-company]
Phone: [your-phone]
ZIP: [your-zip]
Product of interest: [your-product]
Project timing: [your-timing]

Project details:
[your-project]

--- Source tracking ---
Page URL: [_url]
Page: [nyc_page]
Source: [nyc_source]
Product: [nyc_product]
UTM Source: [utm_source]
UTM Medium: [utm_medium]
UTM Campaign: [utm_campaign]
UTM Content: [utm_content]
UTM Term: [utm_term]

--- Submission info ---
Date: [_date]
Time: [_time]
IP: [_remote_ip]
```

Keep your existing To, From, Subject and headers. If photos should arrive as attachments, put `[your-photo]`
in **File attachments**.

## 3. Settings → Vision Graphics NYC

- **Form shortcode:** `[contact-form-7 id="328910e" title="MicroSiteForm_nyc"]`
- **Privacy policy:** your HTTPS privacy-policy URL (outside /nyc/), used by `/nyc/privacy/` in the footer and form note.

After saving, send one test from /nyc/contact/ and one from a popup button on /nyc/, with a photo attached,
and confirm the email arrives with every field filled.
