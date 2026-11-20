<?php
/**
 * Wraps a page's <main> element with the shared site chrome.
 * Returns a callable: function (string $main, bool $chrome = true): string.
 * Routes with "chrome": false in the manifest get $main unchanged.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function ($main, $chrome = true) {
    if (!$chrome) { return $main; }
    return vgnyc_partial('utility-bar') . vgnyc_partial('header') . vgnyc_partial('product-ribbon') . $main . vgnyc_partial('footer');
};
