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
function terralize_load_table_buttons_styles($hook) {
    // Liste des hooks autorisés pour le plugin
    $allowed_hooks = array(
        'toplevel_page_terralize',
        'terralize_page_terralize_map',
        'terralize_page_terralize_list',
        'terralize_page_terralize_settings',
        'terralize_page_terralize_import_export',
        'terralize_page_terralize_settings'
    );

    // Vérifier si nous sommes sur une page autorisée
    if (!in_array($hook, $allowed_hooks)) {
        // Pour les pages de CPT, vérifier le type de post
        if (strpos($hook, 'edit.php') !== false || strpos($hook, 'post.php') !== false || strpos($hook, 'post-new.php') !== false) {
            $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
            if (empty($post_type) && isset($_GET['post'])) {
                $post_type = get_post_type($_GET['post']);
            }
            
            // Liste des types de post spécifiques au plugin
            $allowed_post_types = array(
                'terralize_zone',
                'terralize_commercial',
                'terralize_region'
            );
            
            if (!in_array($post_type, $allowed_post_types)) {
                return;
            }
        } else {
            return;
        }
    }

    // URL du plugin
    $plugin_url = plugin_dir_url(__FILE__);
    
    // Enregistrer et charger le fichier CSS
    wp_register_style(
        'terralize-table-buttons',
        $plugin_url . 'assets/css/table-buttons.css',
        array(),
        '1.0.0'
    );
    
    // Charger le style uniquement sur les pages autorisées
    wp_enqueue_style('terralize-table-buttons');
}
add_action('admin_enqueue_scripts', 'terralize_load_table_buttons_styles');