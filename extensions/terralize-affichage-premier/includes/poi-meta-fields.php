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
function terralize_ap_add_poi_meta_boxes() {
    // Metabox pour les détails techniques
    add_meta_box(
        'panel_details_meta_box',
        'Détails techniques du panneau',
        'terralize_ap_panel_details_callback',
        'poi',
        'normal',
        'high'
    );

    // Metabox pour la localisation
    add_meta_box(
        'panel_location_meta_box',
        'Localisation du panneau',
        'terralize_ap_panel_location_callback',
        'poi',
        'normal',
        'high'
    );

    // Metabox pour la visibilité
    add_meta_box(
        'panel_visibility_meta_box',
        'Visibilité du panneau',
        'terralize_ap_panel_visibility_callback',
        'poi',
        'normal',
        'high'
    );
    
    // Nouvelle metabox pour la photo du panneau
    add_meta_box(
        'panel_photo_meta_box',
        'Photo du panneau',
        'terralize_ap_panel_photo_callback',
        'poi',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'terralize_ap_add_poi_meta_boxes');

/**
 * Callback pour la metabox des détails techniques
 */
function terralize_ap_panel_details_callback($post) {
    // Nonce pour la sécurité
    wp_nonce_field('terralize_ap_panel_save', 'terralize_ap_panel_nonce');

    // Récupérer les valeurs existantes
    $reference = get_post_meta($post->ID, 'panel_reference', true);
    $type = get_post_meta($post->ID, 'panel_type', true);
    $support = get_post_meta($post->ID, 'panel_support', true);
    $width = get_post_meta($post->ID, 'panel_width', true);
    $height = get_post_meta($post->ID, 'panel_height', true);
    $format = get_post_meta($post->ID, 'panel_format', true);
    $format_standard = get_post_meta($post->ID, 'panel_format_standard', true);
    $disponibilite = get_post_meta($post->ID, 'panel_disponibilite', true);
    $annonceur = get_post_meta($post->ID, 'panel_annonceur', true);
    $date_fin = get_post_meta($post->ID, 'panel_date_fin', true);
    ?>

    <div class="terralize_ap-meta-field">
        <label for="panel_reference">Référence :</label>
        <input type="text" id="panel_reference" name="panel_reference" value="<?php echo esc_attr($reference); ?>" />
        <p class="description">Code ou référence unique du panneau</p>
    </div>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="panel_type">Type :</label>
            <select id="panel_type" name="panel_type">
                <option value="" <?php selected($type, ''); ?>>- Sélectionner -</option>
                <option value="DEROULANT" <?php selected($type, 'DEROULANT'); ?>>Déroulant</option>
                <option value="FIXE" <?php selected($type, 'FIXE'); ?>>Fixe</option>
                <option value="FIXE ECLAIRE" <?php selected($type, 'FIXE ECLAIRE'); ?>>Fixe Éclairé</option>
                <option value="TRIVISION" <?php selected($type, 'TRIVISION'); ?>>Trivision</option>
                <option value="DIGITAL" <?php selected($type, 'DIGITAL'); ?>>Digital</option>
            </select>
        </div>

        <div class="terralize_ap-meta-field">
            <label for="panel_support">Support :</label>
            <select id="panel_support" name="panel_support">
                <option value="" <?php selected($support, ''); ?>>- Sélectionner -</option>
                <option value="VITRINE" <?php selected($support, 'VITRINE'); ?>>Vitrine</option>
                <option value="VITRINE MURALE" <?php selected($support, 'VITRINE MURALE'); ?>>Vitrine Murale</option>
                <option value="TOLE" <?php selected($support, 'TOLE'); ?>>Tôle</option>
                <option value="LAME" <?php selected($support, 'LAME'); ?>>Lame</option>
                <option value="TRIVISION" <?php selected($support, 'TRIVISION'); ?>>Trivision</option>
            </select>
        </div>
    </div>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="panel_width">Largeur (cm) :</label>
            <input type="number" id="panel_width" name="panel_width" value="<?php echo esc_attr($width); ?>" min="0" step="1" />
        </div>

        <div class="terralize_ap-meta-field">
            <label for="panel_height">Hauteur (cm) :</label>
            <input type="number" id="panel_height" name="panel_height" value="<?php echo esc_attr($height); ?>" min="0" step="1" />
        </div>
    </div>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="panel_format">Format brut :</label>
            <input type="text" id="panel_format" name="panel_format" value="<?php echo esc_attr($format); ?>" placeholder="Ex: MOBILIER URBAIN" />
        </div>

        <div class="terralize_ap-meta-field">
            <label for="panel_format_standard">Format standard :</label>
            <select id="panel_format_standard" name="panel_format_standard">
                <option value="" <?php selected($format_standard, ''); ?>>- Sélectionner -</option>
                <option value="1,5M2" <?php selected($format_standard, '1,5M2'); ?>>1,5M2</option>
                <option value="2M2" <?php selected($format_standard, '2M2'); ?>>2M2</option>
                <option value="4M2" <?php selected($format_standard, '4M2'); ?>>4M2</option>
                <option value="6M2" <?php selected($format_standard, '6M2'); ?>>6M2</option>
                <option value="8M2" <?php selected($format_standard, '8M2'); ?>>8M2</option>
                <option value="12M2" <?php selected($format_standard, '12M2'); ?>>12M2</option>
                <option value="20M2" <?php selected($format_standard, '20M2'); ?>>20M2</option>
                <option value="40M2" <?php selected($format_standard, '40M2'); ?>>40M2</option>
            </select>
        </div>
    </div>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="panel_disponibilite">Disponibilité :</label>
            <select id="panel_disponibilite" name="panel_disponibilite">
                <option value="" <?php selected($disponibilite, ''); ?>>- Sélectionner -</option>
                <option value="Disponible" <?php selected($disponibilite, 'Disponible'); ?>>Disponible</option>
                <option value="En disponibilité" <?php selected($disponibilite, 'En disponibilité'); ?>>En disponibilité</option>
                <option value="Non disponible" <?php selected($disponibilite, 'Non disponible'); ?>>Non disponible</option>
            </select>
        </div>

        <div class="terralize_ap-meta-field">
            <label for="panel_annonceur">Annonceur :</label>
            <input type="text" id="panel_annonceur" name="panel_annonceur" value="<?php echo esc_attr($annonceur); ?>" />
        </div>
    </div>

    <div class="terralize_ap-meta-field">
        <label for="panel_date_fin">Date de fin d'engagement :</label>
        <input type="text" id="panel_date_fin" name="panel_date_fin" class="date-picker" value="<?php echo esc_attr($date_fin); ?>" placeholder="JJ/MM/AAAA" />
        <p class="description">Date de fin du contrat</p>
    </div>

    <?php
}

/**
 * Callback pour la metabox de localisation
 */
function terralize_ap_panel_location_callback($post) {
    // Récupérer les valeurs existantes
    $address = get_post_meta($post->ID, 'panel_address', true);
    $postal_code = get_post_meta($post->ID, 'panel_postal_code', true);
    $city_name = get_post_meta($post->ID, 'panel_city_name', true);
    $departement = get_post_meta($post->ID, 'panel_departement', true);
    $code_dept = get_post_meta($post->ID, 'panel_code_departement', true);
    $region = get_post_meta($post->ID, 'panel_region', true);
    $latitude = get_post_meta($post->ID, 'panel_latitude', true);
    $longitude = get_post_meta($post->ID, 'panel_longitude', true);
    ?>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="panel_address">Adresse :</label>
            <input type="text" id="panel_address" name="panel_address" value="<?php echo esc_attr($address); ?>" />
        </div>
    </div>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="panel_postal_code">Code postal :</label>
            <input type="text" id="panel_postal_code" name="panel_postal_code" value="<?php echo esc_attr($postal_code); ?>" />
        </div>

        <div class="terralize_ap-meta-field">
            <label for="panel_city_name">Ville :</label>
            <input type="text" id="panel_city_name" name="panel_city_name" value="<?php echo esc_attr($city_name); ?>" />
        </div>
    </div>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="panel_departement">Département :</label>
            <input type="text" id="panel_departement" name="panel_departement" value="<?php echo esc_attr($departement); ?>" readonly />
        </div>

        <div class="terralize_ap-meta-field">
            <label for="panel_code_departement">Code département :</label>
            <input type="text" id="panel_code_departement" name="panel_code_departement" value="<?php echo esc_attr($code_dept); ?>" readonly />
        </div>

        <div class="terralize_ap-meta-field">
            <label for="panel_region">Région :</label>
            <input type="text" id="panel_region" name="panel_region" value="<?php echo esc_attr($region); ?>" readonly />
        </div>
    </div>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="panel_latitude">Latitude :</label>
            <input type="text" id="panel_latitude" name="panel_latitude" value="<?php echo esc_attr($latitude); ?>" placeholder="Ex: 50.23929" />
            <p class="description">Format décimal avec virgule ou point (ex: 50,23929)</p>
        </div>

        <div class="terralize_ap-meta-field">
            <label for="panel_longitude">Longitude :</label>
            <input type="text" id="panel_longitude" name="panel_longitude" value="<?php echo esc_attr($longitude); ?>" placeholder="Ex: 2.65531" />
            <p class="description">Format décimal avec virgule ou point (ex: 2,65531)</p>
        </div>
    </div>

    <div id="map_container" style="height: 300px; margin-top: 20px;">
        <div id="terralize_ap_map" style="height: 100%; width: 100%;"></div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Fonction pour mettre à jour les infos de département et région
        function updateDepartmentInfo() {
            var postalCode = $('#panel_postal_code').val();
            
            if (postalCode && postalCode.length >= 2) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'terralize_ap_get_dept_info',
                        postal_code: postalCode,
                        nonce: '<?php echo wp_create_nonce('terralize_ap_get_dept_info_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#panel_departement').val(response.data.departement);
                            $('#panel_code_departement').val(response.data.code_departement);
                            $('#panel_region').val(response.data.region);
                        }
                    }
                });
            }
        }

        // Déclencher la mise à jour lors de la modification du code postal
        $('#panel_postal_code').on('change', updateDepartmentInfo);

        // Initialiser la carte après chargement complet de la page
        if (typeof L !== 'undefined') {
            var lat = $('#panel_latitude').val() || 46.603354;
            var lng = $('#panel_longitude').val() || 1.888334;
            
            lat = parseFloat(lat.replace(',', '.'));
            lng = parseFloat(lng.replace(',', '.'));
            
            var map = L.map('terralize_ap_map').setView([lat, lng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            var marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);
            
            // Mettre à jour les coordonnées lors du déplacement du marqueur
            marker.on('dragend', function(e) {
                var position = marker.getLatLng();
                $('#panel_latitude').val(position.lat.toFixed(5));
                $('#panel_longitude').val(position.lng.toFixed(5));
            });
            
            // Mettre à jour le marqueur lors de la modification manuelle des coordonnées
            function updateMarkerPosition() {
                var lat = parseFloat($('#panel_latitude').val().replace(',', '.')) || 46.603354;
                var lng = parseFloat($('#panel_longitude').val().replace(',', '.')) || 1.888334;
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 13);
            }
            
            $('#panel_latitude, #panel_longitude').on('change', updateMarkerPosition);
            
            // Rechercher l'adresse et positionner le marqueur
            $('#panel_address, #panel_city_name').on('change', function() {
                var address = $('#panel_address').val();
                var city = $('#panel_city_name').val();
                var postal = $('#panel_postal_code').val();
                
                if (address && city) {
                    var searchQuery = address + ', ' + postal + ' ' + city + ', France';
                    
                    $.ajax({
                        url: 'https://nominatim.openstreetmap.org/search',
                        type: 'GET',
                        data: {
                            q: searchQuery,
                            format: 'json',
                            limit: 1
                        },
                        success: function(data) {
                            if (data && data.length > 0) {
                                var lat = parseFloat(data[0].lat);
                                var lng = parseFloat(data[0].lon);
                                
                                $('#panel_latitude').val(lat.toFixed(5));
                                $('#panel_longitude').val(lng.toFixed(5));
                                
                                marker.setLatLng([lat, lng]);
                                map.setView([lat, lng], 16);
                            }
                        }
                    });
                }
            });
        }
    });
    </script>

    <style>
        .terralize_ap-meta-field-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .terralize_ap-meta-field {
            flex: 1;
            margin-bottom: 15px;
        }
        .terralize_ap-meta-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .terralize_ap-meta-field input,
        .terralize_ap-meta-field select {
            width: 100%;
        }
        .terralize_ap-meta-field .description {
            font-size: 0.85em;
            font-style: italic;
            color: #666;
            margin-top: 5px;
        }
    </style>
    <?php
}

