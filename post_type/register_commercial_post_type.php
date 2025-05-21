<?php
/**
 * Enregistre le type de contenu personnalisé 'commercial'
 */
function terralize_register_commercial_post_type() {
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
add_action('init', 'terralize_register_commercial_post_type');

/**
 * Définition de la structure des métachamps par défaut pour les commerciaux
 */
function terralize_get_commercial_metafields() {
    // Récupérer les champs personnalisés depuis la base de données
    $saved_fields = get_option('terralize_commercial_custom_fields', array());
    
    // Si des champs sont définis en base de données, les utiliser
    if (!empty($saved_fields)) {
        return apply_filters('terralize_commercial_metafields', $saved_fields);
    }
    
    // Sinon, utiliser les champs par défaut
    $default_fields = array(
        'email' => array(
            'label' => 'Email',
            'type' => 'email',
            'required' => false,
            'display_zone' => 'contact',
            'description' => 'Adresse email du commercial'
        ),
        'telephone' => array(
            'label' => 'Téléphone',
            'type' => 'text',
            'required' => false,
            'display_zone' => 'contact',
            'description' => 'Numéro de téléphone du commercial'
        ),
        'address' => array(
            'label' => 'Adresse',
            'type' => 'textarea',
            'required' => false,
            'display_zone' => 'location',
            'description' => 'Adresse postale du commercial'
        ),
        'opening_hours' => array(
            'label' => 'Horaires d\'ouverture',
            'type' => 'textarea',
            'required' => false,
            'display_zone' => 'info',
            'description' => 'Horaires d\'ouverture (format libre)'
        ),
        'social_links' => array(
            'label' => 'Liens sociaux',
            'type' => 'textarea',
            'required' => false,
            'display_zone' => 'contact',
            'description' => 'Liens vers les réseaux sociaux (un par ligne)'
        ),
        'photo' => array(
            'label' => 'Photo',
            'type' => 'image',
            'required' => false,
            'display_zone' => 'header',
            'description' => 'Photo du commercial'
        ),
        'description' => array(
            'label' => 'Description',
            'type' => 'wysiwyg',
            'required' => false,
            'display_zone' => 'info',
            'description' => 'Description détaillée du commercial'
        )
    );
    
    // Filtre permettant de modifier les champs par défaut
    return apply_filters('terralize_commercial_metafields', $default_fields);
}

function terralize_meta_box_callback($post) {
    // Sécurité avec nonce
    wp_nonce_field('commercial_custom_fields_nonce', 'commercial_custom_fields_nonce');
    
    // Récupérer tous les champs définis
    $metafields = terralize_get_commercial_metafields();
    
    // Récupérer les valeurs des couleurs
    $border_color = get_post_meta($post->ID, 'commercial_border_color', true) ?: '#3388ff';
    $fill_color = get_post_meta($post->ID, 'commercial_fill_color', true) ?: '#3388ff';
    
    // Créer un tableau pour stocker les champs par zone d'affichage
    $zones = array(
        'header' => array(),
        'info' => array(),
        'contact' => array(),
        'location' => array()
    );
    
    // Trier les champs par zone d'affichage
    foreach ($metafields as $field_key => $field) {
        $zone = isset($field['display_zone']) ? $field['display_zone'] : 'info';
        $zones[$zone][$field_key] = $field;
    }
    
    // Afficher les champs par zone pour une meilleure organisation
    echo '<div class="commercial-fields-container">';
    
    // Section pour les couleurs (toujours visible en haut)
    echo '<div class="commercial-section" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">';
    echo '<h3 style="margin-top: 0;">Apparence sur la carte</h3>';
    echo '<div class="terralize-color-fields" style="display: flex; gap: 20px; margin-top: 10px;">';
    echo '<div>';
    echo '<label for="commercial_border_color">Couleur de bordure :</label>';
    echo '<input type="color" name="commercial_border_color" id="commercial_border_color" value="' . esc_attr($border_color) . '" />';
    echo '</div>';
    echo '<div>';
    echo '<label for="commercial_fill_color">Couleur de remplissage :</label>';
    echo '<input type="color" name="commercial_fill_color" id="commercial_fill_color" value="' . esc_attr($fill_color) . '" />';
    echo '</div>';
    echo '</div>';
    echo '<p class="description">Ces couleurs seront utilisées pour afficher les zones de ce commercial sur la carte.</p>';
    echo '</div>';
    
    // Afficher chaque zone avec ses champs
    foreach ($zones as $zone_name => $zone_fields) {
        if (empty($zone_fields)) continue;
        
        $zone_titles = array(
            'header' => 'En-tête (images, bannières)',
            'info' => 'Informations générales',
            'contact' => 'Coordonnées de contact',
            'location' => 'Localisation et adresse'
        );
        
        $zone_colors = array(
            'header' => '#9c27b0',
            'info' => '#2196f3',
            'contact' => '#4caf50',
            'location' => '#ff9800'
        );
        
        echo '<div class="commercial-section" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">';
        echo '<h3 style="margin-top: 0; color: ' . $zone_colors[$zone_name] . ';">' . $zone_titles[$zone_name] . '</h3>';
        
        foreach ($zone_fields as $field_key => $field) {
            $meta_key = 'commercial_' . $field_key;
            $value = get_post_meta($post->ID, $meta_key, true);
            
            // Si pas de valeur, utiliser la valeur par défaut
            if (empty($value) && isset($field['default'])) {
                $value = $field['default'];
            }
            
            echo '<div class="commercial-field-container" style="margin-bottom: 15px;">';
            echo '<label for="' . esc_attr($meta_key) . '"><strong>' . esc_html($field['label']) . '</strong>';
            
            if (!empty($field['required'])) {
                echo ' <span class="required" style="color: red;">*</span>';
            }
            
            echo '</label>';
            
            if (!empty($field['description'])) {
                echo '<p class="description" style="margin: 2px 0 8px; font-style: italic; color: #666;">' . esc_html($field['description']) . '</p>';
            }
            
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
                    echo '<div class="commercial-image-field" style="margin-top: 10px;">';
                    
                    // Champ caché pour stocker l'ID de l'image
                    echo '<input type="hidden" name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" />';
                    
                    // Aperçu de l'image
                    echo '<div class="image-preview" style="margin-bottom: 10px;">';
                    if (!empty($value)) {
                        echo wp_get_attachment_image($value, 'medium', false, array('style' => 'max-width: 300px; height: auto;'));
                    } else {
                        echo '<div style="border: 1px dashed #ddd; padding: 30px; text-align: center; color: #888;">Aucune image sélectionnée</div>';
                    }
                    echo '</div>';
                    
                    // Boutons pour ajouter/supprimer l'image
                    echo '<button type="button" class="button upload-image" data-field="' . esc_attr($meta_key) . '">Choisir une image</button> ';
                    echo '<button type="button" class="button remove-image" data-field="' . esc_attr($meta_key) . '" style="' . (empty($value) ? 'display:none;' : '') . '">Supprimer l\'image</button>';
                    echo '</div>';
                    
                    // S'assurer que le script est chargé
                    wp_enqueue_media();
                    break;
                    
                default: // Type texte par défaut
                    echo '<input type="text" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" style="width:100%;" />';
                    break;
            }
            
            echo '</div>';
        }
        
        echo '</div>'; // Fin de la section
    }
    
    echo '</div>'; // Fin du conteneur
    
    // Script pour gérer les champs de type image
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Gestion des champs image
        $('.upload-image').click(function(e) {
            e.preventDefault();
            
            var button = $(this);
            var fieldId = button.data('field');
            
            var frame = wp.media({
                title: 'Sélectionner une image',
                button: {
                    text: 'Utiliser cette image'
                },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#' + fieldId).val(attachment.id);
                
                // Mise à jour de l'aperçu
                var preview = button.siblings('.image-preview');
                preview.html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto;" />');
                
                // Afficher le bouton de suppression
                button.siblings('.remove-image').show();
            });
            
            frame.open();
        });
        
        // Suppression d'une image
        $('.remove-image').click(function(e) {
            e.preventDefault();
            
            var button = $(this);
            var fieldId = button.data('field');
            
            // Vider le champ
            $('#' + fieldId).val('');
            
            // Mettre à jour l'aperçu
            var preview = button.siblings('.image-preview');
            preview.html('<div style="border: 1px dashed #ddd; padding: 30px; text-align: center; color: #888;">Aucune image sélectionnée</div>');
            
            // Masquer le bouton de suppression
            button.hide();
        });
    });
    </script>
    <?php
}

