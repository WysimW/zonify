<?php
function zonify_export_csv() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée');
    }

    header('Content-Disposition: attachment; filename=zones.csv');
    header('Content-Type: text/csv; charset=utf-8');

    $output = fopen('php://output', 'w');
    // En-tête CSV
    fputcsv($output, array('zone_id', 'zone_title', 'commercial_id', 'geojson'));

    $args = array(
        'post_type' => 'zone',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $zid    = get_the_ID();
            $title  = get_the_title($zid);
            $com_id = get_post_meta($zid, 'zone_commercial_id', true);
            $geo    = get_post_meta($zid, 'zone_geojson', true);

            fputcsv($output, array($zid, $title, $com_id, $geo));
        }
        wp_reset_postdata();
    }
    fclose($output);
    exit;
}
add_action('admin_post_zonify_export_csv', 'zonify_export_csv');