/**
 * Callback pour la metabox de visibilité
 */
function terralize_ap_panel_visibility_callback($post) {
    // Récupérer les valeurs existantes
    $visibility_from = get_post_meta($post->ID, 'visibility_from', true);
    $visibility_to = get_post_meta($post->ID, 'visibility_to', true);
    $visibility_angle = get_post_meta($post->ID, 'visibility_angle', true);
    $visibility_distance = get_post_meta($post->ID, 'visibility_distance', true);
    $visibility_note = get_post_meta($post->ID, 'visibility_note', true);
    $panel_traffic = get_post_meta($post->ID, 'panel_traffic', true);
    ?>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="visibility_from">Visible en venant de :</label>
            <input type="text" id="visibility_from" name="visibility_from" value="<?php echo esc_attr($visibility_from); ?>" placeholder="Ex: DOULLENS" />
        </div>

        <div class="terralize_ap-meta-field">
            <label for="visibility_to">Visible en allant à :</label>
            <input type="text" id="visibility_to" name="visibility_to" value="<?php echo esc_attr($visibility_to); ?>" placeholder="Ex: ARRAS" />
        </div>
    </div>

    <div class="terralize_ap-meta-field-row">
        <div class="terralize_ap-meta-field">
            <label for="visibility_angle">Angle de visibilité (degrés) :</label>
            <input type="text" id="visibility_angle" name="visibility_angle" value="<?php echo esc_attr($visibility_angle); ?>" min="0" max="360" placeholder="Ex: 90" />
            <p class="description">Angle de visibilité en degrés (ex: 90)</p>
        </div>

        <div class="terralize_ap-meta-field">
            <label for="visibility_distance">Distance de visibilité (m) :</label>
            <input type="number" id="visibility_distance" name="visibility_distance" value="<?php echo esc_attr($visibility_distance); ?>" min="0" placeholder="Ex: 50" />
        </div>
    </div>

    <div class="terralize_ap-meta-field">
        <label for="panel_traffic">Traffic journalier estimé :</label>
        <input type="number" id="panel_traffic" name="panel_traffic" value="<?php echo esc_attr($panel_traffic); ?>" min="0" step="1" placeholder="Ex: 10000" />
        <p class="description">Nombre de passages quotidiens estimés devant le panneau</p>
    </div>

    <div class="terralize_ap-meta-field">
        <label for="visibility_note">Notes sur la visibilité :</label>
        <textarea id="visibility_note" name="visibility_note" rows="3"><?php echo esc_textarea($visibility_note); ?></textarea>
    </div>

    <?php
}

