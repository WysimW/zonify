<?php
function zonify_import_zones_geojson() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée.');
    }
    if (!empty($_FILES['zones_geojson']['tmp_name'])) {
        $json = file_get_contents($_FILES['zones_geojson']['tmp_name']);
        $data = json_decode($json, true);
        if ($data && isset($data['features'])) {
            foreach ($data['features'] as $feature) {
                // Créer un nouveau post de type 'zone'
                $zone_id = wp_insert_post(array(
                    'post_type' => 'zone',
                    'post_title' => 'Zone - ' . date('Y-m-d H:i:s'),
                    'post_status' => 'publish'
                ));
                if ($zone_id) {
                    // Sauvegarder le GeoJSON dans une meta
                    update_post_meta($zone_id, 'zone_geojson', json_encode($feature));
                }
            }
            echo 'Zones importées avec succès.';
        } else {
            echo 'Erreur lors du parsing du fichier GeoJSON.';
        }
    }
}
