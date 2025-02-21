<?php
function register_commercial_post_type() {
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
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'commercial'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'thumbnail')
    );

    register_post_type('commercial', $args);
}
add_action('init', 'register_commercial_post_type');

function zones_commerciales_meta_box_callback( $post ) {
    // Récupérer les valeurs enregistrées si elles existent
    $email = get_post_meta( $post->ID, 'commercial_email', true );
    $telephone = get_post_meta( $post->ID, 'commercial_telephone', true );
    ?>
    <label for="commercial_email">Email :</label>
    <input type="email" name="commercial_email" id="commercial_email" value="<?php echo esc_attr( $email ); ?>" class="widefat" />
    <br>
    <label for="commercial_telephone">Téléphone :</label>
    <input type="text" name="commercial_telephone" id="commercial_telephone" value="<?php echo esc_attr( $telephone ); ?>" class="widefat" />
    <?php
}

function zones_commerciales_save_meta_box( $post_id ) {
    // Vérifier les permissions et la validité du nonce si vous en utilisez un
    if ( isset( $_POST['commercial_email'] ) ) {
        update_post_meta( $post_id, 'commercial_email', sanitize_email( $_POST['commercial_email'] ) );
    }
    if ( isset( $_POST['commercial_telephone'] ) ) {
        update_post_meta( $post_id, 'commercial_telephone', sanitize_text_field( $_POST['commercial_telephone'] ) );
    }
}
add_action( 'save_post', 'zones_commerciales_save_meta_box' );