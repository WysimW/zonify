<?php
/**
 * Extension Name: Zonify Affichage Premier
 * Description: Extension spécifique pour les fonctionnalités personnalisées du client Affichage Premier.
 * Version: 1.0.0
 * Author: Zonify Team
 */

// Si ce fichier est appelé directement, abandon
if (!defined('ABSPATH')) {
    exit;
}

// Ajouter une notification pour confirmer que l'extension est chargée
add_action('admin_notices', function() {
    echo '<div class="notice notice-success is-dismissible"><p>Extension Zonify Affichage Premier activée avec succès.</p></div>';
});

/**
 * Classe principale de l'extension Affichage Premier
 */
class ZonifyAffichagePremier {
    
    /**
     * Instance unique de la classe
     */
    private static $instance = null;
    
    /**
     * Chemin du répertoire de l'extension
     */
    public $extension_path;
    
    /**
     * URL du répertoire de l'extension
     */
    public $extension_url;

    /**
     * Constructeur privé pour le pattern singleton
     */
    private function __construct() {
        $this->extension_path = plugin_dir_path(__FILE__);
        $this->extension_url = plugin_dir_url(__FILE__);
        
        // Créer le répertoire assets s'il n'existe pas
        $assets_dir = $this->extension_path . 'assets';
        if (!file_exists($assets_dir)) {
            wp_mkdir_p($assets_dir);
        }
        
        // Créer le répertoire js s'il n'existe pas
        $js_dir = $assets_dir . '/js';
        if (!file_exists($js_dir)) {
            wp_mkdir_p($js_dir);
        }
        
        // Charger les fichiers nécessaires
        $this->load_dependencies();
        
        // Initialiser les hooks
        $this->init_hooks();
    }
    
    /**
     * Obtenir l'instance unique de la classe (pattern singleton)
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Charger les fichiers de dépendance
     */
    private function load_dependencies() {
        // Renommer et personnaliser les POI
        require_once $this->extension_path . 'includes/poi-customization.php';
        
        // Champs méta supplémentaires pour les POI
        require_once $this->extension_path . 'includes/poi-meta-fields.php';
        
        // Personnalisation de l'administration
        require_once $this->extension_path . 'includes/admin-customization.php';

        require_once $this->extension_path . 'includes/import-csv.php';

    }
    
    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // Modifier l'interface d'administration
        add_action('admin_init', array($this, 'admin_init'));
        
        // Ajouter un onglet de paramètres spécifique dans les réglages Zonify
        add_filter('zonify_settings_tabs', array($this, 'add_settings_tab'));
        
        // Actions déclenchées après sauvegarde des réglages
        add_action('admin_init', array($this, 'save_extension_settings'));
    }
    
    /**
     * Initialisation de l'admin
     */
    public function admin_init() {
        // Si l'option de désactiver les zones est activée
        if (get_option('zap_disable_zones', '0') === '1') {
            // Supprimer les menus liés aux zones
            add_action('admin_menu', array($this, 'remove_zone_menus'), 999);
            
            // Masquer les options liées aux zones dans les réglages
            add_action('admin_head', array($this, 'hide_zone_settings'));
        }
    }
    
    /**
     * Supprimer les menus liés aux zones
     */
    public function remove_zone_menus() {
        // Supprimer les sous-menus de Zonify liés aux zones
        remove_submenu_page('zonify', 'zonify_map');
        remove_submenu_page('zonify', 'edit.php?post_type=zone');
        remove_submenu_page('zonify', 'zonify_import_export');
    }
    
    /**
     * Masquer les options liées aux zones dans les réglages
     */
    public function hide_zone_settings() {
        echo '<style>
            #zonify-back tr:not(.zap-allowed),
            #zonify-front tr:not(.zap-allowed),
            #zonify-tabs li a[href="#zonify-back"],
            #zonify-tabs li a[href="#zonify-front"] {
                display: none !important;
            }
        </style>';
    }
    
    /**
     * Ajouter un onglet dans les réglages de Zonify
     */
    public function add_settings_tab($tabs) {
        $tabs['affichage_premier'] = array(
            'title' => 'Affichage Premier',
            'callback' => array($this, 'settings_tab_content')
        );
        
        return $tabs;
    }
    
    /**
     * Contenu de l'onglet des réglages
     */
    public function settings_tab_content() {
        // Récupérer les options
        $disable_zones = get_option('zap_disable_zones', '0');
        
        ?>
        <h2>Paramètres spécifiques Affichage Premier</h2>
        <p>Configurez ici les options spécifiques à l'extension Affichage Premier.</p>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Gestion des zones</th>
                <td>
                    <label for="zap_disable_zones">
                        <input type="checkbox" name="zap_disable_zones" id="zap_disable_zones" value="1" <?php checked($disable_zones, '1'); ?> />
                        Désactiver complètement les fonctionnalités de gestion des zones
                    </label>
                    <p class="description">Masque les menus et options liées aux zones commerciales.</p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Sauvegarder les réglages de l'extension
     */
    public function save_extension_settings() {
        if (isset($_POST['zonify_settings_submit']) && check_admin_referer('zonify_settings_nonce')) {
            // Sauvegarder l'option de désactivation des zones
            update_option('zap_disable_zones', isset($_POST['zap_disable_zones']) ? '1' : '0');
        }
    }
}

/**
 * Initialiser l'extension
 */
function ZonifyAffichagePremier_init() {
    // Initialiser directement sans vérifier la classe Zonify
    ZonifyAffichagePremier::get_instance();
}
add_action('plugins_loaded', 'ZonifyAffichagePremier_init', 20); // Augmenter la priorité pour s'assurer que le plugin principal est chargé