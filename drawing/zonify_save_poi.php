<?php
add_action('wp_ajax_save_poi', 'zonify_save_poi');
function zonify_save_poi() {
    if ( ! current_user_can('manage_options') || ! check_ajax_referer('save_poi_nonce', '_ajax_nonce', false) ) {
        wp_send_json_error('Permission refusée');
    }

    $poi_data = isset($_POST['poi_data']) ? wp_unslash($_POST['poi_data']) : '';
    $poi_id   = isset($_POST['poi_id']) ? intval($_POST['poi_id']) : 0;

    if ( empty($poi_data) ) {
        wp_send_json_error('Aucune donnée de point fournie');
    }

    $decoded = json_decode($poi_data, true);
    if ( is_null($decoded) ) {
        wp_send_json_error('Données de point invalides');
    }

    if ( $poi_id && get_post_type($poi_id) === 'poi' ) {
        update_post_meta($poi_id, 'poi_geojson', $poi_data);
        wp_send_json_success(array(
            'poi_id'  => $poi_id,
            'message' => 'Point d’intérêt mis à jour'
        ));
    } else {
        $post_data = array(
            'post_type'   => 'poi',
            'post_title'  => 'Point d’intérêt - ' . current_time('Y-m-d H:i:s'),
            'post_status' => 'publish'
        );
        $new_poi_id = wp_insert_post($post_data);
        if ( ! $new_poi_id ) {
            wp_send_json_error('Erreur lors de la création du point d’intérêt');
        }
        update_post_meta($new_poi_id, 'poi_geojson', $poi_data);
        wp_send_json_success(array(
            'poi_id'  => $new_poi_id,
            'message' => 'Point d’intérêt sauvegardé'
        ));
    }
}
