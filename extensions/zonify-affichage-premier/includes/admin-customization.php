<?php
/**
 * Personnalisation de l'administration pour Affichage Premier
 * Adaptation de l'interface Zonify pour le client Affichage Premier
 */

// Si ce fichier est appelé directement, abandon
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modifier le texte du tableau de bord principal de Zonify
 */
function zap_customize_dashboard_text($content) {
    // Texte personnalisé pour le client Affichage Premier
    $new_content = '
    <div class="zonify-content">
        <section class="zonify-section">
            <h2>Bienvenue sur Zonify pour Affichage Premier</h2>
            <p>
                Cette interface est personnalisée pour la gestion de vos panneaux d\'affichage.
                Utilisez les différentes sections du menu pour gérer votre parc d\'affichage.
            </p>
        </section>
        
        <section class="zonify-section blueprint-grid">
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
add_filter('zonify_main_page_content', 'zap_customize_dashboard_text');

/**
 * Personnaliser le logo et les textes en en-tête
 */
function zap_customize_header() {
    ?>
    <style>
        .zonify-title {
            color: var(--zonify-secondary) !important;
            font-weight: 600 !important;
            letter-spacing: -0.5px !important;
        }
        .zonify-banner {
            border-bottom: 2px solid var(--zonify-secondary) !important;
            box-shadow: 0 2px 10px var(--zonify-shadow) !important;
            border-radius: 8px 8px 0 0 !important;
        }
        .zonify-header {
            border-bottom: 2px solid var(--zonify-secondary) !important;
        }
        .zap-version-badge {
            background-color: var(--zonify-secondary);
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
            $('.zonify-title').each(function() {
                var currentTitle = $(this).text();
                if (!currentTitle.includes('Affichage Premier')) {
                    $(this).html(currentTitle + ' <span class="zap-version-badge">Affichage Premier</span>');
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'zap_customize_header');

/**
 * Personnaliser le menu d'administration
 */
function zap_customize_admin_menu() {
    global $menu, $submenu;
    
    // Renommer le sous-menu "Points d'intérêt" en "Panneaux d'affichage"
    if (isset($submenu['zonify']) && is_array($submenu['zonify'])) {
        foreach ($submenu['zonify'] as $key => $item) {
            // Trouver le sous-menu Points d'Intérêt
            if (isset($item[2]) && $item[2] === 'edit.php?post_type=poi') {
                $submenu['zonify'][$key][0] = 'Panneaux d\'affichage';
            }
            
            // Modifier le texte "Points d'intérêt" dans le titre de la page
            if (isset($item[2]) && $item[2] === 'zonify_poi') {
                $submenu['zonify'][$key][0] = 'Carte des panneaux';
            }
        }
    }
}
add_action('admin_menu', 'zap_customize_admin_menu', 999);

/**
 * Personnaliser les éléments d'interface pour Affichage Premier
 */
function zap_customize_admin_ui() {
    echo '<style>
        /* Variables globales pour unifier le style */
        :root {
            --zap-primary: #70c141;
            --zap-secondary: #E04D00;
            --zap-dark: #5da834;
            --zap-text: #333f4d;
        }
        
        /* Base de style blueprint pour Affichage Premier */
        .zonify-section h2, 
        .zonify-section h3 {
            color: var(--zap-primary);
            position: relative;
            padding-bottom: 15px;
        }
        
        .zonify-section h2::after,
        .zonify-section h3::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: var(--zap-primary);
            border-radius: 3px;
        }
        
        /* Personnalisation des boutons */
        .zonify-content .button-primary,
        .wp-core-ui .button-primary,
        .zap-btn-primary {
            background: var(--zap-primary);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            text-shadow: none;
            box-shadow: 0 2px 4px var(--zonify-shadow);
            transition: all 0.2s ease;
        }
        
        .zonify-content .button-primary:hover,
        .wp-core-ui .button-primary:hover,
        .zap-btn-primary:hover {
            background: var(--zap-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px var(--zonify-shadow);
        }
        
        /* Badge Affichage Premier */
        .zap-badge {
            display: inline-block;
            background: var(--zap-primary);
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
            box-shadow: 0 4px 15px var(--zonify-shadow);
        }
        
        /* Tableau de données des panneaux */
        .wp-list-table {
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--zonify-border);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px var(--zonify-shadow);
        }
        
        .wp-list-table th {
            background-color: var(--zonify-light);
            border-bottom: 2px solid var(--zap-primary);
            padding: 12px 15px;
            font-weight: 600;
            color: var(--zonify-dark);
        }
        
        .wp-list-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--zonify-border);
        }
        
        .wp-list-table tr:hover td {
            background-color: rgba(112, 193, 65, 0.05);
        }
        
        /* Formulaires des métaboxes */
        .postbox {
            border: none;
            box-shadow: 0 3px 10px var(--zonify-shadow);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .postbox .hndle {
            border-bottom: 2px solid var(--zap-primary);
            padding: 12px 15px;
            font-weight: 600;
        }
        
        .postbox .inside {
            padding: 15px;
        }
        
        .form-field label {
            font-weight: 600;
            color: var(--zonify-dark);
            display: block;
            margin-bottom: 8px;
        }
        
        /* Dashboard widget */
        .zap-dashboard-stats {
            background-color: var(--zonify-light);
            border-left: 3px solid var(--zap-primary);
            padding: 15px;
            border-radius: 0 8px 8px 0;
        }
        
        .zap-dashboard-stats ul {
            margin-left: 20px;
        }
        
        .zap-dashboard-stats p strong {
            color: var(--zonify-dark);
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
            border-left: 2px solid var(--zap-primary);
            padding-left: 15px;
            margin: 15px 0;
        }
        
        .blueprint-note {
            background-color: rgba(112, 193, 65, 0.05);
            border-left: 3px solid var(--zap-primary);
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }
    </style>';
}
add_action('admin_head', 'zap_customize_admin_ui');

/**
 * Ajouter une page d'aide personnalisée
 */
function zap_add_help_page() {
    add_submenu_page(
        'zonify',
        'Aide Affichage Premier',
        'Aide',
        'manage_options',
        'zap_help',
        'zap_help_page_content'
    );
}
add_action('admin_menu', 'zap_add_help_page');

/**
 * Contenu de la page d'aide
 */
function zap_help_page_content() {
    $icon_url = plugin_dir_url(dirname(__FILE__)) . '../assets/icons/icon.png';
    ?>
    <div class="wrap">
        <div class="zonify-header">
            <div class="zonify-header-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Zonify Icon" class="zonify-icon" />
                <h1 class="zonify-title">Aide Zonify pour Affichage Premier</h1>
            </div>
        </div>
        
        <div class="zonify-content">
            <section class="zonify-section blueprint-grid">
                <h2>Guide d'utilisation</h2>
                <p>Cette page contient des informations pour vous aider à utiliser Zonify pour la gestion de vos panneaux d'affichage.</p>
                
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
                    <p>Pour chaque panneau, vous pouvez renseigner :</p>
                    <ul>
                        <li><strong>Référence :</strong> Code unique d'identification du panneau</li>
                        <li><strong>Type :</strong> 4x3, 8x3, mural, etc.</li>
                        <li><strong>Dimensions :</strong> Largeur et hauteur en centimètres</li>
                        <li><strong>Visibilité :</strong> Information sur la visibilité et l'angle du panneau</li>
                        <li><strong>Localisation :</strong> Adresse précise, ville, code postal</li>
                    </ul>
                </div>
            </section>
            
            <section class="zonify-section">
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
function zap_create_panel_admin_js() {
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
add_action('admin_init', 'zap_create_panel_admin_js');

/**
 * Enregistrer le script JS pour l'administration des panneaux
 */
function zap_enqueue_panel_admin_js($hook) {
    global $post_type;
    
    if ($post_type === 'poi') {
        // Chemin correct vers le fichier JS
        $js_url = plugin_dir_url(dirname(__FILE__)) . 'assets/js/panel-admin.js';
        wp_enqueue_script('zap-panel-admin-js', $js_url, array('jquery'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'zap_enqueue_panel_admin_js');

/**
 * Personnaliser les messages d'aide contextuelle dans l'interface
 */
function zap_add_contextual_help() {
    $current_screen = get_current_screen();
    
    // Aide pour l'écran d'édition des panneaux d'affichage
    if ($current_screen->id === 'poi' && $current_screen->base === 'post') {
        $current_screen->add_help_tab(array(
            'id'      => 'zap_panel_help',
            'title'   => 'Aide Panneau',
            'content' => '
                <div class="blueprint-grid" style="padding: 15px;">
                    <h2>Comment remplir les informations du panneau</h2>
                    <div class="blueprint-detail">
                        <p><strong>Caractéristiques techniques :</strong> Renseignez le type de panneau, ses dimensions et sa référence unique.</p>
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
            'id'      => 'zap_panel_list_help',
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
add_action('admin_head', 'zap_add_contextual_help');

/**
 * Personnaliser l'écran d'accueil de WordPress (dashboard)
 */
function zap_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'zap_dashboard_widget',
        'Affichage Premier - Panneaux',
        'zap_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'zap_add_dashboard_widget');

/**
 * Contenu du widget de tableau de bord
 */
function zap_dashboard_widget_content() {
    // Récupérer les statistiques
    $panels_count = wp_count_posts('poi')->publish;
    
    // Récupérer la répartition par type
    $panel_types = array(
        'mural'        => 0,
        'pre-enseigne' => 0,
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
    
    echo '<div class="zap-dashboard-stats blueprint-grid">';
    echo '<p><strong>Total des panneaux :</strong> <span class="zap-badge">' . $panels_count . '</span></p>';
    
    echo '<div class="blueprint-detail">';
    echo '<p><strong>Répartition par type :</strong></p>';
    echo '<ul>';
    foreach ($panel_types as $type => $count) {
        if ($count > 0) {
            $label = ucfirst(str_replace('-', ' ', $type));
            echo '<li>' . esc_html($label) . ' : <span class="zap-badge" style="font-size: 0.7em; padding: 2px 6px;">' . $count . '</span></li>';
        }
    }
    echo '</ul>';
    echo '</div>';
    
    echo '<p style="margin-top: 15px;"><a href="' . admin_url('edit.php?post_type=poi') . '" class="button button-primary">Gérer les panneaux</a> <a href="' . admin_url('admin.php?page=zonify_poi') . '" class="button button-secondary">Voir la carte</a></p>';
    echo '</div>';
}

/**
 * Ajouter des styles CSS personnalisés pour les marqueurs de carte
 */
function zap_add_custom_marker_styles() {
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
        
        .marker-type-preenseigne {
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
            font-family: var(--zonify-font);
        }
        
        .marker-popup h3 {
            color: var(--zap-primary);
            margin: 0 0 10px;
            border-bottom: 2px solid var(--zap-primary);
            padding-bottom: 5px;
        }
        
        .marker-popup-details {
            margin-top: 10px;
        }
        
        .marker-popup-detail-group {
            margin-bottom: 8px;
            padding-left: 10px;
            border-left: 2px solid var(--zap-primary);
        }
        
        .marker-popup-label {
            font-weight: bold;
            color: var(--zonify-dark);
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
            background-color: var(--zap-primary);
            color: white;
        }
        
        .marker-popup-btn-primary:hover {
            background-color: var(--zap-dark);
            color: white;
        }
        
        .marker-popup-btn-secondary {
            background-color: var(--zonify-light);
            color: var(--zonify-dark);
            border: 1px solid var(--zonify-border);
        }
        
        .marker-popup-btn-secondary:hover {
            background-color: white;
        }
    </style>
    <?php
}
add_action('admin_head', 'zap_add_custom_marker_styles');