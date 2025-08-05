<?php
/**
 * Enregistrement des assets spécifiques au style blueprint
 * 
 * Ce fichier s'occupe de charger les ressources CSS et JS pour
 * la personnalisation blueprintde l'interface de cartographie.
 */

// Sécurité - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre les scripts et styles blueprint
 */
function terralize_ap_register_blueprint_assets() {
    $plugin_url = plugin_dir_url(dirname(__FILE__));
    
    // Enregistrer le script blueprint-map.js
    wp_register_script(
        'terralize-blueprint-map',
        $plugin_url . 'assets/js/blueprint-map.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Enregistrer le script admin-tables.js
    wp_register_script(
        'terralize-admin-tables',
        $plugin_url . 'assets/js/admin-tables.js',
        array('jquery', 'wp-i18n'),
        '1.0.0',
        true
    );
    
    // Charge le script admin-tables sur toutes les pages d'administration pour les CPT
    $screen = get_current_screen();
    if ($screen && ($screen->base === 'edit' || $screen->base === 'post')) {
        wp_enqueue_script('terralize-admin-tables');
    }
    
    // Déterminer si nous sommes sur une page de carte Terralize
    global $pagenow;
    if (($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'terralize_poi') ||
        ($pagenow == 'post.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'poi') ||
        ($pagenow == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'poi')) {
        
        // Mettre à disposition les données pour le script JS
        $map_data = array(
            'panelTypes' => array('mural', 'pre-implantations', '4x3', '8x3', 'déroulant', 'totem'),
            'defaultCenter' => array(46.227638, 2.213749), // Centre de la France
            'defaultZoom' => 6
        );
        
        // Localiser le script pour passer les données
        wp_localize_script('terralize-blueprint-map', 'terralizeMapData', $map_data);
        
        // Enqueue le script
        wp_enqueue_script('terralize-blueprint-map');
    }
}
add_action('admin_enqueue_scripts', 'terralize_ap_register_blueprint_assets');

/**
 * Ajouter un hook pour changer la classe CSS du conteneur de carte
 */
function terralize_ap_add_blueprint_container_class($classes) {
    return $classes . ' blueprint-map-container';
}
add_filter('terralize_map_container_class', 'terralize_ap_add_blueprint_container_class');

/**
 * Injecter des données personnalisées dans la page
 */
function terralize_ap_inject_blueprint_data() {
    // Vérifier si nous sommes sur une page de carte
    global $pagenow;
    if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'terralize_poi') {
        ?>
        <script>
            // Informations supplémentaires pour la carte blueprint
            window.terralizeMapData = window.terralizeMapData || {};
            
            // Ajouter un hook personnalisé pour les popups
            jQuery(document).ready(function($) {
                $(document).on('terralize_marker_popup_created', function(event, popup, marker) {
                    // Déclencher notre événement personnalisé
                    $(document).trigger('terralize_popup_opened', [popup, marker]);
                });
            });
        </script>
        <?php
    }
}
add_action('admin_footer', 'terralize_ap_inject_blueprint_data');

/**
 * Ajouter le code de chargement du template de popup personnalisé
 */
function terralize_ap_load_panel_popup_template() {
    // Charger notre classe de template pour les popups
    require_once dirname(__FILE__) . '/panel-popup-template.php';
}
add_action('init', 'terralize_ap_load_panel_popup_template');