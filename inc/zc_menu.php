<?php
// Ajouter une page de gestion dans le menu d'administration
function zones_commerciales_admin_menu() {
    add_menu_page(
        'Zones Commerciales',
        'Zones Commerciales',
        'manage_options',
        'zones-commerciales',
        'zones_commerciales_page'
    );
    add_submenu_page(
        'zones-commerciales',
        'Liste des zones',
        'Liste des zones',
        'manage_options',
        'zones-commerciales-list',
        'zones_commerciales_list_page'
    );
}
add_action( 'admin_menu', 'zones_commerciales_admin_menu' );



