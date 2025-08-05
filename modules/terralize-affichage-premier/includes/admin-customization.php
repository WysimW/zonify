<?php
/**
 * Personnalisation de l'administration pour Affichage Premier
 * Adaptation de l'interface Terralize pour le client Affichage Premier
 */

// Si ce fichier est appelé directement, abandon
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modifier le texte du tableau de bord principal de Terralize
 */
function terralize_ap_customize_dashboard_text($content) {
    // Texte personnalisé pour le client Affichage Premier
    $new_content = '
    <div class="terralize-content">
        <section class="terralize-section">
            <h2>Bienvenue sur Terralize pour Affichage Premier</h2>
            <p>
                Cette interface est personnalisée pour la gestion de vos panneaux d\'affichage.
                Utilisez les différentes sections du menu pour gérer votre parc d\'affichage.
            </p>
        </section>
        
        <section class="terralize-section blueprint-grid">
            <h3>Guide rapide</h3>
            <div class="blueprint-note">
                <ol>
                    <li>
                        <strong>Gérer les panneaux :</strong> Pour ajouter ou modifier des panneaux d\'affichage,
                        utilisez la section <a href="' . admin_url('edit.php?post_type=poi') . '">Panneaux d\'affichage</a>.
                    </li>
                    <li>
                        <strong>Visualiser la carte :</strong> La carte interactive vous permet de voir l\'ensemble de vos panneaux
                        et de les filtrer par type ou par ville.
                    </li>
                    <li>
                        <strong>Ajouter un nouveau panneau :</strong> Depuis la carte, vous pouvez directement positionner
                        un nouveau panneau en cliquant sur l\'emplacement souhaité.
                    </li>
                </ol>
            </div>
        </section>
    </div>';
    
    return $new_content;
}
add_filter('terralize_main_page_content', 'terralize_ap_customize_dashboard_text');

/**
 * Personnaliser le logo et les textes en en-tête
 */
