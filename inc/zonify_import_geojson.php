<?php
function zonify_import_geojson() {
    if ( ! current_user_can('manage_options') ) {
        wp_die('Permission refusée');
    }
    check_admin_referer('zonify_import_geojson_nonce');

    if ( empty($_FILES['zones_geojson']['tmp_name']) ) {
        wp_die('Aucun fichier GeoJSON fourni.');
    }

    $file_data = file_get_contents($_FILES['zones_geojson']['tmp_name']);
    error_log("Contenu du fichier importé : " . $file_data);

    $decoded = json_decode($file_data, true);
    if ( ! $decoded || !isset($decoded['type']) || $decoded['type'] !== 'FeatureCollection' ) {
        wp_die('Fichier GeoJSON invalide (pas un FeatureCollection).');
    }

    // Récupérer le titre par défaut depuis le formulaire
    $default_title = isset($_POST['default_zone_title']) ? sanitize_text_field($_POST['default_zone_title']) : '';

    $features = $decoded['features'];
    $count_created = 0;
    $count_updated = 0;

    foreach ( $features as $feat ) {
        if ( empty($feat['geometry']) ) {
            error_log("Feature ignorée : aucune geometry");
            continue;
        }
        $geometry = json_encode($feat['geometry']);
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log("Erreur JSON pour geometry: " . json_last_error_msg());
            continue;
        }

        $props = isset($feat['properties']) ? $feat['properties'] : array();

        // Utiliser le titre défini dans le fichier, sinon utiliser le titre par défaut,
        // et si ce dernier est vide, auto-générer un titre basé sur la date
        $zone_title = !empty($props['zone_title']) ? sanitize_text_field($props['zone_title']) : $default_title;
        if ( empty($zone_title) ) {
            $zone_title = 'Zone - ' . date('Y-m-d H:i:s');
        }

        // Forcer la création d'une nouvelle zone
        $zone_id = 0;
        $com_id = !empty($props['commercial_id']) ? intval($props['commercial_id']) : 0;

        error_log("Traitement de la feature: zone_id=$zone_id, title=$zone_title, commercial_id=$com_id");

        $new_id = wp_insert_post(array(
            'post_type'  => 'zone',
            'post_title' => $zone_title,
            'post_status'=> 'publish'
        ));
        if ( $new_id ) {
            update_post_meta($new_id, 'zone_geojson', $geometry);
            update_post_meta($new_id, 'zone_commercial_id', $com_id);
            $count_created++;
            error_log("Nouvelle zone créée avec ID: " . $new_id);
        } else {
            error_log("Échec de la création d'une nouvelle zone pour la feature");
        }
    }

    wp_redirect(admin_url('admin.php?page=zonify_import_export&geojson_import_done=1&created=' . $count_created . '&updated=' . $count_updated));
    exit;
}
add_action('admin_post_zonify_import_geojson', 'zonify_import_geojson');
