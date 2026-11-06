<?php
/** Section: trust-bar. Proof strip with diamond markers. Fields: items [{title, text, icon?}] (icon kept for future use). */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $h = '<section class="vg-trust"><div class="wrap"><ul class="vg-trust__list">';
    foreach ($s['items'] ?? array() as $item) {
        $h .= '<li><strong>' . esc_html($item['title'] ?? '') . '</strong><span>' . esc_html($item['text'] ?? '') . '</span></li>';
    }
    return $h . '</ul></div></section>';
};
