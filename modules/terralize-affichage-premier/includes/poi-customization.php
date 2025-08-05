<?php
/**
 * Personnalisation des Points d'Intérêt (POI) pour Affichage Premier
 * Renomme les POI en "Panneaux d'affichage" et ajuste l'interface d'administration
 */

// Si ce fichier est appelé directement, abandon
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renommer les Points d'Intérêt en Panneaux d'affichage
 */
function terralize_ap_customize_poi_labels($labels) {
    $new_labels = array(
        'name'               => 'Panneaux d\'affichage',
        'singular_name'      => 'Panneau d\'affichage',
        'menu_name'          => 'Panneaux d\'affichage',
        'name_admin_bar'     => 'Panneau d\'affichage',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter un nouveau panneau',
        'new_item'           => 'Nouveau panneau',
        'edit_item'          => 'Modifier le panneau',
        'view_item'          => 'Voir le panneau',
        'all_items'          => 'Tous les panneaux',
        'search_items'       => 'Rechercher des panneaux',
        'not_found'          => 'Aucun panneau trouvé',
        'not_found_in_trash' => 'Aucun panneau dans la corbeille',
    );
    
    return $new_labels;
}
add_filter('register_post_type_args', 'terralize_ap_modify_poi_post_type', 10, 2);

/**
 * Modifie les arguments du type de publication POI
 */
function terralize_ap_modify_poi_post_type($args, $post_type) {
    if ($post_type === 'poi') {
        // Modifier les étiquettes
        $args['labels'] = terralize_ap_customize_poi_labels($args['labels']);
        
        // Modifier l'icône dans le menu
        $args['menu_icon'] = 'dashicons-format-image';
        
        // Activer l'affichage public et l'archive pour permettre 
        // une éventuelle présentation publique des panneaux d'affichage
        $args['public'] = true;
        $args['has_archive'] = true;
        $args['rewrite'] = array('slug' => 'panneaux-affichage');
    }
    
    return $args;
}

/**
 * Modifier le titre des colonnes dans la liste des POI
 */
function terralize_ap_modify_poi_admin_columns($columns) {
    // Renommer la colonne "title" en "Panneau"
    if (isset($columns['title'])) {
        $columns['title'] = 'Panneau';
    }
    
    return $columns;
}
add_filter('manage_poi_posts_columns', 'terralize_ap_modify_poi_admin_columns');

/**
 * Ajouter des filtres spécifiques pour les panneaux d'affichage
 */
function terralize_ap_add_poi_admin_filters() {
    global $typenow;
    
    if ($typenow === 'poi') {
        // Filtre par ville
        $cities = get_terms(array(
            'taxonomy' => 'city',
            'hide_empty' => true,
        ));
        
        if (!empty($cities) && !is_wp_error($cities)) {
            echo '<select name="city" id="city" class="postform">';
            echo '<option value="">Toutes les villes</option>';
            
            foreach ($cities as $city) {
                $selected = isset($_GET['city']) && $_GET['city'] === $city->slug ? ' selected="selected"' : '';
                echo '<option value="' . esc_attr($city->slug) . '"' . $selected . '>' . esc_html($city->name) . '</option>';
            }
            
            echo '</select>';
        }
        
        // Filtre par type de panneau (utilise des méta-données)
        $panel_types = array(
            '' => 'Tous les types',
            'mural' => 'Mural',
            'pre-implantations' => 'Pré-implantations',
            '4x3' => '4x3',
            '8x3' => '8x3',
            'déroulant' => 'Déroulant',
            'totem' => 'Totem'
        );
        
        echo '<select name="panel_type" id="panel_type">';
        foreach ($panel_types as $value => $label) {
            $selected = isset($_GET['panel_type']) && $_GET['panel_type'] === $value ? ' selected="selected"' : '';
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'terralize_ap_add_poi_admin_filters');

/**
 * Modifier la requête de liste des POI pour prendre en compte les filtres personnalisés
 */
function terralize_ap_filter_poi_admin_list($query) {
    global $pagenow, $typenow;
    
    if (is_admin() && $pagenow === 'edit.php' && $typenow === 'poi' && $query->is_main_query()) {
        // Filtre par type de panneau
        if (!empty($_GET['panel_type'])) {
            $query->set('meta_key', 'panel_type');
            $query->set('meta_value', sanitize_text_field($_GET['panel_type']));
        }
    }
}
add_action('pre_get_posts', 'terralize_ap_filter_poi_admin_list');

/**
 * Ajouter taxonomie pour les villes des panneaux
 */
function terralize_ap_register_city_taxonomy() {
    $labels = array(
        'name'              => 'Villes',
        'singular_name'     => 'Ville',
        'search_items'      => 'Rechercher des villes',
        'all_items'         => 'Toutes les villes',
        'parent_item'       => 'Ville parente',
        'parent_item_colon' => 'Ville parente:',
        'edit_item'         => 'Modifier la ville',
        'update_item'       => 'Mettre à jour la ville',
        'add_new_item'      => 'Ajouter une nouvelle ville',
        'new_item_name'     => 'Nom de la nouvelle ville',
        'menu_name'         => 'Villes',
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'ville'),
    );
    
    register_taxonomy('city', array('poi'), $args);
}
add_action('init', 'terralize_ap_register_city_taxonomy');

/**
 * Changer le texte "Enregistrer" du bouton dans l'éditeur
 */
function terralize_ap_change_publish_button($translation, $text) {
    global $post_type;
    
    if ($post_type === 'poi') {
        if ($text === 'Publier') {
            return 'Enregistrer le panneau';
        } elseif ($text === 'Mettre à jour') {
            return 'Mettre à jour le panneau';
        }
    }
    
    return $translation;
}
add_filter('gettext', 'terralize_ap_change_publish_button', 10, 2);