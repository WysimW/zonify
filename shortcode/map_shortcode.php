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
                        'regions' => $zone_regions
                    ),
                    'geometry' => json_decode($geojson, true)
                );
            }
        }
        wp_reset_postdata();
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

                $zones_data[] = array(
                    'type' => 'Feature',
                    'properties' => array(
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'type' => 'poi',
                        'categories' => $poi_categories,
                        'regions' => $poi_regions,
                        'icon' => !empty($icon_data) ? $icon_data : null
                    ),
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
        'map_zoom'         => intval(get_option('terralize_map_zoom_front', 9)),
        'map_center_lat'   => get_option('terralize_map_center_lat_front', '50.5'),
        'map_center_lng'   => get_option('terralize_map_center_lng_front', '2.5'),
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
        'contact_page_url' => $contact_page_url
    ));

    // Enqueue Leaflet et les scripts/styles nécessaires
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    
    wp_enqueue_style('leaflet-control-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
    wp_enqueue_script('leaflet-control-geocoder', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array('leaflet-js'), null, true);
    
    // Ajout du plugin de géolocalisation Leaflet.locate
    wp_enqueue_style('leaflet-locate-css', 'https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.css');
    wp_enqueue_script('leaflet-locate-js', 'https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.js', array('leaflet-js'), '0.79.0', true);
    
    // Chargement du fichier de style personnalisé pour la carte
    wp_enqueue_style('tracteur-zone-map-styles', plugin_dir_url(__FILE__) . '../css/tracteur-zone-map.css');
    
    // Ajout de Select2
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
    
    // Chargement du script multi-select personnalisé
    wp_enqueue_script('tracteur-zone-multi-select', plugin_dir_url(__FILE__) . '../scripts/tracteur-zone-multi-select.js', array('jquery'), '1.0.0', true);
    
    // Charger notre script JS personnalisé
    wp_enqueue_script('tracteur-zone-map', plugin_dir_url(__FILE__) . '../scripts/tracteur-zone-map.js', array('leaflet-js', 'leaflet-control-geocoder', 'leaflet-locate-js', 'select2-js', 'jquery', 'tracteur-zone-multi-select'), '1.0.0', true);
    
    // Passer les données à notre script
    wp_localize_script('tracteur-zone-map', 'zonesData', $zones_data);
    wp_localize_script('tracteur-zone-map', 'terralizeFrontendOptions', $combined_options);
    wp_localize_script('tracteur-zone-map', 'terralizeCategories', array_map(function ($term) {
        return array(
            'id' => $term->term_id,
            'slug' => $term->slug,
            'name' => $term->name
        );
    }, $categories));
    
    // Générer un identifiant unique pour cette instance de carte
    $map_id = 'tracteur-zone-map-' . uniqid();
    
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
    <div class="tracteur-zone-map-container">
        <div class="map-container-wrapper">
            <?php if ($show_sidebar) : ?>
            <!-- Boutons de contrôle de l'interface -->
            <div class="map-controls">
                <button id="toggle-filters-<?php echo $map_id; ?>" class="control-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Filtres
                </button>
                <button id="toggle-results-<?php echo $map_id; ?>" class="control-btn active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    Résultats <span id="results-counter-<?php echo $map_id; ?>">(0)</span>
                </button>
                <button id="expand-map-<?php echo $map_id; ?>" class="control-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <polyline points="9 21 3 21 3 15"></polyline>
                        <line x1="21" y1="3" x2="14" y2="10"></line>
                        <line x1="3" y1="21" x2="10" y2="14"></line>
                    </svg>
                    Plein écran
                </button>
            </div>

            <!-- Panneau latéral avec onglets -->
            <div class="map-sidebar">
                <div class="sidebar-tabs">
                    <button class="tab-btn" data-tab="filters" data-map-id="<?php echo $map_id; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        Filtres
                    </button>
                    <button class="tab-btn active" data-tab="results" data-map-id="<?php echo $map_id; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        Résultats <span id="tab-results-count-<?php echo $map_id; ?>">0</span>
                    </button>
                    <button class="tab-btn location-tab" data-tab="location" data-map-id="<?php echo $map_id; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Localisation
                    </button>
                </div>

                <!-- Panneau des filtres -->
                <div id="filters-panel-<?php echo $map_id; ?>" class="sidebar-panel">
                    <div class="panel-header">
                        <h2>Filtrer la carte</h2>
                        <button class="panel-close-btn" data-map-id="<?php echo $map_id; ?>">&times;</button>
                    </div>

                    <div class="accordion-filters">
                        <!-- Ajout du champ de recherche textuel en premier -->
                        <div class="accordion-item active">
                            <div class="accordion-header">
                                <h3>Recherche</h3>
                                <span class="accordion-icon">-</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-options search-filter">
                                    <input type="text" id="search-filter-<?php echo $map_id; ?>" class="search-filter-input" placeholder="Rechercher par nom, titre..." />
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <h3>Type d'affichage</h3>
                                <span class="accordion-icon">+</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-options type-filter">
                                    <select id="type-filter-<?php echo $map_id; ?>" class="type-filter-select">
                                        <option value="all">Tout afficher</option>
                                        <option value="zone">Zones commerciales</option>
                                        <option value="poi">Points de vente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($regions)) : ?>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <h3>Régions</h3>
                                    <span class="accordion-icon">+</span>
                                </div>
                                <div class="accordion-content">
                                    <div class="filter-options regions-filter">
                                        <select id="region-filter-<?php echo $map_id; ?>" class="region-filter-select">
                                            <option value="">Toutes les régions</option>
                                            <?php foreach ($regions as $slug => $name) : ?>
                                                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($categories)) : ?>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <h3>Catégories</h3>
                                    <span class="accordion-icon">+</span>
                                </div>
                                <div class="accordion-content">
                                    <div class="filter-options categories-filter">
                                        <select id="categoryFilter-<?php echo $map_id; ?>" class="terralize-category-filter" multiple="multiple" data-placeholder="Sélectionner des catégories">
                                            <?php foreach ($categories as $category) : ?>
                                                <option value="<?php echo esc_attr($category->slug); ?>"><?php echo esc_html($category->name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bouton de géolocalisation personnalisé -->
                    <!-- Les boutons de localisation ont été déplacés dans leur propre onglet -->

                    <div class="filter-actions">
                        <button id="applyFilter-<?php echo $map_id; ?>" class="apply-filters-btn">Appliquer les filtres</button>
                        <button id="resetFilter-<?php echo $map_id; ?>" class="reset-filters-btn">Réinitialiser</button>
                    </div>
                </div>

                <!-- Panneau des résultats -->
                <div id="results-panel-<?php echo $map_id; ?>" class="sidebar-panel active">
                    <div class="panel-header">
                        <h2>Résultats <span id="results-count-<?php echo $map_id; ?>">(0)</span></h2>
                        <button class="panel-close-btn" data-map-id="<?php echo $map_id; ?>">&times;</button>
                    </div>

                    <div id="results-list-<?php echo $map_id; ?>" class="results-list">
                        <p class="no-results">Utilisez les filtres pour afficher les zones et points de vente</p>
                    </div>

                    <!-- Contrôles de pagination -->
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
                
                <!-- Panneau de localisation -->
                <div id="location-panel-<?php echo $map_id; ?>" class="sidebar-panel">
                    <div class="panel-header">
                        <h2>Options de localisation</h2>
                        <button class="panel-close-btn" data-map-id="<?php echo $map_id; ?>">&times;</button>
                    </div>
                    
                    <div class="location-options">
                        <div class="location-option-card">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Ma position
                            </h3>
                            <p>Affichez votre position actuelle sur la carte pour visualiser les zones commerciales à proximité.</p>
                            <button id="locateMe-<?php echo $map_id; ?>" class="locate-me-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Me localiser
                            </button>
                        </div>
                        
                        <div class="location-option-card">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Point de vente le plus proche
                            </h3>
                            <p>Trouvez le point de vente ou la concession la plus proche de votre position actuelle.</p>
                            <button id="findNearestPoi-<?php echo $map_id; ?>" class="find-nearest-poi-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Trouver la concession la plus proche
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Conteneur principal de la carte -->
            <div class="map-main-container <?php echo $show_sidebar ? 'with-sidebar' : 'without-sidebar'; ?>">
                <div id="<?php echo $map_id; ?>" class="tracteur-zone-map" style="height: <?php echo esc_attr($atts['height']); ?>;"></div>
            </div>
        </div>
    </div>

    <?php
    // Passer le map_id au script JS
    wp_localize_script('tracteur-zone-map', 'tracteurZoneMapId', $map_id);
    
    // Retourner la sortie
    return ob_get_clean();
}
add_shortcode('tracteur_zone_map', 'tracteur_zone_map_shortcode'); 