<?php
function zonify_enqueue_scripts($hook) {
    $allowed_hooks = array(
        'toplevel_page_zonify',
        'zonify_page_zonify_map',
        'zonify_page_zonify_list',
        'zonify_page_zonify_settings'
    );
    if (! in_array($hook, $allowed_hooks)) {
        return;
    }

    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    wp_enqueue_style('leaflet-draw-css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css');
    wp_enqueue_script('leaflet-draw-js', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js', array('leaflet-js'), '1.0.4', true);

    wp_enqueue_script('zonify-script', plugin_dir_url(__FILE__) . '../scripts/zonify-tracing.js', array('leaflet-js', 'leaflet-draw-js'), '1.0', true);
    wp_enqueue_style('zonify-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', array(), '1.0');

    $back_options = array(
        'tile_provider'   => get_option('zonify_tile_provider', 'cartodb_light'),
        'tile_custom_url' => get_option('zonify_tile_custom_url', ''),
        'zone_fill_color' => get_option('zonify_zone_fill_color', '#3388ff'),
        'zone_border_color' => get_option('zonify_zone_border_color', '#3388ff'),
        'zone_opacity'    => floatval(get_option('zonify_zone_opacity', 0.5)),
        'map_zoom'        => intval(get_option('zonify_map_zoom', 9)),
        'map_center_lat'  => get_option('zonify_map_center_lat', '50.5'),
        'map_center_lng'  => get_option('zonify_map_center_lng', '2.5'),
        'ajax_url'        => admin_url('admin-ajax.php'),
        'nonce'           => wp_create_nonce('save_zone_nonce'),
        'edit_zone_base'  => admin_url('post.php') // ex: https://example.com/wp-admin/post.php

        
    );

    wp_localize_script('zonify-script', 'zonifyMapVars', $back_options);
}
add_action('admin_enqueue_scripts', 'zonify_enqueue_scripts');
