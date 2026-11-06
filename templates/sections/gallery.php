<?php
/** Section: gallery (project photos). Fields: h2?, intro_html?, items [{image (slot id), caption?}]. Caption falls back to the registry caption. */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $reg = vgnyc_images();
    $h = vgnyc_section_open('gallery', $s, $ctx);
    if (!empty($s['h2']) || !empty($s['intro_html'])) {
        $h .= '<div class="vg-head"><div>';
        if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
        if (!empty($s['intro_html'])) { $h .= '<div class="vg-prose vg-intro">' . vgnyc_kses($s['intro_html']) . '</div>'; }
        $h .= '</div></div>';
    }
    $h .= '<div class="vg-grid vg-grid--gallery">';
    foreach ($s['items'] ?? array() as $item) {
        $slot = (string)($item['image'] ?? '');
        $caption = !empty($item['caption']) ? $item['caption'] : (string)($reg[$slot]['caption'] ?? '');
        $h .= '<figure class="vg-gallery__item">' . vgnyc_image($slot, array('sizes' => '(max-width: 700px) 100vw, 25vw')) . ($caption !== '' ? '<figcaption>' . esc_html($caption) . '</figcaption>' : '') . '</figure>';
    }
    return $h . '</div>' . vgnyc_section_close();
};
