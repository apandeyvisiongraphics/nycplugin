<?php
/**
 * Image slots (Phase 2).
 *
 * - Registry: content/images.json, keyed by slot id (nyc-{page}-{section}-{nn}).
 * - Files:    assets/images/{slot}-{width}.webp, one per registered size.
 * - vgnyc_image() renders a responsive <img> when the files exist, otherwise a
 *   labeled placeholder (the writer's prompt is shown to administrators only).
 * - Local development only: files dropped in images-inbox/ named {slot}.jpg|png|webp
 *   are converted to the registered WebP sizes (VGNYC_DEV + local environment).
 * - Admin: Vision Graphics NYC → Image Brief, with a CSV download for the designer.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

function vgnyc_images() {
    static $registry;
    if ($registry === null) {
        $file = dirname(__DIR__) . '/content/images.json';
        $registry = is_file($file) ? json_decode((string)file_get_contents($file), true) : array();
        if (!is_array($registry)) { $registry = array(); }
    }
    return $registry;
}

function vgnyc_image_dir() {
    return dirname(__DIR__) . '/assets/images/';
}

/** Registered sizes that exist on disk: width => absolute path. */
function vgnyc_image_files($slot) {
    $reg = vgnyc_images();
    if (!isset($reg[$slot]) || !preg_match('/^[a-z0-9-]+$/D', $slot)) { return array(); }
    $files = array();
    $sizes = array_map('intval', (array)($reg[$slot]['sizes'] ?? array(480, 960)));
    sort($sizes);
    foreach ($sizes as $w) {
        $path = vgnyc_image_dir() . $slot . '-' . $w . '.webp';
        if (is_file($path)) { $files[$w] = $path; }
    }
    return $files;
}

/** Ratio "16:9" => array(16, 9). */
function vgnyc_image_ratio($slot) {
    $reg = vgnyc_images();
    if (preg_match('/^(\d+(?:\.\d+)?):(\d+(?:\.\d+)?)$/', (string)($reg[$slot]['ratio'] ?? '3:2'), $m) && (float)$m[2] > 0) {
        return array((float)$m[1], (float)$m[2]);
    }
    return array(3.0, 2.0);
}

/** A slot is ready once its 960px file exists (or its largest size, for thumbnail-only slots). */
function vgnyc_image_ready($slot) {
    $reg = vgnyc_images();
    $files = vgnyc_image_files($slot);
    $sizes = array_map('intval', (array)($reg[$slot]['sizes'] ?? array()));
    return in_array(960, $sizes, true) ? isset($files[960]) : (bool)$files;
}

/** 'missing' | 'ready' | 'check' (smaller than min_width, or ratio off by more than 3%). */
function vgnyc_image_status($slot) {
    if (!vgnyc_image_ready($slot)) { return 'missing'; }
    $reg = vgnyc_images();
    $files = vgnyc_image_files($slot);
    $dims = @getimagesize(end($files));
    if (!$dims || !$dims[1]) { return 'check'; }
    list($rw, $rh) = vgnyc_image_ratio($slot);
    if ($dims[0] < (int)($reg[$slot]['min_width'] ?? 960)) { return 'check'; }
    if (abs(($dims[0] / $dims[1]) / ($rw / $rh) - 1) > 0.03) { return 'check'; }
    return 'ready';
}

/** Public URL of the largest available file for a slot (used for og:image), or ''. */
function vgnyc_image_url($slot) {
    if (!is_string($slot) || !preg_match('/^nyc-[a-z0-9-]+$/D', $slot) || !vgnyc_image_ready($slot)) { return ''; }
    $files = vgnyc_image_files($slot);
    return plugins_url('assets/images/' . basename(end($files)), dirname(__DIR__) . '/vg-nyc-website.php');
}

/**
 * Responsive image for a registered slot, or a labeled placeholder.
 * $args: sizes (the sizes attribute), hero (bool: fetchpriority=high, no lazy loading), class.
 */
