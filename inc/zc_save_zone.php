<?php
function zones_commerciales_save_zone() {
    if ( ! current_user_can('manage_options') || ! check_ajax_referer('save_zone_nonce', '_ajax_nonce', false) ) {
        wp_send_json_error('Permission refusée');
    }
    
    $zone_data    = isset($_POST['zone_data']) ? sanitize_text_field($_POST['zone_data']) : '';
    $commercial_id = isset($_POST['commercial_id']) ? intval($_POST['commercial_id']) : 0;
    
    if ( empty($zone_data) || ! $commercial_id ) {
        wp_send_json_error('Données manquantes');
    }
    
    if ( update_post_meta($commercial_id, 'zone_geojson', $zone_data) ) {
        wp_send_json_success('Zone sauvegardée');
    } else {
        wp_send_json_error('Erreur lors de la sauvegarde');
    }
    wp_die();
}
add_action('wp_ajax_save_zone', 'zones_commerciales_save_zone');
