<?php
function terralize_enqueue_scripts($hook) {
    // Liste des hooks autorisés pour le plugin
    $allowed_hooks = array(
        'toplevel_page_terralize',
        'terralize_page_terralize_map',
        'terralize_page_terralize_list',
        'terralize_page_terralize_settings',
        'terralize_page_terralize_import_export',
        'terralize_page_terralize_settings'
    );

    // Vérifier si nous sommes sur une page autorisée
    if (!in_array($hook, $allowed_hooks)) {
        return;
    }

    // Enqueue Select2 (pour moderniser le multi-select)
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js', array('jquery'), '4.0.13', true);

    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    wp_enqueue_style('leaflet-draw-css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css');
    wp_enqueue_script('leaflet-draw-js', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js', array('leaflet-js'), '1.0.4', true);

    wp_enqueue_script('terralize-script', plugin_dir_url(__FILE__) . '../scripts/terralize-tracing.js', array('leaflet-js', 'leaflet-draw-js'), '1.0', true);
    
    // Optionnel : enqueue du style admin commun
    wp_enqueue_style('terralize-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', array(), '1.0');

    $back_options = array(
        'tile_provider'   => get_option('terralize_tile_provider', 'cartodb_light'),
        'tile_custom_url' => get_option('terralize_tile_custom_url', ''),
        'zone_fill_color' => get_option('terralize_zone_fill_color', '#3388ff'),
        'zone_border_color' => get_option('terralize_zone_border_color', '#3388ff'),
        'zone_opacity'    => floatval(get_option('terralize_zone_opacity', 0.5)),
        'map_zoom'        => intval(get_option('terralize_map_zoom', 9)),
        'map_center_lat'  => get_option('terralize_map_center_lat', '50.5'),
        'map_center_lng'  => get_option('terralize_map_center_lng', '2.5'),
        'ajax_url'        => admin_url('admin-ajax.php'),
        'nonce'           => wp_create_nonce('save_zone_nonce'),
        'edit_zone_base'  => admin_url('post.php'),
        'alwaysShow'      => get_option('terralize_always_show_all_zones', '0')
    );

    // Récupération des régions pour les filtres
    $regions = get_terms([
        'taxonomy' => 'region',
        'hide_empty' => false,
    ]);
    
    $regions_data = [];
    if (!is_wp_error($regions) && !empty($regions)) {
        foreach ($regions as $region) {
            $regions_data[] = [
                'id' => $region->term_id,
                'name' => $region->name,
                'slug' => $region->slug
            ];
        }
    }
    
    // Ajouter les données de région aux options JavaScript
    $back_options['regions'] = $regions_data;
    
    wp_localize_script('terralize-script', 'terralizeMapVars', $back_options);
}
add_action('admin_enqueue_scripts', 'terralize_enqueue_scripts');