/**
 * Callback pour la metabox de la photo du panneau
 */
function terralize_ap_panel_photo_callback($post) {
    // Récupérer la valeur existante
    $photo_id = get_post_meta($post->ID, 'panel_photo_id', true);
    
    ?>
    <div class="terralize_ap-meta-field">
        <div id="panel_photo_container">
            <?php if ($photo_id) : 
                $img_url = wp_get_attachment_image_url($photo_id, 'medium');
            ?>
                <div class="panel-photo-preview">
                    <img src="<?php echo esc_url($img_url); ?>" style="max-width:100%; height:auto; margin-bottom:10px;" />
                    <a href="#" class="remove-panel-photo button"><?php _e('Supprimer la photo', 'terralize-ap'); ?></a>
                </div>
            <?php else : ?>
                <p><?php _e('Aucune photo associée à ce panneau.', 'terralize-ap'); ?></p>
            <?php endif; ?>
        </div>
        <input type="hidden" id="panel_photo_id" name="panel_photo_id" value="<?php echo esc_attr($photo_id); ?>" />
        <p>
            <button type="button" class="button" id="upload_panel_photo_button">
                <?php echo $photo_id ? __('Changer la photo', 'terralize-ap') : __('Ajouter une photo', 'terralize-ap'); ?>
            </button>
        </p>
        <p class="description">Photo du panneau d'affichage</p>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var file_frame;
        
        $('#upload_panel_photo_button').on('click', function(e) {
            e.preventDefault();
            
            if (file_frame) {
                file_frame.open();
                return;
            }
            
            file_frame = wp.media.frames.file_frame = wp.media({
                title: '<?php _e('Sélectionner ou téléverser une photo', 'terralize-ap'); ?>',
                button: {
                    text: '<?php _e('Utiliser cette photo', 'terralize-ap'); ?>'
                },
                multiple: false
            });
            
            file_frame.on('select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();
                $('#panel_photo_id').val(attachment.id);
                
                var preview = '<div class="panel-photo-preview">' +
                              '<img src="' + attachment.url + '" style="max-width:100%; height:auto; margin-bottom:10px;" />' +
                              '<a href="#" class="remove-panel-photo button"><?php _e('Supprimer la photo', 'terralize-ap'); ?></a>' +
                              '</div>';
                              
                $('#panel_photo_container').html(preview);
            });
            
            file_frame.open();
        });
        
        $(document).on('click', '.remove-panel-photo', function(e) {
            e.preventDefault();
            $('#panel_photo_id').val('');
            $('#panel_photo_container').html('<p><?php _e('Aucune photo associée à ce panneau.', 'terralize-ap'); ?></p>');
        });
    });
    </script>
    <?php
}

