<?php
/**
 * Dev-only (not shipped): prints short-lived admin auth cookies as JSON so QA tools can
 * view drafts and ?vgnyc_preview=1 on the local site. Usage: php scripts/admin_cookie.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = parse_url(getenv('VGNYC_BASE_URL') ?: 'http://pandeystore.local', PHP_URL_HOST);
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';
if (wp_get_environment_type() !== 'local') { fwrite(STDERR, "Refusing: not a local environment\n"); exit(2); }
$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$admins) { fwrite(STDERR, "No administrator found\n"); exit(2); }
$uid = (int)$admins[0];
$exp = time() + 2 * HOUR_IN_SECONDS;
echo json_encode(array(
    array('name' => AUTH_COOKIE, 'value' => wp_generate_auth_cookie($uid, $exp, 'auth')),
    array('name' => LOGGED_IN_COOKIE, 'value' => wp_generate_auth_cookie($uid, $exp, 'logged_in')),
)), "\n";