function terralize_ap_customize_header() {
    ?>
    <style>
        .terralize-title {
            color: var(--terralize-secondary) !important;
            font-weight: 600 !important;
            letter-spacing: -0.5px !important;
        }
        .terralize-banner {
            border-bottom: 2px solid var(--terralize-secondary) !important;
            box-shadow: 0 2px 10px var(--terralize-shadow) !important;
            border-radius: 8px 8px 0 0 !important;
        }
        .terralize-header {
            border-bottom: 2px solid var(--terralize-secondary) !important;
        }
        .terralize_ap-version-badge {
            background-color: var(--terralize-secondary);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            margin-left: 10px;
            font-weight: 500;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {
            // Modifier le titre pour ajouter "pour Affichage Premier"
            $('.terralize-title').each(function() {
                var currentTitle = $(this).text();
                if (!currentTitle.includes('Affichage Premier')) {
                    $(this).html(currentTitle + ' <span class="terralize_ap-version-badge">Affichage Premier</span>');
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'terralize_ap_customize_header');

/**
 * Personnaliser le menu d'administration
 */
function terralize_ap_customize_admin_menu() {
    global $menu, $submenu;
    
    // Renommer le sous-menu "Points d'intérêt" en "Panneaux d'affichage"
    if (isset($submenu['terralize']) && is_array($submenu['terralize'])) {
        foreach ($submenu['terralize'] as $key => $item) {
            // Trouver le sous-menu Points d'Intérêt
            if (isset($item[2]) && $item[2] === 'edit.php?post_type=poi') {
                $submenu['terralize'][$key][0] = 'Panneaux d\'affichage';
            }
            
            // Modifier le texte "Points d'intérêt" dans le titre de la page
            if (isset($item[2]) && $item[2] === 'terralize_poi') {
                $submenu['terralize'][$key][0] = 'Carte des panneaux';
            }
        }
    }
}
add_action('admin_menu', 'terralize_ap_customize_admin_menu', 999);

/**
 * Personnaliser les éléments d'interface pour Affichage Premier
 */
function terralize_ap_customize_admin_ui() {
    echo '<style>
        /* Variables globales pour unifier le style */
        :root {
            --terralize_ap-primary: #70c141;
            --terralize_ap-secondary: #E04D00;
            --terralize_ap-dark: #5da834;
            --terralize_ap-text: #333f4d;
        }
        
        /* Base de style blueprint pour Affichage Premier */
        .terralize-section h2, 
        .terralize-section h3 {
            color: var(--terralize_ap-primary);
            position: relative;
            padding-bottom: 15px;
        }
        
        .terralize-section h2::after,
        .terralize-section h3::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: var(--terralize_ap-primary);
            border-radius: 3px;
        }
        
        /* Personnalisation des boutons */
        .terralize-content .button-primary,
        .wp-core-ui .button-primary,
        .terralize_ap-btn-primary {
            background: var(--terralize_ap-primary);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            text-shadow: none;
            box-shadow: 0 2px 4px var(--terralize-shadow);
            transition: all 0.2s ease;
        }
        
        .terralize-content .button-primary:hover,
        .wp-core-ui .button-primary:hover,
        .terralize_ap-btn-primary:hover {
            background: var(--terralize_ap-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px var(--terralize-shadow);
        }
        
        /* Badge Affichage Premier */
        .terralize_ap-badge {
            display: inline-block;
            background: var(--terralize_ap-primary);
            color: white;
            font-size: 0.8em;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
            margin-left: 10px;
        }
        
        /* Carte et cartographie */
        #map {
            border: none !important;
            border-radius: 8px !important;
            margin-top: 20px !important;
            height: 65vh !important;
            box-shadow: 0 4px 15px var(--terralize-shadow);
        }
        
        /* Tableau de données des panneaux */
        .wp-list-table {
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--terralize-border);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px var(--terralize-shadow);
        }
        
        .wp-list-table th {
            background-color: var(--terralize-light);
            border-bottom: 2px solid var(--terralize_ap-primary);
            padding: 12px 15px;
            font-weight: 600;
            color: var(--terralize-dark);
        }
        
        .wp-list-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--terralize-border);
        }
        
        .wp-list-table tr:hover td {
            background-color: rgba(112, 193, 65, 0.05);
        }
        
        /* Formulaires des métaboxes */
        .postbox {
            border: none;
            box-shadow: 0 3px 10px var(--terralize-shadow);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .postbox .hndle {
            border-bottom: 2px solid var(--terralize_ap-primary);
            padding: 12px 15px;
            font-weight: 600;
        }
        
        .postbox .inside {
            padding: 15px;
        }
        
        .form-field label {
            font-weight: 600;
            color: var(--terralize-dark);
            display: block;
            margin-bottom: 8px;
        }
        
        /* Dashboard widget */
        .terralize_ap-dashboard-stats {
            background-color: var(--terralize-light);
            border-left: 3px solid var(--terralize_ap-primary);
            padding: 15px;
            border-radius: 0 8px 8px 0;
        }
        
        .terralize_ap-dashboard-stats ul {
            margin-left: 20px;
        }
        
        .terralize_ap-dashboard-stats p strong {
            color: var(--terralize-dark);
        }
        
        /* Style blueprint pour la page d\'aide */
        .blueprint-grid {
            background-size: 20px 20px;
            background-image:
                linear-gradient(to right, rgba(112, 193, 65, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(112, 193, 65, 0.1) 1px, transparent 1px);
            position: relative;
        }
        
        .blueprint-detail {
            border-left: 2px solid var(--terralize_ap-primary);
            padding-left: 15px;
            margin: 15px 0;
        }
        
        .blueprint-note {
            background-color: rgba(112, 193, 65, 0.05);
            border-left: 3px solid var(--terralize_ap-primary);
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }
    </style>';
}
add_action('admin_head', 'terralize_ap_customize_admin_ui');

/**
 * Ajouter les pages personnalisées (aide + modules + configuration)
 */
function terralize_ap_add_custom_pages() {
    // Page d'aide
    add_submenu_page(
        'terralize',
        'Aide Affichage Premier',
        'Aide',
        'manage_options',
        'terralize_ap_help',
        'terralize_ap_help_page_content'
    );
    
    // Page des modules (pour conserver l'accès)
    add_submenu_page(
        'terralize',
        'Modules Terralize',
        'Modules',
        'manage_options',
        'terralize_modules',
        'terralize_modules_page'
    );
    
    // Page de configuration Affichage Premier
    add_submenu_page(
        'terralize',
        'Configuration Affichage Premier',
        'Configuration AP',
        'manage_options',
        'terralize_ap_config',
        'terralize_ap_config_page_content'
    );
}
add_action('admin_menu', 'terralize_ap_add_custom_pages');

/**
 * Fonction pour gérer l'upload de fichier SVG
 */
function terralize_ap_handle_icon_upload($file) {
    // Vérifications de sécurité
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'error' => 'Erreur lors de l\'upload du fichier.');
    }
    
    // Vérifier l'extension du fichier
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_extension !== 'svg') {
        return array('success' => false, 'error' => 'Seuls les fichiers SVG sont autorisés.');
    }
    
    // Vérifier la taille du fichier (max 1MB)
    if ($file['size'] > 1048576) {
        return array('success' => false, 'error' => 'Le fichier est trop volumineux (max 1MB).');
    }
    
    // Vérifier le type MIME
    $allowed_mime_types = array('image/svg+xml', 'text/plain');
    $file_mime = mime_content_type($file['tmp_name']);
    if (!in_array($file_mime, $allowed_mime_types)) {
        // Vérification supplémentaire en lisant le contenu du fichier
        $file_content = file_get_contents($file['tmp_name']);
        if (strpos($file_content, '<svg') === false) {
            return array('success' => false, 'error' => 'Le fichier ne semble pas être un SVG valide.');
        }
    }
    
    // Créer le répertoire de destination s'il n'existe pas
    $upload_dir = wp_upload_dir();
    $icon_dir = $upload_dir['basedir'] . '/terralize-icons/';
    if (!file_exists($icon_dir)) {
        wp_mkdir_p($icon_dir);
    }
    
    // Générer un nom de fichier unique
    $file_name = 'poi-icon-' . time() . '.svg';
    $file_path = $icon_dir . $file_name;
    
    // Déplacer le fichier uploadé
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $file_url = $upload_dir['baseurl'] . '/terralize-icons/' . $file_name;
        return array('success' => true, 'url' => $file_url);
    } else {
        return array('success' => false, 'error' => 'Impossible de sauvegarder le fichier.');
    }
}

