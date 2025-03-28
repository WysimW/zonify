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

function zonify_register_poi_taxonomy() {
    $labels = array(
        'name'              => 'Catégories POI',
        'singular_name'     => 'Catégorie POI',
        'search_items'      => 'Rechercher des catégories',
        'all_items'         => 'Toutes les catégories',
        'parent_item'       => 'Catégorie parente',
        'parent_item_colon' => 'Catégorie parente:',
        'edit_item'         => 'Modifier la catégorie',
        'update_item'       => 'Mettre à jour la catégorie',
        'add_new_item'      => 'Ajouter une nouvelle catégorie',
        'new_item_name'     => 'Nom de la nouvelle catégorie',
        'menu_name'         => 'Catégories POI',
    );
    $args = array(
        'hierarchical'      => true, // structure en arborescence (comme les catégories)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'poi-category'),
    );
    register_taxonomy('poi_category', 'poi', $args);
}
add_action('init', 'zonify_register_poi_taxonomy');

function poi_add_meta_boxes() {
    add_meta_box(
        'poi_geojson_box',
        'Géométrie (GeoJSON)',
        'poi_geojson_meta_box_callback',
        'poi',
        'normal',
        'high'
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

function poi_save_geojson($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    if (isset($_POST['poi_geojson'])) {
        update_post_meta($post_id, 'poi_geojson', wp_kses_post($_POST['poi_geojson']));
    }
}
add_action('save_post_poi', 'poi_save_geojson');
