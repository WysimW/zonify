<?php
function zonify_register_commercial_post_type() {
    $labels = array(
        'name'                  => 'Commerciaux',
        'singular_name'         => 'Commercial',
        'menu_name'             => 'Commerciaux',
        'name_admin_bar'        => 'Commercial',
        'add_new'               => 'Ajouter',
        'add_new_item'          => 'Ajouter un nouveau commercial',
        'new_item'              => 'Nouveau commercial',
        'edit_item'             => 'Éditer le commercial',
        'view_item'             => 'Voir le commercial',
        'all_items'             => 'Tous les commerciaux',
        'search_items'          => 'Rechercher des commerciaux',
        'not_found'             => 'Aucun commercial trouvé',
        'not_found_in_trash'    => 'Aucun commercial dans la corbeille'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false, // Masquer le menu par défaut
        'query_var'          => true,
        'rewrite'            => array('slug' => 'commercial'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title')
    );

    register_post_type('commercial', $args);
}
add_action('init', 'zonify_register_commercial_post_type');

function zonify_meta_box_callback( $post ) {
    // Récupérer les valeurs enregistrées si elles existent
    $email           = get_post_meta( $post->ID, 'commercial_email', true );
    $telephone       = get_post_meta( $post->ID, 'commercial_telephone', true );
    $address         = get_post_meta( $post->ID, 'commercial_address', true );
    $opening_hours   = get_post_meta( $post->ID, 'commercial_opening_hours', true );
    $social_links    = get_post_meta( $post->ID, 'commercial_social_links', true );
    ?>
    <label for="commercial_email">Email :</label>
    <input type="email" name="commercial_email" id="commercial_email" value="<?php echo esc_attr( $email ); ?>" class="widefat" />
    <br><br>
    <label for="commercial_telephone">Téléphone :</label>
    <input type="text" name="commercial_telephone" id="commercial_telephone" value="<?php echo esc_attr( $telephone ); ?>" class="widefat" />
    <br><br>
    <label for="commercial_address">Adresse :</label>
    <input type="text" name="commercial_address" id="commercial_address" value="<?php echo esc_attr( $address ); ?>" class="widefat" />
    <br><br>
    <label for="commercial_opening_hours">Horaires d'ouverture :</label>
    <input type="text" name="commercial_opening_hours" id="commercial_opening_hours" value="<?php echo esc_attr( $opening_hours ); ?>" class="widefat" />
    <br><br>
    <label for="commercial_social_links">Liens sociaux (séparés par une virgule) :</label>
    <input type="text" name="commercial_social_links" id="commercial_social_links" value="<?php echo esc_attr( $social_links ); ?>" class="widefat" />
    <?php
}


function zonify_save_meta_box( $post_id ) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can('edit_post', $post_id) ) return;
    
    if ( isset( $_POST['commercial_email'] ) ) {
        update_post_meta( $post_id, 'commercial_email', sanitize_email( $_POST['commercial_email'] ) );
    }
    if ( isset( $_POST['commercial_telephone'] ) ) {
        update_post_meta( $post_id, 'commercial_telephone', sanitize_text_field( $_POST['commercial_telephone'] ) );
    }
    if ( isset( $_POST['commercial_address'] ) ) {
        update_post_meta( $post_id, 'commercial_address', sanitize_text_field( $_POST['commercial_address'] ) );
    }
    if ( isset( $_POST['commercial_opening_hours'] ) ) {
        update_post_meta( $post_id, 'commercial_opening_hours', sanitize_text_field( $_POST['commercial_opening_hours'] ) );
    }
    if ( isset( $_POST['commercial_social_links'] ) ) {
        update_post_meta( $post_id, 'commercial_social_links', sanitize_text_field( $_POST['commercial_social_links'] ) );
    }
}
add_action( 'save_post', 'zonify_save_meta_box' );
