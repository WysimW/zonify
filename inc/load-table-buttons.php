<?php
/**
 * Chargement des styles améliorés pour les boutons dans les tableaux CPT
 */

// Sécurité - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajoute les styles CSS pour les boutons des tableaux
 */
function zonify_load_table_buttons_styles() {
    // S'assurer que nous sommes sur une page d'administration
    if (!is_admin()) {
        return;
    }

    // URL du plugin
    $plugin_url = plugin_dir_url(__FILE__);
    
    // Enregistrer et charger le fichier CSS
    wp_register_style(
        'zonify-table-buttons',
        $plugin_url . 'assets/css/table-buttons.css',
        array(),
        '1.0.0'
    );
    
    // Charger le style sur toutes les pages admin
    wp_enqueue_style('zonify-table-buttons');
}
add_action('admin_enqueue_scripts', 'zonify_load_table_buttons_styles');