/**
 * Sauvegarder les données des meta boxes
 */
function terralize_ap_save_panel_meta_boxes($post_id) {
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
    if (!isset($_POST['terralize_ap_panel_nonce']) || !wp_verify_nonce($_POST['terralize_ap_panel_nonce'], 'terralize_ap_panel_save')) {
        return;
    }
    
    // Liste complète des champs à sauvegarder
    $meta_fields = array(
        // Détails techniques
        'panel_reference',
        'panel_type',
        'panel_support',
        'panel_width',
        'panel_height',
        'panel_format',
        'panel_format_standard',
        'panel_disponibilite',
        'panel_annonceur',
        'panel_date_fin',
        
        // Localisation
        'panel_address',
        'panel_postal_code',
        'panel_city_name',
        'panel_departement',
        'panel_code_departement',
        'panel_region',
        'panel_latitude',
        'panel_longitude',
        
        // Visibilité
        'visibility_from',
        'visibility_to',
        'visibility_angle',
        'visibility_distance',
        'visibility_note',
        'panel_traffic',
        
        // Photo
        'panel_photo_id'
    );
    
    // Sauvegarder chaque champ s'il est présent
    foreach ($meta_fields as $field) {
        if (isset($_POST[$field])) {
            // Appliquer des traitements spécifiques selon le champ
            switch ($field) {
                case 'panel_latitude':
                case 'panel_longitude':
                    // S'assurer que les coordonnées sont au format décimal avec point
                    $value = str_replace(',', '.', sanitize_text_field($_POST[$field]));
                    update_post_meta($post_id, $field, $value);
                    break;
                    
                case 'panel_width':
                case 'panel_height':
                case 'visibility_distance':
                case 'panel_traffic':
                    // Convertir en nombre entier
                    update_post_meta($post_id, $field, absint($_POST[$field]));
                    break;
                    
                case 'visibility_note':
                    // Pour les zones de texte
                    update_post_meta($post_id, $field, sanitize_textarea_field($_POST[$field]));
                    break;
                    
                default:
                    // Traitement par défaut pour les champs texte
                    update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
                    break;
            }
        }
    }
    
    // Traitement spécifique pour le code postal: remplir automatiquement le département et la région
    if (isset($_POST['panel_postal_code'])) {
        $postal_code = sanitize_text_field($_POST['panel_postal_code']);
        if (!empty($postal_code)) {
            // Utiliser la fonction existante
            $loc_info = terralize_ap_get_dept_region_from_postal($postal_code);
            
            // Mettre à jour le département et la région si la fonction existe
            if (function_exists('terralize_ap_get_dept_region_from_postal')) {
                update_post_meta($post_id, 'panel_departement', $loc_info['departement']);
                update_post_meta($post_id, 'panel_code_departement', $loc_info['code_departement']);
                update_post_meta($post_id, 'panel_region', $loc_info['region']);
            }
        }
    }
    
    // Créer ou mettre à jour le point GeoJSON si les coordonnées sont définies
    if (isset($_POST['panel_latitude']) && isset($_POST['panel_longitude']) && !empty($_POST['panel_latitude']) && !empty($_POST['panel_longitude'])) {
        $lat = str_replace(',', '.', sanitize_text_field($_POST['panel_latitude']));
        $lng = str_replace(',', '.', sanitize_text_field($_POST['panel_longitude']));
        
        $point = array(
            'type' => 'Point',
            'coordinates' => array(floatval($lng), floatval($lat)) // GeoJSON utilise [longitude, latitude]
        );
        
        update_post_meta($post_id, 'poi_geojson', wp_json_encode($point));
    }
    
    // Sauvegarder l'ID de la photo
    if (isset($_POST['panel_photo_id'])) {
        update_post_meta($post_id, 'panel_photo_id', absint($_POST['panel_photo_id']));
    }
}
add_action('save_post', 'terralize_ap_save_panel_meta_boxes');