/**
 * Récupère les champs personnalisés d'un commercial formatés pour l'affichage
 */
function terralize_get_commercial_custom_fields($commercial_id) {
    if (!$commercial_id) return array();
    
    $metafields = terralize_get_commercial_metafields();
    $custom_fields = array();
    
    foreach ($metafields as $field_key => $field) {
        $meta_key = 'commercial_' . $field_key;
        $value = get_post_meta($commercial_id, $meta_key, true);
        
        // Ajouter le champ uniquement s'il a une valeur
        if (!empty($value) || $value === '0') {
            $custom_fields[$field_key] = array(
                'label' => $field['label'],
                'value' => $value,
                'type' => $field['type'],
                'display_zone' => isset($field['display_zone']) ? $field['display_zone'] : 'info'
            );
        }
    }
    
    return $custom_fields;
}

function terralize_save_meta_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    // Vérifier le nonce pour les champs personnalisés
    if (isset($_POST['commercial_custom_fields_nonce']) && 
        wp_verify_nonce($_POST['commercial_custom_fields_nonce'], 'commercial_custom_fields_nonce')) {
        
        // Récupérer tous les champs définis
        $metafields = terralize_get_commercial_metafields();
        
        // Enregistrer chaque champ
        foreach ($metafields as $field_key => $field) {
            $meta_key = 'commercial_' . $field_key;
            
            if (isset($_POST[$meta_key])) {
                $value = $_POST[$meta_key];
                
                // Sanitize selon le type de champ
                switch ($field['type']) {
                    case 'email':
                        $value = sanitize_email($value);
                        break;
                    case 'url':
                        $value = esc_url_raw($value);
                        break;
                    case 'wysiwyg':
                        $value = wp_kses_post($value);
                        break;
                    case 'textarea':
                        $value = sanitize_textarea_field($value);
                        break;
                    case 'checkbox':
                        $value = ($value == '1') ? '1' : '';
                        break;
                    case 'number':
                        $value = intval($value);
                        break;
                    case 'image':
                        $value = absint($value);
                        break;
                    default:
                        $value = sanitize_text_field($value);
                        break;
                }
                
                update_post_meta($post_id, $meta_key, $value);
            } elseif ($field['type'] == 'checkbox') {
                // Cas spécial pour les cases à cocher: si non présent, c'est décoché
                update_post_meta($post_id, $meta_key, '');
            }
        }
    }
    
    // Enregistrer les couleurs
    if (isset($_POST['commercial_border_color'])) {
        update_post_meta($post_id, 'commercial_border_color', sanitize_hex_color($_POST['commercial_border_color']));
    }
    if (isset($_POST['commercial_fill_color'])) {
        update_post_meta($post_id, 'commercial_fill_color', sanitize_hex_color($_POST['commercial_fill_color']));
    }
}
add_action('save_post', 'terralize_save_meta_box');

