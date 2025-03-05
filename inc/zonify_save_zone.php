<?php
function zonify_save_multiple_zones() {
    if (!current_user_can('manage_options') || ! check_ajax_referer('save_zone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error('Permission refusée');
    }

    // Récupérer l'option
    $always_show_all_zones = get_option('zonify_always_show_all_zones', 0);

    // Récupérer le JSON
    $zones_json = isset($_POST['zones']) ? wp_unslash($_POST['zones']) : '';
    if (!$zones_json) {
        wp_send_json_error('Pas de données transmises');
    }
    $decoded = json_decode($zones_json, true);
    if (!is_array($decoded)) {
        wp_send_json_error('Format des zones invalide');
    }

    if ($always_show_all_zones) {
        // -------------------------------
        // MODE MULTI-COMMERCIAL
        // -------------------------------
        // On utilise la logique "group by commercial_id" et on supprime
        // les orphelins pour chaque commercial

        // Grouper les polygones par commercial_id
        $polys_by_com = array();
        foreach ($decoded as $poly) {
            $com_id = !empty($poly['commercial_id']) ? intval($poly['commercial_id']) : 0;
            $polys_by_com[$com_id][] = $poly;
        }

        foreach ($polys_by_com as $com_id => $polys) {
            // 1) Récupérer zones de ce commercial
            $args = array(
                'post_type'      => 'zone',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'   => 'zone_commercial_id',
                        'value' => $com_id
                    )
                )
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

            // 2) Collecter les IDs reçus pour ce commercial
            $received_ids = array();
            foreach ($polys as $p) {
                if (!empty($p['zone_id'])) {
                    $received_ids[] = intval($p['zone_id']);
                }
            }

            // 3) Supprimer les orphelines
            $ids_to_delete = array_diff($existing_ids, $received_ids);
            foreach ($ids_to_delete as $id_to_del) {
                wp_delete_post($id_to_del, true);
            }

            // 4) Créer / Mettre à jour
            foreach ($polys as $p) {
                $zid = !empty($p['zone_id']) ? intval($p['zone_id']) : 0;
                if (empty($p['geometry'])) {
                    continue;
                }
                $geometry = json_encode($p['geometry']);
                if (!$geometry || !json_decode($geometry, true)) {
                    continue; 
                }
                $c_id = !empty($p['commercial_id']) ? intval($p['commercial_id']) : 0;

                if ($zid && get_post_type($zid) === 'zone') {
                    // update
                    update_post_meta($zid, 'zone_geojson', $geometry);
                    update_post_meta($zid, 'zone_commercial_id', $c_id);
                } else {
                    // create
                    $new_zone_id = wp_insert_post(array(
                        'post_type'   => 'zone',
                        'post_title'  => 'Zone - ' . date('Y-m-d H:i:s'),
                        'post_status' => 'publish'
                    ));
                    if ($new_zone_id) {
                        update_post_meta($new_zone_id, 'zone_geojson', $geometry);
                        update_post_meta($new_zone_id, 'zone_commercial_id', $c_id);
                    }
                }
            }
        }
    } else {
        // -------------------------------
        // MODE SINGLE-COMMERCIAL
        // -------------------------------
        // On part du principe qu'on ne manipule qu'un SEUL commercial
        // => On va chercher son ID dans le 1er polygone (ou tous identiques)
        // => On supprime les orphelins de CE commercial, puis on met à jour

        if (count($decoded) == 0) {
            // S'il n'y a rien => tout supprimer ? A vous de voir la logique
            wp_send_json_error('Aucun polygone dans la requête');
        }

        // Hypothèse: tous les polygones renvoyés ont le même commercial_id
        $first = $decoded[0];
        $com_id = !empty($first['commercial_id']) ? intval($first['commercial_id']) : 0;

        // Récupérer les zones existantes pour ce commercial
        $args = array(
            'post_type'      => 'zone',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => 'zone_commercial_id',
                    'value' => $com_id
                )
            )
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

        // Collecter les IDs reçus
        $received_ids = array();
        foreach ($decoded as $p) {
            if (!empty($p['zone_id'])) {
                $received_ids[] = intval($p['zone_id']);
            }
        }

        // Supprimer celles qui ne sont plus listées
        $ids_to_delete = array_diff($existing_ids, $received_ids);
        foreach ($ids_to_delete as $id_to_del) {
            wp_delete_post($id_to_del, true);
        }

        // Créer / Mettre à jour
        foreach ($decoded as $p) {
            $zid = !empty($p['zone_id']) ? intval($p['zone_id']) : 0;
            if (empty($p['geometry'])) {
                continue;
            }
            $geometry = json_encode($p['geometry']);
            if (!$geometry || !json_decode($geometry, true)) {
                continue; 
            }
            $c_id = !empty($p['commercial_id']) ? intval($p['commercial_id']) : 0;

            if ($zid && get_post_type($zid) === 'zone') {
                // update
                update_post_meta($zid, 'zone_geojson', $geometry);
                update_post_meta($zid, 'zone_commercial_id', $c_id);
            } else {
                // create
                $new_zone_id = wp_insert_post(array(
                    'post_type'   => 'zone',
                    'post_title'  => 'Zone - ' . date('Y-m-d H:i:s'),
                    'post_status' => 'publish'
                ));
                if ($new_zone_id) {
                    update_post_meta($new_zone_id, 'zone_geojson', $geometry);
                    update_post_meta($new_zone_id, 'zone_commercial_id', $c_id);
                }
            }
        }
    }

    wp_send_json_success('Zones sauvegardées / supprimées avec succès');
}
add_action('wp_ajax_save_multiple_zones', 'zonify_save_multiple_zones');
