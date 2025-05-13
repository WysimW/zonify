<?php
function terralize_import_geojson() {
    if ( ! current_user_can('manage_options') ) {
        wp_die('Permission refusée');
    }
    check_admin_referer('terralize_import_geojson_nonce');

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

        // Générer le titre à partir du code et du nom si disponibles
        $zone_title = '';
        if (!empty($props['code']) && !empty($props['nom'])) {
            $zone_title = sanitize_text_field($props['code'] . ' - ' . $props['nom']);
        } elseif (!empty($props['code'])) {
            $zone_title = sanitize_text_field($props['code']);
        } elseif (!empty($props['nom'])) {
            $zone_title = sanitize_text_field($props['nom']);
        } elseif (!empty($props['zone_title'])) {
            // Fallback sur zone_title s'il existe
            $zone_title = sanitize_text_field($props['zone_title']);
        } else {
            // Utiliser le titre par défaut, sinon auto-générer
            $zone_title = $default_title;
            if (empty($zone_title)) {
                $zone_title = 'Zone - ' . date('Y-m-d H:i:s');
            }
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
            
            // Enregistrer également le code et le nom comme meta-données séparées si disponibles
            if (!empty($props['code'])) {
                update_post_meta($new_id, 'zone_code', sanitize_text_field($props['code']));
            }
            if (!empty($props['nom'])) {
                update_post_meta($new_id, 'zone_nom', sanitize_text_field($props['nom']));
            }
            
            $count_created++;
            error_log("Nouvelle zone créée avec ID: " . $new_id);
        } else {
            error_log("Échec de la création d'une nouvelle zone pour la feature");
        }
    }

    wp_redirect(admin_url('admin.php?page=terralize_import_export&geojson_import_done=1&created=' . $count_created . '&updated=' . $count_updated));
    exit;
}
add_action('admin_post_terralize_import_geojson', 'terralize_import_geojson');
