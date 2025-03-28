<?php
function zonify_enqueue_poi_scripts($hook) {
    // Exécuter ce script uniquement sur la page de gestion des POI
    $allowed_hooks = array(
        'zonify_page_zonify_poi'
    );
    if ( ! in_array( $hook, $allowed_hooks ) ) {
        return;
    }

    // Enqueue des styles et scripts nécessaires à Leaflet et Leaflet Draw
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    wp_enqueue_style('leaflet-draw-css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css');
    wp_enqueue_script('leaflet-draw-js', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js', array('leaflet-js'), '1.0.4', true);

    // Optionnel : Enqueue du style admin commun si besoin
    wp_enqueue_style('zonify-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', array(), '1.0');

    // Enqueue du script personnalisé pour la gestion des POI
    wp_enqueue_script('zonify-poi-script', plugin_dir_url(__FILE__) . '../scripts/zonify-poi.js', array('leaflet-js', 'leaflet-draw-js'), '1.0', true);

    // Localiser d'abord les options pour le script des POI
    $poi_options = array(
        'tile_provider'    => get_option('zonify_tile_provider_poi', 'cartodb_light'),
        'tile_custom_url'  => get_option('zonify_tile_custom_url_poi', ''),
        'map_zoom'         => intval(get_option('zonify_map_zoom_poi', 9)),
        'map_center_lat'   => get_option('zonify_map_center_lat_poi', '50.5'),
        'map_center_lng'   => get_option('zonify_map_center_lng_poi', '2.5'),
        'ajax_url'         => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('save_poi_nonce')
    );
    wp_localize_script('zonify-poi-script', 'zonifyPoiVars', $poi_options);

    // Ensuite, récupérer et localiser les POI existants
    $args = array(
        'post_type'      => 'poi',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);
    $pois = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $geojson = get_post_meta(get_the_ID(), 'poi_geojson', true);
            if ($geojson) {
                $pois[] = array(
                    'type' => 'Feature',
                    'properties' => array(
                        'poi_id' => get_the_ID(),
                        'title'  => get_the_title(),
                    ),
                    'geometry' => json_decode($geojson, true)
                );
            }
        }
        wp_reset_postdata();
    }
    wp_localize_script('zonify-poi-script', 'poisData', $pois);

     // Récupération des options issues des settings spécifiques aux POI (similaire à back_options)
     $poi_options = array(
'tile_provider'   => get_option('zonify_tile_provider', 'cartodb_light'),
        'tile_custom_url' => get_option('zonify_tile_custom_url', ''),
        'zone_fill_color' => get_option('zonify_zone_fill_color', '#3388ff'),
        'zone_border_color' => get_option('zonify_zone_border_color', '#3388ff'),
        'zone_opacity'    => floatval(get_option('zonify_zone_opacity', 0.5)),
        'map_zoom'        => intval(get_option('zonify_map_zoom', 9)),
        'map_center_lat'  => get_option('zonify_map_center_lat', '50.5'),
        'map_center_lng'  => get_option('zonify_map_center_lng', '2.5'),
        'ajax_url'         => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('save_poi_nonce')
    );

    wp_localize_script('zonify-poi-script', 'zonifyPoiVars', $poi_options);
}

add_action('admin_enqueue_scripts', 'zonify_enqueue_poi_scripts');
