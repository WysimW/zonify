<?php
/**
 * Terralize - Extension personnalisée pour Affichage Premier
 *
 * @package     Terralize_Affichage_Premier
 * @author      Votre Nom
 * @copyright   2025 Affichage Premier
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Terralize - Affichage Premier
 * Plugin URI:  https://affichagepremier.com
 * Description: Extension personnalisée de Terralize pour la gestion des panneaux d'affichage de la société Affichage Premier.
 * Version:     1.0.0
 * Author:      Votre Nom
 * Author URI:  https://votresite.com
 * Text Domain: terralize-affichage-premier
 * Domain Path: /languages
 */

// Si ce fichier est appelé directement, abandon
if (!defined('ABSPATH')) {
    exit;
}

// Constants
define('TERRALIZE_AP_VERSION', '1.0.0');
define('TERRALIZE_AP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TERRALIZE_AP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TERRALIZE_AP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Vérifie si le plugin parent Terralize est actif
 */
function terralize_ap_check_parent_plugin() {
    if (!is_plugin_active('zone-commercial-pluginwp/zone-commercial-pluginwp.php')) {
        add_action('admin_notices', 'terralize_ap_admin_notice_missing_parent_plugin');
        deactivate_plugins(TERRALIZE_AP_PLUGIN_BASENAME);
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}
add_action('admin_init', 'terralize_ap_check_parent_plugin');

/**
 * Message d'erreur si le plugin parent n'est pas actif
 */
function terralize_ap_admin_notice_missing_parent_plugin() {
   /* $message = sprintf(
        __('L\'extension %1$s nécessite le plugin Terralize qui n\'est pas activé. Veuillez installer et activer %2$s d\'abord.', 'terralize-affichage-premier'),
        '<strong>Terralize - Affichage Premier</strong>',
        '<strong>Terralize</strong>'
    );
    echo '<div class="notice notice-error is-dismissible"><p>' . $message . '</p></div>'; */
}

/**
 * Initialise l'extension
 */
function terralize_ap_init() {
    // Charger les fichiers principaux
    require_once TERRALIZE_AP_PLUGIN_DIR . 'includes/admin-customization.php';
    
    // Charger les assets style blueprint
    require_once TERRALIZE_AP_PLUGIN_DIR . 'includes/blueprint-assets.php';
    
    // Charger la page de documentation
    require_once TERRALIZE_AP_PLUGIN_DIR . 'includes/affichage-docs.php';
    
    // Autres initialisations si nécessaire
}
add_action('plugins_loaded', 'terralize_ap_init');

/**
 * Activation de l'extension
 */
function terralize_ap_activate() {
    // Actions à effectuer lors de l'activation
}
register_activation_hook(__FILE__, 'terralize_ap_activate');

/**
 * Désactivation de l'extension
 */
function terralize_ap_deactivate() {
    // Actions à effectuer lors de la désactivation
}
register_deactivation_hook(__FILE__, 'terralize_ap_deactivate');

/**
 * Classe principale de l'extension Affichage Premier
 */
class TerralizeAffichagePremier {
    
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
        
        // Formulaire de contact pour réservation de panneaux
        require_once $this->extension_path . 'includes/panel-contact-shortcode.php';

        // Shortcode pour la carte des panneaux d'affichage
        require_once $this->extension_path . 'templates/affichage-premier-shortcode.php';
    }
    
    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // Modifier l'interface d'administration
        add_action('admin_init', array($this, 'admin_init'));
        
        // Ajouter un onglet de paramètres spécifique dans les réglages Terralize
        add_filter('terralize_settings_tabs', array($this, 'add_settings_tab'));
        
        // Actions déclenchées après sauvegarde des réglages
        add_action('admin_init', array($this, 'save_extension_settings'));
    }
    
    /**
     * Initialisation de l'admin
     */
    public function admin_init() {
        // Si l'option de désactiver les zones est activée
        if (get_option('terralize_ap_disable_zones', '0') === '1') {
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
        // Supprimer les sous-menus de Terralize liés aux zones
        remove_submenu_page('terralize', 'terralize_map');
        remove_submenu_page('terralize', 'edit.php?post_type=zone');
        remove_submenu_page('terralize', 'terralize_import_export');
    }
    
    /**
     * Masquer les options liées aux zones dans les réglages
     */
    public function hide_zone_settings() {
        echo '<style>
            #terralize-back tr:not(.terralize_ap-allowed),
            #terralize-front tr:not(.terralize_ap-allowed),
            #terralize-tabs li a[href="#terralize-back"],
            #terralize-tabs li a[href="#terralize-front"] {
                display: none !important;
            }
        </style>';
    }
    
    /**
     * Ajouter un onglet dans les réglages de Terralize
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
        $disable_zones = get_option('terralize_ap_disable_zones', '0');
        
        ?>
        <h2>Paramètres spécifiques Affichage Premier</h2>
        <p>Configurez ici les options spécifiques à l'extension Affichage Premier.</p>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Gestion des zones</th>
                <td>
                    <label for="terralize_ap_disable_zones">
                        <input type="checkbox" name="terralize_ap_disable_zones" id="terralize_ap_disable_zones" value="1" <?php checked($disable_zones, '1'); ?> />
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
        if (isset($_POST['terralize_settings_submit']) && check_admin_referer('terralize_settings_nonce')) {
            // Sauvegarder l'option de désactivation des zones
            update_option('terralize_ap_disable_zones', isset($_POST['terralize_ap_disable_zones']) ? '1' : '0');
        }
    }
}

/**
 * Initialiser l'extension
 */
function TerralizeAffichagePremier_init() {
    // Initialiser directement sans vérifier la classe Terralize
    TerralizeAffichagePremier::get_instance();
}
add_action('plugins_loaded', 'TerralizeAffichagePremier_init', 20); // Augmenter la priorité pour s'assurer que le plugin principal est chargé