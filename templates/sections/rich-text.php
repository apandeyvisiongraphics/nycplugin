<?php
/** Section: rich-text. Fields: eyebrow?, h2?, html (paragraphs, lists, tables), cta? {label, href}. */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $h = vgnyc_section_open('rich', $s, $ctx);
    if (!empty($s['eyebrow'])) { $h .= '<p class="vg-eyebrow">' . esc_html($s['eyebrow']) . '</p>'; }
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    if (!empty($s['html'])) { $h .= '<div class="vg-prose vg-rich__body">' . vgnyc_kses($s['html']) . '</div>'; }
    if (!empty($s['cta'])) {
        $h .= '<p class="vg-rich__cta"><a class="btn primary" href="' . esc_url($s['cta']['href'] ?? '#inquiry') . '">' . esc_html($s['cta']['label'] ?? '') . ' ' . vgnyc_icon('arrow-up-right', 17) . '</a></p>';
    }
    return $h . vgnyc_section_close();
};
