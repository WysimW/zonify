<?php
/**
 * Module Terralize GFG
 * Gestion de la relation entre POI et Implantations
 * 
 * @package TerralizeGFG
 * @version 1.0.0
 */

// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale du module Terralize GFG
 */
class Terralize_GFG {
    
    /**
     * Instance unique de la classe
     */
    private static $instance = null;
    
    /**
     * Constructeur
     */
    private function __construct() {
        // Vérifier si le module est activé
        if (!get_option('terralize_gfg_enabled', false)) {
            return;
        }
        
        // Initialiser le module
        $this->init();
    }
    
    /**
     * Obtenir l'instance unique de la classe
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialisation du module
     */
    private function init() {
        // Ajouter les hooks nécessaires
        add_action('init', array($this, 'register_post_type_implantations'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_poi', array($this, 'save_poi_implantations_relation'));
        
        // Ajouter le filtre pour modifier les données des POI dans la carte
        add_filter('terralize_poi_data', array($this, 'add_implantations_data_to_poi'), 10, 2);
        
        // Ajouter les options d'administration
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue les scripts pour l'admin
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Enregistrer le CPT Implantations
     */
    public function register_post_type_implantations() {
        // Vérifier si le CPT existe déjà
        if (post_type_exists('implantations')) {
            return;
        }
        
        $labels = array(
            'name'                  => _x('Implantations', 'Post Type General Name', 'terralize'),
            'singular_name'         => _x('Implantation', 'Post Type Singular Name', 'terralize'),
            'menu_name'             => __('Implantations', 'terralize'),
            'name_admin_bar'        => __('Implantation', 'terralize'),
            'archives'              => __('Archives des implantations', 'terralize'),
            'attributes'            => __('Attributs de l\'implantation', 'terralize'),
            'parent_item_colon'     => __('Implantation parente :', 'terralize'),
            'all_items'             => __('Toutes les implantations', 'terralize'),
            'add_new_item'          => __('Ajouter une nouvelle implantation', 'terralize'),
            'add_new'               => __('Ajouter', 'terralize'),
            'new_item'              => __('Nouvelle implantation', 'terralize'),
            'edit_item'             => __('Modifier l\'implantation', 'terralize'),
            'update_item'           => __('Mettre à jour l\'implantation', 'terralize'),
            'view_item'             => __('Voir l\'implantation', 'terralize'),
            'view_items'            => __('Voir les implantations', 'terralize'),
            'search_items'          => __('Rechercher une implantation', 'terralize'),
            'not_found'             => __('Aucune implantation trouvée', 'terralize'),
            'not_found_in_trash'    => __('Aucune implantation trouvée dans la corbeille', 'terralize'),
            'featured_image'        => __('Logo de l\'implantation', 'terralize'),
            'set_featured_image'    => __('Définir le logo', 'terralize'),
            'remove_featured_image' => __('Supprimer le logo', 'terralize'),
            'use_featured_image'    => __('Utiliser comme logo', 'terralize'),
        );
        
        $args = array(
            'label'                 => __('Implantations', 'terralize'),
            'description'           => __('Gestion des implantationss', 'terralize'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-store',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
            'rewrite'               => array(
                'slug' => 'implantation',
                'with_front' => true
            ),
        );
        
        register_post_type('implantations', $args);
    }
    
    /**
     * Ajouter les metaboxes
     */
    public function add_meta_boxes() {
        // Metabox pour lier un POI à une implantations
        add_meta_box(
            'poi_implantations_relation',
            __('Implantations associée', 'terralize'),
            array($this, 'render_poi_implantations_metabox'),
            'poi',
            'side',
            'default'
        );
        
        // Metabox pour afficher les POI liés à une implantations
        add_meta_box(
            'implantations_poi_list',
            __('Points de vente associés', 'terralize'),
            array($this, 'render_implantations_poi_metabox'),
            'implantations',
            'normal',
            'default'
        );
    }
    
    /**
     * Afficher la metabox de relation POI-Implantations
     */
    public function render_poi_implantations_metabox($post) {
        // Nonce pour la sécurité
        wp_nonce_field('poi_implantations_relation_nonce', 'poi_implantations_relation_nonce_field');
        
        // Récupérer l'implantations liée actuelle
        $implantations_id = get_post_meta($post->ID, '_poi_implantations_id', true);
        
        // Récupérer toutes les implantationss
        $implantationss = get_posts(array(
            'post_type' => 'implantations',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <p>
            <label for="poi_implantations_id"><?php _e('Sélectionner une implantations :', 'terralize'); ?></label>
            <select name="poi_implantations_id" id="poi_implantations_id" style="width: 100%;">
                <option value=""><?php _e('-- Aucune implantations --', 'terralize'); ?></option>
                <?php foreach ($implantationss as $implantations) : ?>
                    <option value="<?php echo esc_attr($implantations->ID); ?>" <?php selected($implantations_id, $implantations->ID); ?>>
                        <?php echo esc_html($implantations->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p class="description">
            <?php _e('Si une implantations est sélectionnée, le bouton "Contacter" du POI redirigera vers la page de l\'implantations.', 'terralize'); ?>
        </p>
        <?php
    }
    
    /**
     * Afficher la metabox des POI liés à une implantations
     */
    public function render_implantations_poi_metabox($post) {
        // Récupérer tous les POI liés à cette implantations
        $linked_pois = get_posts(array(
            'post_type' => 'poi',
            'posts_per_page' => -1,
            'meta_key' => '_poi_implantations_id',
            'meta_value' => $post->ID,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        if (empty($linked_pois)) {
            echo '<p>' . __('Aucun point de vente associé à cette implantations.', 'terralize') . '</p>';
        } else {
            echo '<ul>';
            foreach ($linked_pois as $poi) {
                echo '<li>';
                echo '<a href="' . get_edit_post_link($poi->ID) . '">' . esc_html($poi->post_title) . '</a>';
                echo ' - <a href="' . get_permalink($poi->ID) . '" target="_blank">' . __('Voir', 'terralize') . '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
    }
    
    /**
     * Sauvegarder la relation POI-Implantations
     */
    public function save_poi_implantations_relation($post_id) {
        // Vérifications de sécurité
        if (!isset($_POST['poi_implantations_relation_nonce_field'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['poi_implantations_relation_nonce_field'], 'poi_implantations_relation_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Sauvegarder la relation
        if (isset($_POST['poi_implantations_id'])) {
            $implantations_id = sanitize_text_field($_POST['poi_implantations_id']);
            if (empty($implantations_id)) {
                delete_post_meta($post_id, '_poi_implantations_id');
            } else {
                update_post_meta($post_id, '_poi_implantations_id', $implantations_id);
            }
        }
    }
    
    /**
     * Ajouter les données de l'implantations aux données du POI
     */
    public function add_implantations_data_to_poi($poi_data, $poi_id) {
        $implantations_id = get_post_meta($poi_id, '_poi_implantations_id', true);
        
        if ($implantations_id) {
            $implantations = get_post($implantations_id);
            if ($implantations && $implantations->post_status === 'publish') {
                $poi_data['implantation_id'] = $implantations_id;
                $poi_data['implantation_title'] = $implantations->post_title;
                // Utiliser get_permalink avec le site_url pour les multisites
                $implantation_url = get_permalink($implantations_id);
                // S'assurer que l'URL est complète pour les multisites
                if (is_multisite() && !is_main_site()) {
                    $site_url = get_site_url();
                    $home_url = home_url();
                    // Si l'URL générée ne contient pas le chemin du site, l'ajouter
                    if (strpos($implantation_url, $home_url) === false) {
                        $implantation_url = $home_url . '/' . ltrim(str_replace(get_option('siteurl'), '', $implantation_url), '/');
                    }
                }
                $poi_data['implantation_url'] = $implantation_url;
                
                // Ajouter le logo de l'implantation si disponible
                $logo_id = get_post_thumbnail_id($implantations_id);
                if ($logo_id) {
                    $poi_data['implantation_logo'] = wp_get_attachment_image_url($logo_id, 'medium');
                }
            }
        }
        
        return $poi_data;
    }
    
    /**
     * Ajouter le menu d'administration
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=zone',
            __('Module GFG', 'terralize'),
            __('Module GFG', 'terralize'),
            'manage_options',
            'terralize-gfg',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Afficher la page d'administration
     */
    public function render_admin_page() {
        // Traiter l'activation/désactivation du module
        if (isset($_POST['terralize_gfg_submit']) && wp_verify_nonce($_POST['terralize_gfg_nonce'], 'terralize_gfg_settings')) {
            $enabled = isset($_POST['terralize_gfg_enabled']) ? 1 : 0;
            update_option('terralize_gfg_enabled', $enabled);
            
            echo '<div class="notice notice-success"><p>' . __('Paramètres sauvegardés.', 'terralize') . '</p></div>';
        }
        
        $is_enabled = get_option('terralize_gfg_enabled', false);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('terralize_gfg_settings', 'terralize_gfg_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Activer le module GFG', 'terralize'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="terralize_gfg_enabled" value="1" <?php checked($is_enabled, 1); ?> />
                                <?php _e('Activer la gestion des implantationss', 'terralize'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Active la possibilité de lier les points de vente (POI) à des implantationss.', 'terralize'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Enregistrer les paramètres', 'terralize'), 'primary', 'terralize_gfg_submit'); ?>
            </form>
            
            <?php if ($is_enabled) : ?>
            <hr>
            <h2><?php _e('Statistiques', 'terralize'); ?></h2>
            <?php
            $total_implantationss = wp_count_posts('implantations')->publish;
            $total_pois = wp_count_posts('poi')->publish;
            $linked_pois = get_posts(array(
                'post_type' => 'poi',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_poi_implantations_id',
                        'compare' => 'EXISTS'
                    )
                )
            ));
            $linked_count = count($linked_pois);
            ?>
            <ul>
                <li><?php printf(__('Nombre total d\'implantationss : %d', 'terralize'), $total_implantationss); ?></li>
                <li><?php printf(__('Nombre total de POI : %d', 'terralize'), $total_pois); ?></li>
                <li><?php printf(__('POI liés à une implantations : %d', 'terralize'), $linked_count); ?></li>
            </ul>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Enqueue les scripts admin
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if ($post_type === 'poi' || $post_type === 'implantations') {
            // Ajouter Select2 pour une meilleure sélection
            wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
            wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'));
            
            // Script personnalisé
            wp_add_inline_script('select2', '
                jQuery(document).ready(function($) {
                    $("#poi_implantations_id").select2({
                        placeholder: "-- Aucune implantations --",
                        allowClear: true
                    });
                });
            ');
        }
    }
}

// Initialiser le module
add_action('plugins_loaded', function() {
    Terralize_GFG::get_instance();
}); 