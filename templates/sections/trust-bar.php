<?php
/** Section: trust-bar. Fields: items [{title, text, icon?}] (icon: any vgnyc_icon() name). */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $h = '<section class="' . vgnyc_section_class('vg-trust', $s) . '"><div class="wrap"><ul class="vg-trust__list">';
    foreach ($s['items'] ?? array() as $item) {
        $h .= '<li><span class="vg-trust__icon">' . vgnyc_icon($item['icon'] ?? 'check', 22) . '</span><span class="vg-trust__text"><strong>' . esc_html($item['title'] ?? '') . '</strong><span>' . esc_html($item['text'] ?? '') . '</span></span></li>';
    }
    return $h . '</ul></div></section>';
};
