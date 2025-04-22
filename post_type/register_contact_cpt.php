<?php
function terralize_register_contact_cpt() {
    $labels = array(
        'name'               => _x( 'Contacts', 'post type general name', 'terralize' ),
        'singular_name'      => _x( 'Contact', 'post type singular name', 'terralize' ),
        'menu_name'          => _x( 'Contacts', 'admin menu', 'terralize' ),
        'name_admin_bar'     => _x( 'Contact', 'add new on admin bar', 'terralize' ),
        'add_new'            => _x( 'Ajouter', 'contact', 'terralize' ),
        'add_new_item'       => __( 'Ajouter un contact', 'terralize' ),
        'new_item'           => __( 'Nouveau contact', 'terralize' ),
        'edit_item'          => __( 'Éditer le contact', 'terralize' ),
        'view_item'          => __( 'Voir le contact', 'terralize' ),
        'all_items'          => __( 'Tous les contacts', 'terralize' ),
        'search_items'       => __( 'Rechercher des contacts', 'terralize' ),
        'not_found'          => __( 'Aucun contact trouvé.', 'terralize' ),
        'not_found_in_trash' => __( 'Aucun contact dans la corbeille.', 'terralize' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false, // le CPT n'est pas accessible publiquement (uniquement en back-office)
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => false,
        'rewrite'            => array( 'slug' => 'terralize_contact' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'supports'           => array( 'title', 'editor' ),
    );

    register_post_type( 'terralize_contact', $args );
}
add_action( 'init', 'terralize_register_contact_cpt' );

// Modification des colonnes affichées dans la liste des contacts
function terralize_edit_contact_columns($columns) {
    $columns = array(
        'cb'              => '<input type="checkbox" />',
        'title'           => __( 'Sujet', 'terralize' ),
        'contact_name'    => __( 'Nom', 'terralize' ),
        'contact_email'   => __( 'Email', 'terralize' ),
        'commercial'      => __( 'Commercial', 'terralize' ),
        'date'            => __( 'Date', 'terralize' ),
    );
    return $columns;
}
add_filter('manage_terralize_contact_posts_columns', 'terralize_edit_contact_columns');

// Remplissage des colonnes personnalisées
function terralize_custom_contact_columns($column, $post_id) {
    switch ($column) {
        case 'contact_name':
            echo esc_html( get_post_meta($post_id, 'terralize_contact_name', true) );
            break;
        case 'contact_email':
            echo esc_html( get_post_meta($post_id, 'terralize_contact_email', true) );
            break;
        case 'commercial':
            $commercial_id = get_post_meta($post_id, 'terralize_commercial_id', true);
            if ( $commercial_id ) {
                $commercial_post = get_post($commercial_id);
                echo $commercial_post ? esc_html($commercial_post->post_title) : '';
            }
            break;
    }
}
add_action('manage_terralize_contact_posts_custom_column', 'terralize_custom_contact_columns', 10, 2);
