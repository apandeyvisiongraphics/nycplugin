<?php
/**
 * Dev-only (not shipped). Renders candidate service JSON through the real
 * templates/service-page.php with WordPress loaded, and compares the result
 * byte for byte with the original <main> markup.
 *
 * Usage: php scripts/roundtrip.php <dir>
 *   <dir> holds {slug}.json and {slug}.main.html pairs.
 * Prints a JSON object: { slug: "ok" | "diff at byte N: …" }.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$dir = $argv[1] ?? '';
if (!is_dir($dir)) { fwrite(STDERR, "usage: php roundtrip.php <dir>\n"); exit(2); }

define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = parse_url(getenv('VGNYC_BASE_URL') ?: 'http://pandeystore.local', PHP_URL_HOST);
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
if (!function_exists('vgnyc_template')) { fwrite(STDERR, "vg-nyc-website plugin is not active\n"); exit(2); }

$out = array();
foreach (glob($dir . '/*.json') as $file) {
    $slug = basename($file, '.json');
    $expected = file_get_contents($dir . '/' . $slug . '.main.html');
    $data = json_decode(file_get_contents($file), true);
    $got = call_user_func(vgnyc_template('service-page'), $data);
    if ($got === $expected) { $out[$slug] = 'ok'; continue; }
    $n = strspn($got ^ $expected, "\0");
    $out[$slug] = 'diff at byte ' . $n . ': expected …' . substr($expected, max(0, $n - 60), 160) . '… got …' . substr($got, max(0, $n - 60), 160) . '…';
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
