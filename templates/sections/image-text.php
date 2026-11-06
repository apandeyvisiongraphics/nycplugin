<?php
/** Section: image-text. Fields: h2, html, image (slot id), image_side? ("left" | "right", default right). */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $left = ($s['image_side'] ?? 'right') === 'left';
    $h = vgnyc_section_open('split', $s, $ctx, $left ? 'vg-split--left' : '') . '<div class="vg-split__grid"><div class="vg-split__body">';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    $h .= '<div class="vg-prose">' . vgnyc_kses($s['html'] ?? '') . '</div></div>';
    if (!empty($s['image'])) { $h .= '<figure class="vg-media">' . vgnyc_image($s['image']) . '</figure>'; }
    return $h . '</div>' . vgnyc_section_close();
};
