<?php
defined('ABSPATH') || exit;

function re_opt($key, $default = '') {
    $val = get_option($key, $default);
    return ($val !== '' && $val !== false) ? $val : $default;
}

function re_field($key, $post_id = null) {
    if (function_exists('get_field')) {
        return get_field($key, $post_id);
    }
    $post_id = $post_id ?: get_the_ID();
    return get_post_meta($post_id, $key, true);
}

function re_project_meta($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    return [
        'n'        => re_field('project_number', $post_id) ?: '01',
        'cat'      => re_get_primary_category($post_id),
        'yr'       => re_field('project_year', $post_id) ?: get_the_date('Y', $post_id),
        'client'   => re_field('project_client', $post_id) ?: '',
        'loc'      => re_field('project_location', $post_id) ?: '',
        'frames'   => re_field('project_frames', $post_id) ?: '',
        'format'   => re_field('project_format', $post_id) ?: '',
    ];
}

function re_get_primary_category($post_id = null) {
    $terms = get_the_terms($post_id ?: get_the_ID(), 're_project_cat');
    return ($terms && !is_wp_error($terms)) ? $terms[0]->name : '';
}

function re_placeholder($label = 'Image', $dark = true, $aspect = '4:5') {
    $bg   = $dark ? '#13141A' : '#E8E5DE';
    $fg   = $dark ? '#2A2C34' : '#D0CCC4';
    $text = $dark ? '#4A4C54' : '#A8A49C';
    [$w, $h] = array_map('intval', explode(':', $aspect));
    $sw = 400; $sh = intval(400 * $h / $w);
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $sw . '" height="' . $sh . '" viewBox="0 0 ' . $sw . ' ' . $sh . '">';
    $svg .= '<rect fill="' . $bg . '" width="' . $sw . '" height="' . $sh . '"/>';
    for ($i = 0; $i < max($sw, $sh) * 2; $i += 12) {
        $svg .= '<line x1="' . $i . '" y1="0" x2="' . ($i - $sh) . '" y2="' . $sh . '" stroke="' . $fg . '" stroke-width="1" opacity=".25"/>';
    }
    $svg .= '<text x="50%" y="50%" text-anchor="middle" dy=".35em" fill="' . $text . '" font-family="monospace" font-size="13">' . esc_html($label) . '</text>';
    $svg .= '</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function re_image_or_placeholder($post_id, $size = 're-card', $label = 'Project') {
    $thumb = get_the_post_thumbnail_url($post_id, $size);
    return $thumb ?: re_placeholder($label);
}

function re_emphasize_title($title) {
    $words = explode(' ', $title);
    $out = '';
    foreach ($words as $i => $w) {
        $out .= ($i % 2 === 1) ? '<em>' . esc_html($w) . '</em> ' : '<span>' . esc_html($w) . '</span> ';
    }
    return trim($out);
}

function re_recognition_list($option_key) {
    $raw = re_opt($option_key, '');
    if (!$raw) return [];
    $items = [];
    foreach (explode("\n", trim($raw)) as $line) {
        $parts = array_map('trim', explode('·', $line));
        if (count($parts) >= 3) {
            $items[] = [
                'year'  => $parts[0],
                'title' => $parts[1],
                'org'   => $parts[2],
                'url'   => $parts[3] ?? '',
            ];
        }
    }
    return $items;
}
