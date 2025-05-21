<?php
function terralize_register_poi_post_type() {
    $labels = array(
        'name'               => 'Points d\'intérêt',
        'singular_name'      => 'Point d\'intérêt',
        'menu_name'          => 'Points d\'intérêt',
        'name_admin_bar'     => 'Point d\'intérêt',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter un nouveau point d\'intérêt',
        'new_item'           => 'Nouveau point d\'intérêt',
        'edit_item'          => 'Modifier le point d\'intérêt',
        'view_item'          => 'Voir le point d\'intérêt',
        'all_items'          => 'Tous les points d\'intérêt',
        'search_items'       => 'Rechercher des points d\'intérêt',
        'not_found'          => 'Aucun point d\'intérêt trouvé',
        'not_found_in_trash' => 'Aucun point d\'intérêt dans la corbeille',
    );
    $args = array(
        'labels'             => $labels,
        'public'             => false, // on le gère en back-office
        'show_ui'            => true,
        'show_in_menu'       => false, // intégration dans le menu Terralize
        'supports'           => array('title', 'revisions'),
        'has_archive'        => false,
        'rewrite'            => array('slug' => 'poi'),
    );
    register_post_type('poi', $args);
}
add_action('init', 'terralize_register_poi_post_type');

// Remplacer l'ancienne fonction par la nouvelle taxonomie partagée
function terralize_register_shared_taxonomy() {
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
        'rewrite'           => array('slug' => 'terralize-category'),
    );
    // Associer la taxonomie aux deux CPTs
    register_taxonomy('terralize_category', array('poi', 'zone'), $args);
}
// Supprimer l'ancienne action et ajouter la nouvelle
add_action('init', 'terralize_register_shared_taxonomy');

/**
 * Enregistre la taxonomie 'region' partagée entre les zones et les poi
 */
function terralize_register_region_taxonomy() {
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
add_action('init', 'terralize_register_region_taxonomy');

/**
 * Définition de la structure des métachamps par défaut pour les POI
 * Cette fonction est filtrable pour que d'autres extensions puissent modifier ces champs
 * 
 * @return array Tableau des métachamps disponibles pour les POI
 */
function terralize_get_poi_metafields() {
    // Récupérer les champs personnalisés depuis la base de données
    $saved_fields = get_option('terralize_poi_custom_fields', array());
    
    // Si des champs sont définis en base de données, les utiliser
    if (!empty($saved_fields)) {
        return apply_filters('terralize_poi_metafields', $saved_fields);
    }
    
    // Sinon, utiliser les champs par défaut
    $default_fields = array(
        'address' => array(
            'label' => 'Adresse',
            'type' => 'text',
            'description' => 'Adresse complète du point d\'intérêt',
            'required' => false,
            'default' => '',
            'priority' => 10,
            'display_zone' => 'location' // Zone d'affichage: header, info, contact, location
        ),
        'phone' => array(
            'label' => 'Téléphone',
            'type' => 'text',
            'description' => 'Numéro de téléphone de contact',
            'required' => false,
            'default' => '',
            'priority' => 20,
            'display_zone' => 'contact' // Zone d'affichage: contact
        ),
        'email' => array(
            'label' => 'Email',
            'type' => 'email',
            'description' => 'Adresse email de contact',
            'required' => false,
            'default' => '',
            'priority' => 30,
            'display_zone' => 'contact' // Zone d'affichage: contact
        ),
        'website' => array(
            'label' => 'Site web',
            'type' => 'url',
            'description' => 'URL du site web',
            'required' => false,
            'default' => '',
            'priority' => 40,
            'display_zone' => 'contact' // Zone d'affichage: contact
        ),
        'opening_hours' => array(
            'label' => 'Horaires d\'ouverture',
            'type' => 'textarea',
            'description' => 'Horaires d\'ouverture détaillés',
            'required' => false,
            'default' => '',
            'priority' => 50,
            'display_zone' => 'info' // Zone d'affichage: info
        ),
        'description' => array(
            'label' => 'Description',
            'type' => 'wysiwyg',
            'description' => 'Description détaillée du point d\'intérêt',
            'required' => false,
            'default' => '',
            'priority' => 60,
            'display_zone' => 'info' // Zone d'affichage: info
        )
    );
    
    // Filtre permettant de modifier les champs par défaut
    return apply_filters('terralize_poi_metafields', $default_fields);
}

/**
 * Enregistre les métaboxes pour les métachamps des POI
 */
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
    
    // Ajouter une meta box pour les champs dynamiques
    add_meta_box(
        'poi_custom_fields',
        'Informations du POI',
        'poi_custom_fields_meta_box_callback',
        'poi',
        'normal',
        'default'
    );
    
    // Permet à d'autres extensions d'ajouter leurs propres métaboxes
    do_action('terralize_poi_custom_metaboxes', 'poi');
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
        'post_type' => 'terralize_icon',
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
        echo '<p>Aucune icône disponible. <a href="' . admin_url('post-new.php?post_type=terralize_icon') . '">Créez une icône</a>.</p>';
    }
    
    // Ajouter un nonce pour la sécurité
    wp_nonce_field('poi_icon_nonce', 'poi_icon_nonce');
}

