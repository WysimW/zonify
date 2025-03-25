<?php
function zonify_export_geojson() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée');
    }
    
    // Récupérer toutes les zones
    $args = array(
        'post_type'      => 'zone',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);

    $features = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $geojson = get_post_meta(get_the_ID(), 'zone_geojson', true);
            $com_id  = get_post_meta(get_the_ID(), 'zone_commercial_id', true);
            if ($geojson) {
                $geometry = json_decode($geojson, true); // On suppose que zone_geojson stocke un geometry
                $features[] = array(
                    'type'       => 'Feature',
                    'properties' => array(
                        'zone_id'       => get_the_ID(),
                        'commercial_id' => $com_id,
                        'zone_title'    => get_the_title(),
                    ),
                    'geometry'   => $geometry
                );
            }
        }
        wp_reset_postdata();
    }

    $result = array(
        'type'     => 'FeatureCollection',
        'features' => $features
    );

    header('Content-disposition: attachment; filename=zones.geojson');
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}
add_action('admin_post_zonify_export_geojson', 'zonify_export_geojson');