/**
 * Contenu de la page de configuration Affichage Premier
 */
function terralize_ap_config_page_content() {
    // Traitement du formulaire
    if (isset($_POST['terralize_ap_config_submit']) && wp_verify_nonce($_POST['terralize_ap_config_nonce'], 'terralize_ap_config')) {
        
        // Gestion de l'upload de fichier SVG
        $uploaded_icon_url = '';
        if (!empty($_FILES['poi_icon_file']['name'])) {
            $upload_result = terralize_ap_handle_icon_upload($_FILES['poi_icon_file']);
            if ($upload_result['success']) {
                $uploaded_icon_url = $upload_result['url'];
                update_option('terralize_ap_poi_icon_url', $uploaded_icon_url);
                echo '<div class="notice notice-success is-dismissible"><p>Icône SVG uploadée avec succès : ' . basename($uploaded_icon_url) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Erreur lors de l\'upload : ' . $upload_result['error'] . '</p></div>';
            }
        } else {
            // Si pas d'upload, utiliser l'URL saisie manuellement
            update_option('terralize_ap_poi_icon_url', sanitize_text_field($_POST['poi_icon_url']));
        }
        
        update_option('terralize_ap_poi_icon_size', intval($_POST['poi_icon_size']));
        update_option('terralize_ap_poi_icon_anchor_x', intval($_POST['poi_icon_anchor_x']));
        update_option('terralize_ap_poi_icon_anchor_y', intval($_POST['poi_icon_anchor_y']));
        
        if (empty($uploaded_icon_url)) {
            echo '<div class="notice notice-success is-dismissible"><p>Configuration sauvegardée avec succès !</p></div>';
        }
    }
    
    // Récupérer les valeurs actuelles
    $poi_icon_url = get_option('terralize_ap_poi_icon_url', '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/sucette_panneau_pin.svg');
    $poi_icon_size = get_option('terralize_ap_poi_icon_size', 30);
    $poi_icon_anchor_x = get_option('terralize_ap_poi_icon_anchor_x', 15);
    $poi_icon_anchor_y = get_option('terralize_ap_poi_icon_anchor_y', 40);
    
    $icon_url = plugin_dir_url(dirname(__FILE__)) . '../assets/icons/icon.png';
    ?>
    <div class="wrap">
        <div class="terralize-header">
            <div class="terralize-header-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Terralize Icon" class="terralize-icon" />
                <h1 class="terralize-title">Configuration Affichage Premier</h1>
            </div>
        </div>
        
        <div class="terralize-content">
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('terralize_ap_config', 'terralize_ap_config_nonce'); ?>
                
                <section class="terralize-section">
                    <h2>Configuration des icônes de panneaux</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="poi_icon_file">Upload d'icône SVG</label>
                            </th>
                            <td class="upload-zone">
                                <input type="file" 
                                       id="poi_icon_file" 
                                       name="poi_icon_file" 
                                       accept=".svg,image/svg+xml" 
                                       class="regular-text" />
                                <p class="description">
                                    Uploadez un fichier SVG pour remplacer l'icône actuelle (max 1MB).<br>
                                    <strong>Recommandé :</strong> Utilisez cette méthode pour une meilleure sécurité.<br>
                                    <em>Vous pouvez également glisser-déposer votre fichier SVG ici.</em>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="border-top: 2px solid #ddd; padding-top: 20px;">
                                <label for="poi_icon_url">OU URL de l'icône SVG</label>
                            </th>
                            <td style="border-top: 2px solid #ddd; padding-top: 20px;">
                                <input type="text" 
                                       id="poi_icon_url" 
                                       name="poi_icon_url" 
                                       value="<?php echo esc_attr($poi_icon_url); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    Alternativement, saisissez l'URL complète vers un fichier SVG existant.<br>
                                    Exemple : /wp-content/plugins/zone-commercial-pluginwp/assets/svg/mon_icone.svg<br>
                                    <em>Note : L'upload de fichier a la priorité sur cette URL.</em>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="poi_icon_size">Taille de l'icône</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="poi_icon_size" 
                                       name="poi_icon_size" 
                                       value="<?php echo esc_attr($poi_icon_size); ?>" 
                                       min="10" 
                                       max="100" 
                                       step="1" />
                                <p class="description">Taille en pixels (largeur, la hauteur sera calculée proportionnellement)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="poi_icon_anchor_x">Point d'ancrage X</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="poi_icon_anchor_x" 
                                       name="poi_icon_anchor_x" 
                                       value="<?php echo esc_attr($poi_icon_anchor_x); ?>" 
                                       min="0" 
                                       max="100" 
                                       step="1" />
                                <p class="description">Position horizontale du point d'ancrage (généralement la moitié de la largeur)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="poi_icon_anchor_y">Point d'ancrage Y</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="poi_icon_anchor_y" 
                                       name="poi_icon_anchor_y" 
                                       value="<?php echo esc_attr($poi_icon_anchor_y); ?>" 
                                       min="0" 
                                       max="100" 
                                       step="1" />
                                <p class="description">Position verticale du point d'ancrage (généralement la hauteur totale pour une épingle)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="blueprint-note">
                        <h4>Aperçu de l'icône</h4>
                        <div id="icon-preview-container" style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 4px; margin: 10px 0;">
                            <img id="icon-preview" 
                                 src="<?php echo esc_url($poi_icon_url); ?>" 
                                 alt="Aperçu de l'icône" 
                                 style="max-width: <?php echo esc_attr($poi_icon_size); ?>px; height: auto; border: 1px solid #ddd; border-radius: 4px;" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                            <p id="icon-preview-error" style="display: none; color: #999;">Impossible de charger l'aperçu de l'icône</p>
                            <div id="icon-preview-info" style="margin-top: 10px; font-size: 12px; color: #666;">
                                <p>Taille affichée : <?php echo esc_attr($poi_icon_size); ?>px</p>
                                <p>URL actuelle : <?php echo esc_html(basename($poi_icon_url)); ?></p>
                            </div>
                        </div>
                    </div>
                </section>
                
                <?php submit_button('Enregistrer la configuration', 'primary', 'terralize_ap_config_submit'); ?>
            </form>
        </div>
    </div>
    
    <style>
        .terralize-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-table th {
            width: 200px;
            padding: 15px 10px 15px 0;
        }
        
        .form-table td {
            padding: 15px 10px;
        }
        
        .regular-text {
            width: 400px;
        }
        
        input[type="number"] {
            width: 100px;
        }
        
        #poi_icon_file {
            padding: 8px;
            border: 2px dashed #ddd;
            border-radius: 4px;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        
        #poi_icon_file:hover {
            border-color: #70c141;
            background: #f0f8ff;
        }
        
        #poi_icon_file:focus {
            border-color: #70c141;
            outline: none;
            box-shadow: 0 0 5px rgba(112, 193, 65, 0.3);
        }
        
        .upload-zone {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .upload-zone.dragover {
            border-color: #70c141;
            background: #f0f8ff;
        }
    </style>
    
    <script>
        jQuery(document).ready(function($) {
            // Prévisualisation en temps réel de l'upload
            $('#poi_icon_file').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    // Vérifier l'extension plutôt que le type MIME (plus fiable pour SVG)
                    var fileName = file.name.toLowerCase();
                    var isSVG = fileName.endsWith('.svg') || 
                               file.type === 'image/svg+xml' || 
                               file.type === 'text/plain' ||
                               file.type === 'application/xml';
                    
                    if (isSVG) {
                        console.log('Fichier SVG détecté:', {
                            name: file.name,
                            type: file.type,
                            size: file.size
                        });
                        
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var content = e.target.result;
                            console.log('Contenu lu, taille:', content.length, 'Type:', typeof content);
                            
                            // Vérifier que le contenu contient bien du SVG
                            if (content.indexOf('<svg') !== -1 || content.indexOf('data:image/svg+xml') !== -1) {
                                // Pour les SVG, essayer d'abord l'affichage direct
                                $('#icon-preview').attr('src', content)
                                    .on('load', function() {
                                        console.log('SVG affiché avec succès');
                                        $('#icon-preview').show();
                                        $('#icon-preview-error').hide();
                                        $('#icon-preview-info p:last-child').text('Nouveau fichier : ' + file.name);
                                    })
                                    .on('error', function() {
                                        console.log('Erreur d\'affichage SVG, tentative avec URL blob');
                                        // Si l'affichage direct échoue, essayer avec un blob
                                        var blob = new Blob([content], {type: 'image/svg+xml'});
                                        var url = URL.createObjectURL(blob);
                                        $('#icon-preview').attr('src', url).show();
                                        $('#icon-preview-error').hide();
                                        $('#icon-preview-info p:last-child').text('Nouveau fichier : ' + file.name);
                                    });
                            } else {
                                console.log('Contenu ne semble pas être du SVG:', content.substring(0, 100));
                                $('#icon-preview').hide();
                                $('#icon-preview-error').text('Le fichier ne semble pas contenir de SVG valide.').show();
                            }
                        };
                        reader.onerror = function(error) {
                            console.error('Erreur FileReader:', error);
                            $('#icon-preview').hide();
                            $('#icon-preview-error').text('Erreur lors de la lecture du fichier.').show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        $('#icon-preview').hide();
                        $('#icon-preview-error').text('Veuillez sélectionner un fichier SVG (.svg).').show();
                    }
                } else {
                    // Aucun fichier sélectionné, revenir à l'URL par défaut
                    var defaultUrl = $('#poi_icon_url').val();
                    if (defaultUrl) {
                        $('#icon-preview').attr('src', defaultUrl).show();
                        $('#icon-preview-error').hide();
                        $('#icon-preview-info p:last-child').text('URL : ' + defaultUrl.split('/').pop());
                    }
                }
            });
            
            // Mise à jour de l'aperçu quand la taille change
            $('#poi_icon_size').on('input', function() {
                var newSize = $(this).val() + 'px';
                $('#icon-preview').css('max-width', newSize);
                $('#icon-preview-info p:first-child').text('Taille affichée : ' + $(this).val() + 'px');
            });
            
            // Mise à jour de l'aperçu quand l'URL change
            $('#poi_icon_url').on('input', function() {
                var newUrl = $(this).val();
                if (newUrl && !$('#poi_icon_file').val()) {
                    $('#icon-preview').attr('src', newUrl).show();
                    $('#icon-preview-error').hide();
                    $('#icon-preview-info p:last-child').text('URL : ' + newUrl.split('/').pop());
                }
            });
            
            // Gestion du drag & drop sur la zone d'upload
            $('.upload-zone').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            }).on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            }).on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
                
                var files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    var file = files[0];
                    var fileInput = $('#poi_icon_file')[0];
                    
                    // Créer un nouvel objet FileList pour le champ input
                    var dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    
                    // Déclencher l'événement change
                    $('#poi_icon_file').trigger('change');
                }
            });
            
            // Empêcher le comportement par défaut du drag & drop sur toute la page
            $(document).on('dragover drop', function(e) {
                e.preventDefault();
            });
        });
    </script>
    <?php
}

