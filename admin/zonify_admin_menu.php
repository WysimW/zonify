<?php
// Ajouter une page de gestion dans le menu d'administration

function terralize_admin_menu()
{
    $is_terralize_ap_enabled = class_exists('TerralizeAffichagePremier');

    if (!$is_terralize_ap_enabled) {
        add_menu_page(
            'Terralize',          // Titre de la page
            'Terralize',          // Titre du menu
            'manage_options',  // Capacité requise
            'terralize',          // Slug du menu
            'terralize_main_page', // Fonction callback pour la page principale
            'dashicons-location-alt' // Icône du menu (ou utilisez votre propre icône via URL)
        );

        // Ajout d'un lien vers la gestion des commerciaux (le CPT "commercial")
        add_submenu_page(
            'terralize',
            'Commerciaux',
            'Commerciaux',
            'manage_options',
            'edit.php?post_type=commercial'
        );


        add_submenu_page(
            'terralize',
            'Tracer des zones',
            'Tracer des zones',
            'manage_options',
            'terralize_map',
            'terralize_map_pages'
        );

        add_submenu_page(
            'terralize',
            'Liste des zones',
            'Liste des zones',
            'manage_options',
            'edit.php?post_type=zone'
        );


        add_submenu_page(
            'terralize',
            'Réglages',
            'Réglages',
            'manage_options',
            'terralize_settings',
            'terralize_settings_page'
        );

        add_submenu_page(
            'terralize',
            'Import / Export Zones',
            'Import / Export',
            'manage_options',
            'terralize_import_export',
            'terralize_import_export_page'
        );

        // Ajout du sous-menu pour la gestion des Contacts
        add_submenu_page(
            'terralize',
            'Contacts',
            'Contacts',
            'manage_options',
            'edit.php?post_type=terralize_contact'
        );
        // Ajout du sous-menu pour la liste des Points d'Intérêt (POI)
        add_submenu_page(
            'terralize',
            'Liste des Points d’Intérêt',
            'Liste des Points d’Intérêt',
            'manage_options',
            'edit.php?post_type=poi'
        );

        // Ajout du sous-menu pour la gestion des Points d'Intérêt (POI)
        add_submenu_page(
            'terralize',
            'Points d\'Intérêt',
            'Points d\'Intérêt',
            'manage_options',
            'terralize_poi',
            'terralize_poi_pages'
        );

        add_submenu_page(
            'terralize',
            'Icônes POI',
            'Icônes POI',
            'edit_posts',
            'edit.php?post_type=terralize_icon'
        );
    }

    if ($is_terralize_ap_enabled) {
        add_menu_page(
            'Terralize',          // Titre de la page
            'Terralize',          // Titre du menu
            'manage_options',  // Capacité requise
            'terralize',          // Slug du menu
            'terralize_main_page', // Fonction callback pour la page principale
            'dashicons-location-alt' // Icône du menu (ou utilisez votre propre icône via URL)
        );


        add_submenu_page(
            'terralize',
            'Réglages',
            'Réglages',
            'manage_options',
            'terralize_settings',
            'terralize_settings_page'
        );

        // Ajout du sous-menu pour la gestion des Contacts
        add_submenu_page(
            'terralize',
            'Contacts',
            'Contacts',
            'manage_options',
            'edit.php?post_type=terralize_contact'
        );

        // Ajout du sous-menu pour la liste des Points d'Intérêt (POI)
        add_submenu_page(
            'terralize',
            'Liste des Points d’Intérêt',
            'Liste des Points d’Intérêt',
            'manage_options',
            'edit.php?post_type=poi'
        );

        // Ajout du sous-menu pour la gestion des Points d'Intérêt (POI)
        add_submenu_page(
            'terralize',
            'Points d\'Intérêt',
            'Points d\'Intérêt',
            'manage_options',
            'terralize_poi',
            'terralize_poi_pages'
        );
    }
}
add_action('admin_menu', 'terralize_admin_menu');

// Gérer les écrans de sous-menu
function terralize_fix_submenu_highlight()
{
    global $submenu_file, $plugin_page, $pagenow;

    // Si on est sur l'écran d'édition d'un custom post type
    if ($pagenow === 'edit.php' || $pagenow === 'post-new.php' || $pagenow === 'post.php') {
        $post_type = $_GET['post_type'] ?? get_post_type($_GET['post'] ?? 0);
        if ($post_type === 'zone') {
            $submenu_file = 'edit.php?post_type=zone';
            $plugin_page = null;
        } else if ($post_type === 'commercial') {
            $submenu_file = 'edit.php?post_type=commercial';
            $plugin_page = null;
        } else if ($post_type === 'poi') {
            $submenu_file = 'edit.php?post_type=poi';
            $plugin_page = null;
        } else if ($post_type === 'terralize_icon') {
            $submenu_file = 'edit.php?post_type=terralize_icon';
            $plugin_page = null;
        }
    }

    // Ajouter des conditions similaires pour vos autres CPTs
    // si vous en avez d'autres à gérer
}
add_action('admin_head', 'terralize_fix_submenu_highlight');

// Fonction de callback pour l'affichage du tableau de bord
function terralize_dashboard_page()
{
    // Vérifiez les permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    // Récupérer les statistiques
    $zones_count = wp_count_posts('zone')->publish;
    $commerciaux_count = wp_count_posts('commercial')->publish;
    $poi_count = wp_count_posts('poi')->publish;

    include(plugin_dir_path(__FILE__) . 'templates/dashboard.php');
}
