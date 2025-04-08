<?php
/**
 * Champs méta supplémentaires pour les panneaux d'affichage
 * Ajoute des champs pour la visibilité, dimensions, support, etc.
 */

// Si ce fichier est appelé directement, abandon
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajouter des meta boxes pour les champs supplémentaires
 */
function zap_add_panel_meta_boxes() {
    // Meta box pour les détails techniques
    add_meta_box(
        'zap_panel_tech_details',
        'Caractéristiques techniques',
        'zap_panel_tech_details_callback',
        'poi',
        'normal',
        'high'
    );
    
    // Meta box pour les informations de localisation
    add_meta_box(
        'zap_panel_location',
        'Localisation',
        'zap_panel_location_callback',
        'poi',
        'normal',
        'high'
    );
    
    // Meta box pour les informations de visibilité
    add_meta_box(
        'zap_panel_visibility',
        'Visibilité',
        'zap_panel_visibility_callback',
        'poi',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'zap_add_panel_meta_boxes');

/**
 * Callback pour la meta box des détails techniques
 */
function zap_panel_tech_details_callback($post) {
    // Récupérer les valeurs existantes
    $panel_type = get_post_meta($post->ID, 'panel_type', true);
    $support_type = get_post_meta($post->ID, 'support_type', true);
    $width = get_post_meta($post->ID, 'panel_width', true);
    $height = get_post_meta($post->ID, 'panel_height', true);
    $surface = get_post_meta($post->ID, 'panel_surface', true);
    $reference = get_post_meta($post->ID, 'panel_reference', true);
    
    // Ajouter un nonce pour la sécurité
    wp_nonce_field('zap_panel_meta_nonce', 'zap_panel_meta_nonce');
    
    // Afficher les champs
    ?>
    <style>
        .zap-meta-field {
            margin-bottom: 15px;
        }
        .zap-meta-field label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .zap-meta-field input[type="text"],
        .zap-meta-field input[type="number"],
        .zap-meta-field select {
            width: 100%;
            max-width: 400px;
        }
        .zap-meta-field .description {
            display: block;
            font-style: italic;
            color: #666;
            margin-top: 5px;
        }
        .zap-meta-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        .zap-meta-col {
            padding: 0 10px;
            box-sizing: border-box;
            flex: 1;
        }
    </style>
    
    <div class="zap-meta-field">
        <label for="panel_reference">Référence du panneau</label>
        <input type="text" id="panel_reference" name="panel_reference" value="<?php echo esc_attr($reference); ?>">
        <span class="description">Numéro ou code de référence unique du panneau</span>
    </div>
    
    <div class="zap-meta-row">
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_type">Type de panneau</label>
                <select id="panel_type" name="panel_type">
                    <option value="">-- Sélectionner --</option>
                    <option value="mural" <?php selected($panel_type, 'mural'); ?>>Mural</option>
                    <option value="pre-enseigne" <?php selected($panel_type, 'pre-enseigne'); ?>>Pré-enseigne</option>
                    <option value="4x3" <?php selected($panel_type, '4x3'); ?>>4x3</option>
                    <option value="8x3" <?php selected($panel_type, '8x3'); ?>>8x3</option>
                    <option value="déroulant" <?php selected($panel_type, 'déroulant'); ?>>Déroulant</option>
                    <option value="totem" <?php selected($panel_type, 'totem'); ?>>Totem</option>
                </select>
            </div>
        </div>
        
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="support_type">Type de support</label>
                <select id="support_type" name="support_type">
                    <option value="">-- Sélectionner --</option>
                    <option value="monopied" <?php selected($support_type, 'monopied'); ?>>Monopied</option>
                    <option value="bipied" <?php selected($support_type, 'bipied'); ?>>Bipied</option>
                    <option value="mural" <?php selected($support_type, 'mural'); ?>>Mural</option>
                    <option value="sucette" <?php selected($support_type, 'sucette'); ?>>Sucette</option>
                    <option value="autre" <?php selected($support_type, 'autre'); ?>>Autre</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="zap-meta-row">
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_width">Largeur (cm)</label>
                <input type="number" id="panel_width" name="panel_width" value="<?php echo esc_attr($width); ?>" min="0" step="1">
            </div>
        </div>
        
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_height">Hauteur (cm)</label>
                <input type="number" id="panel_height" name="panel_height" value="<?php echo esc_attr($height); ?>" min="0" step="1">
            </div>
        </div>
        
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_surface">Surface (m²)</label>
                <input type="number" id="panel_surface" name="panel_surface" value="<?php echo esc_attr($surface); ?>" min="0" step="0.01">
            </div>
        </div>
    </div>
    <?php
}

/**
 * Callback pour la meta box de la localisation
 */
function zap_panel_location_callback($post) {
    // Récupérer les valeurs existantes
    $address = get_post_meta($post->ID, 'panel_address', true);
    $postal_code = get_post_meta($post->ID, 'panel_postal_code', true);
    $city_name = get_post_meta($post->ID, 'panel_city_name', true);
    $department = get_post_meta($post->ID, 'panel_department', true);
    $region = get_post_meta($post->ID, 'panel_region', true);
    
    // Afficher les champs
    ?>
    <div class="zap-meta-field">
        <label for="panel_address">Adresse</label>
        <input type="text" id="panel_address" name="panel_address" value="<?php echo esc_attr($address); ?>" class="widefat">
    </div>
    
    <div class="zap-meta-row">
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_postal_code">Code postal</label>
                <input type="text" id="panel_postal_code" name="panel_postal_code" value="<?php echo esc_attr($postal_code); ?>">
            </div>
        </div>
        
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_city_name">Ville</label>
                <input type="text" id="panel_city_name" name="panel_city_name" value="<?php echo esc_attr($city_name); ?>">
            </div>
        </div>
    </div>
    
    <div class="zap-meta-row">
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_department">Département</label>
                <input type="text" id="panel_department" name="panel_department" value="<?php echo esc_attr($department); ?>">
            </div>
        </div>
        
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_region">Région</label>
                <input type="text" id="panel_region" name="panel_region" value="<?php echo esc_attr($region); ?>">
            </div>
        </div>
    </div>
    
    <div class="zap-meta-field">
        <button type="button" id="locate-panel" class="button button-secondary">
            <span class="dashicons dashicons-location" style="vertical-align: middle;"></span> Remplir automatiquement l'adresse depuis la carte
        </button>
    </div>
    <?php
}

/**
 * Callback pour la meta box de la visibilité
 */
function zap_panel_visibility_callback($post) {
    // Récupérer les valeurs existantes
    $visibility_from = get_post_meta($post->ID, 'visibility_from', true);
    $visibility_to = get_post_meta($post->ID, 'visibility_to', true);
    $visibility_angle = get_post_meta($post->ID, 'visibility_angle', true);
    $visibility_distance = get_post_meta($post->ID, 'visibility_distance', true);
    $visibility_note = get_post_meta($post->ID, 'visibility_note', true);
    $traffic = get_post_meta($post->ID, 'panel_traffic', true);
    
    // Afficher les champs
    ?>
    <div class="zap-meta-field">
        <label for="visibility_from">Visibilité venant de</label>
        <input type="text" id="visibility_from" name="visibility_from" value="<?php echo esc_attr($visibility_from); ?>" class="widefat">
        <span class="description">Ex: Avenue Charles de Gaulle, Direction centre-ville</span>
    </div>
    
    <div class="zap-meta-field">
        <label for="visibility_to">Visibilité allant à</label>
        <input type="text" id="visibility_to" name="visibility_to" value="<?php echo esc_attr($visibility_to); ?>" class="widefat">
        <span class="description">Ex: Avenue Charles de Gaulle, Direction périphérique</span>
    </div>
    
    <div class="zap-meta-row">
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="visibility_angle">Angle de visibilité (degrés)</label>
                <input type="number" id="visibility_angle" name="visibility_angle" value="<?php echo esc_attr($visibility_angle); ?>" min="0" max="360">
                <span class="description">De 0 à 360 degrés</span>
            </div>
        </div>
        
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="visibility_distance">Distance de visibilité (m)</label>
                <input type="number" id="visibility_distance" name="visibility_distance" value="<?php echo esc_attr($visibility_distance); ?>" min="0">
                <span class="description">En mètres</span>
            </div>
        </div>
        
        <div class="zap-meta-col">
            <div class="zap-meta-field">
                <label for="panel_traffic">Trafic journalier</label>
                <input type="number" id="panel_traffic" name="panel_traffic" value="<?php echo esc_attr($traffic); ?>" min="0">
                <span class="description">Nombre de véhicules/jour</span>
            </div>
        </div>
    </div>
    
    <div class="zap-meta-field">
        <label for="visibility_note">Remarques sur la visibilité</label>
        <textarea id="visibility_note" name="visibility_note" rows="3" class="widefat"><?php echo esc_textarea($visibility_note); ?></textarea>
    </div>
    <?php
}

/**
 * Sauvegarder les données des meta boxes
 */
function zap_save_panel_meta_boxes($post_id) {
    // Vérifier si c'est une sauvegarde automatique
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Vérifier le type de post
    if (get_post_type($post_id) !== 'poi') {
        return;
    }
    
    // Vérifier les permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Vérifier le nonce
    if (!isset($_POST['zap_panel_meta_nonce']) || !wp_verify_nonce($_POST['zap_panel_meta_nonce'], 'zap_panel_meta_nonce')) {
        return;
    }
    
    // Sauvegarder les champs techniques
    if (isset($_POST['panel_type'])) {
        update_post_meta($post_id, 'panel_type', sanitize_text_field($_POST['panel_type']));
    }
    if (isset($_POST['support_type'])) {
        update_post_meta($post_id, 'support_type', sanitize_text_field($_POST['support_type']));
    }
    if (isset($_POST['panel_width'])) {
        update_post_meta($post_id, 'panel_width', absint($_POST['panel_width']));
    }
    if (isset($_POST['panel_height'])) {
        update_post_meta($post_id, 'panel_height', absint($_POST['panel_height']));
    }
    if (isset($_POST['panel_surface'])) {
        update_post_meta($post_id, 'panel_surface', floatval($_POST['panel_surface']));
    }
    if (isset($_POST['panel_reference'])) {
        update_post_meta($post_id, 'panel_reference', sanitize_text_field($_POST['panel_reference']));
    }
    
    // Sauvegarder les champs de localisation
    if (isset($_POST['panel_address'])) {
        update_post_meta($post_id, 'panel_address', sanitize_text_field($_POST['panel_address']));
    }
    if (isset($_POST['panel_postal_code'])) {
        update_post_meta($post_id, 'panel_postal_code', sanitize_text_field($_POST['panel_postal_code']));
    }
    if (isset($_POST['panel_city_name'])) {
        $city_name = sanitize_text_field($_POST['panel_city_name']);
        update_post_meta($post_id, 'panel_city_name', $city_name);
        
        // Créer ou associer avec la taxonomie ville
        if ($city_name) {
            $city_slug = sanitize_title($city_name);
            $existing_term = get_term_by('name', $city_name, 'city');
            
            if (!$existing_term) {
                $term = wp_insert_term($city_name, 'city', array('slug' => $city_slug));
                if (!is_wp_error($term)) {
                    wp_set_object_terms($post_id, (int)$term['term_id'], 'city');
                }
            } else {
                wp_set_object_terms($post_id, (int)$existing_term->term_id, 'city');
            }
        }
    }
    if (isset($_POST['panel_department'])) {
        update_post_meta($post_id, 'panel_department', sanitize_text_field($_POST['panel_department']));
    }
    if (isset($_POST['panel_region'])) {
        update_post_meta($post_id, 'panel_region', sanitize_text_field($_POST['panel_region']));
    }
    
    // Sauvegarder les champs de visibilité
    if (isset($_POST['visibility_from'])) {
        update_post_meta($post_id, 'visibility_from', sanitize_text_field($_POST['visibility_from']));
    }
    if (isset($_POST['visibility_to'])) {
        update_post_meta($post_id, 'visibility_to', sanitize_text_field($_POST['visibility_to']));
    }
    if (isset($_POST['visibility_angle'])) {
        update_post_meta($post_id, 'visibility_angle', absint($_POST['visibility_angle']));
    }
    if (isset($_POST['visibility_distance'])) {
        update_post_meta($post_id, 'visibility_distance', absint($_POST['visibility_distance']));
    }
    if (isset($_POST['visibility_note'])) {
        update_post_meta($post_id, 'visibility_note', sanitize_textarea_field($_POST['visibility_note']));
    }
    if (isset($_POST['panel_traffic'])) {
        update_post_meta($post_id, 'panel_traffic', absint($_POST['panel_traffic']));
    }
}
add_action('save_post', 'zap_save_panel_meta_boxes');

/**
 * Ajouter des colonnes personnalisées à la liste des panneaux
 */
function zap_add_panel_admin_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        // Ajouter nos colonnes personnalisées après le titre
        if ($key === 'title') {
            $new_columns['panel_reference'] = 'Référence';
            $new_columns['panel_type'] = 'Type';
            $new_columns['panel_location'] = 'Localisation';
            $new_columns['panel_dimensions'] = 'Dimensions';
        }
    }
    
    return $new_columns;
}
add_filter('manage_poi_posts_columns', 'zap_add_panel_admin_columns');

