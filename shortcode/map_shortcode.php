<?php
/**
 * Shortcode pour la carte des commerciaux
 * Permet d'afficher la carte interactive des commerciaux et points d'intérêt
 * Usage : [tracteur_zone_map categories="categorie1,categorie2" regions="region1,region2"]
 */

function tracteur_zone_map_shortcode($atts) {
    // Attributs du shortcode
    $atts = shortcode_atts(array(
        'categories' => '',  // Filtrage par catégorie(s)
        'regions' => '',     // Filtrage par région(s)
        'height' => '600px', // Hauteur de la carte
        'show_sidebar' => 'true', // Afficher la sidebar
        'center_lat' => '',  // Latitude du centre (si vide, utilise les options par défaut)
        'center_lng' => '',  // Longitude du centre (si vide, utilise les options par défaut)
        'zoom' => '',        // Niveau de zoom (si vide, utilise les options par défaut)
        'debug' => 'false',  // Active le mode débogage
        'load_fontawesome' => 'auto', // 'auto', 'true', 'false' - Contrôle le chargement de Font Awesome
        
        // Options de style
        'button_border_radius' => '',
        'button_border_width' => '',
        'button_border_style' => '',
        'button_border_color' => '',
        'button_background_color' => '',
        'button_text_color' => '',
        'button_hover_background' => '',
        'button_hover_text' => '',
        'sidebar_background' => '',
        'sidebar_border_color' => '',
        'popup_background' => '',
        'popup_border_radius' => '',
        'popup_border_color' => '',
        'popup_border_width' => '',
        'popup_border_style' => '',
        'popup_shadow' => ''
    ), $atts, 'tracteur_zone_map');
    
    // Récupérer les options de style par défaut
    $style_settings = get_option('terralize_style_settings', array());
    
    // Fusionner les options de style avec les attributs du shortcode
    $style_vars = array();
    foreach ($style_settings as $key => $value) {
        $style_vars[$key] = !empty($atts[$key]) ? $atts[$key] : $value;
    }

    // 1. Récupération des zones (CPT "zone") depuis la base de données
    $args = array(
        'post_type'      => 'zone',
        'posts_per_page' => -1
    );
    
    // Tableau pour stocker les conditions de requête tax_query
    $tax_query = array('relation' => 'AND');
    
    // Si des catégories sont spécifiées, les utiliser pour filtrer
    if (!empty($atts['categories'])) {
        $categories = array_map('trim', explode(',', $atts['categories']));
        $tax_query[] = array(
            'taxonomy' => 'terralize_category',
            'field'    => 'slug',
            'terms'    => $categories,
        );
    }
    
    // Si des régions sont spécifiées, les utiliser pour filtrer
    if (!empty($atts['regions'])) {
        $regions = array_map('trim', explode(',', $atts['regions']));
        $tax_query[] = array(
            'taxonomy' => 'region',
            'field'    => 'slug',
            'terms'    => $regions,
        );
    }
    
    // Ajouter la tax_query si elle contient des éléments
    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    $zones_query = new WP_Query($args);
    $zones_data = array();

    if ($zones_query->have_posts()) {
        while ($zones_query->have_posts()) {
            $zones_query->the_post();
            $geojson = get_post_meta(get_the_ID(), 'zone_geojson', true);
            $comm_id = get_post_meta(get_the_ID(), 'zone_commercial_id', true);

            if ($geojson) {
                $nom_commercial = '';
                $infos = '';
                $email = '';
                $telephone = '';
                $address = '';
                $opening_hours = '';
                $social_links = '';
                $border_color = '';
                $fill_color = '';
                $commercial_slug = '';
                $commercial_custom_fields = array();

                if ($comm_id) {
                    $nom_commercial = get_the_title($comm_id);
                    $infos = get_the_excerpt($comm_id);
                    $email = get_post_meta($comm_id, 'commercial_email', true);
                    $telephone = get_post_meta($comm_id, 'commercial_telephone', true);
                    $address = get_post_meta($comm_id, 'commercial_address', true);
                    $opening_hours = get_post_meta($comm_id, 'commercial_opening_hours', true);
                    $social_links = get_post_meta($comm_id, 'commercial_social_links', true);
                    $border_color = get_post_meta($comm_id, 'commercial_border_color', true);
                    $fill_color = get_post_meta($comm_id, 'commercial_fill_color', true);
                    $commercial_slug = get_post_field('post_name', $comm_id);
                    
                    // Récupérer les champs personnalisés du commercial s'ils existent
                    $commercial_custom_fields = array();
                    if (function_exists('terralize_get_commercial_custom_fields')) {
                        $commercial_custom_fields = terralize_get_commercial_custom_fields($comm_id);
                        
                        // DEBUG - Afficher les champs du commercial récupérés dans la console
                        $debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
                        if ($debug_enabled) {
                            echo '<script>console.log("[DEBUG] Custom fields for commercial ' . $comm_id . ':", ' . json_encode($commercial_custom_fields) . ');</script>';
                            
                            // Vérifier la présence de la propriété display_zone
                            $zones_count = array('header' => 0, 'info' => 0, 'contact' => 0, 'location' => 0);
                            foreach ($commercial_custom_fields as $field) {
                                if (isset($field['display_zone'])) {
                                    $zone = $field['display_zone'];
                                    $zones_count[$zone] = isset($zones_count[$zone]) ? $zones_count[$zone] + 1 : 1;
                                } else {
                                    $zones_count['non_défini'] = isset($zones_count['non_défini']) ? $zones_count['non_défini'] + 1 : 1;
                                }
                            }
                            echo '<script>console.log("[DEBUG] Répartition des zones d\'affichage pour commercial ' . $comm_id . ':", ' . json_encode($zones_count) . ');</script>';
                        }
                        
                        // Traiter les champs de type image pour obtenir les URLs
                        foreach ($commercial_custom_fields as $key => $field) {
                            if ($field['type'] === 'image' && !empty($field['value'])) {
                                $image_url = wp_get_attachment_image_url($field['value'], 'medium');
                                if ($image_url) {
                                    $commercial_custom_fields[$key]['value'] = $image_url;
                                }
                            }
                        }
                    }
                }

                // Récupérer les catégories de cette zone
                $zone_categories = array();
                $terms = get_the_terms(get_the_ID(), 'terralize_category');
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $zone_categories[] = array(
                            'id' => $term->term_id,
                            'slug' => $term->slug,
                            'name' => $term->name
                        );
                    }
                }

                // Récupérer les régions de cette zone
                $zone_regions = array();
                $region_terms = get_the_terms(get_the_ID(), 'region');
                if ($region_terms && !is_wp_error($region_terms)) {
                    foreach ($region_terms as $term) {
                        $zone_regions[] = array(
                            'id' => $term->term_id,
                            'slug' => $term->slug,
                            'name' => $term->name
                        );
                    }
                }

                $zones_data[] = array(
                    'type' => 'Feature',
                    'properties' => array(
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'type' => 'zone',
                        'commercial_id' => $comm_id,
                        'commercial_slug' => $commercial_slug,
                        'nom_commercial' => $nom_commercial,
                        'infos' => $infos,
                        'email' => $email,
                        'telephone' => $telephone,
                        'address' => $address,
                        'opening_hours' => $opening_hours,
                        'social_links' => $social_links,
                        'border_color' => $border_color,
                        'fill_color' => $fill_color,
                        'categories' => $zone_categories,
                        'regions' => $zone_regions,
                        'commercial_custom_fields' => $commercial_custom_fields
                    ),
                    'geometry' => json_decode($geojson, true)
                );
            }
        }
        wp_reset_postdata();
    } else {
        // Aucune donnée disponible, créer un FeatureCollection vide
        $zones_data = array(
            'type' => 'FeatureCollection',
            'features' => array()
        );
    }

    // Récupération des points d'intérêt (POI)
    $poi_args = array(
        'post_type'      => 'poi',
        'posts_per_page' => -1
    );
    
    // Utiliser les mêmes filtres que pour les zones si définis
    if (count($tax_query) > 1) {
        $poi_args['tax_query'] = $tax_query;
    }
    
    $poi_query = new WP_Query($poi_args);
    if ($poi_query->have_posts()) {
        while ($poi_query->have_posts()) {
            $poi_query->the_post();
            $poi_geojson = get_post_meta(get_the_ID(), 'poi_geojson', true);

            if ($poi_geojson) {
                // Récupérer les catégories de ce POI
                $poi_categories = array();
                $terms = get_the_terms(get_the_ID(), 'terralize_category');
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $poi_categories[] = array(
                            'id' => $term->term_id,
                            'slug' => $term->slug,
                            'name' => $term->name
                        );
                    }
                }

                // Récupérer les régions de ce POI
                $poi_regions = array();
                $region_terms = get_the_terms(get_the_ID(), 'region');
                if ($region_terms && !is_wp_error($region_terms)) {
                    foreach ($region_terms as $term) {
                        $poi_regions[] = array(
                            'id' => $term->term_id,
                            'slug' => $term->slug,
                            'name' => $term->name
                        );
                    }
                }

                // Récupérer l'icône personnalisée si définie
                $icon_data = array();
                $icon_id = get_post_meta(get_the_ID(), 'poi_icon_id', true);
                if ($icon_id) {
                    $icon_url = get_the_post_thumbnail_url($icon_id, 'full');
                    $icon_width = (int)get_post_meta($icon_id, 'icon_width', true) ?: 32;
                    $icon_height = (int)get_post_meta($icon_id, 'icon_height', true) ?: 32;
                    $icon_anchor_x = (int)get_post_meta($icon_id, 'icon_anchor_x', true) ?: ($icon_width / 2);
                    $icon_anchor_y = (int)get_post_meta($icon_id, 'icon_anchor_y', true) ?: $icon_height;
                    
                    if ($icon_url) {
                        $icon_data = array(
                            'url' => $icon_url,
                            'width' => $icon_width,
                            'height' => $icon_height,
                            'anchor_x' => $icon_anchor_x,
                            'anchor_y' => $icon_anchor_y
                        );
                    }
                }

                // Récupérer les champs personnalisés
                $custom_fields = terralize_get_poi_custom_fields(get_the_ID());
                
                // DEBUG - Afficher les champs récupérés dans la console
                $debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
                if ($debug_enabled) {
                    echo '<script>console.log("[DEBUG] Custom fields for POI ' . get_the_ID() . ':", ' . json_encode($custom_fields) . ');</script>';
                    
                    // Vérifier la présence de la propriété display_zone
                    $zones_count = array('header' => 0, 'info' => 0, 'contact' => 0, 'location' => 0);
                    foreach ($custom_fields as $field) {
                        if (isset($field['display_zone'])) {
                            $zone = $field['display_zone'];
                            $zones_count[$zone] = isset($zones_count[$zone]) ? $zones_count[$zone] + 1 : 1;
                        } else {
                            $zones_count['non_défini'] = isset($zones_count['non_défini']) ? $zones_count['non_défini'] + 1 : 1;
                        }
                    }
                    echo '<script>console.log("[DEBUG] Répartition des zones d\'affichage:", ' . json_encode($zones_count) . ');</script>';
                }
                
                // Récupérer l'image principale si elle existe
                $image_url = '';
                foreach ($custom_fields as $field_key => $field) {
                    if ($field['type'] === 'image' && !empty($field['value'])) {
                        $image_url = wp_get_attachment_image_url($field['value'], 'medium');
                        break;
                    }
                }

                // Préparer les données de base du POI
                $poi_data = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'type' => 'poi',
                    'categories' => $poi_categories,
                    'regions' => $poi_regions,
                    'icon' => !empty($icon_data) ? $icon_data : null,
                    'custom_fields' => $custom_fields,
                    'image_url' => $image_url
                );
                
                // Appliquer le filtre pour permettre aux modules d'ajouter des données
                $poi_data = apply_filters('terralize_poi_data', $poi_data, get_the_ID());

                $zones_data[] = array(
                    'type' => 'Feature',
                    'properties' => $poi_data,
                    'geometry' => json_decode($poi_geojson, true)
                );
            }
        }
        wp_reset_postdata();
    }

    // Récupérer toutes les catégories pour les filtres
    $categories = get_terms(array(
        'taxonomy' => 'terralize_category',
        'hide_empty' => true,
    ));

    // Récupérer tous les termes de la taxonomie région pour les filtres
    $region_terms = get_terms(array(
        'taxonomy' => 'region',
        'hide_empty' => true,
    ));

    // Convertir les termes de région en format utilisable pour le filtre
    $regions = array();
    if (!empty($region_terms) && !is_wp_error($region_terms)) {
        foreach ($region_terms as $term) {
            $regions[$term->slug] = $term->name;
        }
    }

    // Récupérer les options front-end de terralize
    $front_options = array(
        'tile_provider'    => get_option('terralize_tile_provider_front', 'cartodb_light'),
        'tile_custom_url'  => get_option('terralize_tile_custom_url_front', ''),
        'zone_fill_color'  => get_option('terralize_zone_fill_color_front', '#3388ff'),
        'zone_border_color' => get_option('terralize_zone_border_color_front', '#3388ff'),
        'zone_opacity'     => floatval(get_option('terralize_zone_opacity_front', 0.5)),
        'map_zoom'         => !empty($atts['zoom']) ? intval($atts['zoom']) : intval(get_option('terralize_map_zoom_front', 9)),
        'map_center_lat'   => !empty($atts['center_lat']) ? $atts['center_lat'] : get_option('terralize_map_center_lat_front', '50.5'),
        'map_center_lng'   => !empty($atts['center_lng']) ? $atts['center_lng'] : get_option('terralize_map_center_lng_front', '2.5'),
        'show_category_filter' => true,
    );
    
    $popup_options = array(
        'popup_show_address'       => get_option('terralize_popup_show_address', 0),
        'popup_show_hours'         => get_option('terralize_popup_show_hours', 0),
        'popup_show_social'        => get_option('terralize_popup_show_social', 0),
        'popup_font_family'        => get_option('terralize_popup_font_family', 'Arial, sans-serif'),
        'popup_font_size'          => get_option('terralize_popup_font_size', '14px'),
        'popup_font_color'         => get_option('terralize_popup_font_color', '#333333'),
        'popup_enable_email_btn'   => get_option('terralize_popup_enable_email_btn', 1),
        'popup_enable_phone_btn'   => get_option('terralize_popup_enable_phone_btn', 1),
        'popup_enable_contact_btn' => get_option('terralize_popup_enable_contact_btn', 0)
    );
    
    $contact_page_url = get_option('terralize_contact_page_url', '/contact');
    $combined_options = array_merge($front_options, $popup_options, array(
        'contact_page_url' => $contact_page_url,
        'map_id' => 'terralize-map-' . uniqid(), // Ajout d'un ID unique pour cette instance de carte
        'site_url' => home_url(), // URL de base du site pour gérer les multisites
        'base_path' => parse_url(home_url(), PHP_URL_PATH) ?: '', // Chemin de base pour les multisites
    ));

    // Enqueue Leaflet et les scripts/styles nécessaires
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    
    wp_enqueue_style('leaflet-control-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
    wp_enqueue_script('leaflet-control-geocoder', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array('leaflet-js'), null, true);
    
    // Ajout du plugin de géolocalisation Leaflet.locate
    wp_enqueue_style('leaflet-locate-css', 'https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.css');
    wp_enqueue_script('leaflet-locate-js', 'https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.js', array('leaflet-js'), '0.79.0', true);
    
    // Gestion intelligente du chargement de Font Awesome
    $load_fa = $atts['load_fontawesome'];
    
    if ($load_fa === 'true') {
        // Forcer le chargement de Font Awesome
        wp_enqueue_style('terralize-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4');
    } elseif ($load_fa === 'auto') {
        // Mode automatique : vérifier si Font Awesome est déjà chargé
        $font_awesome_loaded = false;
        
        // Vérifier les différentes variantes de Font Awesome qui peuvent être chargées
        $fa_handles = array('font-awesome', 'fontawesome', 'bricks-font-awesome', 'fa', 'font-awesome-5', 'fontawesome-css');
        foreach ($fa_handles as $handle) {
            if (wp_style_is($handle, 'enqueued') || wp_style_is($handle, 'registered')) {
                $font_awesome_loaded = true;
                break;
            }
        }
        
        // Charger Font Awesome seulement si aucune version n'est détectée
        if (!$font_awesome_loaded) {
            wp_enqueue_style('terralize-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4');
        }
    }
    // Si $load_fa === 'false', ne rien charger
    
    // Chargement du fichier de style personnalisé pour la carte
    wp_enqueue_style('terralize-map-styles', plugin_dir_url(__FILE__) . '../assets/css/terralize-map.css');
    
    // Ajout de Select2
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
    
    // Charger notre script JS personnalisé
    wp_enqueue_script('terralize-map', plugin_dir_url(__FILE__) . '../scripts/terralize-map.js', array('leaflet-js', 'leaflet-control-geocoder', 'leaflet-locate-js', 'select2-js', 'jquery'), '1.0.0', true);
    
    // Forcer le type module pour le script terralize-map
    add_filter('script_loader_tag', function(
        $tag, $handle
    ) {
        if ($handle === 'terralize-map') {
            return str_replace('<script ', '<script type="module" ', $tag);
        }
        return $tag;
    }, 10, 2);
    
    // Passer les données à notre script
    // S'assurer que les données sont au format FeatureCollection pour Leaflet
    if (!empty($zones_data)) {
        if (!isset($zones_data['type'])) {
            // Si c'est un tableau de features, les transformer en FeatureCollection
            $zones_data = array(
                'type' => 'FeatureCollection',
                'features' => $zones_data
            );
        }
    } else {
        // Si aucune donnée n'est disponible, créer un FeatureCollection vide
        $zones_data = array(
            'type' => 'FeatureCollection',
            'features' => array()
        );
    }
    
    wp_localize_script('terralize-map', 'zonesData', $zones_data);
    wp_localize_script('terralize-map', 'terralizeFrontendOptions', $combined_options);
    wp_localize_script('terralize-map', 'terralizeCategories', array_map(function ($term) {
        return array(
            'id' => $term->term_id,
            'slug' => $term->slug,
            'name' => $term->name
        );
    }, $categories));
    
    // Générer un identifiant unique pour cette instance de carte
    $map_id = $combined_options['map_id'];
    
    // Commencer à capturer la sortie
    ob_start();
    
    // Convertir show_sidebar en booléen
    $show_sidebar = filter_var($atts['show_sidebar'], FILTER_VALIDATE_BOOLEAN);

    // Ajouter les variables CSS personnalisées
    $custom_css = '<style>';
    foreach ($style_vars as $key => $value) {
        $custom_css .= sprintf('--terralize-%s: %s;', str_replace('_', '-', $key), $value);
    }
    $custom_css .= '</style>';

    // Ajouter le CSS personnalisé avant la sortie
    echo $custom_css;
    ?>

    <div class="terralize-map-container">
        <!-- Définir les variables JavaScript globales avant le reste du code -->
        <script>
            // Déclarer les variables globales pour cette instance de carte
            window['mapId_<?php echo $map_id; ?>'] = '<?php echo $map_id; ?>';
            window.mapOptions = <?php echo json_encode($combined_options); ?>;
            
            // DONNÉES DE TEST DIRECTES pour garantir qu'elles sont bien transmises
            window.zonesData = <?php echo json_encode($zones_data); ?>;
            
            console.log("Variables globales initialisées pour la carte:", '<?php echo $map_id; ?>');
        </script>
        
        <div class="map-container-wrapper" data-map-id="<?php echo $map_id; ?>">
            <?php if ($show_sidebar) : ?>
            <!-- Bouton flèche pour ouvrir/fermer la sidebar -->
            <button id="sidebar-toggle-<?php echo $map_id; ?>" class="sidebar-toggle-btn">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Sidebar pour les filtres et résultats -->
            <div class="map-sidebar" data-map-id="<?php echo $map_id; ?>">
                <div class="sidebar-tabs">
                    <button class="tab-btn active" data-tab="filters" data-map-id="<?php echo $map_id; ?>">
                        Filtres <span id="results-counter-<?php echo $map_id; ?>">(0)</span>
                    </button>
                    <button class="tab-btn" data-tab="results" data-map-id="<?php echo $map_id; ?>">
                        Résultats <span id="results-count-<?php echo $map_id; ?>">(0)</span>
                    </button>
                    <button class="panel-close-btn" data-map-id="<?php echo $map_id; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- Panneau des filtres -->
                <div id="filters-panel-<?php echo $map_id; ?>" class="sidebar-panel active" data-map-id="<?php echo $map_id; ?>">
                    <div class="accordion-filters">
                        <h3>Filtrer les zones commerciales</h3>
                        
                        <!-- Filtre de recherche -->
                        <div class="filter-options search-filter">
                            <label for="search-filter-<?php echo $map_id; ?>">Rechercher</label>
                            <input type="text" id="search-filter-<?php echo $map_id; ?>" class="search-filter-input" placeholder="Rechercher par nom, titre...">
                        </div>

                        <!-- Accordéon Localisation -->
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span>Localisation</span>
                                <span class="accordion-icon">+</span>
                            </div>
                            <div class="accordion-content">
                                <?php if (!empty($regions)) : ?>
                                <div class="filter-options regions-filter">
                                    <label for="region-filter-<?php echo $map_id; ?>">Région</label>
                                    <select id="region-filter-<?php echo $map_id; ?>" class="region-filter-select">
                                        <option value="">Toutes les régions</option>
                                        <?php foreach ($regions as $slug => $name) : ?>
                                            <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <!-- Géolocalisation -->
                                <div class="location-action">
                                    <label>Ma position</label>
                                    <div class="geolocation-controls">
                                        <button id="locate-me-btn-<?php echo $map_id; ?>" class="locate-me-btn">
                                            <i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> Me localiser
                                        </button>
                                        <div id="geo-radius-container-<?php echo $map_id; ?>" style="display: none;">
                                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                                                <select id="geo-radius-filter-<?php echo $map_id; ?>" class="region-filter-select" style="flex-grow: 1;">
                                                    <option value="1">1 km</option>
                                                    <option value="2">2 km</option>
                                                    <option value="5" selected>5 km</option>
                                                    <option value="10">10 km</option>
                                                    <option value="20">20 km</option>
                                                    <option value="50">50 km</option>
                                                </select>
                                                <button id="apply-geo-filter-<?php echo $map_id; ?>" class="apply-filters-btn" style="margin-top: 0;">
                                                    Filtrer
                                                </button>
                                            </div>
                                            <div id="geo-status-<?php echo $map_id; ?>" style="margin-top: 8px; font-size: 12px; color: #666;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordéon Type d'affichage -->
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span>Type d'affichage</span>
                                <span class="accordion-icon">+</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-options type-filter">
                                    <label for="type-filter-<?php echo $map_id; ?>">Type</label>
                                    <select id="type-filter-<?php echo $map_id; ?>" class="type-filter-select">
                                        <option value="all">Tout afficher</option>
                                        <option value="zone">Zones commerciales</option>
                                        <option value="poi">Points de vente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Accordéon Catégories -->
                        <?php if (!empty($categories)) : ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span>Catégories</span>
                                <span class="accordion-icon">+</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-options categories-filter">
                                    <label for="categoryFilter-<?php echo $map_id; ?>">Catégories</label>
                                    <select id="categoryFilter-<?php echo $map_id; ?>" class="terralize-category-filter" multiple="multiple" data-placeholder="Sélectionner des catégories">
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?php echo esc_attr($category->slug); ?>"><?php echo esc_html($category->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="filter-actions">
                            <button id="apply-filter-<?php echo $map_id; ?>" class="apply-filters-btn">Appliquer les filtres</button>
                            <button id="reset-filter-<?php echo $map_id; ?>" class="reset-filters-btn">Réinitialiser</button>
                        </div>
                    </div>
                </div>

                <!-- Panneau des résultats -->
                <div id="results-panel-<?php echo $map_id; ?>" class="sidebar-panel" data-map-id="<?php echo $map_id; ?>">
                    <div class="panel-header">
                        <h2>Résultats <span id="tab-results-count-<?php echo $map_id; ?>">0</span> éléments</h2>
                    </div>
                    <div id="results-list-<?php echo $map_id; ?>" class="results-list">
                        <p class="no-results">Utilisez les filtres pour afficher les zones et points de vente</p>
                    </div>
                    <div class="pagination-controls">
                        <div class="pagination-info">
                            Page <span id="current-page-<?php echo $map_id; ?>">1</span> sur <span id="total-pages-<?php echo $map_id; ?>">1</span>
                        </div>
                        <div class="pagination-buttons">
                            <button id="prev-page-<?php echo $map_id; ?>" class="pagination-btn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>
                            <button id="next-page-<?php echo $map_id; ?>" class="pagination-btn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Conteneur de la carte -->
            <div class="map-main-container <?php echo $show_sidebar ? 'with-sidebar' : 'without-sidebar'; ?>">
                <div id="<?php echo $map_id; ?>" class="terralize-map" style="height: <?php echo esc_attr($atts['height']); ?>;"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Définir l'ID de la carte directement pour cette instance
        var mapId = '<?php echo $map_id; ?>';
        console.log("Script initialisé pour la carte:", mapId);
        
        // Variables pour les éléments de l'interface
        var sidebar = document.querySelector('.map-sidebar[data-map-id="' + mapId + '"]');
        var mapContainer = document.querySelector('.map-container-wrapper[data-map-id="' + mapId + '"]');
        var map = document.getElementById(mapId);
        
        // Gestionnaire d'événements pour le bouton "Me localiser"
        var locateMeBtn = document.getElementById('locate-me-btn-' + mapId);
        if (locateMeBtn) {
            locateMeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Utiliser une API alternative si la carte n'est pas disponible
                if (window.maps && window.maps[mapId]) {
                    // La carte est disponible, utiliser sa fonction de localisation
                    console.log("Utilisation de l'API terralizemap pour la localisation");
                    if (window.terralizemap && typeof window.terralizemap.locateUser === 'function') {
                        window.terralizemap.locateUser(mapId);
                    } else {
                        console.error("L'API terralizemap n'est pas disponible");
                    }
                } else if (window.terralizemap && typeof window.terralizemap.locateUser === 'function') {
                    // Utiliser l'API terralizemap si disponible
                    console.log("Utilisation de l'API terralizemap pour la localisation");
                    window.terralizemap.locateUser(mapId);
                } else {
                    // Fallback avec l'API de géolocalisation du navigateur
                    console.log("Fallback avec l'API de géolocalisation du navigateur");
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            var latitude = position.coords.latitude;
                            var longitude = position.coords.longitude;
                            console.log("Position obtenue:", latitude, longitude);
                            
                            // Afficher le conteneur du rayon
                            var geoRadiusContainer = document.getElementById('geo-radius-container-' + mapId);
                            if (geoRadiusContainer) {
                                geoRadiusContainer.style.display = 'block';
                            }
                            
                            // Mettre à jour le statut
                            var geoStatus = document.getElementById('geo-status-' + mapId);
                            if (geoStatus) {
                                geoStatus.textContent = 'Position trouvée: ' + latitude.toFixed(5) + ', ' + longitude.toFixed(5);
                            }
                            
                            // Si l'API terralizemap est chargée après l'obtention de la position, l'utiliser
                            if (window.terralizemap && typeof window.terralizemap.locateUser === 'function') {
                                window.terralizemap.locateUser(mapId, latitude, longitude);
                            } else {
                                console.warn("L'API terralizemap n'est toujours pas disponible pour utiliser les coordonnées", latitude, longitude);
                                // Essayer de stocker les coordonnées pour une utilisation future
                                window.userLatLng = L && L.latLng ? L.latLng(latitude, longitude) : {
                                    lat: latitude,
                                    lng: longitude
                                };
                            }
                        }, function(error) {
                            console.error("Erreur de géolocalisation:", error.message);
                            alert("Erreur de géolocalisation: " + error.message);
                        });
                    } else {
                        alert("La géolocalisation n'est pas supportée par votre navigateur.");
                    }
                }
            });
        }

        // Gestionnaire pour le bouton de filtrage par distance
        var applyGeoFilterBtn = document.getElementById('apply-geo-filter-' + mapId);
        if (applyGeoFilterBtn) {
            applyGeoFilterBtn.addEventListener('click', function() {
                if (!window.userLatLng) {
                    alert("Veuillez d'abord vous localiser.");
                    return;
                }
                
                var radiusSelect = document.getElementById('geo-radius-filter-' + mapId);
                if (!radiusSelect) return;
                
                var radius = parseInt(radiusSelect.value, 10);
                
                // Si l'API terralizemap est disponible, utiliser sa fonction de filtrage
                if (window.terralizemap && typeof window.terralizemap.filterByDistance === 'function') {
                    window.terralizemap.filterByDistance(window.userLatLng, radius * 1000);
                } else {
                    console.warn("Fonction de filtrage par distance non disponible");
                    alert("Le filtrage par distance n'est pas disponible pour le moment. Veuillez réessayer après le chargement complet de la page.");
                }
            });
        }

        // Force le recalcul des dimensions de la carte
        function updateMapSize() {
            // Récupérer la référence à l'objet Leaflet map à partir de window.maps
            var leafletMap = window.maps && window.maps[mapId];
            
            // Vérifier si la carte existe, sinon attendre qu'elle soit chargée
            if (!leafletMap && window.terralizemap && window.terralizemap.getMapInstance) {
                // Utiliser l'API terralizemap pour obtenir l'instance de carte
                leafletMap = window.terralizemap.getMapInstance(mapId);
                // Stocker la référence dans l'objet maps global
                if (leafletMap) {
                    window.maps[mapId] = leafletMap;
                }
            }
            // Sécurisation supplémentaire
            if (!leafletMap || typeof leafletMap.invalidateSize !== 'function') {
                console.error("Impossible de redimensionner la carte - la carte Leaflet n'est pas initialisée correctement", {mapId, maps: window.maps});
                return;
            }
            setTimeout(function() {
                leafletMap.invalidateSize();
            }, 300);
        }

        // Fonction pour ouvrir la sidebar
        function openSidebar(tabName) {
            if (mapContainer) mapContainer.classList.add('sidebar-open');
            if (sidebar) sidebar.classList.add('sidebar-visible');
            
            // Modifier l'icône du bouton toggle
            var toggleBtn = document.getElementById('sidebar-toggle-' + mapId);
            if (toggleBtn && toggleBtn.querySelector('i')) {
                toggleBtn.querySelector('i').className = 'fas fa-chevron-left';
            }
            
            // Activer l'onglet spécifié, ou 'filters' par défaut
            var targetTab = tabName || 'filters';
            var tabElement = document.querySelector('.tab-btn[data-tab="' + targetTab + '"][data-map-id="' + mapId + '"]');
            if (tabElement) {
                tabElement.click();
            } else {
                console.error("Onglet non trouvé:", targetTab, mapId);
            }
            
            // Essayer de redimensionner la carte, mais ne pas bloquer si ça échoue
            try {
                updateMapSize();
            } catch (e) {
                console.error("Erreur lors du redimensionnement de la carte:", e);
            }
        }

        // Fonction pour fermer la sidebar
        function closeSidebar() {
            if (mapContainer) mapContainer.classList.remove('sidebar-open');
            if (sidebar) sidebar.classList.remove('sidebar-visible');
            
            // Modifier l'icône du bouton toggle
            var toggleBtn = document.getElementById('sidebar-toggle-' + mapId);
            if (toggleBtn && toggleBtn.querySelector('i')) {
                toggleBtn.querySelector('i').className = 'fas fa-chevron-right';
            }
            
            // Essayer de redimensionner la carte, mais ne pas bloquer si ça échoue
            try {
                updateMapSize();
            } catch (e) {
                console.error("Erreur lors du redimensionnement de la carte:", e);
            }
        }

        // Écouteur d'événement pour le bouton de basculement de la sidebar
        var sidebarToggleBtn = document.getElementById('sidebar-toggle-' + mapId);
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', function() {
                if (mapContainer && mapContainer.classList.contains('sidebar-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        // Fermeture de la sidebar via le bouton de fermeture
        document.querySelectorAll('.panel-close-btn[data-map-id="' + mapId + '"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                closeSidebar();
            });
        });
        
        // Gestion des onglets
        document.querySelectorAll('.tab-btn[data-map-id="' + mapId + '"]').forEach(function(tab) {
            tab.addEventListener('click', function() {
                var tabName = this.getAttribute('data-tab');
                
                // Désactiver tous les onglets et panneaux
                document.querySelectorAll('.tab-btn[data-map-id="' + mapId + '"]').forEach(function(t) {
                    t.classList.remove('active');
                });
                
                document.querySelectorAll('.sidebar-panel[data-map-id="' + mapId + '"]').forEach(function(panel) {
                    panel.classList.remove('active');
                });
                
                // Activer l'onglet et le panneau sélectionnés
                this.classList.add('active');
                document.getElementById(tabName + '-panel-' + mapId).classList.add('active');
            });
        });

        // Gestionnaire pour les boutons de filtrage
        var applyFilterBtn = document.getElementById('apply-filter-' + mapId);
        if (applyFilterBtn) {
            applyFilterBtn.addEventListener('click', function() {
                var selectedCategories = [];
                
                // Récupérer les catégories sélectionnées via Select2 si disponible
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    var categoryFilter = jQuery('#categoryFilter-' + mapId);
                    if (categoryFilter.length) {
                        selectedCategories = categoryFilter.val() || [];
                    }
                }
                
                // Récupérer les autres valeurs de filtres
                var regionFilter = document.getElementById('region-filter-' + mapId) ? 
                                  document.getElementById('region-filter-' + mapId).value : '';
                var typeFilter = document.getElementById('type-filter-' + mapId) ? 
                                document.getElementById('type-filter-' + mapId).value : 'all';
                var searchQuery = document.getElementById('search-filter-' + mapId) ? 
                                 document.getElementById('search-filter-' + mapId).value : '';
                
                // Utiliser l'API terralizemap si disponible
                if (window.terralizemap && typeof window.terralizemap.filterData === 'function') {
                    window.terralizemap.filterData(mapId, {
                        categories: selectedCategories,
                        region: regionFilter,
                        type: typeFilter,
                        searchQuery: searchQuery
                    });
                } else {
                    console.error("API terralizemap.filterData non disponible");
                }
            });
        }
        
        // Gestionnaire pour le bouton de réinitialisation des filtres
        var resetFilterBtn = document.getElementById('reset-filter-' + mapId);
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', function() {
                // Utiliser l'API terralizemap si disponible
                if (window.terralizemap && typeof window.terralizemap.resetFilters === 'function') {
                    window.terralizemap.resetFilters(mapId);
                    
                    // Réinitialiser manuellement l'interface
                    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                        var categoryFilter = jQuery('#categoryFilter-' + mapId);
                        if (categoryFilter.length) {
                            categoryFilter.val(null).trigger('change');
                        }
                    }
                    
                    var regionFilter = document.getElementById('region-filter-' + mapId);
                    if (regionFilter) regionFilter.value = '';
                    
                    var typeFilter = document.getElementById('type-filter-' + mapId);
                    if (typeFilter) typeFilter.value = 'all';
                    
                    var searchFilter = document.getElementById('search-filter-' + mapId);
                    if (searchFilter) searchFilter.value = '';
                } else {
                    console.error("API terralizemap.resetFilters non disponible");
                }
            });
        }

        // Écouter les événements de filtrage
        document.addEventListener('terralize:filter_data', function(e) {
            if (e.detail && e.detail.mapId === mapId) {
                console.log("Événement de filtrage reçu:", e.detail);
            }
        });
        
        // Écouter les événements de réinitialisation des filtres
        document.addEventListener('terralize:reset_filters', function(e) {
            if (e.detail && e.detail.mapId === mapId) {
                console.log("Événement de réinitialisation des filtres reçu:", e.detail);
            }
        });

        // Initialisation de Select2
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            jQuery('.select2-filter').each(function() {
                jQuery(this).select2({
                    width: '100%',
                    placeholder: jQuery(this).attr('placeholder') || "Sélectionner...",
                    allowClear: true
                });
            });
        }
    });
    </script>
    <?php
    
    // Passer le map_id au script JS
    wp_localize_script('terralize-map', 'tracteurZoneMapId', $map_id);
    
    // Ajouter le script de débogage si l'option est activée
    $debug_mode = filter_var($atts['debug'], FILTER_VALIDATE_BOOLEAN);
    if ($debug_mode) {
        wp_enqueue_script('terralize-debug-helper', plugin_dir_url(__FILE__) . '../debug-helper.js', array('jquery'), '1.0.0', true);
        
        // Alerte pour signaler que le mode debug est activé
        $output = '<script>console.log("TERRALIZE MAP: Mode débogage activé!");</script>' . ob_get_clean();
        return $output;
    }
    
    // Retourner la sortie
    return ob_get_clean();
}
add_shortcode('tracteur_zone_map', 'tracteur_zone_map_shortcode'); 