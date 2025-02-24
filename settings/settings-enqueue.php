<?php
// reglage/settings-enqueue.php
if ( ! defined( 'ABSPATH' ) ) exit;

function zonify_reglage_enqueue_scripts($hook) {
    // On cible la page de réglages Zonify
    $allowed_hooks = array(
        'toplevel_page_zonify',
        'zonify_page_zonify_settings'
    );
    if ( ! in_array($hook, $allowed_hooks) ) {
        return;
    }
    // Charger jQuery UI pour les onglets
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    // Enqueue du script settings-tabs.js
    wp_enqueue_script('zonify-settings-tabs', plugin_dir_url(__FILE__) . 'settings-tabs.js', array('jquery', 'jquery-ui-tabs'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'zonify_reglage_enqueue_scripts');
