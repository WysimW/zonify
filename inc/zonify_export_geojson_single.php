<?php
function zonify_export_geojson_single() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée');
    }

    $zone_id = isset($_GET['zone_id']) ? intval($_GET['zone_id']) : 0;
    if (!$zone_id) {
        wp_die('ID de zone manquant.');
    }

    // Vérifier que c’est bien un post "zone"
    if (get_post_type($zone_id) !== 'zone') {
        wp_die('Ce post n’est pas une zone.');
    }

    $geojson = get_post_meta($zone_id, 'zone_geojson', true);
    $com_id  = get_post_meta($zone_id, 'zone_commercial_id', true);

    if (!$geojson) {
        wp_die('Aucun GeoJSON pour cette zone.');
    }

    $geometry = json_decode($geojson, true);
    if (!$geometry) {
        wp_die('GeoJSON invalide pour ce post.');
    }

    $feature = array(
        'type'       => 'Feature',
        'properties' => array(
            'zone_id'       => $zone_id,
            'commercial_id' => $com_id,
            'zone_title'    => get_the_title($zone_id),
        ),
        'geometry'   => $geometry
    );

    // On peut renvoyer un Feature unique ou un FeatureCollection contenant 1 feature
    $result = array(
        'type'     => 'FeatureCollection',
        'features' => array($feature)
    );

    header('Content-disposition: attachment; filename=zone_'.$zone_id.'.geojson');
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}
add_action('admin_post_zonify_export_geojson_single', 'zonify_export_geojson_single');
