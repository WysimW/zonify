<?php /**
 * Récupérer les zones (get_zone)
 */
function zonify_get_zone_callback() {
    check_ajax_referer('zonify_ajax_nonce', '_ajax_nonce');
    
    $commercial_id = isset($_POST['commercial_id']) ? intval($_POST['commercial_id']) : 0;
    $region_id = isset($_POST['region_id']) ? intval($_POST['region_id']) : 0;
    
    $debug = array();
    
    // Arguments pour la requête WordPress
    $args = array(
        'post_type'      => 'zone',
        'posts_per_page' => -1
    );
    
    // Filtre par commercial_id si spécifié et différent de 0 (0 = toutes les zones)
    if ($commercial_id > 0) {
        $args['meta_key'] = 'zone_commercial_id';
        $args['meta_value'] = $commercial_id;
    } elseif (strpos($commercial_id, ',') !== false) {
        // Si commercial_id est une liste d'IDs séparés par des virgules
        $commercial_ids = array_map('intval', explode(',', $commercial_id));
        $args['meta_query'] = array(
            array(
                'key' => 'zone_commercial_id',
                'value' => $commercial_ids,
                'compare' => 'IN'
            )
        );
        $debug[] = "Filtre par multiple commercial_ids : " . implode(', ', $commercial_ids);
    } else {
        $debug[] = "Aucun filtre par commercial_id";
    }
    
    // Filtre supplémentaire par region_id si spécifié et différent de 0
    if ($region_id > 0) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'region',
                'field' => 'term_id',
                'terms' => $region_id
            )
        );
        $debug[] = "Filtre par region_id : $region_id";
    } elseif (strpos($region_id, ',') !== false) {
        // Si region_id est une liste d'IDs séparés par des virgules
        $region_ids = array_map('intval', explode(',', $region_id));
        
        if (!isset($args['tax_query'])) {
            $args['tax_query'] = array();
        }
        
        $args['tax_query'][] = array(
            'taxonomy' => 'region',
            'field'    => 'term_id',
            'terms'    => $region_ids,
            'operator' => 'IN'
        );
        
        $debug[] = "Filtre par multiple region_ids : " . implode(', ', $region_ids);
    }
    
    // Exécution de la requête
    $query = new WP_Query($args);
    $result = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            $zone_id = get_the_ID();
            $geojson = get_post_meta($zone_id, 'zone_geojson', true);
            
            if (empty($geojson)) {
                continue;
            }
            
            // Récupérer l'ID du commercial associé à cette zone
            $comm_id = get_post_meta($zone_id, 'zone_commercial_id', true);
            
            // Récupérer les couleurs personnalisées du commercial
            $border_color = '';
            $fill_color = '';
            if ($comm_id) {
                $border_color = get_post_meta($comm_id, 'commercial_border_color', true);
                $fill_color = get_post_meta($comm_id, 'commercial_fill_color', true);
            }
            
            // Récupérer les termes de la taxonomie region pour cette zone
            $region_terms = get_the_terms($zone_id, 'region');
            $region_ids = array();
            $region_names = array();
            if ($region_terms && !is_wp_error($region_terms)) {
                foreach ($region_terms as $term) {
                    $region_ids[] = $term->term_id;
                    $region_names[] = $term->name;
                }
            }
            
            // Essayer de décoder le GeoJSON
            $geojson_obj = json_decode($geojson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $debug[] = "Erreur de décodage JSON pour la zone #$zone_id : " . json_last_error_msg();
                continue;
            }
            
            // Ajouter les informations de la zone au résultat
            $feature = array(
                'type' => 'Feature',
                'properties' => array(
                    'zone_id' => $zone_id,
                    'commercial_id' => $comm_id,
                    'region_ids' => $region_ids,
                    'region_names' => $region_names,
                    'border_color' => $border_color,
                    'fill_color' => $fill_color
                ),
                'geometry' => $geojson_obj
            );
            
            $result[] = $feature;
        }
        wp_reset_postdata();
    }
    
    // Renvoyer le résultat
    wp_send_json_success(array(
        'zone_data' => $result,
        'debug' => $debug
    ));
}