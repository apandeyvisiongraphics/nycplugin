<?php
/**
 * Section: cta-band. A call-to-action band between content sections (uses the site's .cta-band look).
 * Fields: h2?, html?, cta {label, href, style?: primary|secondary}, variant? ("compact": one line + button).
 * Buttons that point to #inquiry open the quote popup (assets/landing.js).
 * Mid-page bands added by scripts/build_pages.py carry "generated": "mid-cta" and reuse the
 * writer's own wording (closing heading/intro, hero "send a photo" line).
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $compact = ($s['variant'] ?? '') === 'compact';
    $secondary = ($s['cta']['style'] ?? '') === 'secondary';
    $h = '<section class="' . vgnyc_section_class('vg-cta', $s, $compact ? 'vg-cta--compact' : '') . '"><div class="wrap"><div class="cta-band vg-cta__band">';
    $h .= '<div class="vg-cta__text">';
    // Styled as a heading but not an <h2>: mid-page bands repeat the closing heading, and a page should not have duplicate H2s.
    if (!empty($s['h2'])) { $h .= '<p class="vg-cta__title">' . esc_html($s['h2']) . '</p>'; }
    if (!empty($s['html'])) { $h .= '<div class="vg-prose">' . vgnyc_kses($s['html']) . '</div>'; }
    $h .= '</div>';
    if (!empty($s['cta'])) {
        $h .= '<a class="btn ' . ($secondary ? 'dark' : 'primary') . '" href="' . esc_url($s['cta']['href'] ?? '#inquiry') . '">'
            . ($secondary ? vgnyc_icon('camera', 18) . ' ' . esc_html($s['cta']['label'] ?? '') : esc_html($s['cta']['label'] ?? '') . ' ' . vgnyc_icon('arrow-up-right', 18)) . '</a>';
    }
    return $h . '</div></div></section>';
};