/**
 * Contenu de la page d'aide
 */
function terralize_ap_help_page_content() {
    $icon_url = plugin_dir_url(dirname(__FILE__)) . '../assets/icons/icon.png';
    ?>
    <div class="wrap">
        <div class="terralize-header">
            <div class="terralize-header-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Terralize Icon" class="terralize-icon" />
                <h1 class="terralize-title">Aide Terralize pour Affichage Premier</h1>
            </div>
        </div>
        
        <div class="terralize-content">
            <section class="terralize-section blueprint-grid">
                <h2>Guide d'utilisation</h2>
                <p>Cette page contient des informations pour vous aider à utiliser Terralize pour la gestion de vos panneaux d'affichage.</p>
                
                <div class="blueprint-detail">
                    <h3>Gestion des panneaux d'affichage</h3>
                    <ol>
                        <li>
                            <strong>Ajouter un panneau :</strong> Depuis la section "Panneaux d'affichage", cliquez sur "Ajouter".
                            Remplissez les informations demandées, notamment la position géographique du panneau.
                        </li>
                        <li>
                            <strong>Modifier un panneau existant :</strong> Cliquez sur le panneau concerné dans la liste, puis effectuez vos modifications.
                        </li>
                        <li>
                            <strong>Géolocalisation :</strong> Pour positionner précisément un panneau, vous pouvez utiliser la carte interactive.
                            Placez le marqueur à l'emplacement exact du panneau.
                        </li>
                    </ol>
                </div>
                
                <div class="blueprint-note">
                    <h3>Informations techniques</h3>
                    <p>Pour chaque panneau, vous pouvez rimplantationsr :</p>
                    <ul>
                        <li><strong>Référence :</strong> Code unique d'identification du panneau</li>
                        <li><strong>Type :</strong> 4x3, 8x3, mural, etc.</li>
                        <li><strong>Dimensions :</strong> Largeur et hauteur en centimètres</li>
                        <li><strong>Visibilité :</strong> Information sur la visibilité et l'angle du panneau</li>
                        <li><strong>Localisation :</strong> Adresse précise, ville, code postal</li>
                    </ul>
                </div>
            </section>
            
            <section class="terralize-section">
                <h2>Contacter le support</h2>
                <p>Pour toute question ou problème technique, contactez notre équipe de support :</p>
                <div class="blueprint-detail">
                    <ul>
                        <li>Email: <a href="mailto:support@votresociete.com">support@votresociete.com</a></li>
                        <li>Téléphone: 01 23 45 67 89</li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
    <?php
}

