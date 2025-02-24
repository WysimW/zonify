<?php
function register_zone_post_type() {
    $labels = array(
        'name'               => 'Zones',
        'singular_name'      => 'Zone',
        'menu_name'          => 'Zones',
        'name_admin_bar'     => 'Zone',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter une nouvelle zone',
        'new_item'           => 'Nouvelle zone',
        'edit_item'          => 'Modifier la zone',
        'view_item'          => 'Voir la zone',
        'all_items'          => 'Toutes les zones',
        'search_items'       => 'Rechercher des zones',
        'not_found'          => 'Aucune zone trouvée',
        'not_found_in_trash' => 'Aucune zone dans la corbeille',
    );
    $args = array(
        'labels'             => $labels,
        'public'             => false,               // vous pouvez mettre true si vous voulez rendre le CPT accessible publiquement
        'show_ui'            => true,                // afficher dans l’admin
        'show_in_menu'       => false,               // on masque le menu par défaut, pour l'intégrer plus tard dans votre menu Zonify
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'supports'           => array('title', 'revisions'), 
        'has_archive'        => false,
        'rewrite'            => array('slug' => 'zone'),
    );
    register_post_type('zone', $args);
}
add_action('init', 'register_zone_post_type');
function zone_add_meta_box() {
    add_meta_box(
        'zone_commercial_id',
        'Commercial associé',
        'zone_meta_box_callback',
        'zone',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'zone_add_meta_box');

function zone_meta_box_callback($post) {
    // Récupérer la valeur stockée
    $commercial_id = get_post_meta($post->ID, 'zone_commercial_id', true);
    
    // Lister tous les commerciaux
    $args = array(
        'post_type' => 'commercial',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);
    
    echo '<select name="zone_commercial_id" id="zone_commercial_id">';
    echo '<option value="">-- Sélectionnez un commercial --</option>';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $selected = ($commercial_id == get_the_ID()) ? 'selected' : '';
            echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
        }
        wp_reset_postdata();
    }
    echo '</select>';
}

function zone_save_meta_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    if (isset($_POST['zone_commercial_id'])) {
        update_post_meta($post_id, 'zone_commercial_id', sanitize_text_field($_POST['zone_commercial_id']));
    }
}
add_action('save_post_zone', 'zone_save_meta_box');
function zone_add_geojson_meta_box() {
    add_meta_box(
        'zone_geojson_box',
        'Géométrie (GeoJSON)',
        'zone_geojson_meta_box_callback',
        'zone',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'zone_add_geojson_meta_box');

function zone_geojson_meta_box_callback($post) {
    $zone_geojson = get_post_meta($post->ID, 'zone_geojson', true);
    ?>
    <textarea name="zone_geojson" style="width:100%;height:150px;"><?php echo esc_textarea($zone_geojson); ?></textarea>
    <p>Saisissez (ou collez) la géométrie en GeoJSON.</p>
    <?php
}

function zone_save_geojson($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    if (isset($_POST['zone_geojson'])) {
        update_post_meta($post_id, 'zone_geojson', wp_kses_post($_POST['zone_geojson']));
    }
}
add_action('save_post_zone', 'zone_save_geojson');
