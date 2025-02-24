<?php
function zonify_get_zone() {
     if ( ! current_user_can('manage_options') ) {
         wp_send_json_error('Permission refusée');
     }
     $commercial_id = isset($_POST['commercial_id']) ? intval($_POST['commercial_id']) : 0;
     if ( ! $commercial_id ) {
         wp_send_json_error('Données manquantes');
     }
 
     // Trouver un post "zone" qui a la meta 'zone_commercial_id' = $commercial_id
     $args = array(
         'post_type' => 'zone',
         'meta_query' => array(
             array(
                 'key'   => 'zone_commercial_id',
                 'value' => $commercial_id,
             ),
         ),
         'posts_per_page' => 1 // Suppose qu'il n'y a qu'une zone par commercial
     );
     $query = new WP_Query($args);
     if ($query->have_posts()) {
         $query->the_post();
         $zone_id = get_the_ID();
         $zone_data = get_post_meta($zone_id, 'zone_geojson', true);
         wp_reset_postdata();
         wp_send_json_success( array(
             'zone_data' => $zone_data,
             'zone_id'   => $zone_id
         ) );
     } else {
         // Aucune zone trouvée
         wp_send_json_success( array( 'zone_data' => '', 'zone_id' => 0 ) );
     }
 }
 add_action('wp_ajax_get_zone', 'zonify_get_zone');
 