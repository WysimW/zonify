<?php
function zonify_get_zone() {
    if ( ! current_user_can('manage_options') ) {
         wp_send_json_error('Permission refusée');
    }
    $commercial_id = isset($_POST['commercial_id']) ? intval($_POST['commercial_id']) : 0;
    if ( ! $commercial_id ) {
         wp_send_json_error('Données manquantes');
    }
    $zone_data = get_post_meta($commercial_id, 'zone_geojson', true);
    wp_send_json_success( array( 'zone_data' => $zone_data ) );
}
add_action('wp_ajax_get_zone', 'zonify_get_zone');