/**
 * Ajouter des colonnes personnalisées à la liste des panneaux
 */
function terralize_ap_add_panel_admin_columns($columns) {
    $new_columns = array();
    
    // Insérer la colonne photo après la case à cocher
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        if ($key === 'cb') {
            $new_columns['panel_photo'] = __('Photo', 'terralize-ap');
        }
    }
    
    // Les autres colonnes personnalisées
    $new_columns['panel_reference'] = __('Référence', 'terralize-ap');
    $new_columns['panel_type'] = __('Type', 'terralize-ap');
    $new_columns['panel_format'] = __('Format', 'terralize-ap');
    $new_columns['panel_city'] = __('Ville', 'terralize-ap');
    $new_columns['panel_dept'] = __('Département', 'terralize-ap');
    $new_columns['panel_disponibilite'] = __('Disponibilité', 'terralize-ap');
    
    return $new_columns;
}
add_filter('manage_poi_posts_columns', 'terralize_ap_add_panel_admin_columns');

/**
 * Remplir les colonnes personnalisées
 */
function terralize_ap_fill_panel_admin_columns($column, $post_id) {
    switch ($column) {
        case 'panel_photo':
            $photo_id = get_post_meta($post_id, 'panel_photo_id', true);
            if ($photo_id) {
                $img_url = wp_get_attachment_image_url($photo_id, 'thumbnail');
                echo '<a href="' . esc_url(get_edit_post_link($post_id)) . '"><img src="' . esc_url($img_url) . '" style="max-width:50px; height:auto;" /></a>';
            } else {
                echo '<span class="dashicons dashicons-camera" style="color:#ddd; font-size:24px; width:24px; height:24px;"></span>';
            }
            break;
        
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
            
        case 'panel_format':
            $format = get_post_meta($post_id, 'panel_format', true);
            echo esc_html($format);
            break;
            
        case 'panel_city':
            $city = get_post_meta($post_id, 'panel_city_name', true);
            echo esc_html($city);
            break;
            
        case 'panel_dept':
            $departement = get_post_meta($post_id, 'panel_departement', true);
            echo esc_html($departement);
            break;
            
        case 'panel_disponibilite':
            $disponibilite = get_post_meta($post_id, 'panel_disponibilite', true);
            echo esc_html($disponibilite);
            break;
    }
}
add_action('manage_poi_posts_custom_column', 'terralize_ap_fill_panel_admin_columns', 10, 2);