/**
 * Affiche les champs personnalisés dans la métabox
 */
function poi_custom_fields_meta_box_callback($post) {
    // Sécurité avec nonce
    wp_nonce_field('poi_custom_fields_nonce', 'poi_custom_fields_nonce');
    
    // Récupérer tous les champs définis
    $metafields = terralize_get_poi_metafields();
    
    // Trier les champs par priorité
    uasort($metafields, function($a, $b) {
        return $a['priority'] - $b['priority'];
    });
    
    echo '<div class="poi-custom-fields">';
    
    // Parcourir chaque champ et l'afficher
    foreach ($metafields as $field_key => $field) {
        $meta_key = 'poi_' . $field_key;
        $value = get_post_meta($post->ID, $meta_key, true);
        
        // Si pas de valeur, utiliser la valeur par défaut
        if (empty($value) && isset($field['default'])) {
            $value = $field['default'];
        }
        
        echo '<div class="poi-field-container" style="margin-bottom: 15px;">';
        echo '<label for="' . esc_attr($meta_key) . '"><strong>' . esc_html($field['label']) . '</strong>';
        
        if (!empty($field['required'])) {
            echo ' <span class="required">*</span>';
        }
        
        echo '</label>';
        
        // Afficher le champ selon son type
        switch ($field['type']) {
            case 'textarea':
                echo '<textarea id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" rows="4" style="width:100%;">' . esc_textarea($value) . '</textarea>';
                break;
                
            case 'wysiwyg':
                wp_editor($value, $meta_key, array(
                    'textarea_name' => $meta_key,
                    'textarea_rows' => 5,
                    'media_buttons' => true,
                    'teeny' => true
                ));
                break;
                
            case 'select':
                if (isset($field['options']) && is_array($field['options'])) {
                    echo '<select id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" style="width:100%;">';
                    foreach ($field['options'] as $option_value => $option_label) {
                        echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
                    }
                    echo '</select>';
                }
                break;
                
            case 'checkbox':
                echo '<input type="checkbox" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="1" ' . checked($value, '1', false) . ' />';
                break;
                
            case 'email':
                echo '<input type="email" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" style="width:100%;" />';
                break;
                
            case 'url':
                echo '<input type="url" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" style="width:100%;" />';
                break;
                
            case 'number':
                echo '<input type="number" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" style="width:100%;" />';
                break;
                
            case 'image':
                $image_id = intval($value);
                $image_url = '';
                if ($image_id > 0) {
                    $image_url = wp_get_attachment_image_url($image_id, 'medium');
                }
                ?>
                <div class="poi-image-field">
                    <input type="hidden" name="<?php echo esc_attr($meta_key); ?>" id="<?php echo esc_attr($meta_key); ?>" value="<?php echo esc_attr($value); ?>" />
                    
                    <div class="image-preview-wrapper" style="margin-bottom: 10px;">
                        <?php if (!empty($image_url)) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="" style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px; display: block;" />
                        <?php endif; ?>
                    </div>
                    
                    <input type="button" class="button upload-image-button" value="Choisir une image" 
                           data-field-id="<?php echo esc_attr($meta_key); ?>" />
                    
                    <?php if (!empty($image_url)) : ?>
                        <input type="button" class="button remove-image-button" value="Supprimer l'image"
                               data-field-id="<?php echo esc_attr($meta_key); ?>" />
                    <?php endif; ?>
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    // Si le media uploader n'est pas déjà initialisé
                    if (typeof wp.media === 'undefined') {
                        return;
                    }
                    
                    // Initialiser le media uploader pour ce champ spécifique
                    var fieldID = '<?php echo esc_js($meta_key); ?>';
                    var mediaUploader;
                    
                    $(document).on('click', '.upload-image-button[data-field-id="' + fieldID + '"]', function(e) {
                        e.preventDefault();
                        
                        // Si l'uploader existe déjà, l'ouvrir
                        if (mediaUploader) {
                            mediaUploader.open();
                            return;
                        }
                        
                        // Créer un nouvel uploader
                        mediaUploader = wp.media({
                            title: 'Choisir une image',
                            button: {
                                text: 'Utiliser cette image'
                            },
                            multiple: false
                        });
                        
                        // Quand une image est sélectionnée
                        mediaUploader.on('select', function() {
                            var attachment = mediaUploader.state().get('selection').first().toJSON();
                            $('#' + fieldID).val(attachment.id);
                            
                            // Mettre à jour l'aperçu de l'image
                            var previewWrapper = $('.upload-image-button[data-field-id="' + fieldID + '"]').closest('.poi-image-field').find('.image-preview-wrapper');
                            previewWrapper.html('<img src="' + attachment.url + '" alt="" style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px; display: block;" />');
                            
                            // Ajouter un bouton de suppression s'il n'existe pas
                            if ($('.remove-image-button[data-field-id="' + fieldID + '"]').length === 0) {
                                $('.upload-image-button[data-field-id="' + fieldID + '"]').after('<input type="button" class="button remove-image-button" value="Supprimer l\'image" data-field-id="' + fieldID + '" />');
                            }
                        });
                        
                        // Ouvrir l'uploader
                        mediaUploader.open();
                    });
                    
                    // Gestion de la suppression d'image
                    $(document).on('click', '.remove-image-button[data-field-id="' + fieldID + '"]', function() {
                        $('#' + fieldID).val('');
                        $(this).closest('.poi-image-field').find('.image-preview-wrapper').empty();
                        $(this).remove();
                    });
                });
                </script>
                <?php
                break;
                
            case 'text':
            default:
                echo '<input type="text" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" style="width:100%;" />';
                break;
        }
        
        if (!empty($field['description'])) {
            echo '<p class="description">' . esc_html($field['description']) . '</p>';
        }
        
        echo '</div>';
    }
    
    // Permettre à d'autres extensions d'ajouter leur propre affichage de champs
    do_action('terralize_poi_custom_fields_display', $post);
    
    echo '</div>';
}