/**
 * Remplir les colonnes personnalisées
 */
function zap_fill_panel_admin_columns($column, $post_id) {
    switch ($column) {
        case 'panel_reference':
            echo esc_html(get_post_meta($post_id, 'panel_reference', true));
            break;
            
        case 'panel_type':
            $panel_type = get_post_meta($post_id, 'panel_type', true);
            $types = array(
                'mural' => 'Mural',
                'pre-enseigne' => 'Pré-enseigne',
                '4x3' => '4x3',
                '8x3' => '8x3',
                'déroulant' => 'Déroulant',
                'totem' => 'Totem'
            );
            echo isset($types[$panel_type]) ? esc_html($types[$panel_type]) : '';
            break;
            
        case 'panel_location':
            $postal_code = get_post_meta($post_id, 'panel_postal_code', true);
            $city = get_post_meta($post_id, 'panel_city_name', true);
            echo $postal_code ? esc_html($postal_code) . ' ' : '';
            echo esc_html($city);
            break;
            
        case 'panel_dimensions':
            $width = get_post_meta($post_id, 'panel_width', true);
            $height = get_post_meta($post_id, 'panel_height', true);
            
            if ($width && $height) {
                echo esc_html($width) . ' × ' . esc_html($height) . ' cm';
            }
            break;
    }
}
add_action('manage_poi_posts_custom_column', 'zap_fill_panel_admin_columns', 10, 2);

