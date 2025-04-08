<?php
add_action('wp_ajax_save_zone', 'zonify_save_zone');
function zonify_save_zone() {
    // Vérifier les permissions et le nonce
    if ( ! current_user_can('manage_options') || ! check_ajax_referer('save_zone_nonce', '_ajax_nonce', false) ) {
        wp_send_json_error('Permission refusée');
    }

    $zone_data    = isset($_POST['zone_data']) ? wp_unslash($_POST['zone_data']) : '';
    $commercial_id = isset($_POST['commercial_id']) ? intval($_POST['commercial_id']) : 0;
    $zone_id      = isset($_POST['zone_id']) ? intval($_POST['zone_id']) : 0;
    $region_id    = isset($_POST['region_id']) ? intval($_POST['region_id']) : 0;
    $region_ids   = isset($_POST['region_ids']) ? json_decode(wp_unslash($_POST['region_ids']), true) : array();

    if ( empty($zone_data) ) {
        wp_send_json_error('Aucune donnée de zone fournie');
    }

    // Valider le JSON
    $decoded = json_decode($zone_data, true);
    if ( is_null($decoded) ) {
        wp_send_json_error('Données de zone invalides');
    }

    if ( $zone_id && get_post_type($zone_id) === 'zone' ) {
        // Mise à jour de la zone existante
        update_post_meta($zone_id, 'zone_geojson', $zone_data);
        update_post_meta($zone_id, 'zone_commercial_id', $commercial_id);
        
        // Mise à jour de la région
        if ($region_id > 0) {
            // Si une seule région est passée directement
            wp_set_object_terms($zone_id, array($region_id), 'region');
        } elseif (!empty($region_ids)) {
            // Si un tableau de régions est passé
            wp_set_object_terms($zone_id, $region_ids, 'region');
        }
        
        wp_send_json_success(array(
            'zone_id' => $zone_id,
            'message' => 'Zone mise à jour'
        ));
    } else {
        // Création d'une nouvelle zone
        $post_data = array(
            'post_type'   => 'zone',
            'post_title'  => 'Zone - ' . current_time('Y-m-d H:i:s'),
            'post_status' => 'publish'
        );
        $zone_post_id = wp_insert_post($post_data);
        if ( ! $zone_post_id ) {
            wp_send_json_error('Erreur lors de la création de la zone');
        }
        update_post_meta($zone_post_id, 'zone_geojson', $zone_data);
        update_post_meta($zone_post_id, 'zone_commercial_id', $commercial_id);
        
        // Attribution de la région
        if ($region_id > 0) {
            // Si une seule région est passée directement
            wp_set_object_terms($zone_post_id, array($region_id), 'region');
        } elseif (!empty($region_ids)) {
            // Si un tableau de régions est passé
            wp_set_object_terms($zone_post_id, $region_ids, 'region');
        }
        
        wp_send_json_success(array(
            'zone_id' => $zone_post_id,
            'message' => 'Zone sauvegardée'
        ));
    }
}

// Fonction de sauvegarde de plusieurs zones à la fois (non utilisée par défaut)
add_action('wp_ajax_save_multiple_zones', 'zonify_save_multiple_zones');
function zonify_save_multiple_zones() {
    if (!current_user_can('manage_options') || !check_ajax_referer('save_zone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error('Permission refusée');
    }
    
    $zones_data = isset($_POST['zones']) ? wp_unslash($_POST['zones']) : '';
    $zones = json_decode($zones_data, true);
    
    if (!is_array($zones) || empty($zones)) {
        wp_send_json_error('Aucune donnée de zone fournie ou format invalide');
    }

    // Parcourir toutes les zones et les enregistrer
    $updated = 0;
    $created = 0;
    
    foreach ($zones as $zone) {
        $zone_id = !empty($zone['zone_id']) ? intval($zone['zone_id']) : 0;
        $geometry = !empty($zone['geometry']) ? json_encode($zone['geometry']) : '';
        $commercial_id = !empty($zone['commercial_id']) ? intval($zone['commercial_id']) : 0;
        $region_ids = !empty($zone['region_ids']) ? $zone['region_ids'] : array();
        
        if (empty($geometry)) continue;
        
        if ($zone_id && get_post_type($zone_id) === 'zone') {
            // Mise à jour d'une zone existante
            update_post_meta($zone_id, 'zone_geojson', $geometry);
            update_post_meta($zone_id, 'zone_commercial_id', $commercial_id);
            
            // Mise à jour des régions
            if (!empty($region_ids)) {
                wp_set_object_terms($zone_id, $region_ids, 'region');
            }
            
            $updated++;
        } else {
            // Création d'une nouvelle zone
            $post_data = array(
                'post_type'   => 'zone',
                'post_title'  => 'Zone - ' . current_time('Y-m-d H:i:s'),
                'post_status' => 'publish'
            );
            $new_zone_id = wp_insert_post($post_data);
            
            if ($new_zone_id) {
                update_post_meta($new_zone_id, 'zone_geojson', $geometry);
                update_post_meta($new_zone_id, 'zone_commercial_id', $commercial_id);
                
                // Attribution des régions
                if (!empty($region_ids)) {
                    wp_set_object_terms($new_zone_id, $region_ids, 'region');
                }
                
                $created++;
            }
        }
    }
    
    wp_send_json_success("Zones sauvegardées: $created créées, $updated mises à jour.");
}
