<?php
/**
 * Section: inquiry (the form). Dark band, heading on the left, form in a white card on the right.
 * Fields: h2, intro_html?, note_html?.
 * Always id="inquiry" with the single <!-- VGNYC_FORM --> placeholder, which the renderer
 * replaces with the Contact Form 7 shortcode from Settings → Vision Graphics NYC
 * (email/phone fallback when it is not configured). Only the first inquiry section on a page
 * gets the placeholder.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $h = vgnyc_section_open('inquiry', $s, $ctx, '', empty($ctx['form']) ? '' : ' id="inquiry"') . '<div class="vg-inquiry__grid"><div class="vg-inquiry__intro">';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    if (!empty($s['intro_html'])) { $h .= '<div class="vg-prose">' . vgnyc_kses($s['intro_html']) . '</div>'; }
    $h .= '</div><div class="vg-inquiry__card">' . (empty($ctx['form']) ? '' : '<!-- VGNYC_FORM -->');
    if (!empty($s['note_html'])) { $h .= '<div class="vg-inquiry__note">' . vgnyc_kses($s['note_html']) . '</div>'; }
    return $h . '</div></div>' . vgnyc_section_close();
};
