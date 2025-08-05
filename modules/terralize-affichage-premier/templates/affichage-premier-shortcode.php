<?php
/**
 * Shortcode pour la carte des panneaux d'affichage
 * Permet d'afficher la carte interactive des panneaux publicitaires
 * Usage : [affichage_premier_map height="600px" show_sidebar="true"]
 */

function affichage_premier_map_shortcode($atts) {
    // Attributs du shortcode avec valeurs par défaut
    $atts = shortcode_atts(array(
        'height' => '600px',            // Hauteur de la carte
        'show_sidebar' => 'true',       // Afficher la sidebar
        'center_lat' => '',             // Latitude du centre (si vide, utilise les options par défaut)
        'center_lng' => '',             // Longitude du centre (si vide, utilise les options par défaut)
        'zoom' => '',                   // Niveau de zoom (si vide, utilise les options par défaut)
        'department' => '',             // Filtre par département
        'format' => '',                 // Filtre par format de panneau
        'type' => '',                   // Filtre par type de panneau
        'support' => ''                 // Filtre par support de panneau
    ), $atts, 'affichage_premier_map');

    // Démarrer la capture de sortie
    ob_start();
    
    // Options de la carte
    $front_options = array(
        'map_center_lat'   => !empty($atts['center_lat']) ? $atts['center_lat'] : get_option('terralize_map_center_lat_front', '46.2276'),
        'map_center_lng'   => !empty($atts['center_lng']) ? $atts['center_lng'] : get_option('terralize_map_center_lng_front', '2.2137'),
        'map_zoom'         => !empty($atts['zoom']) ? intval($atts['zoom']) : intval(get_option('terralize_map_zoom_front', 6)),
        'show_category_filter' => true,
        'map_id'           => 'affichage-premier-map-' . uniqid(),
    );

    $popup_options = array(
        'popup_show_address'       => 1,
        'popup_show_details'       => 1,
        'popup_font_family'        => get_option('terralize_popup_font_family', 'Arial, sans-serif'),
        'popup_font_size'          => get_option('terralize_popup_font_size', '14px'),
        'popup_font_color'         => get_option('terralize_popup_font_color', '#333333'),
    );

    $combined_options = $front_options + $popup_options;

    // Récupérer le chemin vers le plugin terralize Affichage Premier
    $plugin_dir = WP_PLUGIN_DIR . '/zone-commercial-pluginwp/modules/terralize-affichage-premier';
    $plugin_url = plugins_url('/zone-commercial-pluginwp/modules/terralize-affichage-premier');

    // Enqueue Leaflet et les scripts/styles nécessaires
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);

    wp_enqueue_style('leaflet-control-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
    wp_enqueue_script('leaflet-control-geocoder', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array('leaflet-js'), null, true);

    // Ajouter le plugin de géolocalisation
    wp_enqueue_style('leaflet-locate-css', 'https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.css');
    wp_enqueue_script('leaflet-locate-js', 'https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.js', array('leaflet-js'), null, true);

    // Ajouter Font Awesome pour les icônes
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

    // Styles personnalisés pour la carte
    wp_enqueue_style('ap-map-styles', plugins_url('/assets/css/map-styles.css', dirname(__FILE__)));

    // Styles pour le formulaire de contact
    wp_enqueue_style('ap-contact-styles', plugins_url('/assets/css/contact.css', dirname(__FILE__)));

    // Ajout de Select2
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);

    // Ajout du script personnalisé pour la carte d'affichage (depuis le plugin)
    wp_enqueue_script('ap-map-frontend', $plugin_url . '/assets/js/affichage-premier-map.js', array('jquery', 'leaflet-js'), '1.0', true);

    // Charger les options d'icônes du module Affichage Premier
    $ap_icon_options = array(
        'poi_icon_url' => get_option('terralize_ap_poi_icon_url', '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/sucette_panneau_pin.svg'),
        'poi_icon_size' => get_option('terralize_ap_poi_icon_size', 30),
        'poi_icon_anchor_x' => get_option('terralize_ap_poi_icon_anchor_x', 15),
        'poi_icon_anchor_y' => get_option('terralize_ap_poi_icon_anchor_y', 40)
    );
    wp_localize_script('ap-map-frontend', 'terralizeAPIconOptions', $ap_icon_options);

    // Récupérer les points d'intérêt depuis la base de données
    $poi_args = array(
        'post_type'      => 'poi',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => 'panel_disponibilite',
                'value'   => 'Disponible',
                'compare' => '='
            ),
            array(
                'key'     => 'panel_disponibilite',
                'value'   => 'En disponibilité',
                'compare' => '='
            ),
        ),
    );
    
    // Ajouter des filtres supplémentaires si spécifiés dans les attributs
    $meta_filters = array();
    
    if (!empty($atts['department'])) {
        $meta_filters[] = array(
            'key'     => 'panel_departement',
            'value'   => $atts['department'],
            'compare' => '='
        );
    }
    
    if (!empty($atts['format'])) {
        $meta_filters[] = array(
            'key'     => 'panel_format',
            'value'   => $atts['format'],
            'compare' => '='
        );
    }
    
    if (!empty($atts['type'])) {
        $meta_filters[] = array(
            'key'     => 'panel_type',
            'value'   => $atts['type'],
            'compare' => '='
        );
    }
    
    if (!empty($atts['support'])) {
        $meta_filters[] = array(
            'key'     => 'panel_support',
            'value'   => $atts['support'],
            'compare' => '='
        );
    }
    
    // Ajouter les filtres supplémentaires à la meta_query si nécessaire
    if (!empty($meta_filters)) {
        $poi_args['meta_query']['relation'] = 'AND';
        $poi_args['meta_query'][] = array(
            'relation' => 'AND',
            $meta_filters
        );
    }

    $poi_query = new WP_Query($poi_args);
    $panneaux_data = array();

    if ($poi_query->have_posts()) {
        while ($poi_query->have_posts()) {
            $poi_query->the_post();

            // Récupérer les coordonnées depuis le champ GeoJSON
            $poi_geojson = get_post_meta(get_the_ID(), 'poi_geojson', true);
            $lat = '';
            $lng = '';

            // Tenter de parser le GeoJSON pour extraire les coordonnées
            if (!empty($poi_geojson)) {
                $geojson_data = json_decode($poi_geojson, true);

                // Vérifier si le JSON est valide et contient des coordonnées
                if ($geojson_data && isset($geojson_data['coordinates']) && is_array($geojson_data['coordinates']) && count($geojson_data['coordinates']) >= 2) {
                    // Dans GeoJSON, les coordonnées sont [longitude, latitude]
                    $lng = $geojson_data['coordinates'][0];
                    $lat = $geojson_data['coordinates'][1];
                }
            }

            // Ne pas ajouter les POI sans coordonnées
            if (empty($lat) || empty($lng)) {
                continue;
            }

            // Détails techniques
            $panel_width = get_post_meta(get_the_ID(), 'panel_width', true);
            $panel_height = get_post_meta(get_the_ID(), 'panel_height', true);
            $panel_surface = get_post_meta(get_the_ID(), 'panel_surface', true);
            $panel_reference = get_post_meta(get_the_ID(), 'panel_reference', true);
            $panel_type = get_post_meta(get_the_ID(), 'panel_type', true);
            $panel_status = get_post_meta(get_the_ID(), 'panel_disponibilite', true);
            $panel_support = get_post_meta(get_the_ID(), 'panel_support', true);
            $panel_format_standard = get_post_meta(get_the_ID(), 'panel_format_standard', true);
            $panel_format = get_post_meta(get_the_ID(), 'panel_format', true);

            // Adresse et localisation
            $address = get_post_meta(get_the_ID(), 'panel_address', true);
            $postal_code = get_post_meta(get_the_ID(), 'panel_postal_code', true);
            $city_name = get_post_meta(get_the_ID(), 'panel_city_name', true);
            $department = get_post_meta(get_the_ID(), 'panel_departement', true);

            // Visibilité
            $visibility_note = get_post_meta(get_the_ID(), 'visibility_note', true);

            // Image à la une
            $image = '';
            if (has_post_thumbnail()) {
                $image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
            }

            // Photo du panneau via panel_photo_id
            $photo_url = '';
            $photo_id = get_post_meta(get_the_ID(), 'panel_photo_id', true);
            if ($photo_id) {
                $photo_url = wp_get_attachment_image_url($photo_id, 'large');
            }

            // Catégories
            $categories = array();
            $terms = get_the_terms(get_the_ID(), 'poi_category');
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $categories[] = array(
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                    );
                }
            }

            // Villes
            $cities = array();
            $city_terms = get_the_terms(get_the_ID(), 'poi_city');
            if (!empty($city_terms) && !is_wp_error($city_terms)) {
                foreach ($city_terms as $city) {
                    $cities[] = array(
                        'id' => $city->term_id,
                        'name' => $city->name,
                        'slug' => $city->slug,
                    );
                }
            }

            // Créer un élément du tableau pour ce panneau
            $panneaux_data[] = array(
                'type' => 'Feature',
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array((float)$lng, (float)$lat)
                ),
                'properties' => array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'reference' => $panel_reference,
                    'panel_type' => $panel_type,
                    'support_type' => $panel_support,
                    'panel_support' => $panel_support,
                    'width' => $panel_width,
                    'height' => $panel_height,
                    'surface' => $panel_surface,
                    'format' => $panel_format,
                    'panel_format' => $panel_format,
                    'panel_format_standard' => $panel_format_standard,
                    'address' => $address,
                    'postal_code' => $postal_code,
                    'city_name' => $city_name,
                    'department' => $department,
                    'panel_departement' => $department,
                    'region' => get_post_meta(get_the_ID(), 'panel_region', true),
                    'panel_region' => get_post_meta(get_the_ID(), 'panel_region', true),
                    'status' => $panel_status,
                    'panel_disponibilite' => $panel_status,
                    'visibility_from' => get_post_meta(get_the_ID(), 'visibility_from', true),
                    'visibility_to' => get_post_meta(get_the_ID(), 'visibility_to', true),
                    'visibility_angle' => get_post_meta(get_the_ID(), 'visibility_angle', true),
                    'visibility_distance' => get_post_meta(get_the_ID(), 'visibility_distance', true),
                    'visibility_note' => get_post_meta(get_the_ID(), 'visibility_note', true),
                    'panel_traffic' => get_post_meta(get_the_ID(), 'panel_traffic', true),
                    'image' => $image,
                    'photo_url' => $photo_url,
                    'categories' => $categories,
                    'cities' => $cities,
                    'permalink' => get_permalink(),
                )
            );
        }
        wp_reset_postdata();
    }

    // Générer un identifiant unique pour cette instance de carte
    $map_id = $front_options['map_id'];

    // S'assurer que l'ID est disponible dans les options JavaScript
    $combined_options['map_id'] = $map_id;

    // Passer les données et options au script JavaScript
    wp_localize_script('ap-map-frontend', 'panneauxData', $panneaux_data);
    wp_localize_script('ap-map-frontend', 'mapOptions', $combined_options);
    
    // Ajouter le script de filtres
    wp_enqueue_script('ap-filters', plugins_url('/assets/js/filters.js', dirname(__FILE__)), array('jquery'), '1.0', true);
    
    // Convertir show_sidebar en booléen
    $show_sidebar = filter_var($atts['show_sidebar'], FILTER_VALIDATE_BOOLEAN);
    
    // Hauteur de la carte
    $map_height = esc_attr($atts['height']);
    
    // Début du HTML
    ?>
    <div class="affichage-premier-map-container">
        <!-- Définir les variables JavaScript globales avant le reste du code -->
        <script>
            // Déclarer les variables globales pour cette instance de carte
            window.mapId_<?php echo $map_id; ?> = '<?php echo $map_id; ?>';
            window.mapOptions = <?php echo json_encode($combined_options); ?>;
            window.panneauxData = <?php echo json_encode($panneaux_data); ?>;
            console.log("Variables globales initialisées pour la carte:", '<?php echo $map_id; ?>');
        </script>
        
        <div class="map-container-wrapper" data-map-id="<?php echo $map_id; ?>">
            <?php if ($show_sidebar) : ?>
            <!-- Les boutons map-controls ont été retirés, la flèche latérale gère désormais l'affichage de la sidebar -->

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

                <!-- Les mêmes panneaux de filtres et résultats que dans le template -->
                <!-- Panneau des filtres -->
                <div id="filters-panel-<?php echo $map_id; ?>" class="sidebar-panel active" data-map-id="<?php echo $map_id; ?>">
                    <div class="filters-container">
                        <h3>Filtrer les panneaux</h3>
                        
                        <!-- Inclure ici les mêmes filtres que dans le fichier template original -->
                        <div class="filter-group">
                            <label for="search-filter-<?php echo $map_id; ?>">Rechercher</label>
                            <input type="text" id="search-filter-<?php echo $map_id; ?>" placeholder="Recherchez par nom, référence, adresse...">
                        </div>

                        <div class="accordion-item active">
                            <div class="accordion-header">
                                <span>Localisation</span>
                                <span class="accordion-icon">-</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-group">
                                    <label for="department-filter-<?php echo $map_id; ?>">Département</label>
                                    <select id="department-filter-<?php echo $map_id; ?>" class="select2-filter">
                                        <option value="">Tous les départements</option>
                                        <?php
                                        // Récupérer tous les départements depuis les panneaux
                                        $departments = array();
                                        $args = array(
                                            'post_type' => 'poi',
                                            'posts_per_page' => -1,
                                            'fields' => 'ids',
                                        );
                                        $poi_query = new WP_Query($args);

                                        if ($poi_query->have_posts()) {
                                            foreach ($poi_query->posts as $poi_id) {
                                                $department = get_post_meta($poi_id, 'panel_departement', true);
                                                if (!empty($department) && !in_array($department, $departments)) {
                                                    $departments[] = $department;
                                                }
                                            }

                                            sort($departments);
                                            foreach ($departments as $dept) {
                                                echo '<option value="' . esc_attr($dept) . '">' . esc_html($dept) . '</option>';
                                            }
                                        }
                                        wp_reset_postdata();
                                        ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="center-city-filter-<?php echo $map_id; ?>">Ville centrale</label>
                                    <select id="center-city-filter-<?php echo $map_id; ?>" class="select2-filter">
                                        <option value="">Sélectionner une ville</option>
                                        <?php
                                        // Récupérer les communes depuis la base de données (CPT terralize_commune)
                                        $communes = array();
                                        $args = array(
                                            'post_type' => 'terralize_commune',
                                            'posts_per_page' => -1,
                                            'orderby' => 'title',
                                            'order' => 'ASC'
                                        );
                                        
                                        $communes_query = new WP_Query($args);
                                        
                                        if ($communes_query->have_posts()) {
                                            while ($communes_query->have_posts()) {
                                                $communes_query->the_post();
                                                $commune_id = get_the_ID();
                                                
                                                // Récupérer les métadonnées de la commune
                                                $nom = get_post_meta($commune_id, 'nom', true);
                                                $code_postal = get_post_meta($commune_id, 'code_postal', true);
                                                $latitude = get_post_meta($commune_id, 'latitude', true);
                                                $longitude = get_post_meta($commune_id, 'longitude', true);
                                                $departement = get_post_meta($commune_id, 'departement', true);
                                                
                                                // Créer une clé unique pour chaque commune
                                                $key = $nom . '-' . $code_postal;
                                                
                                                // Stocker les infos dans un tableau
                                                $communes[$key] = array(
                                                    'nom' => $nom,
                                                    'code_postal' => $code_postal,
                                                    'latitude' => $latitude,
                                                    'longitude' => $longitude,
                                                    'departement' => $departement
                                                );
                                            }
                                            wp_reset_postdata();
                                            
                                            // Trier par nom de commune
                                            uasort($communes, function($a, $b) {
                                                return strcmp($a['nom'], $b['nom']);
                                            });
                                            
                                            // Générer les options pour la liste déroulante, avec Arras en premier
                                            $arras_option = '';
                                            foreach ($communes as $key => $commune) {
                                                $display_name = $commune['nom'] . ' (' . $commune['code_postal'] . ', ' . $commune['departement'] . ')';
                                                $value = $key . '|' . $commune['latitude'] . '|' . $commune['longitude'];
                                                
                                                // Extraire et mettre en avant Arras
                                                if (strpos($commune['nom'], 'Arras') === 0) {
                                                    $arras_option = '<option value="' . esc_attr($value) . '">' . esc_html($display_name) . '</option>';
                                                } else {
                                                    echo '<option value="' . esc_attr($value) . '">' . esc_html($display_name) . '</option>';
                                                }
                                            }
                                            
                                            // Insérer Arras au début
                                            if (!empty($arras_option)) {
                                                echo '<option value="">---</option>';
                                                echo $arras_option;
                                                echo '<option value="">---</option>';
                                            }
                                        } else {
                                            echo '<option value="" disabled>Aucune commune trouvée</option>';
                                            
                                            // Message pour l'administrateur seulement
                                            if (current_user_can('manage_options')) {
                                                echo '<option value="" disabled>Veuillez importer les communes depuis le panneau d\'administration</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="filter-group">
                                    <label for="radius-km-filter-<?php echo $map_id; ?>">Rayon (km)</label>
                                    <select id="radius-km-filter-<?php echo $map_id; ?>" class="select2-filter">
                                        <option value="">Aucun filtre</option>
                                        <option value="5">5 km</option>
                                        <option value="10">10 km</option>
                                        <option value="20">20 km</option>
                                        <option value="30">30 km</option>
                                        <option value="50">50 km</option>
                                        <option value="100">100 km</option>
                                    </select>
                                </div>

                                <!-- Géolocalisation -->
                                <div class="filter-group">
                                    <label>Ma position</label>
                                    <div class="geolocation-controls">
                                        <button id="locate-me-btn-<?php echo $map_id; ?>" class="btn" style="background-color: #70c141; color: white; border: none; width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 4px; cursor: pointer;">
                                            <i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> Me localiser
                                        </button>
                                        <div id="geo-radius-container-<?php echo $map_id; ?>" style="display: none;">
                                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                                                <select id="geo-radius-filter-<?php echo $map_id; ?>" class="select2-filter" style="flex-grow: 1;">
                                                    <option value="1">1 km</option>
                                                    <option value="2">2 km</option>
                                                    <option value="5" selected>5 km</option>
                                                    <option value="10">10 km</option>
                                                    <option value="20">20 km</option>
                                                    <option value="50">50 km</option>
                                                </select>
                                                <button id="apply-geo-filter-<?php echo $map_id; ?>" class="btn" style="background-color: #70c141; color: white; border: none; padding: 8px 15px; border-radius: 4px; white-space: nowrap;">
                                                    Filtrer
                                                </button>
                                            </div>
                                            <div id="geo-status-<?php echo $map_id; ?>" style="margin-top: 8px; font-size: 12px; color: #666;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span>Caractéristiques</span>
                                <span class="accordion-icon">+</span>
                            </div>
                            <div class="accordion-content">
                                <div class="filter-group">
                                    <label for="type-filter-<?php echo $map_id; ?>">Type de panneau</label>
                                    <select id="type-filter-<?php echo $map_id; ?>" class="select2-filter">
                                        <option value="">Tous les types</option>
                                        <option value="DEROULANT">Déroulant</option>
                                        <option value="FIXE">Fixe</option>
                                        <option value="FIXE ECLAIRE">Fixe Éclairé</option>
                                        <option value="TRIVISION">Trivision</option>
                                        <option value="DIGITAL">Digital</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="support-filter-<?php echo $map_id; ?>">Support</label>
                                    <select id="support-filter-<?php echo $map_id; ?>" class="select2-filter">
                                        <option value="">Tous les supports</option>
                                        <option value="VITRINE">Vitrine</option>
                                        <option value="VITRINE MURALE">Vitrine Murale</option>
                                        <option value="TOLE">Tôle</option>
                                        <option value="LAME">Lame</option>
                                        <option value="TRIVISION">Trivision</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="format-filter-<?php echo $map_id; ?>">Format</label>
                                    <select id="format-filter-<?php echo $map_id; ?>" class="select2-filter">
                                        <option value="">Tous les formats</option>
                                        <option value="1,5M2">1,5M2</option>
                                        <option value="2M2">2M2</option>
                                        <option value="4M2">4M2</option>
                                        <option value="6M2">6M2</option>
                                        <option value="8M2">8M2</option>
                                        <option value="12M2">12M2</option>
                                        <option value="20M2">20M2</option>
                                        <option value="40M2">40M2</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="filter-actions">
                            <button id="apply-filter-<?php echo $map_id; ?>" class="btn btn-primary">Appliquer</button>
                            <button id="reset-filter-<?php echo $map_id; ?>" class="btn btn-outline">Réinitialiser</button>
                        </div>
                    </div>
                </div>

                <!-- Panneau des résultats -->
                <div id="results-panel-<?php echo $map_id; ?>" class="sidebar-panel" data-map-id="<?php echo $map_id; ?>">
                    <div class="results-header">
                        <h3>Résultats <span id="tab-results-count-<?php echo $map_id; ?>">0</span> panneaux</h3>
                    </div>
                    <div id="results-list-<?php echo $map_id; ?>" class="results-container">
                        <!-- Les résultats seront ajoutés dynamiquement via JS -->
                        <p class="no-results">Recherchez des panneaux en utilisant les filtres.</p>
                    </div>
                    <div class="pagination">
                        <button id="prev-page-<?php echo $map_id; ?>" class="pagination-btn" disabled>Précédent</button>
                        <span class="page-info">Page <span id="current-page-<?php echo $map_id; ?>">1</span> sur <span id="total-pages-<?php echo $map_id; ?>">1</span></span>
                        <button id="next-page-<?php echo $map_id; ?>" class="pagination-btn" disabled>Suivant</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Conteneur de la carte -->
            <div class="map-container">
                <div id="<?php echo $map_id; ?>" class="affichage-premier-map" style="height: <?php echo $map_height; ?>;"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Définir l'ID de la carte directement pour cette instance
        var mapId = '<?php echo $map_id; ?>';
        console.log("Script initialisé pour la carte:", mapId);
        
        // Variables pour les éléments de l'interface
        var toggleFiltersBtn = document.getElementById('toggle-filters-' + mapId);
        var toggleResultsBtn = document.getElementById('toggle-results-' + mapId);
        var expandMapBtn = document.getElementById('expand-map-' + mapId);
        var sidebar = document.querySelector('.map-sidebar[data-map-id="' + mapId + '"]');
        var mapContainer = document.querySelector('.map-container-wrapper[data-map-id="' + mapId + '"]');
        var map = document.getElementById(mapId);

        // Définir si l'utilisateur est administrateur
        window.isAdminUser = <?php echo current_user_can('edit_posts') ? 'true' : 'false'; ?>;
        console.log("L'utilisateur est administrateur:", window.isAdminUser);

        // Force le recalcul des dimensions de la carte
        function updateMapSize() {
            // Récupérer la référence à l'objet Leaflet map à partir de window.maps
            var leafletMap = window.maps[mapId];
            if (leafletMap && typeof leafletMap.invalidateSize === 'function') {
                setTimeout(function() {
                    leafletMap.invalidateSize();
                }, 300);
            } else {
                console.error("Impossible de redimensionner la carte - la carte Leaflet n'est pas initialisée correctement", {mapId, map: leafletMap});
            }
        }

        // Fonction pour ouvrir la sidebar
        function openSidebar(tabName) {
            mapContainer.classList.add('sidebar-open');
            sidebar.classList.add('sidebar-visible');
            
            // Activer l'onglet spécifié
            if (tabName) {
                var tabElement = document.querySelector('.tab-btn[data-tab="' + tabName + '"][data-map-id="' + mapId + '"]');
                if (tabElement) {
                    tabElement.click();
                } else {
                    console.error("Onglet non trouvé:", tabName, mapId);
                }
            }
            
            // Changer le texte du bouton plein écran
            expandMapBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
            Réduire
            `;
            
            updateMapSize();
        }

        // Fonction pour fermer la sidebar
        function closeSidebar() {
            mapContainer.classList.remove('sidebar-open');
            sidebar.classList.remove('sidebar-visible');
            
            // Changer le texte du bouton plein écran
            expandMapBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
            Plein écran
            `;
            
            updateMapSize();
        }

        // Écouteurs d'événements pour les boutons
        if (toggleFiltersBtn) {
            toggleFiltersBtn.addEventListener('click', function() {
                openSidebar('filters');
            });
        }
        
        if (toggleResultsBtn) {
            toggleResultsBtn.addEventListener('click', function() {
                openSidebar('results');
            });
        }

        if (expandMapBtn) {
            expandMapBtn.addEventListener('click', function() {
                if (this.textContent.trim() === 'Réduire') {
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

        // Gestion des accordéons
        document.querySelectorAll('.accordion-item').forEach(function(item) {
            var header = item.querySelector('.accordion-header');
            if (header) {
                header.addEventListener('click', function() {
                    item.classList.toggle('active');
                    var iconSpan = this.querySelector('.accordion-icon');
                    if (iconSpan) {
                        iconSpan.textContent = item.classList.contains('active') ? '-' : '+';
                    }
                });
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
    
    // Terminer la capture et retourner le contenu
    return ob_get_clean();
}

// Enregistrer le shortcode
add_shortcode('affichage_premier_map', 'affichage_premier_map_shortcode'); 