/**
 * Ajouter un script JavaScript pour le calcul automatique de la surface des panneaux
 */
function terralize_ap_create_panel_admin_js() {
    // Créer le répertoire pour les assets JavaScript s'il n'existe pas
    $js_dir = dirname(dirname(__FILE__)) . '/assets/js';
    if (!file_exists($js_dir)) {
        wp_mkdir_p($js_dir);
    }
    
    // Chemin vers le fichier JavaScript
    $js_file = $js_dir . '/panel-admin.js';
    
    // Vérifier si le fichier existe déjà pour éviter de l'écraser
    if (!file_exists($js_file)) {
        $js_content = <<<EOT
/**
 * JavaScript pour les fonctionnalités administratives des panneaux d'affichage
 */
jQuery(document).ready(function($) {
    // Fonction pour calculer la surface automatiquement en m²
    function calculateSurface() {
        var width = parseInt($('#panel_width').val()) || 0;
        var height = parseInt($('#panel_height').val()) || 0;
        
        // Calcul de la surface en m² (convertir de cm² en m²)
        var surface = (width * height) / 10000;
        
        // Arrondir à 2 décimales
        surface = Math.round(surface * 100) / 100;
        
        // Mettre à jour le champ de surface
        $('#panel_surface').val(surface);
    }
    
    // Déclencher le calcul quand largeur ou hauteur change
    $('#panel_width, #panel_height').on('input', function() {
        calculateSurface();
    });
    
    // Bouton pour remplir l'adresse depuis la carte
    $('#locate-panel').on('click', function() {
        var geojson = $('#poi_geojson').val();
        
        if (!geojson) {
            alert('Veuillez d\'abord positionner le panneau sur la carte.');
            return;
        }
        
        try {
            var geoData = JSON.parse(geojson);
            var lat = geoData.coordinates[1];
            var lng = geoData.coordinates[0];
            
            // Appel à l'API de géocodage inverse (OpenStreetMap Nominatim)
            $.ajax({
                url: 'https://nominatim.openstreetmap.org/reverse',
                data: {
                    format: 'json',
                    lat: lat,
                    lon: lng,
                    zoom: 18,
                    addressdetails: 1
                },
                success: function(data) {
                    if (data && data.address) {
                        // Remplir les champs d'adresse avec les données récupérées
                        var address = data.address;
                        
                        // Construire l'adresse complète
                        var fullAddress = [];
                        if (address.road) fullAddress.push(address.road);
                        if (address.house_number) fullAddress.push(address.house_number);
                        
                        $('#panel_address').val(fullAddress.join(' '));
                        $('#panel_postal_code').val(address.postcode || '');
                        $('#panel_city_name').val(address.city || address.town || address.village || '');
                        $('#panel_department').val(address.county || '');
                        $('#panel_region').val(address.state || '');
                    } else {
                        alert('Aucune information d\'adresse trouvée pour cette position.');
                    }
                },
                error: function() {
                    alert('Erreur lors de la récupération de l\'adresse.');
                }
            });
        } catch (e) {
            alert('Erreur: Format GeoJSON invalide.');
        }
    });
});
EOT;
        
        // Écrire le contenu dans le fichier
        file_put_contents($js_file, $js_content);
    }
}
add_action('admin_init', 'terralize_ap_create_panel_admin_js');