/**
 * Rendre les colonnes triables
 */
function terralize_ap_sortable_panel_columns($columns) {
    $columns['panel_reference'] = 'panel_reference';
    $columns['panel_type'] = 'panel_type';
    $columns['panel_city_name'] = 'panel_city_name';
    
    return $columns;
}
add_filter('manage_edit-poi_sortable_columns', 'terralize_ap_sortable_panel_columns');

/**
 * Gérer le tri des colonnes personnalisées
 */
function terralize_ap_sort_panel_columns($query) {
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
add_action('pre_get_posts', 'terralize_ap_sort_panel_columns');

/**
 * Ajouter un script pour calculer automatiquement la surface
 */
function terralize_ap_panel_admin_scripts() {
    global $post_type;
    
    if ($post_type === 'poi') {
        wp_enqueue_script('terralize_ap-panel-admin-js', plugin_dir_url(__FILE__) . '../../assets/js/panel-admin.js', array('jquery'), '1.0', true);
    }
    
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'terralize_ap_panel_admin_scripts');

/**
 * Fonction AJAX pour récupérer les informations de département et région à partir d'un code postal
 */
function terralize_ap_ajax_get_dept_info() {
    // Vérifier le nonce
    check_ajax_referer('terralize_ap_get_dept_info_nonce', 'nonce');
    
    // Récupérer le code postal
    $postal_code = isset($_POST['postal_code']) ? sanitize_text_field($_POST['postal_code']) : '';
    
    if (empty($postal_code)) {
        wp_send_json_error(array('message' => 'Code postal manquant.'));
        return;
    }
    
    // Utiliser la fonction existante pour obtenir les informations
    $loc_info = terralize_ap_get_dept_region_from_postal($postal_code);
    
    // Renvoyer les données
    wp_send_json_success($loc_info);
}
add_action('wp_ajax_terralize_ap_get_dept_info', 'terralize_ap_ajax_get_dept_info');

/**
 * Enregistrer les scripts et styles nécessaires
 */
function terralize_ap_enqueue_poi_admin_scripts() {
    $screen = get_current_screen();
    
    // N'enregistrer les scripts que sur la page d'édition des POI
    if ($screen && $screen->post_type === 'poi') {
        // Leaflet pour la carte
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
        
        // Styles personnalisés
        
        // Script personnalisé pour la gestion des dates
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        
        // Script d'initialisation des éléments
        wp_enqueue_script('terralize_ap-poi-admin-js', plugin_dir_url(dirname(__FILE__)) . 'assets/js/poi-admin.js', array('jquery', 'jquery-ui-datepicker'), '1.0.0', true);
    }
}
add_action('admin_enqueue_scripts', 'terralize_ap_enqueue_poi_admin_scripts');