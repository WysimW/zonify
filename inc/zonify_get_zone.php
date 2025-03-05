<?php
add_action('wp_ajax_get_zone', 'zonify_get_zone');
// add_action('wp_ajax_nopriv_get_zone', 'zonify_get_zone'); // si besoin pour non-connectés

function zonify_get_zone() {
    // Vérifier permission ou nonce si besoin
     if (!current_user_can('manage_options')) {
         wp_send_json_error('Permission refusée');
     }

    // Récupérer l'ID du commercial
    $commercial_id = isset($_POST['commercial_id']) ? intval($_POST['commercial_id']) : 0;

    $args = array(
        'post_type'      => 'zone',
        'posts_per_page' => -1
    );

    // Si commercial_id > 0 => on filtre, sinon => toutes les zones
    if ($commercial_id > 0) {
        $args['meta_query'] = array(
            array(
                'key'   => 'zone_commercial_id',
                'value' => $commercial_id
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
                    // Récupérer la valeur réelle en BDD, au cas où
                    $real_com_id = get_post_meta(get_the_ID(), 'zone_commercial_id', true);

                    $features[] = array(
                        'type'       => 'Feature',
                        'properties' => array(
                            'zone_id'       => get_the_ID(),
                            'commercial_id' => intval($real_com_id)
                        ),
                        'geometry'   => $decoded
                    );
                }
            }
        }
        wp_reset_postdata();
    }

    wp_send_json_success(array('zone_data' => $features));
}
