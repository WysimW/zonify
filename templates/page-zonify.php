<?php

/**
 * Template Name: Page Carte des Commerciaux
 * Description: Modèle pour afficher la carte des commerciaux en plein écran avec filtres
 */
get_header();

// Récupération des zones (CPT "zone") depuis la base de données
$zones_query = new WP_Query(array(
    'post_type'      => 'zone',
    'posts_per_page' => -1
));

// Préparation des données GeoJSON pour la carte
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
            $terms = get_the_terms(get_the_ID(), 'zonify_category');
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
                    "commercial_id" => $comm_id,
                    'nom_commercial' => $nom_commercial,
                    'infos' => $infos,
                    'email' => $email,
                    'telephone' => $telephone,
                    'address' => $address,
                    'opening_hours' => $opening_hours,
                    'social_links' => $social_links,
                    'categories' => $zone_categories,
                    'regions' => $zone_regions,
                    'border_color' => $border_color,
                    'fill_color' => $fill_color
                ),
                'geometry' => json_decode($geojson, true)
            );
        }
    }
    wp_reset_postdata();
}

// Récupération des points d'intérêt (POI)
$poi_query = new WP_Query(array(
    'post_type'      => 'poi',
    'posts_per_page' => -1
));

if ($poi_query->have_posts()) {
    while ($poi_query->have_posts()) {
        $poi_query->the_post();
        $poi_geojson = get_post_meta(get_the_ID(), 'poi_geojson', true);

        if ($poi_geojson) {
            // Récupérer les catégories de ce POI
            $poi_categories = array();
            $terms = get_the_terms(get_the_ID(), 'zonify_category');
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
    'taxonomy' => 'zonify_category',
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

// Récupérer les options front-end de Zonify
$front_options = array(
    'tile_provider'    => get_option('zonify_tile_provider_front', 'cartodb_light'),
    'tile_custom_url'  => get_option('zonify_tile_custom_url_front', ''),
    'zone_fill_color'  => get_option('zonify_zone_fill_color_front', '#3388ff'),
    'zone_border_color' => get_option('zonify_zone_border_color_front', '#3388ff'),
    'zone_opacity'     => floatval(get_option('zonify_zone_opacity_front', 0.5)),
    'map_zoom'         => intval(get_option('zonify_map_zoom_front', 9)),
    'map_center_lat'   => get_option('zonify_map_center_lat_front', '50.5'),
    'map_center_lng'   => get_option('zonify_map_center_lng_front', '2.5'),
    'show_category_filter' => true,
);

$popup_options = array(
    'popup_show_address'       => get_option('zonify_popup_show_address', 0),
    'popup_show_hours'         => get_option('zonify_popup_show_hours', 0),
    'popup_show_social'        => get_option('zonify_popup_show_social', 0),
    'popup_font_family'        => get_option('zonify_popup_font_family', 'Arial, sans-serif'),
    'popup_font_size'          => get_option('zonify_popup_font_size', '14px'),
    'popup_font_color'         => get_option('zonify_popup_font_color', '#333333'),
    'popup_enable_email_btn'   => get_option('zonify_popup_enable_email_btn', 1),
    'popup_enable_phone_btn'   => get_option('zonify_popup_enable_phone_btn', 1),
    'popup_enable_contact_btn' => get_option('zonify_popup_enable_contact_btn', 0)
);

$contact_page_url = get_option('zonify_contact_page_url', '/contact');
$combined_options = array_merge($front_options, $popup_options, array(
    'contact_page_url' => $contact_page_url
));

// Enqueue Leaflet et les scripts/styles nécessaires
wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);

wp_enqueue_style('leaflet-control-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
wp_enqueue_script('leaflet-control-geocoder', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array('leaflet-js'), null, true);

// Chargement du fichier de style personnalisé pour la carte
wp_enqueue_style('map-styles', get_template_directory_uri() . '/css/map-styles.css');

// Ajout de Select2
wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
?>

<main id="primary" class="site-main fullwidth-map-page">
    <div class="map-hero">
        <div class="site-container">
            <div class="map-hero-content">
                <h1>Carte des Commerciaux</h1>
                <p>Trouvez facilement le commercial Tracteur Zone le plus proche de votre exploitation</p>
            </div>
        </div>
    </div>

    <div class="map-container-wrapper">
        <!-- Boutons de contrôle de l'interface -->
        <div class="map-controls">
            <button id="toggle-filters" class="control-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filtres
            </button>
            <button id="toggle-results" class="control-btn active">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                Résultats <span id="results-counter">(0)</span>
            </button>
            <button id="expand-map" class="control-btn">
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
                <button class="tab-btn" data-tab="filters">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Filtres
                </button>
                <button class="tab-btn active" data-tab="results">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    Résultats (<span id="tab-results-count">0</span>)
                </button>
            </div>

            <!-- Panneau des filtres -->
            <div id="filters-panel" class="sidebar-panel">
                <div class="panel-header">
                    <h2>Filtrer la carte</h2>
                    <button class="panel-close-btn">&times;</button>
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
                                <input type="text" id="search-filter" class="search-filter-input" placeholder="Rechercher par nom, titre..." />
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
                                <select id="type-filter" class="type-filter-select">
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
                                    <select id="region-filter" class="region-filter-select">
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
                                    <select id="categoryFilter" class="zonify-category-filter" multiple="multiple" data-placeholder="Sélectionner des catégories">
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?php echo esc_attr($category->slug); ?>"><?php echo esc_html($category->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="filter-actions">
                    <button id="applyFilter" class="apply-filters-btn">Appliquer les filtres</button>
                    <button id="resetFilter" class="reset-filters-btn">Réinitialiser</button>
                </div>
            </div>

            <!-- Panneau des résultats -->
            <div id="results-panel" class="sidebar-panel active">
                <div class="panel-header">
                    <h2>Résultats <span id="results-count">(0)</span></h2>
                    <button class="panel-close-btn">&times;</button>
                </div>

                <div id="results-list" class="results-list">
                    <p class="no-results">Utilisez les filtres pour afficher les zones et points de vente</p>
                </div>

                <!-- Contrôles de pagination -->
                <div class="pagination-controls">
                    <div class="pagination-info">
                        Page <span id="current-page">1</span> sur <span id="total-pages">1</span>
                    </div>
                    <div class="pagination-buttons">
                        <button id="prev-page" class="pagination-btn" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <button id="next-page" class="pagination-btn" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteneur principal de la carte -->
        <div class="map-main-container">
            <div id="map" style="height: 100%;"></div>
        </div>
    </div>

    <div class="map-cta-section">
        <div class="site-container">
            <div class="map-cta-content">
                <h2>Besoin d'assistance?</h2>
                <p>Vous ne trouvez pas de commercial dans votre zone? Contactez notre équipe centrale qui vous mettra en relation avec l'expert le plus proche ou disponible pour répondre à vos besoins.</p>
                <div class="cta-buttons">
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="cta-button primary">Contacter notre équipe</a>
                    <a href="tel:+33123456789" class="cta-button secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        01 23 45 67 89
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Passage des données PHP au JavaScript
    var zonesData = <?php echo json_encode($zones_data); ?>;
    var zonifyFrontendOptions = <?php echo json_encode($combined_options); ?>;
    var zonifyCategories = <?php echo json_encode(array_map(function ($term) {
                                return array(
                                    'id' => $term->term_id,
                                    'slug' => $term->slug,
                                    'name' => $term->name
                                );
                            }, $categories)); ?>;

    // Initialisation de la carte quand le DOM est chargé
    document.addEventListener('DOMContentLoaded', function() {
        var options = zonifyFrontendOptions || {};

        // 1) Choix du provider de tuiles
        var provider = options.tile_provider || 'cartodb_light';
        var tileLayerUrl, attribution;

        if (provider === 'cartodb_dark') {
            tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
        } else if (provider === 'osm') {
            tileLayerUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            attribution = '© OpenStreetMap contributors';
        } else if (provider === 'opentopo') {
            tileLayerUrl = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
            attribution = '© OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)';
        } else if (provider === 'esri_topo') {
            tileLayerUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}';
            attribution = 'Tiles © Esri — Source: Esri, USGS, NOAA';
        } else if (provider === 'custom') {
            tileLayerUrl = options.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            attribution = 'Personnalisé';
        } else {
            // Par défaut
            tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
            attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
        }

        // 2) Initialisation de la carte
        var zoom = options.map_zoom || 9;
        var centerLat = parseFloat(options.map_center_lat || 50.5);
        var centerLng = parseFloat(options.map_center_lng || 2.5);

        var map = L.map('map').setView([centerLat, centerLng], zoom);
        L.tileLayer(tileLayerUrl, {
            attribution: attribution
        }).addTo(map);

        // Exposer la carte globalement pour les interactions
        window.zonifyMap = map;

        // Ajouter le geocoder sur la carte
        var geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            position: 'topleft',
            placeholder: 'Rechercher une adresse...'
        }).on('markgeocode', function(e) {
            var bbox = e.geocode.bbox;
            var poly = L.polygon([
                bbox.getSouthEast(),
                bbox.getNorthEast(),
                bbox.getNorthWest(),
                bbox.getSouthWest()
            ]);
            map.fitBounds(poly.getBounds());

            // Mise à jour des résultats après recherche
            updateResultsFromSearch(e.geocode);
        }).addTo(map);

        // 3) Styles par défaut
        var defaultStyle = {
            color: options.zone_border_color || '#3388ff',
            fillColor: options.zone_fill_color || '#3388ff',
            fillOpacity: parseFloat(options.zone_opacity || 0.5),
            weight: 2
        };

        var poiStyle = {
            radius: 8,
            fillColor: "#ff7800",
            color: "#000",
            weight: 1,
            opacity: 1,
            fillOpacity: 0.8
        };

        // 4) Variable pour stocker la couche GeoJSON
        var geoJSONLayer;

        // Variables globales pour la pagination
        var currentPage = 1;
        var resultsPerPage = 5; // Nombre de résultats par page
        var allResults = []; // Stocke tous les résultats pour la pagination

        // Fonctions de pagination
        function updatePagination(totalResults) {
            var totalPages = Math.max(1, Math.ceil(totalResults.length / resultsPerPage));

            // Mettre à jour les compteurs
            document.getElementById('current-page').textContent = currentPage;
            document.getElementById('total-pages').textContent = totalPages;

            // Activer/désactiver les boutons de pagination
            var prevButton = document.getElementById('prev-page');
            var nextButton = document.getElementById('next-page');

            if (prevButton) {
                prevButton.disabled = currentPage <= 1;
            }

            if (nextButton) {
                nextButton.disabled = currentPage >= totalPages;
            }
        }

        function displayResultsPage(page) {
            // Vérifier si la page est valide
            var totalPages = Math.max(1, Math.ceil(allResults.length / resultsPerPage));
            if (page < 1) page = 1;
            if (page > totalPages) page = totalPages;

            currentPage = page;

            // Calculer les index de début et fin
            var startIndex = (page - 1) * resultsPerPage;
            var endIndex = Math.min(startIndex + resultsPerPage, allResults.length);

            // Récupérer la sous-liste des résultats pour cette page
            var pageResults = allResults.slice(startIndex, endIndex);

            // Mettre à jour le DOM avec cette page de résultats
            renderResultsList(pageResults);

            // Mettre à jour les contrôles de pagination
            updatePagination(allResults);
        }

        // 5) Fonction pour filtrer et afficher les données
        function filterAndRenderData(selectedCategories = [], regionFilter = '', typeFilter = 'all', searchQuery = '') {
            // Supprimer la couche existante si elle existe
            if (geoJSONLayer) {
                map.removeLayer(geoJSONLayer);
            }

            // Clone des données pour ne pas modifier l'original
            var filteredData = JSON.parse(JSON.stringify(zonesData));
            var displayedResults = [];

            // Préparation des filtres
            var selectedRegions = [];
            if (regionFilter && regionFilter !== '') {
                selectedRegions = [regionFilter]; // Convertir en tableau pour la compatibilité
            }

            // Normaliser la recherche (convertir en minuscules, supprimer les accents)
            var normalizedSearchQuery = '';
            if (searchQuery) {
                normalizedSearchQuery = searchQuery.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            }

            // Appliquer les filtres
            if (selectedCategories.length > 0 || selectedRegions.length > 0 || typeFilter !== 'all' || normalizedSearchQuery) {
                filteredData = filteredData.filter(function(feature) {
                    let matchesCategory = true;
                    let matchesRegion = true;
                    let matchesType = true;
                    let matchesSearch = true;

                    // Filtre par catégorie
                    if (selectedCategories.length > 0) {
                        if (!feature.properties.categories || feature.properties.categories.length === 0) {
                            matchesCategory = false;
                        } else {
                            matchesCategory = feature.properties.categories.some(function(category) {
                                return selectedCategories.includes(category.slug);
                            });
                        }
                    }

                    // Filtre par région basé sur la taxonomie 'region'
                    if (selectedRegions.length > 0) {
                        if (!feature.properties.regions || feature.properties.regions.length === 0) {
                            matchesRegion = false;
                        } else {
                            matchesRegion = feature.properties.regions.some(function(region) {
                                return selectedRegions.includes(region.slug);
                            });
                        }
                    }

                    // Filtre par type
                    if (typeFilter !== 'all') {
                        matchesType = feature.properties.type === typeFilter;
                    }

                    // Filtre par recherche textuelle
                    if (normalizedSearchQuery) {
                        matchesSearch = false;
                        
                        // Fonction pour normaliser le texte (minuscules, sans accents)
                        function normalizeText(text) {
                            if (!text) return '';
                            return text.toString().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        }
                        
                        // Vérifier dans le titre
                        if (feature.properties.title) {
                            if (normalizeText(feature.properties.title).includes(normalizedSearchQuery)) {
                                matchesSearch = true;
                            }
                        }
                        
                        // Vérifier dans le nom commercial pour les zones
                        if (feature.properties.type === 'zone' && feature.properties.nom_commercial) {
                            if (normalizeText(feature.properties.nom_commercial).includes(normalizedSearchQuery)) {
                                matchesSearch = true;
                            }
                        }
                        
                        // Vérifier dans les infos pour les zones
                        if (feature.properties.type === 'zone' && feature.properties.infos) {
                            if (normalizeText(feature.properties.infos).includes(normalizedSearchQuery)) {
                                matchesSearch = true;
                            }
                        }
                        
                        // Vérifier dans le téléphone pour les zones
                        if (feature.properties.type === 'zone' && feature.properties.telephone) {
                            if (normalizeText(feature.properties.telephone).includes(normalizedSearchQuery)) {
                                matchesSearch = true;
                            }
                        }
                        
                        // Vérifier dans l'email pour les zones
                        if (feature.properties.type === 'zone' && feature.properties.email) {
                            if (normalizeText(feature.properties.email).includes(normalizedSearchQuery)) {
                                matchesSearch = true;
                            }
                        }
                        
                        // Vérifier dans l'adresse pour les zones
                        if (feature.properties.type === 'zone' && feature.properties.address) {
                            if (normalizeText(feature.properties.address).includes(normalizedSearchQuery)) {
                                matchesSearch = true;
                            }
                        }
                        
                        // Vérifier dans les noms des catégories
                        if (feature.properties.categories && feature.properties.categories.length > 0) {
                            for (var i = 0; i < feature.properties.categories.length; i++) {
                                if (normalizeText(feature.properties.categories[i].name).includes(normalizedSearchQuery)) {
                                    matchesSearch = true;
                                    break;
                                }
                            }
                        }
                        
                        // Vérifier dans les noms des régions
                        if (feature.properties.regions && feature.properties.regions.length > 0) {
                            for (var i = 0; i < feature.properties.regions.length; i++) {
                                if (normalizeText(feature.properties.regions[i].name).includes(normalizedSearchQuery)) {
                                    matchesSearch = true;
                                    break;
                                }
                            }
                        }
                    }

                    // Si tous les filtres passent, ajouter aux résultats à afficher dans le panneau
                    if (matchesCategory && matchesRegion && matchesType && matchesSearch) {
                        // Pour les zones commerciales
                        if (feature.properties.type === 'zone') {
                            let regionNames = feature.properties.regions ? 
                                feature.properties.regions.map(r => r.name).join(', ') : '';
                            
                            displayedResults.push({
                                id: feature.properties.id,
                                name: feature.properties.nom_commercial || feature.properties.title,
                                region: regionNames || feature.properties.title,
                                products: feature.properties.categories ? feature.properties.categories.map(c => c.name).join(', ') : '',
                                phone: feature.properties.telephone || '',
                                type: 'zone' // Type pour le formatage
                            });
                        }
                        // Pour les points d'intérêt (POI)
                        else if (feature.properties.type === 'poi') {
                            let regionNames = feature.properties.regions ? 
                                feature.properties.regions.map(r => r.name).join(', ') : '';
                            
                            displayedResults.push({
                                id: feature.properties.id,
                                name: feature.properties.title,
                                categories: feature.properties.categories ? feature.properties.categories.map(c => c.name).join(', ') : '',
                                region: regionNames || '',
                                type: 'poi' // Type pour le formatage
                            });
                        }
                    }

                    return matchesCategory && matchesRegion && matchesType && matchesSearch;
                });
            } else {
                // Si aucun filtre, préparer quand même les résultats pour l'affichage
                filteredData.forEach(function(feature) {
                    if (feature.properties.type === 'zone') {
                        let regionNames = feature.properties.regions ? 
                            feature.properties.regions.map(r => r.name).join(', ') : '';
                            
                        displayedResults.push({
                            id: feature.properties.id,
                            name: feature.properties.nom_commercial || feature.properties.title,
                            region: regionNames || feature.properties.title,
                            products: feature.properties.categories ? feature.properties.categories.map(c => c.name).join(', ') : '',
                            phone: feature.properties.telephone || '',
                            type: 'zone'
                        });
                    } else if (feature.properties.type === 'poi') {
                        let regionNames = feature.properties.regions ? 
                            feature.properties.regions.map(r => r.name).join(', ') : '';
                            
                        displayedResults.push({
                            id: feature.properties.id,
                            name: feature.properties.title,
                            categories: feature.properties.categories ? feature.properties.categories.map(c => c.name).join(', ') : '',
                            region: regionNames || '',
                            type: 'poi'
                        });
                    }
                });
            }

            // Mise à jour du nombre de résultats et de la liste des résultats
            updateResultsCount(displayedResults.length);
            updateResultsList(displayedResults);

            // Créer et ajouter la nouvelle couche GeoJSON
            geoJSONLayer = L.geoJSON(filteredData, {
                style: function(feature) {
                    if (feature.properties.type !== 'poi') {
                        return {
                            color: feature.properties.border_color || defaultStyle.color,
                            fillColor: feature.properties.fill_color || defaultStyle.fillColor,
                            fillOpacity: defaultStyle.fillOpacity,
                            weight: defaultStyle.weight
                        };
                    }
                },
                pointToLayer: function(feature, latlng) {
                    if (feature.properties && feature.properties.type === 'poi') {
                        // Si le POI a une icône personnalisée, l'utiliser
                        if (feature.properties.icon) {
                            var icon = L.icon({
                                iconUrl: feature.properties.icon.url,
                                iconSize: [feature.properties.icon.width, feature.properties.icon.height],
                                iconAnchor: [feature.properties.icon.anchor_x, feature.properties.icon.anchor_y]
                            });
                            return L.marker(latlng, { icon: icon });
                        } else {
                            // Sinon, utiliser le marqueur circulaire par défaut
                            return L.circleMarker(latlng, poiStyle);
                        }
                    }
                    return L.marker(latlng);
                },
                onEachFeature: function(feature, layer) {
                    // Au clic sur la zone ou le POI
                    layer.on('click', function() {
                        // Construction du contenu de la popup 
                        var content = '<div class="popup-container" style="' +
                            'font-family:' + (options.popup_font_family || 'Arial,sans-serif') + ';' +
                            ' font-size:' + (options.popup_font_size || '14px') + ';' +
                            ' color:' + (options.popup_font_color || '#333') + ';">';

                        if (feature.properties.type === 'poi') {
                            // Popup pour les points d'intérêt
                            content += '<h2>' + (feature.properties.title || 'Point d\'intérêt') + '</h2>';

                            if (feature.properties.categories && feature.properties.categories.length > 0) {
                                content += '<p><strong>Catégories :</strong> ';
                                feature.properties.categories.forEach(function(cat, index) {
                                    content += cat.name;
                                    if (index < feature.properties.categories.length - 1) {
                                        content += ', ';
                                    }
                                });
                                content += '</p>';
                            }
                        } else {
                            // Popup pour les zones
                            content += '<h2>' + (feature.properties.nom_commercial || 'Commercial') + '</h2>';

                            if (feature.properties.infos) {
                                content += '<p>' + feature.properties.infos + '</p>';
                            }

                            if (feature.properties.categories && feature.properties.categories.length > 0) {
                                content += '<p><strong>Catégories :</strong> ';
                                feature.properties.categories.forEach(function(cat, index) {
                                    content += cat.name;
                                    if (index < feature.properties.categories.length - 1) {
                                        content += ', ';
                                    }
                                });
                                content += '</p>';
                            }

                            if (feature.properties.address &&
                                parseInt(options.popup_show_address) === 1) {
                                content += '<p><strong>Adresse :</strong> ' + feature.properties.address + '</p>';
                            }

                            if (feature.properties.opening_hours &&
                                parseInt(options.popup_show_hours) === 1) {
                                content += '<p><strong>Horaires :</strong> ' + feature.properties.opening_hours + '</p>';
                            }

                            if (feature.properties.social_links &&
                                parseInt(options.popup_show_social) === 1) {
                                var links = feature.properties.social_links.split(',');
                                content += '<p><strong>Réseaux sociaux :</strong> ';
                                links.forEach(function(link) {
                                    var trimmed = link.trim();
                                    if (trimmed) {
                                        content += '<a href="' + trimmed + '" target="_blank">' + trimmed + '</a> ';
                                    }
                                });
                                content += '</p>';
                            }

                            if (feature.properties.email &&
                                parseInt(options.popup_enable_email_btn) === 1) {
                                content += '<p><strong>Email :</strong> ' +
                                    '<a href="mailto:' + feature.properties.email + '">' +
                                    feature.properties.email + '</a></p>';
                            }

                            if (feature.properties.telephone &&
                                parseInt(options.popup_enable_phone_btn) === 1) {
                                content += '<p><strong>Téléphone :</strong> ' +
                                    '<a href="tel:' + feature.properties.telephone + '">' +
                                    feature.properties.telephone + '</a></p>';
                            }

                            if (parseInt(options.popup_enable_contact_btn) === 1) {
                                var commercialId = feature.properties.commercial_id || 0;
                                var contactUrl = options.contact_page_url || '/contact';
                                content += '<p><a href="' + contactUrl + '?commercial_id=' + commercialId + '" class="btn-contact">Contacter</a></p>';
                            }
                        }

                        content += '</div>';

                        // Ouvrir la popup
                        var popupLatLng;
                        if (layer.getBounds) {
                            popupLatLng = layer.getBounds().getCenter();
                        } else if (layer.getLatLng) {
                            popupLatLng = layer.getLatLng();
                        } else {
                            popupLatLng = map.getCenter();
                        }

                        L.popup()
                            .setLatLng(popupLatLng)
                            .setContent(content)
                            .openOn(map);
                    });
                }
            }).addTo(map);

            // Ajuster la vue si nécessaire
            if (filteredData.length > 0) {
                try {
                    if (geoJSONLayer && typeof geoJSONLayer.getBounds === 'function') {
                        map.fitBounds(geoJSONLayer.getBounds());
                    }
                } catch (e) {
                    console.error("Impossible d'ajuster la vue:", e);
                }
            }
        }

        // 6) Initialiser Select2 pour le filtre de catégories
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            jQuery('#categoryFilter').select2({
                placeholder: "Sélectionnez une ou plusieurs catégories",
                allowClear: true,
                theme: 'classic',
                width: '100%',
                closeOnSelect: false
            });

            jQuery('#region-filter').select2({
                width: '100%'
            });

            jQuery('#type-filter').select2({
                width: '100%'
            });
        }

        // 7) Gestionnaires d'événements pour le filtrage
        var applyFilterBtn = document.getElementById('applyFilter');
        var resetFilterBtn = document.getElementById('resetFilter');

        // Appliquer les filtres
        if (applyFilterBtn) {
            applyFilterBtn.addEventListener('click', function() {
                var selectedCategories = [];
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    selectedCategories = jQuery('#categoryFilter').val() || [];
                }

                var regionValue = document.getElementById('region-filter').value;
                var typeValue = document.getElementById('type-filter').value;
                var searchQuery = document.getElementById('search-filter').value;

                filterAndRenderData(selectedCategories, regionValue, typeValue, searchQuery);
            });
        }

        // Réinitialiser les filtres
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', function() {
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    jQuery('#categoryFilter').val(null).trigger('change');
                    jQuery('#region-filter').val('').trigger('change');
                    jQuery('#type-filter').val('all').trigger('change');
                }

                document.getElementById('search-filter').value = '';

                filterAndRenderData();

                // Réinitialiser la vue de la carte
                map.setView([centerLat, centerLng], zoom);
            });
        }

        // 8) Fonctions pour mettre à jour l'interface utilisateur
        function updateResultsCount(count) {
            var resultsCountEl = document.getElementById('results-count');
            if (resultsCountEl) {
                resultsCountEl.textContent = `(${count})`;
            }
        }

        function updateResultsList(results) {
            // Stocker tous les résultats pour la pagination
            allResults = results;

            var resultsListEl = document.getElementById('results-list');
            if (!resultsListEl) return;

            if (results.length === 0) {
                resultsListEl.innerHTML = '<p class="no-results">Aucun résultat ne correspond à vos critères.</p>';
                // Réinitialiser la pagination
                currentPage = 1;
                updatePagination(results);
                return;
            }

            // Reset à la première page lors d'une nouvelle recherche
            currentPage = 1;

            // Afficher la première page des résultats
            displayResultsPage(1);

            // Mettre à jour les contrôles de pagination
            updatePagination(results);
        }

        // Fonction pour rendre uniquement les résultats de la page courante
        function renderResultsList(pageResults) {
            var resultsListEl = document.getElementById('results-list');
            if (!resultsListEl) return;

            var html = '';
            pageResults.forEach(function(result) {
                // Format différent selon le type (zone commerciale ou POI)
                if (result.type === 'zone') {
                    // Format pour les zones commerciales
                    html += `
                <div class="result-item commercial-item">
                    <div class="result-badge zone">Zone</div>
                    <h4>${result.name || 'Sans nom'}</h4>
                    <div class="result-details">
                        <p><strong>Zone:</strong> ${result.region || 'Non spécifiée'}</p>
                        <p><strong>Services:</strong> ${result.products || 'Non spécifiés'}</p>
                        ${result.phone ? `<p><strong>Contact:</strong> <a href="tel:${result.phone.replace(/\s/g, '')}">${result.phone}</a></p>` : ''}
                    </div>
                    <button class="locate-on-map" data-id="${result.id}">
                        Localiser sur la carte
                    </button>
                </div>
                `;
                } else if (result.type === 'poi') {
                    // Format pour les points d'intérêt
                    html += `
                <div class="result-item poi-item">
                    <div class="result-badge poi">Point de vente</div>
                    <h4>${result.name || 'Sans nom'}</h4>
                    <div class="result-details">
                        <p><strong>Catégories:</strong> ${result.categories || 'Non spécifiées'}</p>
                    </div>
                    <button class="locate-on-map" data-id="${result.id}">
                        Localiser sur la carte
                    </button>
                </div>
                `;
                }
            });

            resultsListEl.innerHTML = html;

            // Ajouter les écouteurs d'événements pour la localisation sur la carte
            document.querySelectorAll('.locate-on-map').forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = parseInt(this.getAttribute('data-id'));

                    // Trouver l'élément correspondant dans les données
                    var feature = zonesData.find(f => f.properties.id === id);
                    if (feature && geoJSONLayer) {
                        // Trouver la couche correspondante dans la couche GeoJSON
                        geoJSONLayer.eachLayer(function(layer) {
                            if (layer.feature && layer.feature.properties.id === id) {
                                // Centrer la carte sur cette couche
                                if (layer.getBounds) {
                                    map.fitBounds(layer.getBounds());
                                } else if (layer.getLatLng) {
                                    map.setView(layer.getLatLng(), 13);
                                }

                                // Simuler un clic sur la couche pour ouvrir la popup
                                layer.fire('click');
                            }
                        });
                    }
                });
            });
        }

        function updateResultsFromSearch(result) {
            // Cette fonction serait à adapter pour récupérer les commerciaux proches du point recherché
            // Pour l'instant, on affiche tous les commerciaux dans les zones visibles
            var visibleResults = [];
            var bounds = map.getBounds();

            // Filtrer les zones visibles sur la carte
            zonesData.forEach(function(feature) {
                if (feature.properties.type === 'zone') {
                    // Vérifier si la zone est dans la vue actuelle (approximatif)
                    var isVisible = false;
                    try {
                        if (feature.geometry && feature.geometry.coordinates) {
                            // Pour les polygones
                            if (feature.geometry.type === 'Polygon') {
                                // Prendre le premier point comme approximation
                                var coord = feature.geometry.coordinates[0][0];
                                var latlng = L.latLng(coord[1], coord[0]);
                                isVisible = bounds.contains(latlng);
                            }
                            // Pour les points
                            else if (feature.geometry.type === 'Point') {
                                var coord = feature.geometry.coordinates;
                                var latlng = L.latLng(coord[1], coord[0]);
                                isVisible = bounds.contains(latlng);
                            }
                        }
                    } catch (e) {
                        console.error("Erreur lors de la vérification de visibilité:", e);
                    }

                    if (isVisible) {
                        visibleResults.push({
                            id: feature.properties.id,
                            name: feature.properties.nom_commercial || feature.properties.title,
                            region: result.name || 'Région',
                            products: feature.properties.categories ? feature.properties.categories.map(c => c.name).join(', ') : '',
                            phone: feature.properties.telephone || ''
                        });
                    }
                }
            });

            updateResultsCount(visibleResults.length);
            updateResultsList(visibleResults);
        }

        // 9) Afficher initialement toutes les données
        filterAndRenderData();

        // Gestionnaires d'événements pour les boutons de pagination
        var prevPageBtn = document.getElementById('prev-page');
        var nextPageBtn = document.getElementById('next-page');

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function() {
                if (currentPage > 1) {
                    displayResultsPage(currentPage - 1);
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function() {
                var totalPages = Math.ceil(allResults.length / resultsPerPage);
                if (currentPage < totalPages) {
                    displayResultsPage(currentPage + 1);
                }
            });
        }

        // 10) Gestionnaires d'événements pour l'UI améliorée
        // Gestion des onglets
        document.querySelectorAll('.tab-btn').forEach(function(tab) {
            tab.addEventListener('click', function() {
                // Désactiver tous les onglets et pannels
                document.querySelectorAll('.tab-btn').forEach(function(t) {
                    t.classList.remove('active');
                });
                document.querySelectorAll('.sidebar-panel').forEach(function(p) {
                    p.classList.remove('active');
                });

                // Activer l'onglet cliqué et le panneau correspondant
                this.classList.add('active');
                var tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-panel').classList.add('active');
            });
        });

        // Gestion de l'accordéon des filtres
        document.querySelectorAll('.accordion-header').forEach(function(header) {
            header.addEventListener('click', function() {
                // Toggle de la classe active pour l'élément parent
                var accordionItem = this.parentNode;
                accordionItem.classList.toggle('active');

                // Changer l'icône
                var icon = this.querySelector('.accordion-icon');
                if (accordionItem.classList.contains('active')) {
                    icon.textContent = '-';
                } else {
                    icon.textContent = '+';
                }
            });
        });

        // Boutons de contrôle
        var toggleFiltersBtn = document.getElementById('toggle-filters');
        var toggleResultsBtn = document.getElementById('toggle-results');
        var expandMapBtn = document.getElementById('expand-map');
        var sidebar = document.querySelector('.map-sidebar');
        var mapContainer = document.querySelector('.map-container-wrapper');

        if (toggleFiltersBtn) {
            toggleFiltersBtn.addEventListener('click', function() {
                // Activer l'onglet filtres
                document.querySelector('.tab-btn[data-tab="filters"]').click();

                // Ajouter la classe 'sidebar-visible' si pas déjà présente
                if (!sidebar.classList.contains('sidebar-visible')) {
                    sidebar.classList.add('sidebar-visible');
                    mapContainer.classList.add('sidebar-open');
                }
            });
        }

        if (toggleResultsBtn) {
            toggleResultsBtn.addEventListener('click', function() {
                // Activer l'onglet résultats
                document.querySelector('.tab-btn[data-tab="results"]').click();

                // Ajouter la classe 'sidebar-visible' si pas déjà présente
                if (!sidebar.classList.contains('sidebar-visible')) {
                    sidebar.classList.add('sidebar-visible');
                    mapContainer.classList.add('sidebar-open');
                }
            });
        }

        if (expandMapBtn) {
            expandMapBtn.addEventListener('click', function() {
                // Toggle mode plein écran pour la carte
                sidebar.classList.toggle('sidebar-visible');
                mapContainer.classList.toggle('sidebar-open');

                // Mettre à jour le texte du bouton
                if (sidebar.classList.contains('sidebar-visible')) {
                    this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                Plein écran
                `;
                } else {
                    this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                Réduire
                `;
                }

                // Redimensionner la carte après changement d'affichage
                setTimeout(function() {
                    map.invalidateSize();
                }, 300);
            });
        }

        // Fermeture des panneaux
        document.querySelectorAll('.panel-close-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                sidebar.classList.remove('sidebar-visible');
                mapContainer.classList.remove('sidebar-open');

                // Redimensionner la carte après changement d'affichage
                setTimeout(function() {
                    map.invalidateSize();
                }, 300);
            });
        });

        // Mise à jour du compteur dans l'onglet résultats
        function updateResultsCount(count) {
            var resultsCountEl = document.getElementById('results-count');
            var tabResultsCount = document.getElementById('tab-results-count');
            var resultsCounter = document.getElementById('results-counter');

            if (resultsCountEl) {
                resultsCountEl.textContent = `(${count})`;
            }

            if (tabResultsCount) {
                tabResultsCount.textContent = count;
            }

            if (resultsCounter) {
                resultsCounter.textContent = `(${count})`;
            }
        }
    });
</script>



<?php get_footer(); ?>