/**
 * Sauvegarde des données de métaboxes
 */
function poi_save_geojson($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    // Sauvegarde du GeoJSON
    if (isset($_POST['poi_geojson'])) {
        update_post_meta($post_id, 'poi_geojson', wp_kses_post($_POST['poi_geojson']));
    }
    
    // Sauvegarde de l'icône
    if (isset($_POST['poi_icon_nonce']) && wp_verify_nonce($_POST['poi_icon_nonce'], 'poi_icon_nonce')) {
        if (isset($_POST['poi_icon_id'])) {
            update_post_meta($post_id, 'poi_icon_id', sanitize_text_field($_POST['poi_icon_id']));
        }
    }
    
    // Sauvegarde des champs personnalisés
    if (isset($_POST['poi_custom_fields_nonce']) && wp_verify_nonce($_POST['poi_custom_fields_nonce'], 'poi_custom_fields_nonce')) {
        $metafields = terralize_get_poi_metafields();
        
        // Parcourir chaque champ et sauvegarder sa valeur
        foreach ($metafields as $field_key => $field) {
            $meta_key = 'poi_' . $field_key;
            
            if (isset($_POST[$meta_key])) {
                $value = $_POST[$meta_key];
                
                // Sanitize selon le type
                switch ($field['type']) {
                    case 'textarea':
                        $value = wp_kses_post($value);
                        break;
                        
                    case 'wysiwyg':
                        $value = wp_kses_post($value);
                        break;
                        
                    case 'email':
                        $value = sanitize_email($value);
                        break;
                        
                    case 'url':
                        $value = esc_url_raw($value);
                        break;
                        
                    case 'number':
                        $value = floatval($value);
                        break;
                        
                    case 'checkbox':
                        $value = ($value == '1') ? '1' : '0';
                        break;
                        
                    case 'image':
                        $value = absint($value); // Assurer que c'est un entier positif (ID d'attachement)
                        break;
                        
                    case 'text':
                    case 'select':
                    default:
                        $value = sanitize_text_field($value);
                        break;
                }
                
                update_post_meta($post_id, $meta_key, $value);
            } else if ($field['type'] == 'checkbox') {
                // Pour les cases à cocher, si elles ne sont pas cochées, elles ne sont pas envoyées
                update_post_meta($post_id, $meta_key, '0');
            }
        }
        
        // Permettre à d'autres extensions de sauvegarder leurs propres champs
        do_action('terralize_poi_custom_fields_save', $post_id, $_POST);
    }
}
add_action('save_post_poi', 'poi_save_geojson');

