<?php

function zones_commerciales_frontend_shortcode() {
    // Récupérer les commerciaux ayant une zone enregistrée
    $args_zone = array(
        'post_type'      => 'commercial',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'zone_geojson',
                'compare' => 'EXISTS',
            ),
        ),
    );
    $query = new WP_Query($args_zone);
    $zones = array();
    if($query->have_posts()){
        while($query->have_posts()){
            $query->the_post();
            $zone = get_post_meta(get_the_ID(), 'zone_geojson', true);
            if($zone) {
                $zones[] = array(
                    'type'       => 'Feature',
                    'properties' => array(
                        'nom_commercial' => get_the_title(),
                        'infos'          => get_the_excerpt(),
                        'email'          => get_post_meta(get_the_ID(), 'commercial_email', true),
                        'telephone'      => get_post_meta(get_the_ID(), 'commercial_telephone', true)
                    ),
                    // On reconstruit le GeoJSON complet en supposant que le champ stocke la géométrie
                    'geometry'   => json_decode($zone, true)
                );
            }
        }
        wp_reset_postdata();
    }
    
    // Enqueue des scripts pour le front-end
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    
    wp_enqueue_script('zones-commerciaux-frontend', plugin_dir_url(__FILE__) . '../scripts/zones-commerciales-frontend.js', array('leaflet-js'), '1.0', true);
    wp_enqueue_style('zones-commerciales-frontend-css', plugin_dir_url(__FILE__) . '../assets/css/zc-frontend.css', array('leaflet-css'), '1.0');

    // Passage des zones au script front-end
    wp_localize_script('zones-commerciaux-frontend', 'zonesData', $zones);
    
    ob_start();
    ?>
    <div id="map" style="height: 500px;"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('zones_commerciales_map', 'zones_commerciales_frontend_shortcode');
