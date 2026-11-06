<?php
/** Section: faq. Centered accordion (+ turns into ×). Fields: h2, items [{q, a_html}]. Also feeds FAQPage JSON-LD (vgnyc_faq_schema). */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $h = vgnyc_section_open('faq', $s, $ctx) . '<div class="faqs vg-faq__inner">';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    foreach ($s['items'] ?? array() as $item) {
        $h .= '<details><summary>' . esc_html($item['q'] ?? '') . '<span>+</span></summary>' . vgnyc_kses($item['a_html'] ?? '') . '</details>';
    }
    return $h . '</div>' . vgnyc_section_close();
};
