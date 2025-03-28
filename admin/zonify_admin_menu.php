<?php
// Ajouter une page de gestion dans le menu d'administration

function zonify_admin_menu() {
    add_menu_page(
        'Zonify',          // Titre de la page
        'Zonify',          // Titre du menu
        'manage_options',  // Capacité requise
        'zonify',          // Slug du menu
        'zonify_main_page',// Fonction callback pour la page principale
        'dashicons-location-alt' // Icône du menu (ou utilisez votre propre icône via URL)
    );

        // Ajout d'un lien vers la gestion des commerciaux (le CPT "commercial")
        add_submenu_page(
            'zonify',
            'Commerciaux',
            'Commerciaux',
            'manage_options',
            'edit.php?post_type=commercial'
        );
    
    
    add_submenu_page(
        'zonify',
        'Tracer des zones',
        'Tracer des zones',
        'manage_options',
        'zonify_map',
        'zonify_map_pages'
    );
    
    add_submenu_page(
        'zonify',
        'Liste des zones',
        'Liste des zones',
        'manage_options',
        'edit.php?post_type=zone'
    );
    

    add_submenu_page(
        'zonify',
        'Réglages',
        'Réglages',
        'manage_options',
        'zonify_settings',
        'zonify_settings_page'
    );

    add_submenu_page(
        'zonify',
        'Import / Export Zones',
        'Import / Export',
        'manage_options',
        'zonify_import_export',
        'zonify_import_export_page'
    );
    
       // Ajout du sous-menu pour la gestion des Contacts
       add_submenu_page(
        'zonify',
        'Contacts',
        'Contacts',
        'manage_options',
        'edit.php?post_type=zonify_contact'
    );
}
add_action( 'admin_menu', 'zonify_admin_menu' );
