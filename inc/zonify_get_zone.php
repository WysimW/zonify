<?php
add_action('wp_ajax_get_zone', 'zonify_get_zone');
function zonify_get_zone() {
    // Récupérer la valeur envoyée (ex: "0" ou "12,34,56")
    $commercial_input = isset($_POST['commercial_id']) ? sanitize_text_field($_POST['commercial_id']) : '';
    
    // Si vide ou "0", on charge toutes les zones
    if(empty($commercial_input) || $commercial_input === "0") {
        $commercial_ids = array();
    } else {
        // Convertir la chaîne en tableau d'entiers
        $commercial_ids = array_map('intval', explode(',', $commercial_input));
    }
    
    $args = array(
        'post_type'      => 'zone',
        'posts_per_page' => -1
    );
    
    if (!empty($commercial_ids)) {
        $args['meta_query'] = array(
            array(
                'key'     => 'zone_commercial_id',
                'value'   => $commercial_ids,
                'compare' => 'IN',
                'type'    => 'NUMERIC'
            )
        );
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
                    $features[] = array(
                        'type'       => 'Feature',
                        'properties' => array(
                            'zone_id'       => get_the_ID(),
                            'commercial_id' => intval(get_post_meta(get_the_ID(), 'zone_commercial_id', true))
                        ),
                        'geometry'   => $decoded
                    );
                }
            }
        }
        wp_reset_postdata();
    } else {
        // Vous pouvez ajouter un message d'erreur dans un champ debug si nécessaire
        $features = array();
    }
    wp_send_json_success(array('zone_data' => $features));
}
