<?php
add_action('wp_ajax_get_zone', 'zonify_get_zone');
function zonify_get_zone() {
    // Récupérer la valeur envoyée (ex: "0" ou "12,34,56")
    $commercial_input = isset($_POST['commercial_id']) ? sanitize_text_field($_POST['commercial_id']) : '';
    $region_input = isset($_POST['region_id']) ? sanitize_text_field($_POST['region_id']) : '';
    
    // Créer le tableau des arguments de requête
    $args = array(
        'post_type'      => 'zone',
        'posts_per_page' => -1
    );
    
    // Tableau pour les conditions de requête
    $tax_query = array();
    $meta_query = array();
    
    // Si commercial_id est fourni et n'est pas "0"
    if(!empty($commercial_input) && $commercial_input !== "0") {
        // Convertir la chaîne en tableau d'entiers
        $commercial_ids = array_map('intval', explode(',', $commercial_input));
        
        $meta_query[] = array(
            'key'     => 'zone_commercial_id',
            'value'   => $commercial_ids,
            'compare' => 'IN',
            'type'    => 'NUMERIC'
        );
    }
    
    // Si region_id est fourni et n'est pas "0"
    if(!empty($region_input) && $region_input !== "0") {
        // Convertir la chaîne en tableau d'entiers
        $region_ids = array_map('intval', explode(',', $region_input));
        
        $tax_query[] = array(
            'taxonomy' => 'region',
            'field'    => 'term_id',
            'terms'    => $region_ids,
            'operator' => 'IN',
        );
    }
    
    // Ajouter les conditions de requête s'il y en a
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    $query = new WP_Query($args);
    $features = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $geojson = get_post_meta(get_the_ID(), 'zone_geojson', true);
            if ($geojson) {
                $decoded = json_decode($geojson, true);
                if (is_array($decoded)) {
                    // Récupérer les IDs des régions associées à cette zone
                    $region_terms = wp_get_post_terms(get_the_ID(), 'region', array('fields' => 'ids'));
                    $region_ids = !is_wp_error($region_terms) ? $region_terms : array();
                    
                    // Récupérer les noms des régions pour affichage
                    $region_names = array();
                    if (!empty($region_ids)) {
                        foreach (wp_get_post_terms(get_the_ID(), 'region') as $region) {
                            $region_names[] = $region->name;
                        }
                    }
                    
                    $features[] = array(
                        'type'       => 'Feature',
                        'properties' => array(
                            'zone_id'       => get_the_ID(),
                            'commercial_id' => intval(get_post_meta(get_the_ID(), 'zone_commercial_id', true)),
                            'region_ids'    => $region_ids,
                            'region_names'  => $region_names
                        ),
                        'geometry'   => $decoded
                    );
                }
            }
        }
        wp_reset_postdata();
    } else {
        // Pas de résultats - pourrions retourner un message différent, mais ce n'est pas une erreur
        // On laisse juste features comme un tableau vide
    }
    
    // Tout s'est bien passé, retourner les données
    $debug = array();
    wp_send_json_success(array(
        'zone_data' => $features,
        'debug'     => $debug  // Pour faciliter le débogage
    ));
}