function vgnyc_image($slot, $args = array()) {
    $reg = vgnyc_images();
    if (!isset($reg[$slot])) { return ''; }
    $meta = $reg[$slot];
    $alt = (string)($meta['alt'] ?? '');
    list($rw, $rh) = vgnyc_image_ratio($slot);
    $hero = !empty($args['hero']);
    $class = isset($args['class']) ? ' class="' . esc_attr($args['class']) . '"' : '';

    if (vgnyc_image_ready($slot)) {
        $files = vgnyc_image_files($slot);
        $set = array();
        foreach ($files as $w => $path) {
            $set[] = '/nyc/assets/images/' . $slot . '-' . $w . '.webp?v=' . filemtime($path) . ' ' . $w . 'w';
        }
        $src_w = isset($files[960]) ? 960 : max(array_keys($files));
        $src = '/nyc/assets/images/' . $slot . '-' . $src_w . '.webp?v=' . filemtime($files[$src_w]);
        $width = max(array_keys($files));
        $height = (int)round($width * $rh / $rw);
        $sizes = $args['sizes'] ?? '(max-width: 700px) 100vw, 50vw';
        return '<img' . $class . ' src="' . esc_attr($src) . '" srcset="' . esc_attr(implode(', ', $set)) . '" sizes="' . esc_attr($sizes) . '" width="' . $width . '" height="' . $height . '" alt="' . esc_attr($alt) . '"' . ($hero ? ' fetchpriority="high"' : ' loading="lazy"') . ' decoding="async">';
    }

    $ratio = rtrim(rtrim(sprintf('%.3f', $rw), '0'), '.') . '/' . rtrim(rtrim(sprintf('%.3f', $rh), '0'), '.');
    $h = '<div class="vgnyc-img-placeholder' . (isset($args['class']) ? ' ' . esc_attr($args['class']) : '') . '" style="aspect-ratio:' . esc_attr($ratio) . '" role="img" aria-label="' . esc_attr($alt ?: 'Image coming soon') . '">';
    $h .= '<span class="vgnyc-img-placeholder__name">' . esc_html($slot) . '</span>';
    $h .= '<span class="vgnyc-img-placeholder__size">' . esc_html(($meta['ratio'] ?? '3:2') . ' · min ' . (int)($meta['min_width'] ?? 960) . 'px') . '</span>';
    if (function_exists('current_user_can') && current_user_can('manage_options') && !empty($meta['prompt'])) {
        $h .= '<span class="vgnyc-img-placeholder__prompt">' . esc_html($meta['prompt']) . '</span>';
    }
    return $h . '</div>';
}

// ---------------------------------------------------------------- local processing

/** Image processing only ever runs on a local development site. */
function vgnyc_images_dev() {
    return defined('VGNYC_DEV') && VGNYC_DEV && function_exists('wp_get_environment_type') && wp_get_environment_type() === 'local';
}

/**
 * Converts up to $limit files from images-inbox/ into the registered WebP sizes.
 * Returns array(slot => 'ok' | error message).
 */
function vgnyc_process_images($limit = 5) {
    if (!vgnyc_images_dev()) { return array(); }
    $inbox = dirname(__DIR__) . '/images-inbox';
    if (!is_dir($inbox)) { return array(); }
    $reg = vgnyc_images();
    $queue = array();
    foreach ((array)scandir($inbox) as $f) {
        if (preg_match('/^([A-Za-z0-9-]+)\.(jpe?g|png|webp)$/i', $f, $m) && isset($reg[strtolower($m[1])])) { $queue[] = array($f, strtolower($m[1]), strtolower($m[2])); }
    }
    if (!$queue) { return array(); }
    if (!wp_image_editor_supports(array('mime_type' => 'image/webp'))) {
        set_transient('vgnyc_webp_missing', 1, HOUR_IN_SECONDS);
        return array();
    }
    delete_transient('vgnyc_webp_missing');
    wp_mkdir_p(vgnyc_image_dir());
    wp_mkdir_p($inbox . '/processed');
    // Re-encode even when the size is unchanged, so metadata is stripped.
    add_filter('wp_image_resize_identical_dimensions', '__return_true');
    $done = array();
    foreach (array_slice($queue, 0, $limit) as $item) {
        list($file, $slot, $ext) = $item;
        $src = $inbox . '/' . $file;
        $probe = wp_get_image_editor($src);
        if (is_wp_error($probe)) { $done[$slot] = $probe->get_error_message(); continue; }
        $size = $probe->get_size();
        $sw = (int)$size['width'];
        $sizes = array_map('intval', (array)$reg[$slot]['sizes']);
        sort($sizes);
        $error = '';
        foreach ($sizes as $w) {
            // Never upscale. A source narrower than 960 still fills the 960 file at its own width (flagged ⚠️).
            if ($w > $sw && $w !== 960) { continue; }
            $editor = wp_get_image_editor($src);
            if (is_wp_error($editor)) { $error = $editor->get_error_message(); break; }
            $resized = $editor->resize(min($w, $sw), null, false);
            if (is_wp_error($resized)) { $error = $resized->get_error_message(); break; }
            $editor->set_quality(82);
            $saved = $editor->save(vgnyc_image_dir() . $slot . '-' . $w . '.webp', 'image/webp');
            if (is_wp_error($saved)) { $error = $saved->get_error_message(); break; }
        }
        if ($error) { $done[$slot] = $error; continue; }
        rename($src, $inbox . '/processed/' . $slot . '-' . gmdate('YmdHis') . '.' . $ext);
        $done[$slot] = 'ok';
    }
    remove_filter('wp_image_resize_identical_dimensions', '__return_true');
    return $done;
}