function terralize_add_commercial_meta_box() {
    add_meta_box(
        'terralize_commercial_info',
        'Informations du commercial',
        'terralize_meta_box_callback',
        'commercial',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'terralize_add_commercial_meta_box');

/**
 * Ajoute les scripts nécessaires pour l'interface d'administration des commerciaux
 */
function terralize_commercial_admin_scripts($hook) {
    global $post;
    
    // N'ajouter que sur les pages d'édition des commerciaux
    if (!($hook == 'post.php' || $hook == 'post-new.php') || 
        !is_object($post) || $post->post_type != 'commercial') {
        return;
    }
    
    // Enregistrer les médias
    wp_enqueue_media();
    
    // Style CSS pour l'interface
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // Script personnalisé
    wp_enqueue_script(
        'terralize-commercial-admin',
        plugin_dir_url(__FILE__) . '../scripts/commercial-admin.js',
        array('jquery', 'wp-color-picker'),
        '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'terralize_commercial_admin_scripts');

/**
 * Ajoute une page d'administration pour gérer les champs des commerciaux
 */
function terralize_register_commercial_fields_admin_page() {
    add_submenu_page(
        'terralize',
        'Gestion des champs commerciaux',
        'Gestion des champs commerciaux',
        'manage_options',
        'terralize_commercial_fields',
        'terralize_commercial_fields_admin_page'
    );
}
add_action('admin_menu', 'terralize_register_commercial_fields_admin_page');

/**
 * Affiche la page d'administration pour gérer les champs des commerciaux
 */
function terralize_commercial_fields_admin_page() {
    // Vérifier les droits d'accès
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Traiter les actions de sauvegarde
    if (isset($_POST['save_commercial_fields']) && check_admin_referer('terralize_commercial_fields_nonce')) {
        terralize_save_commercial_fields();
    }
    
    // Récupérer les champs existants
    $fields = terralize_get_commercial_metafields();
    
    // Types de champs disponibles
    $field_types = array(
        'text' => 'Texte',
        'textarea' => 'Zone de texte',
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
        'header' => 'En-tête (images, bannières)',
        'info' => 'Informations générales',
        'contact' => 'Coordonnées de contact',
        'location' => 'Localisation et adresse'
    );
    
    // Afficher le formulaire
    ?>
    <div class="wrap">
        <h1>Gestion des champs personnalisés pour les commerciaux</h1>
        
        <div class="notice notice-info">
            <p>Configurez ici les champs qui seront disponibles pour chaque commercial. Chaque champ peut être associé à une zone d'affichage spécifique dans les popups.</p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('terralize_commercial_fields_nonce'); ?>
            
            <table class="widefat" id="commercial-fields-table">
                <thead>
                    <tr>
                        <th>Clé</th>
                        <th>Libellé</th>
                        <th>Type</th>
                        <th>Zone d'affichage</th>
                        <th>Requis</th>
                        <th>Description</th>
                        <th>Options (pour liste)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Afficher les champs existants
                    foreach ($fields as $field_key => $field) : 
                        $required = isset($field['required']) && $field['required'] ? 'checked' : '';
                        $display_zone = isset($field['display_zone']) ? $field['display_zone'] : 'info';
                    ?>
                    <tr class="field-row">
                        <td>
                            <input type="text" name="field_keys[]" value="<?php echo esc_attr($field_key); ?>" required readonly style="background-color: #f0f0f0;" />
                        </td>
                        <td>
                            <input type="text" name="field_labels[]" value="<?php echo esc_attr($field['label']); ?>" required />
                        </td>
                        <td>
                            <select name="field_types[]" class="field-type">
                                <?php foreach ($field_types as $type => $label) : ?>
                                    <option value="<?php echo esc_attr($type); ?>" <?php selected($field['type'], $type); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="field_display_zones[]">
                                <?php foreach ($display_zones as $zone => $label) : ?>
                                    <option value="<?php echo esc_attr($zone); ?>" <?php selected($display_zone, $zone); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="checkbox" name="field_required[<?php echo esc_attr($field_key); ?>]" value="1" <?php echo $required; ?> />
                        </td>
                        <td>
                            <input type="text" name="field_descriptions[]" value="<?php echo esc_attr($field['description'] ?? ''); ?>" />
                        </td>
                        <td>
                            <textarea name="field_options[]" class="field-options" <?php echo ($field['type'] !== 'select') ? 'style="display:none;"' : ''; ?>><?php 
                                if (isset($field['options']) && is_array($field['options'])) {
                                    foreach ($field['options'] as $value => $label) {
                                        echo esc_textarea($value . '=' . $label . "\n");
                                    }
                                }
                            ?></textarea>
                        </td>
                        <td>
                            <button type="button" class="button remove-field">Supprimer</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- Template pour un nouveau champ -->
                    <tr id="new-field-template" style="display:none;">
                        <td>
                            <input type="text" name="template_field_key" value="" placeholder="champ_exemple" />
                        </td>
                        <td>
                            <input type="text" name="template_field_label" value="" placeholder="Libellé du champ" />
                        </td>
                        <td>
                            <select name="template_field_type" class="field-type">
                                <?php foreach ($field_types as $type => $label) : ?>
                                    <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="template_field_display_zone">
                                <?php foreach ($display_zones as $zone => $label) : ?>
                                    <option value="<?php echo esc_attr($zone); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="checkbox" name="template_field_required" value="1" />
                        </td>
                        <td>
                            <input type="text" name="template_field_description" value="" placeholder="Description du champ" />
                        </td>
                        <td>
                            <textarea name="template_field_options" class="field-options" style="display:none;" placeholder="valeur=Libellé&#10;valeur2=Libellé 2"></textarea>
                        </td>
                        <td>
                            <button type="button" class="button remove-field">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <button type="button" class="button" id="add-field">Ajouter un champ</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_commercial_fields" class="button-primary" value="Enregistrer les champs" />
            </p>
        </form>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Afficher/masquer les options selon le type de champ
            $(document).on('change', '.field-type', function() {
                var options = $(this).closest('tr').find('.field-options');
                if ($(this).val() === 'select') {
                    options.show();
                } else {
                    options.hide();
                }
            });
            
            // Ajouter un nouveau champ
            $('#add-field').click(function() {
                var template = $('#new-field-template').clone();
                template.removeAttr('id').show();
                
                // Renommer correctement les champs pour éviter le problème de validation
                template.find('input[name="template_field_key"]').attr('name', 'new_field_keys[]').prop('required', true);
                template.find('input[name="template_field_label"]').attr('name', 'new_field_labels[]').prop('required', true);
                template.find('select[name="template_field_type"]').attr('name', 'new_field_types[]');
                template.find('select[name="template_field_display_zone"]').attr('name', 'new_field_display_zones[]');
                template.find('input[name="template_field_required"]').attr('name', 'new_field_required[]');
                template.find('input[name="template_field_description"]').attr('name', 'new_field_descriptions[]');
                template.find('textarea[name="template_field_options"]').attr('name', 'new_field_options[]');
                
                $('#commercial-fields-table tbody').append(template);
            });
            
            // Supprimer un champ
            $(document).on('click', '.remove-field', function() {
                $(this).closest('tr').remove();
            });
        });
        </script>
    </div>
    <?php
}

/**
 * Enregistre les champs personnalisés des commerciaux
 */
function terralize_save_commercial_fields() {
    // Récupérer les champs existants
    $field_keys = isset($_POST['field_keys']) ? $_POST['field_keys'] : array();
    $field_labels = isset($_POST['field_labels']) ? $_POST['field_labels'] : array();
    $field_types = isset($_POST['field_types']) ? $_POST['field_types'] : array();
    $field_display_zones = isset($_POST['field_display_zones']) ? $_POST['field_display_zones'] : array();
    $field_required = isset($_POST['field_required']) ? $_POST['field_required'] : array();
    $field_descriptions = isset($_POST['field_descriptions']) ? $_POST['field_descriptions'] : array();
    $field_options = isset($_POST['field_options']) ? $_POST['field_options'] : array();
    
    // Récupérer les nouveaux champs
    $new_field_keys = isset($_POST['new_field_keys']) ? $_POST['new_field_keys'] : array();
    $new_field_labels = isset($_POST['new_field_labels']) ? $_POST['new_field_labels'] : array();
    $new_field_types = isset($_POST['new_field_types']) ? $_POST['new_field_types'] : array();
    $new_field_display_zones = isset($_POST['new_field_display_zones']) ? $_POST['new_field_display_zones'] : array();
    $new_field_required = isset($_POST['new_field_required']) ? $_POST['new_field_required'] : array();
    $new_field_descriptions = isset($_POST['new_field_descriptions']) ? $_POST['new_field_descriptions'] : array();
    $new_field_options = isset($_POST['new_field_options']) ? $_POST['new_field_options'] : array();
    
    // Construire le tableau des champs
    $fields = array();
    
    // Traiter les champs existants
    for ($i = 0; $i < count($field_keys); $i++) {
        $key = sanitize_key($field_keys[$i]);
        if (empty($key)) continue;
        
        $fields[$key] = array(
            'label' => sanitize_text_field($field_labels[$i]),
            'type' => sanitize_text_field($field_types[$i]),
            'display_zone' => sanitize_text_field($field_display_zones[$i]),
            'required' => isset($field_required[$key]),
            'description' => isset($field_descriptions[$i]) ? sanitize_text_field($field_descriptions[$i]) : ''
        );
        
        // Traiter les options pour les listes déroulantes
        if ($field_types[$i] === 'select' && !empty($field_options[$i])) {
            $options = array();
            $lines = explode("\n", $field_options[$i]);
            
            foreach ($lines as $line) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $option_key = trim($parts[0]);
                    $option_value = trim($parts[1]);
                    if (!empty($option_key)) {
                        $options[$option_key] = $option_value;
                    }
                }
            }
            
            if (!empty($options)) {
                $fields[$key]['options'] = $options;
            }
        }
    }
    
    // Traiter les nouveaux champs
    for ($i = 0; $i < count($new_field_keys); $i++) {
        $key = sanitize_key($new_field_keys[$i]);
        if (empty($key)) continue;
        
        $fields[$key] = array(
            'label' => sanitize_text_field($new_field_labels[$i]),
            'type' => sanitize_text_field($new_field_types[$i]),
            'display_zone' => sanitize_text_field($new_field_display_zones[$i]),
            'required' => in_array($i, $new_field_required),
            'description' => isset($new_field_descriptions[$i]) ? sanitize_text_field($new_field_descriptions[$i]) : ''
        );
        
        // Traiter les options pour les listes déroulantes
        if ($new_field_types[$i] === 'select' && !empty($new_field_options[$i])) {
            $options = array();
            $lines = explode("\n", $new_field_options[$i]);
            
            foreach ($lines as $line) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $option_key = trim($parts[0]);
                    $option_value = trim($parts[1]);
                    if (!empty($option_key)) {
                        $options[$option_key] = $option_value;
                    }
                }
            }
            
            if (!empty($options)) {
                $fields[$key]['options'] = $options;
            }
        }
    }
    
    // Enregistrer les champs
    update_option('terralize_commercial_custom_fields', $fields);
    
    // Afficher un message de succès
    add_settings_error(
        'terralize_commercial_fields',
        'fields_updated',
        'Les champs personnalisés des commerciaux ont été mis à jour avec succès.',
        'success'
    );
}

