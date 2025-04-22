<?php
/**
 * Custom Post Type pour les icônes de POI
 */
function terralize_register_icon_post_type() {
    $labels = array(
        'name'               => 'Icônes POI',
        'singular_name'      => 'Icône POI',
        'menu_name'          => 'Icônes POI',
        'name_admin_bar'     => 'Icône POI',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter une nouvelle icône',
        'new_item'           => 'Nouvelle icône',
        'edit_item'          => 'Modifier l\'icône',
        'view_item'          => 'Voir l\'icône',
        'all_items'          => 'Toutes les icônes',
        'search_items'       => 'Rechercher des icônes',
        'not_found'          => 'Aucune icône trouvée',
        'not_found_in_trash' => 'Aucune icône dans la corbeille',
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => false, // Pas besoin d'être accessible publiquement
        'show_ui'            => true,
        'show_in_menu'       => false, // Sera intégré dans le menu Terralize
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'supports'           => array('title', 'thumbnail'), // Support des images mises en avant (thumbnail)
        'has_archive'        => false,
        'menu_icon'          => 'dashicons-marker',
    );
    
    register_post_type('terralize_icon', $args);
}
add_action('init', 'terralize_register_icon_post_type');

/**
 * Ajouter une méta box pour les paramètres de l'icône
 */
function terralize_icon_add_meta_box() {
    add_meta_box(
        'terralize_icon_settings',
        'Paramètres de l\'icône',
        'terralize_icon_settings_callback',
        'terralize_icon',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'terralize_icon_add_meta_box');

/**
 * Callback pour la méta box des paramètres d'icône
 */
function terralize_icon_settings_callback($post) {
    // Récupérer les valeurs existantes
    $icon_width = get_post_meta($post->ID, 'icon_width', true) ?: '32';
    $icon_height = get_post_meta($post->ID, 'icon_height', true) ?: '32';
    $icon_anchor_x = get_post_meta($post->ID, 'icon_anchor_x', true) ?: '16';
    $icon_anchor_y = get_post_meta($post->ID, 'icon_anchor_y', true) ?: '32';
    
    // Nonce pour la sécurité
    wp_nonce_field('terralize_icon_settings_nonce', 'terralize_icon_settings_nonce');
    
    // Afficher les champs
    ?>
    <p>
        <strong><?php _e('Note:', 'terralize'); ?></strong> 
        <?php _e('Veuillez définir une image mise en avant pour cette icône en utilisant le panneau "Image mise en avant".', 'terralize'); ?>
    </p>
    
    <table class="form-table">
        <tr>
            <th><label for="icon_width"><?php _e('Largeur (px)', 'terralize'); ?></label></th>
            <td>
                <input type="number" id="icon_width" name="icon_width" value="<?php echo esc_attr($icon_width); ?>" min="1" />
            </td>
        </tr>
        <tr>
            <th><label for="icon_height"><?php _e('Hauteur (px)', 'terralize'); ?></label></th>
            <td>
                <input type="number" id="icon_height" name="icon_height" value="<?php echo esc_attr($icon_height); ?>" min="1" />
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Point d\'ancrage', 'terralize'); ?></label></th>
            <td>
                <p><?php _e('Point d\'ancrage de l\'icône (en pixels depuis le coin supérieur gauche):', 'terralize'); ?></p>
                X: <input type="number" name="icon_anchor_x" value="<?php echo esc_attr($icon_anchor_x); ?>" style="width:60px">
                Y: <input type="number" name="icon_anchor_y" value="<?php echo esc_attr($icon_anchor_y); ?>" style="width:60px">
                <p class="description"><?php _e('Généralement, X = largeur/2 et Y = hauteur (pour que la pointe de l\'icône soit sur le point exact).', 'terralize'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Sauvegarder les paramètres de l'icône
 */
function terralize_save_icon_settings($post_id) {
    // Vérifier le nonce
    if (!isset($_POST['terralize_icon_settings_nonce']) || !wp_verify_nonce($_POST['terralize_icon_settings_nonce'], 'terralize_icon_settings_nonce')) {
        return;
    }
    
    // Vérifier si c'est une sauvegarde automatique
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Vérifier les permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Sauvegarder les données
    if (isset($_POST['icon_width'])) {
        update_post_meta($post_id, 'icon_width', sanitize_text_field($_POST['icon_width']));
    }
    
    if (isset($_POST['icon_height'])) {
        update_post_meta($post_id, 'icon_height', sanitize_text_field($_POST['icon_height']));
    }
    
    if (isset($_POST['icon_anchor_x'])) {
        update_post_meta($post_id, 'icon_anchor_x', sanitize_text_field($_POST['icon_anchor_x']));
    }
    
    if (isset($_POST['icon_anchor_y'])) {
        update_post_meta($post_id, 'icon_anchor_y', sanitize_text_field($_POST['icon_anchor_y']));
    }
}
add_action('save_post_terralize_icon', 'terralize_save_icon_settings');

/**
 * Ajouter la colonne d'aperçu dans la liste des icônes
 */
function terralize_icon_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        // Ajouter la colonne d'aperçu après le titre
        if ($key === 'title') {
            $new_columns['preview'] = 'Aperçu';
        }
    }
    return $new_columns;
}
add_filter('manage_terralize_icon_posts_columns', 'terralize_icon_columns');

/**
 * Afficher l'aperçu de l'icône dans la colonne
 */
function terralize_icon_column_content($column, $post_id) {
    if ($column === 'preview') {
        if (has_post_thumbnail($post_id)) {
            echo get_the_post_thumbnail($post_id, array(32, 32));
        } else {
            echo 'Aucune image';
        }
    }
}
add_action('manage_terralize_icon_posts_custom_column', 'terralize_icon_column_content', 10, 2);