<?php
function zonify_export_csv_single() {
    // Vérifier les capacités
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée');
    }

    // Récupérer l'ID de zone
    $zone_id = isset($_GET['zone_id']) ? intval($_GET['zone_id']) : 0;
    if (!$zone_id) {
        wp_die('ID de zone manquant');
    }

    // Vérifier que c'est bien un CPT "zone"
    if (get_post_type($zone_id) !== 'zone') {
        wp_die('Ce post n’est pas une zone ou n’existe pas');
    }

    // Récupérer les infos pour remplir la ligne CSV
    $title  = get_the_title($zone_id);
    $com_id = get_post_meta($zone_id, 'zone_commercial_id', true);
    $geo    = get_post_meta($zone_id, 'zone_geojson', true);

    // Préparer la sortie CSV
    header('Content-Disposition: attachment; filename=zone_' . $zone_id . '.csv');
    header('Content-Type: text/csv; charset=utf-8');

    // Ouvrir la sortie
    $output = fopen('php://output', 'w');

    // Écrire l’en-tête CSV (colonnes)
    fputcsv($output, array('zone_id', 'zone_title', 'commercial_id', 'geojson'));

    // Écrire la ligne unique
    fputcsv($output, array($zone_id, $title, $com_id, $geo));

    fclose($output);
    exit;
}
add_action('admin_post_zonify_export_csv_single', 'zonify_export_csv_single');

function zone_export_csv_meta_box_callback($post) {
    $zone_id = $post->ID;
    $export_url = admin_url('admin-post.php?action=zonify_export_csv_single&zone_id=' . $zone_id);
    echo '<a href="' . esc_url($export_url) . '" class="button button-primary" target="_blank">Exporter cette zone en CSV</a>';
}

function zone_export_csv_meta_box() {
    add_meta_box(
        'zone_export_csv',
        'Exporter la zone (CSV)',
        'zone_export_csv_meta_box_callback',
        'zone',
        'side',
        'low'
    );
}
add_action('add_meta_boxes', 'zone_export_csv_meta_box');