/**
 * Endpoint AJAX pour récupérer les champs personnalisés d'un commercial
 */
function terralize_ajax_get_commercial_fields() {
    // Débogage - Ajouter un log dans les headers
    header('X-Debug: Début de traitement AJAX pour commercial');
    
    // Vérifier si l'ID du commercial est fourni
    if (!isset($_GET['commercial_id']) || empty($_GET['commercial_id'])) {
        wp_send_json_error('ID du commercial non fourni.');
        return;
    }
    
    $commercial_id = intval($_GET['commercial_id']);
    
    // Récupérer les champs personnalisés
    $custom_fields = terralize_get_commercial_custom_fields($commercial_id);
    
    // Débogage - Vérifier les champs récupérés
    error_log('Champs commerciaux récupérés pour ID ' . $commercial_id . ': ' . json_encode($custom_fields));
    
    // Ajouter le nom du commercial
    $commercial_title = get_the_title($commercial_id);
    $result = array(
        'commercial_id' => $commercial_id,
        'title' => $commercial_title,
        'fields' => $custom_fields
    );
    
    // Traiter les champs de type image pour obtenir les URLs
    foreach ($result['fields'] as $key => $field) {
        if ($field['type'] === 'image' && !empty($field['value'])) {
            $image_url = wp_get_attachment_image_url($field['value'], 'medium');
            if ($image_url) {
                $result['fields'][$key]['value'] = $image_url;
            }
        }
    }
    
    // Ajouter des en-têtes de débogage
    header('X-Fields-Count: ' . count($custom_fields));
    
    // Débogage - Log final avant envoi
    error_log('Réponse AJAX finale pour commercial ' . $commercial_id . ': ' . json_encode($result));
    
    // Envoyer la réponse
    wp_send_json_success($result);
}
// Ces actions sont commentées car nous chargeons maintenant les données directement dans le shortcode
// add_action('wp_ajax_get_commercial_fields', 'terralize_ajax_get_commercial_fields');
// add_action('wp_ajax_nopriv_get_commercial_fields', 'terralize_ajax_get_commercial_fields');