add_action('admin_notices', function () {
    if (current_user_can('manage_options') && get_transient('vgnyc_webp_missing')) {
        echo '<div class="notice notice-warning"><p><strong>Vision Graphics NYC:</strong> images in <code>images-inbox/</code> could not be converted because this server\'s image editor cannot write WebP. Enable the Imagick PHP extension with WebP support, or GD compiled with WebP, then refresh the Image Brief page.</p></div>';
    }
});

// ---------------------------------------------------------------- admin: Image Brief

add_action('admin_menu', function () {
    add_menu_page('Vision Graphics NYC', 'Vision Graphics NYC', 'manage_options', 'vgnyc-brief', 'vgnyc_brief_page', 'dashicons-format-image', 81);
    add_submenu_page('vgnyc-brief', 'Image Brief', 'Image Brief', 'manage_options', 'vgnyc-brief', 'vgnyc_brief_page');
    add_submenu_page('vgnyc-brief', 'Settings', 'Settings', 'manage_options', 'options-general.php?page=vgnyc');
});

function vgnyc_brief_rows() {
    $rows = array();
    foreach (vgnyc_images() as $slot => $m) {
        $rows[] = array('slot' => $slot, 'page' => (string)($m['page'] ?? ''), 'section' => (string)($m['section'] ?? ''), 'ratio' => (string)($m['ratio'] ?? ''),
            'min_width' => (int)($m['min_width'] ?? 0), 'sizes' => implode('/', (array)($m['sizes'] ?? array())), 'alt' => (string)($m['alt'] ?? ''),
            'prompt' => (string)($m['prompt'] ?? ''), 'notes' => (string)($m['notes'] ?? ''), 'status' => vgnyc_image_status($slot));
    }
    return $rows;
}

