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
        
        <section class="zonify-section">
            <h3>Guide rapide</h3>
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
            color: #FF5500 !important;
        }
        .zonify-banner {
            border-bottom-color: #FF5500 !important;
        }
        .zap-version-badge {
            background-color: #FF5500;
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
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
        /* Couleurs personnalisées */
        .zonify-section h2, 
        .zonify-section h3 {
            color: #FF5500;
        }
        
        /* Personnalisation des boutons */
        .zonify-content .button-primary,
        .zap-btn-primary {
            background: #FF5500;
            border-color: #E04D00;
            color: white;
        }
        .zonify-content .button-primary:hover,
        .zap-btn-primary:hover {
            background: #E04D00;
            border-color: #CC4600;
        }
        
        /* Badge Affichage Premier */
        .zap-badge {
            display: inline-block;
            background: #FF5500;
            color: white;
            font-size: 0.8em;
            padding: 2px 8px;
            border-radius: 3px;
            margin-left: 10px;
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
            <section class="zonify-section">
                <h2>Guide d'utilisation</h2>
                <p>Cette page contient des informations pour vous aider à utiliser Zonify pour la gestion de vos panneaux d'affichage.</p>
                
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
                
                <h3>Informations techniques</h3>
                <p>Pour chaque panneau, vous pouvez renseigner :</p>
                <ul>
                    <li><strong>Référence :</strong> Code unique d'identification du panneau</li>
                    <li><strong>Type :</strong> 4x3, 8x3, mural, etc.</li>
                    <li><strong>Dimensions :</strong> Largeur et hauteur en centimètres</li>
                    <li><strong>Visibilité :</strong> Information sur la visibilité et l'angle du panneau</li>
                    <li><strong>Localisation :</strong> Adresse précise, ville, code postal</li>
                </ul>
            </section>
            
            <section class="zonify-section">
                <h2>Contacter le support</h2>
                <p>Pour toute question ou problème technique, contactez notre équipe de support :</p>
                <ul>
                    <li>Email: <a href="mailto:support@votresociete.com">support@votresociete.com</a></li>
                    <li>Téléphone: 01 23 45 67 89</li>
                </ul>
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
                <h2>Comment remplir les informations du panneau</h2>
                <p><strong>Caractéristiques techniques :</strong> Renseignez le type de panneau, ses dimensions et sa référence unique.</p>
                <p><strong>Localisation :</strong> Vous pouvez soit saisir manuellement l\'adresse, soit utiliser le bouton "Remplir automatiquement l\'adresse" après avoir positionné le panneau sur la carte.</p>
                <p><strong>Visibilité :</strong> Ces informations sont importantes pour évaluer l\'efficacité du panneau. Indiquez la direction de visibilité, l\'angle et la distance de visibilité.</p>
            ',
        ));
    }
    
    // Aide pour la liste des panneaux
    if ($current_screen->id === 'edit-poi') {
        $current_screen->add_help_tab(array(
            'id'      => 'zap_panel_list_help',
            'title'   => 'Gestion des panneaux',
            'content' => '
                <h2>Gestion de votre parc de panneaux d\'affichage</h2>
                <p>Cette page liste tous vos panneaux d\'affichage. Vous pouvez :</p>
                <ul>
                    <li>Trier les panneaux par référence, type ou localisation en cliquant sur les en-têtes de colonne</li>
                    <li>Filtrer les panneaux par ville ou type en utilisant les menus déroulants au-dessus de la liste</li>
                    <li>Ajouter un nouveau panneau en cliquant sur le bouton "Ajouter"</li>
                    <li>Modifier un panneau existant en cliquant sur son nom</li>
                </ul>
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
    
    echo '<div class="zap-dashboard-stats">';
    echo '<p><strong>Total des panneaux :</strong> ' . $panels_count . '</p>';
    
    echo '<p><strong>Répartition par type :</strong></p>';
    echo '<ul>';
    foreach ($panel_types as $type => $count) {
        if ($count > 0) {
            $label = ucfirst(str_replace('-', ' ', $type));
            echo '<li>' . esc_html($label) . ' : ' . $count . '</li>';
        }
    }
    echo '</ul>';
    
    echo '<p><a href="' . admin_url('edit.php?post_type=poi') . '" class="button button-primary">Gérer les panneaux</a></p>';
    echo '</div>';
}