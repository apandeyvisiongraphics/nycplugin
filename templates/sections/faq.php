<?php
/** Section: faq. Fields: h2, items [{q, a_html}]. Uses the existing .faqs styling; also feeds FAQPage JSON-LD (vgnyc_faq_schema). */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $h = '<section class="' . vgnyc_section_class('wrap section faqs vg-faq', $s) . '">';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    foreach ($s['items'] ?? array() as $item) {
        $h .= '<details><summary>' . esc_html($item['q'] ?? '') . '<span>+</span></summary>' . vgnyc_kses($item['a_html'] ?? '') . '</details>';
    }
    return $h . '</section>';
};