/**
 * Récupère toutes les valeurs des champs personnalisés d'un POI
 * 
 * @param int $post_id ID du post POI
 * @return array Tableau des valeurs des champs personnalisés
 */
function terralize_get_poi_custom_fields($post_id) {
    $metafields = terralize_get_poi_metafields();
    $custom_fields = array();
    
    foreach ($metafields as $field_key => $field) {
        $meta_key = 'poi_' . $field_key;
        $value = get_post_meta($post_id, $meta_key, true);
        
        if (empty($value) && isset($field['default'])) {
            $value = $field['default'];
        }
        
        $custom_fields[$field_key] = array(
            'label' => $field['label'],
            'value' => $value,
            'type' => $field['type'],
            'display_zone' => isset($field['display_zone']) ? $field['display_zone'] : 'info'
        );
    }
    
    // Permettre à d'autres extensions d'ajouter leurs propres valeurs
    return apply_filters('terralize_poi_custom_fields_values', $custom_fields, $post_id);
}

/**
 * Exemple d'utilisation du filtre pour ajouter un champ personnalisé
 * À placer dans un fichier d'extension ou functions.php du thème
 */
/* 
function my_custom_poi_fields($fields) {
    // Ajouter un nouveau champ
    $fields['my_custom_field'] = array(
        'label' => 'Mon champ personnalisé',
        'type' => 'text',
        'description' => 'Description de mon champ',
        'required' => false,
        'default' => '',
        'priority' => 100 // Plus haut numéro = plus bas dans la liste
    );
    
    // Retirer un champ existant
    if (isset($fields['opening_hours'])) {
        unset($fields['opening_hours']);
    }
    
    return $fields;
}
add_filter('terralize_poi_metafields', 'my_custom_poi_fields');
*/

/**
 * Ajout d'une page d'administration pour gérer les champs personnalisés des POI
 */
