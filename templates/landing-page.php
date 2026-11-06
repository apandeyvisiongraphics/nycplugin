<?php
/**
 * Generic landing page: renders content/pages/{slug}.json section by section.
 * Returns a callable that turns the decoded JSON into the page's <main> element.
 * Only the first "inquiry" section gets the form placeholder (one form per page).
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $page) {
    $h = '<main class="vg-landing">';
    $form = false;
    foreach ($page['sections'] ?? array() as $i => $s) {
        if (!is_array($s)) { continue; }
        $type = (string)($s['type'] ?? '');
        $ctx = array('index' => $i, 'form' => $type === 'inquiry' && !$form);
        if ($type === 'inquiry') { $form = true; }
        $h .= vgnyc_section($type, $s, $ctx);
    }
    return $h . '</main>';
};
