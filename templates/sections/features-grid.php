<?php
/**
 * Section: features-grid. Fields: h2, intro_html?, items_heading?, items [{title, html?, image?}], image? (slot id, shown beside the intro).
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $img = !empty($s['image']);
    $h = '<section class="' . vgnyc_section_class('wrap section vg-features', $s) . '"><div class="vg-head' . ($img ? ' vg-head--media' : '') . '"><div>';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    if (!empty($s['intro_html'])) { $h .= '<div class="vg-prose vg-intro">' . vgnyc_kses($s['intro_html']) . '</div>'; }
    $h .= '</div>';
    if ($img) { $h .= '<figure class="vg-media">' . vgnyc_image($s['image']) . '</figure>'; }
    $h .= '</div>';
    if (!empty($s['items_heading'])) { $h .= '<p class="vg-subhead">' . esc_html($s['items_heading']) . '</p>'; }
    $n = count($s['items'] ?? array());
    $h .= '<div class="vg-grid' . ($n === 2 ? ' vg-grid--cols-2' : ($n % 4 === 0 ? ' vg-grid--cols-4' : '')) . '">';
    foreach ($s['items'] ?? array() as $item) {
        $h .= '<div class="vg-card">';
        if (!empty($item['image'])) { $h .= '<figure class="vg-card__media">' . vgnyc_image($item['image'], array('sizes' => '(max-width: 700px) 100vw, 33vw')) . '</figure>'; }
        $h .= '<h3>' . esc_html($item['title'] ?? '') . '</h3>';
        if (!empty($item['html'])) { $h .= '<div class="vg-prose">' . vgnyc_kses($item['html']) . '</div>'; }
        $h .= '</div>';
    }
    return $h . '</div></section>';
};
