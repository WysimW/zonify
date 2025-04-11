<?php
/**
 * Template Name: Carte des Panneaux d'Affichage
 * Description: Modèle pour afficher la carte des panneaux d'affichage en plein écran avec filtres
 */
get_header();

// Récupération des panneaux (CPT "poi") depuis la base de données
$panneaux_query = new WP_Query(array(
    'post_type'      => 'poi',
    'posts_per_page' => -1
));

// Préparation des données GeoJSON pour la carte
$panneaux_data = array();
if ($panneaux_query->have_posts()) {
    while ($panneaux_query->have_posts()) {
        $panneaux_query->the_post();
        $geojson = get_post_meta(get_the_ID(), 'poi_geojson', true);

        if ($geojson) {
            // Récupération des métadonnées spécifiques des panneaux
            $panel_reference = get_post_meta(get_the_ID(), 'panel_reference', true);
            $panel_type = get_post_meta(get_the_ID(), 'panel_type', true);
            $panel_width = get_post_meta(get_the_ID(), 'panel_width', true);
            $panel_height = get_post_meta(get_the_ID(), 'panel_height', true);
            $panel_surface = get_post_meta(get_the_ID(), 'panel_surface', true);
            $panel_address = get_post_meta(get_the_ID(), 'panel_address', true);
            $panel_postal_code = get_post_meta(get_the_ID(), 'panel_postal_code', true);
            $panel_city_name = get_post_meta(get_the_ID(), 'panel_city_name', true);
            $panel_department = get_post_meta(get_the_ID(), 'panel_department', true);
            $panel_region = get_post_meta(get_the_ID(), 'panel_region', true);
            $panel_visibility = get_post_meta(get_the_ID(), 'panel_visibility', true);
            $panel_notes = get_post_meta(get_the_ID(), 'panel_notes', true);
            $panel_status = get_post_meta(get_the_ID(), 'panel_status', true);
            
            // Image du panneau
            $panel_image_id = get_post_meta(get_the_ID(), 'panel_image_id', true);
            $panel_image_url = '';
            if ($panel_image_id) {
                $panel_image_url = wp_get_attachment_image_url($panel_image_id, 'medium');
            }

            // Récupérer les catégories du panneau
            $panel_categories = array();
            $terms = get_the_terms(get_the_ID(), 'zonify_category');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $panel_categories[] = array(
                        'id' => $term->term_id,
                        'slug' => $term->slug,
                        'name' => $term->name
                    );
                }
            }

            // Récupérer les villes du panneau (taxonomie ville)
            $panel_cities = array();
            $city_terms = get_the_terms(get_the_ID(), 'city');
            if ($city_terms && !is_wp_error($city_terms)) {
                foreach ($city_terms as $term) {
                    $panel_cities[] = array(
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

            $panneaux_data[] = array(
                'type' => 'Feature',
                'properties' => array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'reference' => $panel_reference,
                    'panel_type' => $panel_type,
                    'width' => $panel_width,
                    'height' => $panel_height,
                    'surface' => $panel_surface,
                    'address' => $panel_address,
                    'postal_code' => $panel_postal_code,
                    'city_name' => $panel_city_name,
                    'department' => $panel_department,
                    'region' => $panel_region,
                    'visibility' => $panel_visibility,
                    'notes' => $panel_notes,
                    'status' => $panel_status,
                    'image' => $panel_image_url,
                    'categories' => $panel_categories,
                    'cities' => $panel_cities,
                    'icon' => !empty($icon_data) ? $icon_data : null
                ),
                'geometry' => json_decode($geojson, true)
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

// Récupérer tous les types de panneaux uniques pour le filtre
$panel_types = array();
global $wpdb;
$types_results = $wpdb->get_results(
    "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
     WHERE meta_key = 'panel_type' 
     AND meta_value != '' 
     AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'poi' AND post_status = 'publish')"
);

if ($types_results) {
    foreach ($types_results as $result) {
        $panel_types[] = $result->meta_value;
    }
}

// Récupérer toutes les villes pour les filtres
$cities = get_terms(array(
    'taxonomy' => 'city',
    'hide_empty' => true,
));

// Récupérer les départements uniques pour le filtre
$departments = array();
$dept_results = $wpdb->get_results(
    "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
     WHERE meta_key = 'panel_department' 
     AND meta_value != '' 
     AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'poi' AND post_status = 'publish')"
);

if ($dept_results) {
    foreach ($dept_results as $result) {
        $departments[] = $result->meta_value;
    }
}

// Récupérer les options front-end de Zonify
$front_options = array(
    'tile_provider'    => get_option('zonify_tile_provider_front', 'cartodb_light'),
    'tile_custom_url'  => get_option('zonify_tile_custom_url_front', ''),
    'map_zoom'         => intval(get_option('zonify_map_zoom_front', 9)),
    'map_center_lat'   => get_option('zonify_map_center_lat_front', '46.2276'),
    'map_center_lng'   => get_option('zonify_map_center_lng_front', '2.2137'),
    'show_category_filter' => true,
);

$popup_options = array(
    'popup_show_address'       => 1,
    'popup_show_details'       => 1,
    'popup_font_family'        => get_option('zonify_popup_font_family', 'Arial, sans-serif'),
    'popup_font_size'          => get_option('zonify_popup_font_size', '14px'),
    'popup_font_color'         => get_option('zonify_popup_font_color', '#333333'),
);

$combined_options = $front_options + $popup_options;

// Enqueue Leaflet et les scripts/styles nécessaires
wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);

wp_enqueue_style('leaflet-control-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
wp_enqueue_script('leaflet-control-geocoder', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array('leaflet-js'), null, true);

// Styles personnalisés pour la carte
// Le chemin est relatif à l'extension plutôt qu'au thème
wp_enqueue_style('ap-map-styles', plugin_dir_url(dirname(__FILE__)) . 'assets/css/map-styles.css');

// Ajout de Select2
wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);

// Ajout du script personnalisé pour la carte d'affichage
wp_enqueue_script('ap-map-frontend', plugin_dir_url(dirname(__FILE__)) . 'assets/js/affichage-premier-map.js', array('jquery', 'leaflet-js'), '1.0', true);
?>

<main id="primary" class="site-main fullwidth-map-page">
    <div class="map-hero" style="background-color: #70c141;">
        <div class="site-container">
            <div class="map-hero-content">
                <h1>Carte des Panneaux d'Affichage</h1>
                <p>Explorez notre réseau de panneaux publicitaires à travers la France</p>
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
                    <h2>Filtrer les panneaux</h2>
                    <button class="panel-close-btn">&times;</button>
                </div>

                <div class="accordion-filters">
                    <!-- Recherche textuelle -->
                    <div class="accordion-item active">
                        <div class="accordion-header">
                            <h3>Recherche</h3>
                            <span class="accordion-icon">-</span>
                        </div>
                        <div class="accordion-content">
                            <div class="filter-options search-filter">
                                <input type="text" id="search-filter" class="search-filter-input" placeholder="Rechercher par référence, adresse..." />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Type de panneau -->
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <h3>Type de panneau</h3>
                            <span class="accordion-icon">+</span>
                        </div>
                        <div class="accordion-content">
                            <div class="filter-options type-filter">
                                <select id="type-filter" class="type-filter-select">
                                    <option value="">Tous les types</option>
                                    <?php foreach ($panel_types as $type) : ?>
                                        <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ville -->
                    <?php if (!empty($cities)) : ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <h3>Ville</h3>
                                <span class="accordion-icon">+</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-options city-filter">
                                    <select id="city-filter" class="city-filter-select">
                                        <option value="">Toutes les villes</option>
                                        <?php foreach ($cities as $city) : ?>
                                            <option value="<?php echo esc_attr($city->slug); ?>"><?php echo esc_html($city->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Département -->
                    <?php if (!empty($departments)) : ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <h3>Département</h3>
                                <span class="accordion-icon">+</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-options department-filter">
                                    <select id="department-filter" class="department-filter-select">
                                        <option value="">Tous les départements</option>
                                        <?php foreach ($departments as $dept) : ?>
                                            <option value="<?php echo esc_attr($dept); ?>"><?php echo esc_html($dept); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Surface minimale -->
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <h3>Surface minimale (m²)</h3>
                            <span class="accordion-icon">+</span>
                        </div>
                        <div class="accordion-content">
                            <div class="filter-options surface-filter">
                                <input type="range" id="surface-filter" min="0" max="50" step="1" value="0">
                                <div class="surface-display">
                                    <span id="surface-value">0</span> m²
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catégories -->
                    <?php if (!empty($categories)) : ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <h3>Catégories</h3>
                                <span class="accordion-icon">+</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-options categories-filter">
                                    <select id="category-filter" class="category-filter-select" multiple="multiple" data-placeholder="Sélectionner des catégories">
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?php echo esc_attr($category->slug); ?>"><?php echo esc_html($category->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Statut du panneau -->
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <h3>Statut</h3>
                            <span class="accordion-icon">+</span>
                        </div>
                        <div class="accordion-content">
                            <div class="filter-options status-filter">
                                <select id="status-filter" class="status-filter-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="disponible">Disponible</option>
                                    <option value="reserve">Réservé</option>
                                    <option value="loue">Loué</option>
                                    <option value="maintenance">En maintenance</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-actions">
                    <button id="apply-filter" class="apply-filters-btn">Appliquer les filtres</button>
                    <button id="reset-filter" class="reset-filters-btn">Réinitialiser</button>
                </div>
            </div>

            <!-- Panneau des résultats -->
            <div id="results-panel" class="sidebar-panel active">
                <div class="panel-header">
                    <h2>Panneaux <span id="results-count">(0)</span></h2>
                    <button class="panel-close-btn">&times;</button>
                </div>

                <div id="results-list" class="results-list">
                    <p class="no-results">Utilisez les filtres pour afficher les panneaux d'affichage</p>
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

    <div class="map-cta-section" style="background-color: #f8f8f8;">
        <div class="site-container">
            <div class="map-cta-content">
                <h2>Besoin d'assistance?</h2>
                <p>Une question sur nos panneaux d'affichage? Nous sommes à votre disposition pour vous aider à trouver les emplacements parfaits pour votre campagne publicitaire.</p>
                <div class="cta-buttons">
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="cta-button primary" style="background-color: #70c141;">Contacter notre équipe</a>
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
    var panneauxData = <?php echo json_encode($panneaux_data); ?>;
    var mapOptions = <?php echo json_encode($combined_options); ?>;
</script>

<?php get_footer(); ?>