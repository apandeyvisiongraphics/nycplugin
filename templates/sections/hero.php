<?php
/** Section: hero. Fields: eyebrow?, h1, lead_html?, ctas? [{label, href, style: primary|secondary}], image? (slot id). */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $img = !empty($s['image']);
    $h = '<section class="' . vgnyc_section_class('wrap product-hero vg-hero', $s, $img ? '' : 'vg-hero--text') . '"><div>';
    if (!empty($s['eyebrow'])) { $h .= '<p class="eyebrow">' . esc_html($s['eyebrow']) . '</p>'; }
    $h .= '<h1>' . esc_html($s['h1'] ?? '') . '</h1>';
    if (!empty($s['lead_html'])) { $h .= '<div class="lead vg-prose">' . vgnyc_kses($s['lead_html']) . '</div>'; }
    if (!empty($s['ctas'])) {
        $h .= '<div class="hero-actions">';
        foreach ($s['ctas'] as $cta) {
            $secondary = ($cta['style'] ?? '') === 'secondary';
            $h .= '<a class="btn ' . ($secondary ? 'secondary' : 'primary') . '" href="' . esc_url($cta['href'] ?? '#inquiry') . '">'
                . ($secondary ? vgnyc_icon('camera', 17) . ' ' . esc_html($cta['label'] ?? '') : esc_html($cta['label'] ?? '') . ' ' . vgnyc_icon('arrow-up-right', 17)) . '</a>';
        }
        $h .= '</div>';
    }
    $h .= '</div>';
    if ($img) { $h .= '<figure class="project-image product-photo vg-hero__media">' . vgnyc_image($s['image'], array('hero' => true, 'sizes' => '(max-width: 980px) 100vw, 50vw')) . '</figure>'; }
    return $h . '</section>';
};
