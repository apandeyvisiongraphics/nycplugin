<?php
/**
 * Section: hero. Full-width image with a dark overlay and white text (a solid dark band when there is no image).
 * Fields: eyebrow?, h1, lead_html?, ctas? [{label, href, style: primary|secondary}], image? (slot id).
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $img = !empty($s['image']);
    $h = '<section class="vg-hero' . ($img ? '' : ' vg-hero--plain') . '">';
    if ($img) {
        $h .= '<div class="vg-hero__media">' . vgnyc_image($s['image'], array('hero' => true, 'sizes' => '100vw', 'class' => 'vg-hero__img')) . '</div><div class="vg-hero__scrim" aria-hidden="true"></div>';
    }
    $h .= '<div class="wrap vg-hero__inner"><div class="vg-hero__content">';
    if (!empty($s['eyebrow'])) { $h .= '<p class="vg-hero__eyebrow">' . esc_html($s['eyebrow']) . '</p>'; }
    $h .= '<h1>' . esc_html($s['h1'] ?? '') . '</h1>';
    if (!empty($s['lead_html'])) { $h .= '<div class="vg-hero__lead vg-prose">' . vgnyc_kses($s['lead_html']) . '</div>'; }
    if (!empty($s['ctas'])) {
        $h .= '<div class="vg-hero__actions">';
        foreach ($s['ctas'] as $cta) {
            $secondary = ($cta['style'] ?? '') === 'secondary';
            $h .= '<a class="btn ' . ($secondary ? 'vg-btn-outline' : 'primary') . '" href="' . esc_url($cta['href'] ?? '#inquiry') . '">'
                . ($secondary ? vgnyc_icon('camera', 17) . ' ' . esc_html($cta['label'] ?? '') : esc_html($cta['label'] ?? '') . ' ' . vgnyc_icon('arrow-up-right', 17)) . '</a>';
        }
        $h .= '</div>';
    }
    return $h . '</div></div></section>';
};
