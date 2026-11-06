<?php
/**
 * Dev-only (not shipped). LOCAL SITE ONLY.
 * Creates (or updates) a local stand-in for the live Contact Form 7 form
 * [contact-form-7 id="328910e" title="MicroSiteForm_nyc"] so the NYC pages can show and test the
 * real form and popup locally, and saves that shortcode in Settings → Vision Graphics NYC.
 * Fields follow the contact-page doc (inbox/done/2026-11-06/VGNYCLandingPages0511.html, section 3).
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
FORM;

// Reuse the stand-in if it already exists (matched by its shortcode hash).
$cf = function_exists('wpcf7_get_contact_form_by_hash') ? wpcf7_get_contact_form_by_hash(VGNYC_FORM_HASH) : null;
if (!$cf) { $cf = WPCF7_ContactForm::get_template(array('title' => 'MicroSiteForm_nyc')); }
$props = $cf->get_properties();
$props['form'] = $form;
$props['mail']['recipient'] = get_option('admin_email');
$props['mail']['subject'] = 'NYC sign quote request: [product-interest] in [borough]';
$props['mail']['sender'] = '[_site_title] <wordpress@' . parse_url(home_url(), PHP_URL_HOST) . '>';
$props['mail']['additional_headers'] = 'Reply-To: [your-name] <[your-email]>';
$props['mail']['attachments'] = '[project-photos]';
$props['mail']['body'] = "Name: [your-name]\nBusiness: [business-name]\nEmail: [your-email]\nPhone: [your-phone]\nProduct of interest: [product-interest]\nBorough: [borough]\nTiming: [project-timing]\n\nProject details:\n[project-details]\n\nSent from: [_url]";
$props['mail_2']['active'] = true;
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
