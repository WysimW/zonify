<?php
add_action('wp_ajax_save_zone', 'zonify_save_zone');
function zonify_save_zone() {
    // Vérifier les permissions et le nonce
    if ( ! current_user_can('manage_options') || ! check_ajax_referer('save_zone_nonce', '_ajax_nonce', false) ) {
        wp_send_json_error('Permission refusée');
    }

    $zone_data    = isset($_POST['zone_data']) ? wp_unslash($_POST['zone_data']) : '';
    $commercial_id = isset($_POST['commercial_id']) ? intval($_POST['commercial_id']) : 0;
    $zone_id      = isset($_POST['zone_id']) ? intval($_POST['zone_id']) : 0;

    if ( empty($zone_data) ) {
        wp_send_json_error('Aucune donnée de zone fournie');
    }

    // Valider le JSON
    $decoded = json_decode($zone_data, true);
    if ( is_null($decoded) ) {
        wp_send_json_error('Données de zone invalides');
    }

    if ( $zone_id && get_post_type($zone_id) === 'zone' ) {
        // Mise à jour de la zone existante
        update_post_meta($zone_id, 'zone_geojson', $zone_data);
        update_post_meta($zone_id, 'zone_commercial_id', $commercial_id);
        wp_send_json_success(array(
            'zone_id' => $zone_id,
            'message' => 'Zone mise à jour'
        ));
    } else {
        // Création d'une nouvelle zone
        $post_data = array(
            'post_type'   => 'zone',
            'post_title'  => 'Zone - ' . current_time('Y-m-d H:i:s'),
            'post_status' => 'publish'
        );
        $zone_post_id = wp_insert_post($post_data);
        if ( ! $zone_post_id ) {
            wp_send_json_error('Erreur lors de la création de la zone');
        }
        update_post_meta($zone_post_id, 'zone_geojson', $zone_data);
        update_post_meta($zone_post_id, 'zone_commercial_id', $commercial_id);
        wp_send_json_success(array(
            'zone_id' => $zone_post_id,
            'message' => 'Zone sauvegardée'
        ));
    }
}
