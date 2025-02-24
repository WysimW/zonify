<?php
function zonify_save_multiple_zones() {
    if (!current_user_can('manage_options') || ! check_ajax_referer('save_zone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error('Permission refusée');
    }

    $zones_json = isset($_POST['zones']) ? wp_unslash($_POST['zones']) : '';
    if (!$zones_json) {
        wp_send_json_error('Pas de données transmises');
    }
    $decoded = json_decode($zones_json, true);
    if (!is_array($decoded)) {
        wp_send_json_error('Format des zones invalide');
    }

    // 1. Récupérer tous les posts "zone" existants
    $args = array(
        'post_type' => 'zone',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);
    $existing_ids = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $existing_ids[] = get_the_ID();
        }
        wp_reset_postdata();
    }

    // 2. Collecter les zone_id transmis
    $received_ids = array();
    foreach ($decoded as $poly) {
        if (!empty($poly['zone_id'])) {
            $received_ids[] = intval($poly['zone_id']);
        }
    }

    // 3. Supprimer celles qui ne sont plus présentes
    $ids_to_delete = array_diff($existing_ids, $received_ids);
    foreach ($ids_to_delete as $id_to_del) {
        wp_delete_post($id_to_del, true);
    }

    // 4. Créer / Mettre à jour
    foreach ($decoded as $poly) {
        $zid = !empty($poly['zone_id']) ? intval($poly['zone_id']) : 0;
        $geometry = '';
        if (!empty($poly['geometry'])) {
            $geometry = json_encode($poly['geometry']);
        }
        if (!$geometry || !json_decode($geometry, true)) {
            continue; // On ignore si geometry invalide
        }
        // Récupérer le commercial_id
        $com_id = !empty($poly['commercial_id']) ? intval($poly['commercial_id']) : 0;

        if ($zid) {
            // Mise à jour
            update_post_meta($zid, 'zone_geojson', $geometry);
            update_post_meta($zid, 'zone_commercial_id', $com_id);
        } else {
            // Nouveau
            $new_id = wp_insert_post(array(
                'post_type'   => 'zone',
                'post_title'  => 'Zone - ' . date('Y-m-d H:i:s'),
                'post_status' => 'publish'
            ));
            if ($new_id) {
                update_post_meta($new_id, 'zone_geojson', $geometry);
                update_post_meta($new_id, 'zone_commercial_id', $com_id);
            }
        }
    }

    wp_send_json_success('Zones sauvegardées / supprimées avec succès');
}
add_action('wp_ajax_save_multiple_zones', 'zonify_save_multiple_zones');