function vgnyc_brief_page() {
    if (!current_user_can('manage_options')) { return; }
    $processed = vgnyc_process_images();
    $rows = vgnyc_brief_rows();
    $pages = array_values(array_unique(array_column($rows, 'page')));
    $f_page = isset($_GET['brief_page']) ? sanitize_text_field(wp_unslash($_GET['brief_page'])) : '';
    $f_status = isset($_GET['brief_status']) ? sanitize_key(wp_unslash($_GET['brief_status'])) : '';
    $labels = array('missing' => '⏳ Missing', 'ready' => '✅ Ready', 'check' => '⚠️ Check size');
    $counts = array_count_values(array_column($rows, 'status'));
    $csv = wp_nonce_url(admin_url('admin-post.php?action=vgnyc_brief_csv'), 'vgnyc_brief_csv');
    echo '<div class="wrap"><h1>Image Brief</h1>';
    echo '<div class="notice notice-info inline"><p><strong>For the designer:</strong> Export each image with the exact file name below (jpg/png/webp, at least the minimum width). Put the files in <code>images-inbox/</code>, then refresh this page.</p></div>';
    if (!vgnyc_images_dev()) {
        echo '<p><em>Automatic image processing is off on this site. It runs only on the local development site (<code>VGNYC_DEV</code> and a local environment). Finished files reach this site through a normal plugin deploy.</em></p>';
    }
    foreach ($processed as $slot => $result) {
        echo '<div class="notice ' . ($result === 'ok' ? 'notice-success' : 'notice-error') . ' inline"><p><code>' . esc_html($slot) . '</code>: ' . esc_html($result === 'ok' ? 'processed.' : $result) . '</p></div>';
    }
    echo '<p>' . count($rows) . ' slots: ' . (int)($counts['ready'] ?? 0) . ' ready, ' . (int)($counts['check'] ?? 0) . ' to check, ' . (int)($counts['missing'] ?? 0) . ' missing. <a class="button button-primary" href="' . esc_url($csv) . '">Download CSV</a></p>';
    echo '<form method="get"><input type="hidden" name="page" value="vgnyc-brief"><select name="brief_page"><option value="">All pages</option>';
    foreach ($pages as $p) { echo '<option value="' . esc_attr($p) . '"' . selected($f_page, $p, false) . '>' . esc_html($p) . '</option>'; }
    echo '</select> <select name="brief_status"><option value="">All statuses</option>';
    foreach ($labels as $k => $l) { echo '<option value="' . esc_attr($k) . '"' . selected($f_status, $k, false) . '>' . esc_html($l) . '</option>'; }
    echo '</select> <button class="button">Filter</button></form><br>';
    echo '<table class="widefat striped"><thead><tr><th>Preview</th><th>Slot / file name</th><th>Page</th><th>Section</th><th>Ratio</th><th>Min width</th><th>Alt</th><th>Prompt</th><th>Status</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        if (($f_page && $r['page'] !== $f_page) || ($f_status && $r['status'] !== $f_status)) { continue; }
        $files = vgnyc_image_files($r['slot']);
        $thumb = $files ? '<img src="' . esc_url(plugins_url('assets/images/' . basename(reset($files)), dirname(__DIR__) . '/vg-nyc-website.php')) . '" width="96" alt="">' : '';
        echo '<tr><td>' . $thumb . '</td><td><code>' . esc_html($r['slot']) . '</code><br><small>.jpg / .png / .webp</small></td><td>' . esc_html($r['page']) . '</td><td>' . esc_html($r['section']) . '</td><td>' . esc_html($r['ratio']) . '</td><td>' . (int)$r['min_width'] . 'px</td><td>' . esc_html($r['alt']) . '</td>';
        echo '<td style="max-width:420px">' . esc_html($r['prompt']) . ($r['notes'] && $r['notes'] !== $r['prompt'] ? '<br><em>' . esc_html($r['notes']) . '</em>' : '') . '<br><button type="button" class="button button-small vgnyc-copy" data-prompt="' . esc_attr($r['prompt']) . '">Copy prompt</button></td>';
        echo '<td>' . esc_html($labels[$r['status']]) . '</td></tr>';
    }
    echo '</tbody></table></div>';
    echo '<script>document.addEventListener("click",function(e){var b=e.target.closest(".vgnyc-copy");if(!b)return;navigator.clipboard.writeText(b.dataset.prompt).then(function(){b.textContent="Copied";setTimeout(function(){b.textContent="Copy prompt"},1500)})});</script>';
}

add_action('admin_post_vgnyc_brief_csv', function () {
    if (!current_user_can('manage_options') || !check_admin_referer('vgnyc_brief_csv')) { wp_die('Not allowed.'); }
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="vg-nyc-image-brief-' . gmdate('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, array('File name (use exactly)', 'Page', 'Section', 'Ratio', 'Minimum width (px)', 'Sizes produced', 'Alt text', 'Prompt / brief', 'Notes', 'Status'));
    foreach (vgnyc_brief_rows() as $r) {
        fputcsv($out, array($r['slot'] . '.jpg', $r['page'], $r['section'], $r['ratio'], $r['min_width'], $r['sizes'], $r['alt'], $r['prompt'], $r['notes'], $r['status']));
    }
    exit;
});