function terralize_register_poi_fields_admin_page() {
    add_submenu_page(
        'edit.php?post_type=poi',
        'Gestion des champs',
        'Gestion des champs',
        'manage_options',
        'terralize-poi-fields',
        'terralize_poi_fields_admin_page'
    );
}
add_action('admin_menu', 'terralize_register_poi_fields_admin_page');

/**
 * Affichage de la page d'administration pour gérer les champs des POI
 */
function terralize_poi_fields_admin_page() {
    // Vérifier les droits d'accès
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les droits suffisants pour accéder à cette page.'));
    }
    
    // Sauvegarder les changements si formulaire soumis
    if (isset($_POST['terralize_save_poi_fields']) && check_admin_referer('terralize_poi_fields_nonce')) {
        terralize_save_poi_fields();
        echo '<div class="notice notice-success is-dismissible"><p>Les champs ont été mis à jour avec succès.</p></div>';
    }
    
    // Récupérer les champs actuels
    $fields = get_option('terralize_poi_custom_fields', array());
    
    // Si vide, utiliser les champs par défaut
    if (empty($fields)) {
        $default_fields = terralize_get_poi_metafields();
        // Enlever les filtres pour avoir les valeurs par défaut pures
        remove_all_filters('terralize_poi_metafields');
        $fields = terralize_get_poi_metafields();
        // Remettre les filtres
        add_filter('terralize_poi_metafields', function() use ($default_fields) { return $default_fields; });
    }
    
    // Types de champs disponibles
    $field_types = array(
        'text' => 'Texte court',
        'textarea' => 'Texte long',
        'wysiwyg' => 'Éditeur visuel',
        'email' => 'Email',
        'url' => 'URL',
        'number' => 'Nombre',
        'checkbox' => 'Case à cocher',
        'select' => 'Liste déroulante',
        'image' => 'Image'
    );
    
    // Zones d'affichage disponibles
    $display_zones = array(
        'header' => 'En-tête (images uniquement)',
        'info' => 'Onglet Informations',
        'contact' => 'Section Contact',
        'location' => 'Onglet Localisation'
    );
    
    ?>
    <div class="wrap">
        <h1>Gestion des champs des Points d'Intérêt</h1>
        <p>Personnalisez les champs disponibles pour vos points d'intérêt. Vous pouvez ajouter, modifier ou supprimer des champs selon vos besoins.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('terralize_poi_fields_nonce'); ?>
            
            <div id="poi-fields-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="12%">ID du champ</th>
                            <th width="15%">Libellé</th>
                            <th width="15%">Type</th>
                            <th width="25%">Description</th>
                            <th width="15%">Zone d'affichage</th>
                            <th width="8%">Requis</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="poi-fields-list">
                        <?php 
                        if (!empty($fields)) : 
                            foreach ($fields as $field_id => $field) : 
                        ?>
                            <tr class="field-row">
                                <td>
                                    <input type="text" name="field_id[]" value="<?php echo esc_attr($field_id); ?>" required readonly class="regular-text">
                                </td>
                                <td>
                                    <input type="text" name="field_label[]" value="<?php echo esc_attr($field['label']); ?>" required class="regular-text">
                                </td>
                                <td>
                                    <select name="field_type[]" class="field-type-select">
                                        <?php foreach ($field_types as $type_id => $type_name) : ?>
                                            <option value="<?php echo esc_attr($type_id); ?>" <?php selected($field['type'], $type_id); ?>><?php echo esc_html($type_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="field_description[]" value="<?php echo esc_attr($field['description']); ?>" class="regular-text">
                                </td>
                                <td>
                                    <select name="field_display_zone[]" class="display-zone-select">
                                        <?php foreach ($display_zones as $zone_id => $zone_name) : ?>
                                            <option value="<?php echo esc_attr($zone_id); ?>" <?php selected(isset($field['display_zone']) ? $field['display_zone'] : 'info', $zone_id); ?>>
                                                <?php echo esc_html($zone_name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="checkbox" name="field_required[]" value="1" <?php checked(!empty($field['required']), true); ?>>
                                </td>
                                <td>
                                    <button type="button" class="button remove-field">Supprimer</button>
                                </td>
                                <input type="hidden" name="field_priority[]" value="<?php echo esc_attr($field['priority']); ?>">
                                <input type="hidden" name="field_options[]" value="<?php echo isset($field['options']) ? esc_attr(json_encode($field['options'])) : ''; ?>">
                                <input type="hidden" name="field_default[]" value="<?php echo esc_attr($field['default']); ?>">
                            </tr>
                        <?php 
                            endforeach; 
                        endif; 
                        ?>
                    </tbody>
                </table>
                
                <p>
                    <button type="button" id="add-field" class="button button-secondary">Ajouter un champ</button>
                </p>
            </div>
            
            <p class="submit">
                <input type="submit" name="terralize_save_poi_fields" class="button button-primary" value="Enregistrer les modifications">
            </p>
        </form>
    </div>
    
    <!-- Template pour un nouveau champ -->
    <template id="new-field-template">
        <tr class="field-row">
            <td>
                <input type="text" name="field_id[]" value="" required placeholder="ID unique (sans espaces)" class="regular-text">
            </td>
            <td>
                <input type="text" name="field_label[]" value="" required placeholder="Libellé du champ" class="regular-text">
            </td>
            <td>
                <select name="field_type[]" class="field-type-select">
                    <?php foreach ($field_types as $type_id => $type_name) : ?>
                        <option value="<?php echo esc_attr($type_id); ?>"><?php echo esc_html($type_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="text" name="field_description[]" value="" placeholder="Description du champ" class="regular-text">
            </td>
            <td>
                <select name="field_display_zone[]" class="display-zone-select">
                    <?php foreach ($display_zones as $zone_id => $zone_name) : ?>
                        <option value="<?php echo esc_attr($zone_id); ?>">
                            <?php echo esc_html($zone_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="checkbox" name="field_required[]" value="1">
            </td>
            <td>
                <button type="button" class="button remove-field">Supprimer</button>
            </td>
            <input type="hidden" name="field_priority[]" value="10">
            <input type="hidden" name="field_options[]" value="">
            <input type="hidden" name="field_default[]" value="">
        </tr>
    </template>
    
    <style>
    /* Styles pour les sélecteurs de zone */
    .display-zone-select option[value="header"] {
        background-color: #f0f7ff;
    }
    .display-zone-select option[value="info"] {
        background-color: #f0fff4;
    }
    .display-zone-select option[value="contact"] {
        background-color: #fff5f5;
    }
    .display-zone-select option[value="location"] {
        background-color: #fffde7;
    }
    
    /* Styliser les sélecteurs de zone désactivés */
    .display-zone-select option:disabled {
        color: #999;
        background-color: #f5f5f5;
        font-style: italic;
    }
    
    /* Ajouter une couleur au fond du message d'aide */
    #poi-field-help {
        background-color: #f8f9fa;
        border-left: 4px solid #007cba;
        padding: 12px;
        margin: 15px 0;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Ajouter une div pour les messages d'aide
        $('<div id="poi-field-help" style="background: #f8f9fa; border-left: 4px solid #007cba; padding: 12px; margin: 15px 0; display: none;"></div>').insertBefore('#poi-fields-container');
        
        // Afficher un message d'aide selon la zone sélectionnée
        function showZoneHelp(zone) {
            var helpText = '';
            switch(zone) {
                case 'header':
                    helpText = '<strong>Zone En-tête</strong>: Réservée aux images qui apparaîtront en haut du popup. Seuls les champs de type "image" peuvent être placés ici.';
                    break;
                case 'contact':
                    helpText = '<strong>Section Contact</strong>: Les informations de contact (téléphone, email, site web...) seront affichées dans la première section de l\'onglet Informations.';
                    break;
                case 'info':
                    helpText = '<strong>Onglet Informations</strong>: Les champs apparaîtront dans la section principale de l\'onglet Informations.';
                    break;
                case 'location':
                    helpText = '<strong>Onglet Localisation</strong>: Ces champs seront affichés dans l\'onglet Localisation du popup. Idéal pour l\'adresse et autres informations géographiques.';
                    break;
            }
            
            $('#poi-field-help').html(helpText).show();
        }
        
        // Écouter les changements sur les sélecteurs de zone
        $(document).on('change', '.display-zone-select', function() {
            showZoneHelp($(this).val());
        });
        
        // Ajouter un nouveau champ
        $('#add-field').on('click', function() {
            var template = document.querySelector('#new-field-template');
            var clone = document.importNode(template.content, true);
            $('#poi-fields-list').append(clone);
            
            // Ajouter des restrictions selon le type de champ
            updateTypeRestrictions(clone.querySelector('.field-type-select'));
        });
        
        // Supprimer un champ
        $(document).on('click', '.remove-field', function() {
            $(this).closest('tr').remove();
        });
        
        // Restreindre les zones d'affichage selon le type de champ
        $(document).on('change', '.field-type-select', function() {
            updateTypeRestrictions(this);
        });
        
        function updateTypeRestrictions(typeSelect) {
            var row = $(typeSelect).closest('tr');
            var displayZoneSelect = row.find('.display-zone-select');
            var selectedType = $(typeSelect).val();
            
            // Réinitialiser les options
            displayZoneSelect.find('option').prop('disabled', false);
            
            // Si c'est une image, forcer l'affichage dans le header
            if (selectedType === 'image') {
                displayZoneSelect.val('header');
                displayZoneSelect.find('option:not([value="header"])').prop('disabled', true);
                // Montrer un message d'aide
                showZoneHelp('header');
            }
            
            // Pour les autres types, désactiver l'option header
            else {
                if (displayZoneSelect.val() === 'header') {
                    displayZoneSelect.val('info');
                }
                displayZoneSelect.find('option[value="header"]').prop('disabled', true);
                
                // Proposer des zones appropriées selon le type
                if (selectedType === 'email' || selectedType === 'phone' || selectedType === 'url') {
                    displayZoneSelect.val('contact');
                    showZoneHelp('contact');
                } else if (selectedType === 'text' && row.find('input[name="field_label[]"]').val().toLowerCase().includes('adresse')) {
                    displayZoneSelect.val('location');
                    showZoneHelp('location');
                }
            }
        }
        
        // Initialiser les restrictions pour les champs existants
        $('.field-type-select').each(function() {
            updateTypeRestrictions(this);
        });
        
        // Message d'introduction
        $('<div class="notice notice-info" style="margin: 15px 0;"><p><strong>Zones d\'affichage</strong>: Choisissez où chaque champ sera affiché dans les popups. Les champs sont groupés par zone pour une meilleure lisibilité.</p></div>').insertBefore('#poi-fields-container');
        
        // Gestion des options pour le type select
        $(document).on('change', '.field-type-select', function() {
            var row = $(this).closest('tr');
            if ($(this).val() === 'select') {
                var options = row.find('input[name="field_options[]"]').val();
                var optionsObj = options ? JSON.parse(options) : {};
                var optionsHtml = '';
                
                // Créer l'éditeur d'options
                if (row.find('.select-options').length === 0) {
                    row.find('td:nth-child(4)').append('<div class="select-options" style="margin-top: 5px;"><strong>Options:</strong><div class="options-list"></div><button type="button" class="button add-option">Ajouter une option</button></div>');
                    
                    // Ajouter les options existantes
                    for (var key in optionsObj) {
                        row.find('.options-list').append('<div class="option-row"><input type="text" placeholder="Valeur" value="' + key + '" class="option-key"> : <input type="text" placeholder="Libellé" value="' + optionsObj[key] + '" class="option-value"> <button type="button" class="button remove-option">×</button></div>');
                    }
                }
            } else {
                row.find('.select-options').remove();
            }
        });
        
        // Ajouter une option pour les selects
        $(document).on('click', '.add-option', function(e) {
            e.preventDefault();
            var optionsList = $(this).prev('.options-list');
            optionsList.append('<div class="option-row"><input type="text" placeholder="Valeur" class="option-key"> : <input type="text" placeholder="Libellé" class="option-value"> <button type="button" class="button remove-option">×</button></div>');
            updateOptionsValue($(this).closest('tr'));
        });
        
        // Supprimer une option
        $(document).on('click', '.remove-option', function() {
            var row = $(this).closest('tr');
            $(this).closest('.option-row').remove();
            updateOptionsValue(row);
        });
        
        // Mettre à jour la valeur des options quand elles changent
        $(document).on('change', '.option-key, .option-value', function() {
            updateOptionsValue($(this).closest('tr'));
        });
        
        // Fonction pour mettre à jour le champ caché des options
        function updateOptionsValue(row) {
            var options = {};
            row.find('.option-row').each(function() {
                var key = $(this).find('.option-key').val();
                var value = $(this).find('.option-value').val();
                if (key && value) {
                    options[key] = value;
                }
            });
            row.find('input[name="field_options[]"]').val(JSON.stringify(options));
        }
        
        // Initialiser les éditeurs d'options pour les selects existants
        $('.field-type-select').each(function() {
            if ($(this).val() === 'select') {
                $(this).trigger('change');
            }
        });
    });
    </script>
    <?php
}

/**
 * Sauvegarde les champs personnalisés depuis le formulaire d'administration
 */
function terralize_save_poi_fields() {
    if (!isset($_POST['field_id']) || !isset($_POST['field_label']) || !isset($_POST['field_type'])) {
        return;
    }
    
    $field_ids = $_POST['field_id'];
    $field_labels = $_POST['field_label'];
    $field_types = $_POST['field_type'];
    $field_descriptions = $_POST['field_description'];
    $field_priorities = $_POST['field_priority'];
    $field_defaults = $_POST['field_default'];
    $field_options = $_POST['field_options'];
    $field_display_zones = $_POST['field_display_zone'];
    
    // Initialiser le tableau des champs
    $fields = array();
    
    // Parcourir tous les champs
    for ($i = 0; $i < count($field_ids); $i++) {
        // Sanitizer les données
        $field_id = sanitize_key($field_ids[$i]);
        
        if (empty($field_id)) {
            continue; // Ignorer les champs sans ID
        }
        
        $fields[$field_id] = array(
            'label' => sanitize_text_field($field_labels[$i]),
            'type' => sanitize_text_field($field_types[$i]),
            'description' => sanitize_text_field($field_descriptions[$i]),
            'required' => isset($_POST['field_required']) && in_array('1', $_POST['field_required'], true) && isset($_POST['field_required'][$i]),
            'default' => sanitize_text_field($field_defaults[$i]),
            'priority' => intval($field_priorities[$i]),
            'display_zone' => sanitize_text_field($field_display_zones[$i])
        );
        
        // Gestion des options pour les champs de type select
        if ($field_types[$i] === 'select' && !empty($field_options[$i])) {
            $options_array = json_decode(stripslashes($field_options[$i]), true);
            if (is_array($options_array)) {
                $fields[$field_id]['options'] = $options_array;
            }
        }
    }
    
    // Sauvegarder les champs en base de données
    update_option('terralize_poi_custom_fields', $fields);
    
    return true;
}

/**
 * Ajout des scripts nécessaires pour le sélecteur d'images
 */
function terralize_poi_admin_enqueue_scripts($hook) {
    global $post;

    // Vérifier que nous sommes sur la page d'édition d'un POI
    if (!($hook == 'post.php' || $hook == 'post-new.php') || empty($post) || $post->post_type !== 'poi') {
        return;
    }

    // Enqueue du script Media Uploader de WordPress
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'terralize_poi_admin_enqueue_scripts');
