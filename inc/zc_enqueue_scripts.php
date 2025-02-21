<?php
// Enqueue des scripts et styles pour la page d'administration
function zones_commerciales_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_zones-commerciales') {
        return;
    }
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);

    wp_enqueue_style('leaflet-draw-css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css');
    wp_enqueue_script('leaflet-draw-js', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js', array('leaflet-js'), '1.0.4', true);

    // Script personnalisé pour la gestion de la carte en backoffice
    wp_enqueue_script('zones-commerciaux-script', plugin_dir_url(__FILE__) . '../scripts/zones-commerciales.js', array('leaflet-js', 'leaflet-draw-js'), '1.0', true);

     // Enqueue du CSS personnalisé pour le backoffice
     wp_enqueue_style('zones-commerciales-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', array(), '1.0');

    // Passage de variables (ajax_url et nonce) à notre script
    wp_localize_script('zones-commerciaux-script', 'zoneVars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('save_zone_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'zones_commerciales_enqueue_scripts');