/**
 * Rendre les colonnes triables
 */
function zap_sortable_panel_columns($columns) {
    $columns['panel_reference'] = 'panel_reference';
    $columns['panel_type'] = 'panel_type';
    $columns['panel_location'] = 'panel_city_name';
    
    return $columns;
}
add_filter('manage_edit-poi_sortable_columns', 'zap_sortable_panel_columns');

/**
 * Gérer le tri des colonnes personnalisées
 */
function zap_sort_panel_columns($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    switch ($orderby) {
        case 'panel_reference':
            $query->set('meta_key', 'panel_reference');
            $query->set('orderby', 'meta_value');
            break;
            
        case 'panel_type':
            $query->set('meta_key', 'panel_type');
            $query->set('orderby', 'meta_value');
            break;
            
        case 'panel_city_name':
            $query->set('meta_key', 'panel_city_name');
            $query->set('orderby', 'meta_value');
            break;
    }
}
add_action('pre_get_posts', 'zap_sort_panel_columns');

/**
 * Ajouter un script pour calculer automatiquement la surface
 */
function zap_panel_admin_scripts() {
    global $post_type;
    
    if ($post_type === 'poi') {
        wp_enqueue_script('zap-panel-admin-js', plugin_dir_url(__FILE__) . '../../assets/js/panel-admin.js', array('jquery'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'zap_panel_admin_scripts');