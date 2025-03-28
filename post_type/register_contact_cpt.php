<?php
function zonify_register_contact_cpt() {
    $labels = array(
        'name'               => _x( 'Contacts', 'post type general name', 'zonify' ),
        'singular_name'      => _x( 'Contact', 'post type singular name', 'zonify' ),
        'menu_name'          => _x( 'Contacts', 'admin menu', 'zonify' ),
        'name_admin_bar'     => _x( 'Contact', 'add new on admin bar', 'zonify' ),
        'add_new'            => _x( 'Ajouter', 'contact', 'zonify' ),
        'add_new_item'       => __( 'Ajouter un contact', 'zonify' ),
        'new_item'           => __( 'Nouveau contact', 'zonify' ),
        'edit_item'          => __( 'Éditer le contact', 'zonify' ),
        'view_item'          => __( 'Voir le contact', 'zonify' ),
        'all_items'          => __( 'Tous les contacts', 'zonify' ),
        'search_items'       => __( 'Rechercher des contacts', 'zonify' ),
        'not_found'          => __( 'Aucun contact trouvé.', 'zonify' ),
        'not_found_in_trash' => __( 'Aucun contact dans la corbeille.', 'zonify' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false, // le CPT n'est pas accessible publiquement (uniquement en back-office)
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => array( 'slug' => 'zonify_contact' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'supports'           => array( 'title', 'editor' ),
    );

    register_post_type( 'zonify_contact', $args );
}
add_action( 'init', 'zonify_register_contact_cpt' );

// Modification des colonnes affichées dans la liste des contacts
function zonify_edit_contact_columns($columns) {
    $columns = array(
        'cb'              => '<input type="checkbox" />',
        'title'           => __( 'Sujet', 'zonify' ),
        'contact_name'    => __( 'Nom', 'zonify' ),
        'contact_email'   => __( 'Email', 'zonify' ),
        'commercial'      => __( 'Commercial', 'zonify' ),
        'date'            => __( 'Date', 'zonify' ),
    );
    return $columns;
}
add_filter('manage_zonify_contact_posts_columns', 'zonify_edit_contact_columns');

// Remplissage des colonnes personnalisées
function zonify_custom_contact_columns($column, $post_id) {
    switch ($column) {
        case 'contact_name':
            echo esc_html( get_post_meta($post_id, 'zonify_contact_name', true) );
            break;
        case 'contact_email':
            echo esc_html( get_post_meta($post_id, 'zonify_contact_email', true) );
            break;
        case 'commercial':
            $commercial_id = get_post_meta($post_id, 'zonify_commercial_id', true);
            if ( $commercial_id ) {
                $commercial_post = get_post($commercial_id);
                echo $commercial_post ? esc_html($commercial_post->post_title) : '';
            }
            break;
    }
}
add_action('manage_zonify_contact_posts_custom_column', 'zonify_custom_contact_columns', 10, 2);
