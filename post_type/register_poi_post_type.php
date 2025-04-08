<?php
function zonify_register_poi_post_type() {
    $labels = array(
        'name'               => 'Points d’intérêt',
        'singular_name'      => 'Point d’intérêt',
        'menu_name'          => 'Points d’intérêt',
        'name_admin_bar'     => 'Point d’intérêt',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter un nouveau point d’intérêt',
        'new_item'           => 'Nouveau point d’intérêt',
        'edit_item'          => 'Modifier le point d’intérêt',
        'view_item'          => 'Voir le point d’intérêt',
        'all_items'          => 'Tous les points d’intérêt',
        'search_items'       => 'Rechercher des points d’intérêt',
        'not_found'          => 'Aucun point d’intérêt trouvé',
        'not_found_in_trash' => 'Aucun point d’intérêt dans la corbeille',
    );
    $args = array(
        'labels'             => $labels,
        'public'             => false, // on le gère en back-office
        'show_ui'            => true,
        'show_in_menu'       => false, // intégration dans le menu Zonify
        'supports'           => array('title', 'revisions'),
        'has_archive'        => false,
        'rewrite'            => array('slug' => 'poi'),
    );
    register_post_type('poi', $args);
}
add_action('init', 'zonify_register_poi_post_type');

// Remplacer l'ancienne fonction par la nouvelle taxonomie partagée
function zonify_register_shared_taxonomy() {
    $labels = array(
        'name'              => 'Catégories',
        'singular_name'     => 'Catégorie',
        'search_items'      => 'Rechercher des catégories',
        'all_items'         => 'Toutes les catégories',
        'parent_item'       => 'Catégorie parente',
        'parent_item_colon' => 'Catégorie parente:',
        'edit_item'         => 'Modifier la catégorie',
        'update_item'       => 'Mettre à jour la catégorie',
        'add_new_item'      => 'Ajouter une nouvelle catégorie',
        'new_item_name'     => 'Nom de la nouvelle catégorie',
        'menu_name'         => 'Catégories',
    );
    $args = array(
        'hierarchical'      => true, // structure en arborescence (comme les catégories)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'zonify-category'),
    );
    // Associer la taxonomie aux deux CPTs
    register_taxonomy('zonify_category', array('poi', 'zone'), $args);
}
// Supprimer l'ancienne action et ajouter la nouvelle
add_action('init', 'zonify_register_shared_taxonomy');

/**
 * Enregistre la taxonomie 'region' partagée entre les zones et les poi
 */
function zonify_register_region_taxonomy() {
    $labels = array(
        'name'              => 'Régions',
        'singular_name'     => 'Région',
        'search_items'      => 'Rechercher des régions',
        'all_items'         => 'Toutes les régions',
        'parent_item'       => 'Région parente',
        'parent_item_colon' => 'Région parente:',
        'edit_item'         => 'Modifier la région',
        'update_item'       => 'Mettre à jour la région',
        'add_new_item'      => 'Ajouter une nouvelle région',
        'new_item_name'     => 'Nom de la nouvelle région',
        'menu_name'         => 'Régions',
    );
    
    $args = array(
        'hierarchical'      => true, // structure en arborescence comme les catégories
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'region'),
    );
    
    // Associer la taxonomie aux deux CPTs
    register_taxonomy('region', array('poi', 'zone'), $args);
}

// Enregistrer la taxonomie région
add_action('init', 'zonify_register_region_taxonomy');

function poi_add_meta_boxes() {
    add_meta_box(
        'poi_geojson_box',
        'Géométrie (GeoJSON)',
        'poi_geojson_meta_box_callback',
        'poi',
        'normal',
        'high'
    );
    
    // Ajouter une meta box pour la sélection d'icône
    add_meta_box(
        'poi_icon_box',
        'Icône personnalisée',
        'poi_icon_meta_box_callback',
        'poi',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'poi_add_meta_boxes');

function poi_geojson_meta_box_callback($post) {
    $poi_geojson = get_post_meta($post->ID, 'poi_geojson', true);
    ?>
    <textarea name="poi_geojson" style="width:100%;height:150px;"><?php echo esc_textarea($poi_geojson); ?></textarea>
    <p>Collez ici la géométrie en GeoJSON (format Point).</p>
    <?php
}

// Callback pour la meta box d'icône
function poi_icon_meta_box_callback($post) {
    // Récupérer l'ID de l'icône sélectionnée (s'il y en a une)
    $selected_icon_id = get_post_meta($post->ID, 'poi_icon_id', true);
    
    // Récupérer toutes les icônes disponibles
    $icons = get_posts(array(
        'post_type' => 'zonify_icon',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    // Si des icônes sont disponibles, afficher le sélecteur
    if (!empty($icons)) {
        echo '<label for="poi_icon_id">Sélectionnez une icône :</label><br>';
        echo '<select id="poi_icon_id" name="poi_icon_id" style="width:100%">';
        echo '<option value="">-- Icône par défaut --</option>';
        
        foreach ($icons as $icon) {
            $selected = selected($selected_icon_id, $icon->ID, false);
            echo '<option value="' . esc_attr($icon->ID) . '" ' . $selected . '>' . esc_html($icon->post_title) . '</option>';
        }
        
        echo '</select>';
        
        // Afficher l'aperçu de l'icône sélectionnée
        if ($selected_icon_id && has_post_thumbnail($selected_icon_id)) {
            echo '<div style="margin-top:10px;"><strong>Aperçu :</strong><br>';
            echo get_the_post_thumbnail($selected_icon_id, array(32, 32));
            echo '</div>';
        }
    } else {
        echo '<p>Aucune icône disponible. <a href="' . admin_url('post-new.php?post_type=zonify_icon') . '">Créez une icône</a>.</p>';
    }
    
    // Ajouter un nonce pour la sécurité
    wp_nonce_field('poi_icon_nonce', 'poi_icon_nonce');
}

function poi_save_geojson($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    if (isset($_POST['poi_geojson'])) {
        update_post_meta($post_id, 'poi_geojson', wp_kses_post($_POST['poi_geojson']));
    }
    
    // Sauvegarder l'ID de l'icône sélectionnée
    if (isset($_POST['poi_icon_nonce']) && wp_verify_nonce($_POST['poi_icon_nonce'], 'poi_icon_nonce')) {
        if (isset($_POST['poi_icon_id'])) {
            update_post_meta($post_id, 'poi_icon_id', sanitize_text_field($_POST['poi_icon_id']));
        }
    }
}
add_action('save_post_poi', 'poi_save_geojson');