/**
 * Enregistrer le script JS pour l'administration des panneaux
 */
function terralize_ap_enqueue_panel_admin_js($hook) {
    global $post_type;
    
    if ($post_type === 'poi') {
        // Chemin correct vers le fichier JS
        $js_url = plugin_dir_url(dirname(__FILE__)) . 'assets/js/panel-admin.js';
        wp_enqueue_script('terralize_ap-panel-admin-js', $js_url, array('jquery'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'terralize_ap_enqueue_panel_admin_js');

/**
 * Personnaliser les messages d'aide contextuelle dans l'interface
 */
function terralize_ap_add_contextual_help() {
    $current_screen = get_current_screen();
    
    // Aide pour l'écran d'édition des panneaux d'affichage
    if ($current_screen->id === 'poi' && $current_screen->base === 'post') {
        $current_screen->add_help_tab(array(
            'id'      => 'terralize_ap_panel_help',
            'title'   => 'Aide Panneau',
            'content' => '
                <div class="blueprint-grid" style="padding: 15px;">
                    <h2>Comment remplir les informations du panneau</h2>
                    <div class="blueprint-detail">
                        <p><strong>Caractéristiques techniques :</strong> Rimplantationsz le type de panneau, ses dimensions et sa référence unique.</p>
                    </div>
                    <div class="blueprint-detail">
                        <p><strong>Localisation :</strong> Vous pouvez soit saisir manuellement l\'adresse, soit utiliser le bouton "Remplir automatiquement l\'adresse" après avoir positionné le panneau sur la carte.</p>
                    </div>
                    <div class="blueprint-detail">
                        <p><strong>Visibilité :</strong> Ces informations sont importantes pour évaluer l\'efficacité du panneau. Indiquez la direction de visibilité, l\'angle et la distance de visibilité.</p>
                    </div>
                </div>
            ',
        ));
    }
    
    // Aide pour la liste des panneaux
    if ($current_screen->id === 'edit-poi') {
        $current_screen->add_help_tab(array(
            'id'      => 'terralize_ap_panel_list_help',
            'title'   => 'Gestion des panneaux',
            'content' => '
                <div class="blueprint-grid" style="padding: 15px;">
                    <h2>Gestion de votre parc de panneaux d\'affichage</h2>
                    <div class="blueprint-note">
                        <p>Cette page liste tous vos panneaux d\'affichage. Vous pouvez :</p>
                        <ul>
                            <li>Trier les panneaux par référence, type ou localisation en cliquant sur les en-têtes de colonne</li>
                            <li>Filtrer les panneaux par ville ou type en utilisant les menus déroulants au-dessus de la liste</li>
                            <li>Ajouter un nouveau panneau en cliquant sur le bouton "Ajouter"</li>
                            <li>Modifier un panneau existant en cliquant sur son nom</li>
                        </ul>
                    </div>
                </div>
            ',
        ));
    }
}
add_action('admin_head', 'terralize_ap_add_contextual_help');

/**
 * Personnaliser l'écran d'accueil de WordPress (dashboard)
 */
function terralize_ap_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'terralize_ap_dashboard_widget',
        'Affichage Premier - Panneaux',
        'terralize_ap_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'terralize_ap_add_dashboard_widget');

/**
 * Contenu du widget de tableau de bord
 */
function terralize_ap_dashboard_widget_content() {
    // Récupérer les statistiques
    $panels_count = wp_count_posts('poi')->publish;
    
    // Récupérer la répartition par type
    $panel_types = array(
        'mural'        => 0,
        'pre-implantations' => 0,
        '4x3'          => 0,
        '8x3'          => 0,
        'déroulant'    => 0,
        'totem'        => 0,
    );
    
    global $wpdb;
    $results = $wpdb->get_results(
        "SELECT meta_value, COUNT(*) as count 
         FROM {$wpdb->postmeta} 
         WHERE meta_key = 'panel_type' 
         AND post_id IN (
             SELECT ID FROM {$wpdb->posts} 
             WHERE post_type = 'poi' 
             AND post_status = 'publish'
         )
         GROUP BY meta_value"
    );
    
    if ($results) {
        foreach ($results as $result) {
            if (isset($panel_types[$result->meta_value])) {
                $panel_types[$result->meta_value] = $result->count;
            }
        }
    }
    
    echo '<div class="terralize_ap-dashboard-stats blueprint-grid">';
    echo '<p><strong>Total des panneaux :</strong> <span class="terralize_ap-badge">' . $panels_count . '</span></p>';
    
    echo '<div class="blueprint-detail">';
    echo '<p><strong>Répartition par type :</strong></p>';
    echo '<ul>';
    foreach ($panel_types as $type => $count) {
        if ($count > 0) {
            $label = ucfirst(str_replace('-', ' ', $type));
            echo '<li>' . esc_html($label) . ' : <span class="terralize_ap-badge" style="font-size: 0.7em; padding: 2px 6px;">' . $count . '</span></li>';
        }
    }
    echo '</ul>';
    echo '</div>';
    
    echo '<p style="margin-top: 15px;"><a href="' . admin_url('edit.php?post_type=poi') . '" class="button button-primary">Gérer les panneaux</a> <a href="' . admin_url('admin.php?page=terralize_poi') . '" class="button button-secondary">Voir la carte</a></p>';
    echo '</div>';
}

/**
 * Ajouter les options d'icônes POI pour le front-end
 */
function terralize_ap_add_poi_icon_options() {
    // Récupérer les options sauvegardées
    $poi_icon_url = get_option('terralize_ap_poi_icon_url', '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/sucette_panneau_pin.svg');
    $poi_icon_size = get_option('terralize_ap_poi_icon_size', 30);
    $poi_icon_anchor_x = get_option('terralize_ap_poi_icon_anchor_x', 15);
    $poi_icon_anchor_y = get_option('terralize_ap_poi_icon_anchor_y', 40);
    
    // Passer les options au script JavaScript (pour le shortcode et l'admin)
    if (wp_script_is('ap-map-frontend', 'enqueued')) {
        wp_localize_script('ap-map-frontend', 'terralizeAPIconOptions', array(
            'poi_icon_url' => $poi_icon_url,
            'poi_icon_size' => $poi_icon_size,
            'poi_icon_anchor_x' => $poi_icon_anchor_x,
            'poi_icon_anchor_y' => $poi_icon_anchor_y
        ));
    }
    
    if (wp_script_is('terralize-map', 'enqueued')) {
        wp_localize_script('terralize-map', 'terralizeAPIconOptions', array(
            'poi_icon_url' => $poi_icon_url,
            'poi_icon_size' => $poi_icon_size,
            'poi_icon_anchor_x' => $poi_icon_anchor_x,
            'poi_icon_anchor_y' => $poi_icon_anchor_y
        ));
    }
}
add_action('wp_enqueue_scripts', 'terralize_ap_add_poi_icon_options');

/**
 * Ajouter des styles CSS personnalisés pour les marqueurs de carte
 */
function terralize_ap_add_custom_marker_styles() {
    ?>
    <style>
        /* Personnalisation des marqueurs de carte */
        .custom-marker-icon {
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            border-radius: 50%;
            text-align: center;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .marker-type-mural {
            background-color: #70c141;
        }
        
        .marker-type-preimplantations {
            background-color: #E04D00;
        }
        
        .marker-type-4x3 {
            background-color: #2c5aa0;
        }
        
        .marker-type-8x3 {
            background-color: #1e3c68;
        }
        
        .marker-type-deroulant {
            background-color: #9C27B0;
        }
        
        .marker-type-totem {
            background-color: #FF9800;
        }
        
        .custom-marker-icon:hover {
            transform: scale(1.1);
            z-index: 1000 !important;
        }
        
        /* Amélioration des popups */
        .marker-popup {
            font-family: var(--terralize-font);
        }
        
        .marker-popup h3 {
            color: var(--terralize_ap-primary);
            margin: 0 0 10px;
            border-bottom: 2px solid var(--terralize_ap-primary);
            padding-bottom: 5px;
        }
        
        .marker-popup-details {
            margin-top: 10px;
        }
        
        .marker-popup-detail-group {
            margin-bottom: 8px;
            padding-left: 10px;
            border-left: 2px solid var(--terralize_ap-primary);
        }
        
        .marker-popup-label {
            font-weight: bold;
            color: var(--terralize-dark);
        }
        
        .marker-popup-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .marker-popup-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9em;
            text-align: center;
            flex: 1;
            margin: 0 5px;
            transition: all 0.2s ease;
        }
        
        .marker-popup-btn-primary {
            background-color: var(--terralize_ap-primary);
            color: white;
        }
        
        .marker-popup-btn-primary:hover {
            background-color: var(--terralize_ap-dark);
            color: white;
        }
        
        .marker-popup-btn-secondary {
            background-color: var(--terralize-light);
            color: var(--terralize-dark);
            border: 1px solid var(--terralize-border);
        }
        
        .marker-popup-btn-secondary:hover {
            background-color: white;
        }
    </style>
    <?php
}
add_action('admin_head', 'terralize_ap_add_custom_marker_styles');