<?php
add_action('wp_ajax_toggle_always_show', 'zonify_toggle_always_show');
// add_action('wp_ajax_nopriv_toggle_always_show', 'zonify_toggle_always_show'); 
// si vous voulez que des visiteurs non-connectés puissent le faire (probablement pas)

function zonify_toggle_always_show() {
    // Vérifier permissions (au moins un administrateur)
    if (! current_user_can('manage_options')) {
        wp_send_json_error('Permission refusée');
    }

    // Vérifier la nonce si besoin (optionnel)
    // if (! check_ajax_referer('save_zone_nonce', '_ajax_nonce', false)) {
    //     wp_send_json_error('Nonce invalide');
    // }

    // Lire la valeur actuelle
    $current_val = get_option('zonify_always_show_all_zones', 0);
    $new_val = ($current_val == 1) ? 0 : 1;

    // Mettre à jour
    update_option('zonify_always_show_all_zones', $new_val);

    // Renvoyer la nouvelle valeur
    wp_send_json_success(array(
        'always_show_all_zones' => $new_val,
        'message' => 'Option mise à jour, now = '.$new_val
    ));
}

function zonify_admin_toggle_enqueue($hook) {
    // Déterminez les pages où vous voulez charger ce JS.
    // Par exemple, vous avez $allowed_hooks = array(...).
    // Ou si vous ne ciblez qu'une seule page, vérifiez si $hook correspond à son slug.

    $allowed_hooks = array(
        'toplevel_page_zonify',
        'zonify_page_zonify_map',
        // etc.
    );
    if (! in_array($hook, $allowed_hooks)) {
        return;
    }

    // Enqueue votre fichier JS
    wp_enqueue_script(
        'zonify-toggle-always',
        plugin_dir_url(__FILE__) . '../scripts/zonify-toggle-always.js',
        array('jquery'),    // ou array() si vous n'utilisez pas jQuery
        '1.0',
        true
    );

    // Vous pouvez si besoin localiser des variables ici
    // Par ex. passation de nonce, ou d’autres infos
    // wp_localize_script('zonify-toggle-always', 'toggleVars', array(
    //     'ajax_url' => admin_url('admin-ajax.php'),
    //     'nonce'    => wp_create_nonce('save_zone_nonce'),
    // ));
}
add_action('admin_enqueue_scripts', 'zonify_admin_toggle_enqueue');
