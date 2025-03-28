<?php
function zonify_delete_zone() {
    if (!current_user_can('manage_options') || !check_ajax_referer('save_zone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error('Permission refusée');
    }
    $zone_id = isset($_POST['zone_id']) ? intval($_POST['zone_id']) : 0;
    if (!$zone_id) {
        wp_send_json_error('Aucun ID de zone spécifié');
    }

    $deleted = wp_delete_post($zone_id, true); // true => supprimer définitivement
    if ($deleted) {
        wp_send_json_success('Zone supprimée');
    } else {
        wp_send_json_error('Échec de la suppression de la zone');
    }
}
add_action('wp_ajax_delete_zone', 'zonify_delete_zone');
