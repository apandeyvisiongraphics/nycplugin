<?php
/** Section: testimonials. Fields: h2?, items [{quote, name, role?, company?}]. Initials avatars only, no photos. */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $h = '<section class="' . vgnyc_section_class('wrap section vg-quotes', $s) . '">';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    $h .= '<div class="vg-quotes__grid">';
    foreach ($s['items'] ?? array() as $q) {
        $name = (string)($q['name'] ?? '');
        $parts = preg_split('/\s+/', trim($name));
        $initials = $name === '' ? '' : strtoupper(substr($parts[0], 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''));
        $who = implode(', ', array_filter(array($q['role'] ?? '', $q['company'] ?? '')));
        $h .= '<figure class="vg-quote"><blockquote><p>' . esc_html($q['quote'] ?? '') . '</p></blockquote>';
        if ($name !== '') {
            $h .= '<figcaption><span class="vg-avatar" aria-hidden="true">' . esc_html($initials) . '</span><span class="vg-quote__who"><strong>' . esc_html($name) . '</strong>' . ($who ? '<span>' . esc_html($who) . '</span>' : '') . '</span></figcaption>';
        }
        $h .= '</figure>';
    }
    return $h . '</div></section>';
};
