<?php
function zonify_export_zones_geojson() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée.');
    }
    
    $args = array(
        'post_type'      => 'zone',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    
    $features = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // Supposons que vous stockez le GeoJSON de la zone dans une meta 'zone_geojson'
            $geojson = get_post_meta(get_the_ID(), 'zone_geojson', true);
            if ($geojson) {
                $features[] = json_decode($geojson, true);
            }
        }
        wp_reset_postdata();
    }
    
    $geojson_output = json_encode(array(
        'type' => 'FeatureCollection',
        'features' => $features
    ));
    
    header('Content-disposition: attachment; filename=zones.geojson');
    header('Content-type: application/json');
    echo $geojson_output;
    exit;
}
