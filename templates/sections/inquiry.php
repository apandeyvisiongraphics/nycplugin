<?php
/**
 * Section: inquiry (the form). Fields: h2, intro_html?, note_html?.
 * Always id="inquiry" with the single <!-- VGNYC_FORM --> placeholder, which the renderer
 * replaces with the Contact Form 7 shortcode from Settings → Vision Graphics NYC
 * (email/phone fallback when it is not configured). Only the first inquiry section on a page
 * gets the placeholder.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $h = '<section class="' . vgnyc_section_class('wrap section landing-conversion vg-inquiry', $s) . '"' . (empty($ctx['form']) ? '' : ' id="inquiry"') . '><div>';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    if (!empty($s['intro_html'])) { $h .= '<div class="lead vg-prose">' . vgnyc_kses($s['intro_html']) . '</div>'; }
    $h .= '</div><div class="vg-inquiry__form">' . (empty($ctx['form']) ? '' : '<!-- VGNYC_FORM -->');
    if (!empty($s['note_html'])) { $h .= '<div class="vg-inquiry__note">' . vgnyc_kses($s['note_html']) . '</div>'; }
    return $h . '</div></section>';
};
