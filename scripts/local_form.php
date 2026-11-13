<?php
/**
 * Dev-only (not shipped). LOCAL SITE ONLY.
 * Creates (or updates) a local stand-in for the live Contact Form 7 form
 * [contact-form-7 id="328910e" title="MicroSiteForm_nyc"] so the NYC pages can show and test the
 * real form and popup locally, and saves that shortcode in Settings → Vision Graphics NYC.
 * Fields mirror the live MicroSiteForm_nyc form (same field names), laid out as in docs/LIVE_FORM_SETUP.md.
 * On live, the existing MicroSiteForm_nyc form is used as-is; this script refuses to run there.
 *
 * Usage: php -d mysqli.default_socket=… scripts/local_form.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = parse_url(getenv('VGNYC_BASE_URL') ?: 'http://pandeystore.local', PHP_URL_HOST);
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
if (wp_get_environment_type() !== 'local') { fwrite(STDERR, "Refusing: not a local environment\n"); exit(2); }
if (!class_exists('WPCF7_ContactForm')) { fwrite(STDERR, "Contact Form 7 is not active\n"); exit(2); }

const VGNYC_FORM_HASH = '328910e';
$shortcode = '[contact-form-7 id="' . VGNYC_FORM_HASH . '" title="MicroSiteForm_nyc"]';

$form = <<<'FORM'
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
FORM;

// Reuse the stand-in if it already exists (matched by its shortcode hash).
$cf = function_exists('wpcf7_get_contact_form_by_hash') ? wpcf7_get_contact_form_by_hash(VGNYC_FORM_HASH) : null;
if (!$cf) { $cf = WPCF7_ContactForm::get_template(array('title' => 'MicroSiteForm_nyc')); }
$props = $cf->get_properties();
$props['form'] = $form;
$props['mail']['recipient'] = get_option('admin_email');
$props['mail']['subject'] = 'New NYC inquiry from [your-name]';
$props['mail']['sender'] = '[_site_title] <wordpress@' . parse_url(home_url(), PHP_URL_HOST) . '>';
$props['mail']['additional_headers'] = 'Reply-To: [your-name] <[your-email]>';
$props['mail']['attachments'] = '[your-photo]';
$props['mail']['body'] = <<<'MAIL'
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
MAIL;
$props['mail_2']['active'] = false; // the live form has no autoresponder
$props['mail_2']['recipient'] = '[your-email]';
$props['mail_2']['subject'] = 'We received your sign quote request';
$props['mail_2']['sender'] = '[_site_title] <wordpress@' . parse_url(home_url(), PHP_URL_HOST) . '>';
$props['mail_2']['additional_headers'] = '';
$props['mail_2']['body'] = "Hi [your-name],\n\nThanks for sending your project. We have your request for [product-interest] in [borough].\n\nA Vision Graphics project lead will review your photos and details and reply within one business day with questions, a site survey request, or a quote. If you need to add photos or details, reply to this email.\n\nThe Vision Graphics team";
$props['messages']['mail_sent_ok'] = 'Thanks. We have your project. A Vision Graphics project lead will review your photos and details and reply within one business day with questions, a site survey request, or a quote.';
$cf->set_properties($props);
$id = $cf->save();
// Make the live shortcode id ("328910e") resolve to this local form.
update_post_meta($id, '_hash', VGNYC_FORM_HASH . substr(sha1('vgnyc-local-' . $id), 7));

$o = get_option('vgnyc_settings', array());
$o = is_array($o) ? $o : array();
$o['form'] = $shortcode;
update_option('vgnyc_settings', $o);

$html = do_shortcode($shortcode);
echo 'Local form #' . $id . ' saved; shortcode renders a form: ' . (stripos($html, '<form') !== false ? 'yes' : 'NO') . "\n";
