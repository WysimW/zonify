<?php
function zonify_frontend_shortcode($atts) {
    // Attributs du shortcode
    $atts = shortcode_atts(array(
        'categories' => '',  // Filtrage par catégorie(s)
        'regions' => '',     // Filtrage par région(s)
    ), $atts, 'zonify_map');
    
    // 1. Récupérer toutes les zones (CPT "zone") avec filtrage par catégorie possible
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
            'taxonomy' => 'zonify_category',
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
    
    $query = new WP_Query($args);
    $zones = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
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

                if ($comm_id) {
                    $nom_commercial = get_the_title($comm_id);
                    $infos = get_the_excerpt($comm_id);
                    $email = get_post_meta($comm_id, 'commercial_email', true);
                    $telephone = get_post_meta($comm_id, 'commercial_telephone', true);
                    $address = get_post_meta($comm_id, 'commercial_address', true);
                    $opening_hours = get_post_meta($comm_id, 'commercial_opening_hours', true);
                    $social_links = get_post_meta($comm_id, 'commercial_social_links', true);
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

                $zones[] = array(
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
                        'regions' => $zone_regions
                    ),
                    'geometry' => json_decode($geojson, true)
                );
            }
        }
        wp_reset_postdata();
    }
    
    // Récupérer également les points d'intérêt (POI)
    $poi_args = array(
        'post_type'      => 'poi',
        'posts_per_page' => -1
    );
    
    // Tableau pour stocker les conditions de requête tax_query pour les POI
    $poi_tax_query = array('relation' => 'AND');
    
    // Si des catégories sont spécifiées, les utiliser pour filtrer également les POI
    if (!empty($atts['categories'])) {
        $poi_tax_query[] = array(
            'taxonomy' => 'zonify_category',
            'field'    => 'slug',
            'terms'    => array_map('trim', explode(',', $atts['categories'])),
        );
    }
    
    // Si des régions sont spécifiées, les utiliser pour filtrer également les POI
    if (!empty($atts['regions'])) {
        $poi_tax_query[] = array(
            'taxonomy' => 'region',
            'field'    => 'slug',
            'terms'    => array_map('trim', explode(',', $atts['regions'])),
        );
    }
    
    // Ajouter la tax_query si elle contient des éléments
    if (count($poi_tax_query) > 1) {
        $poi_args['tax_query'] = $poi_tax_query;
    }
    
    $poi_query = new WP_Query($poi_args);
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

                $zones[] = array(
                    'type' => 'Feature',
                    'properties' => array(
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'type' => 'poi',
                        'categories' => $poi_categories,
                        'regions' => $poi_regions
                    ),
                    'geometry' => json_decode($poi_geojson, true)
                );
            }
        }
        wp_reset_postdata();
    }

    // 2. Enqueue Leaflet et Leaflet Control Geocoder
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);

    wp_enqueue_style('leaflet-control-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
    wp_enqueue_script('leaflet-control-geocoder', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array('leaflet-js'), null, true);

    // Ajout de Select2
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);

    // Enqueue script + style custom
    wp_enqueue_script('zonify-frontend', plugin_dir_url(__FILE__) . '../scripts/zonify-frontend.js', array('leaflet-js','leaflet-control-geocoder', 'select2-js'), '1.0.1', true);
    wp_enqueue_style('zonify-frontend-css', plugin_dir_url(__FILE__) . '../assets/css/zc-frontend.css', array('leaflet-css', 'select2-css'), '1.0.1');

    // 3. Passage des zones à JavaScript
    wp_localize_script('zonify-frontend', 'zonesData', $zones);
    
    // Récupérer toutes les catégories pour le filtre frontend
    $all_categories = get_terms(array(
        'taxonomy' => 'zonify_category',
        'hide_empty' => true,
    ));
    
    $categories_for_js = array();
    if (!is_wp_error($all_categories) && !empty($all_categories)) {
        foreach ($all_categories as $category) {
            $categories_for_js[] = array(
                'id' => $category->term_id,
                'slug' => $category->slug,
                'name' => $category->name
            );
        }
    }
    
    // Passer les catégories au JavaScript
    wp_localize_script('zonify-frontend', 'zonifyCategories', $categories_for_js);
    
    // Récupérer toutes les régions pour le filtre frontend
    $all_regions = get_terms(array(
        'taxonomy' => 'region',
        'hide_empty' => true,
    ));
    
    $regions_for_js = array();
    if (!is_wp_error($all_regions) && !empty($all_regions)) {
        foreach ($all_regions as $region) {
            $regions_for_js[] = array(
                'id' => $region->term_id,
                'slug' => $region->slug,
                'name' => $region->name
            );
        }
    }
    
    // Passer les régions au JavaScript
    wp_localize_script('zonify-frontend', 'zonifyRegions', $regions_for_js);

    // 4. Récupérer + localiser les options front
    $front_options = array(
        'tile_provider'    => get_option('zonify_tile_provider_front', 'cartodb_light'),
        'tile_custom_url'  => get_option('zonify_tile_custom_url_front', ''),
        'zone_fill_color'  => get_option('zonify_zone_fill_color_front', '#3388ff'),
        'zone_border_color'=> get_option('zonify_zone_border_color_front', '#3388ff'),
        'zone_opacity'     => floatval(get_option('zonify_zone_opacity_front', 0.5)),
        'map_zoom'         => intval(get_option('zonify_map_zoom_front', 9)),
        'map_center_lat'   => get_option('zonify_map_center_lat_front', '50.5'),
        'map_center_lng'   => get_option('zonify_map_center_lng_front', '2.5'),
        'show_category_filter' => true, // Activer le filtre par catégorie
        'show_region_filter' => true,   // Activer le filtre par région
        // Les deux réglages geocoder
        'geocoder_mode'     => get_option('zonify_geocoder_mode_front', 'on_map'),
        'geocoder_position' => get_option('zonify_geocoder_position_front', 'topleft'),
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
    wp_localize_script('zonify-frontend', 'zonifyFrontendOptions', $combined_options);

    // 5. Retourner la div #map avec le filtre par catégorie
    ob_start();
    ?>
    <!-- Si on veut un champ de recherche hors de la carte : -->
    <div id="outsideSearchContainer" style="display:none;">
        <input type="text" id="searchInput" placeholder="Rechercher une ville..." />
        <button id="searchBtn">Recherche</button>
    </div>
    
    <!-- Ajout du filtre par catégorie avec Select2 -->
    <div id="zonifyFilterContainer" class="zonify-filter-container">
        <label for="categoryFilter">Filtrer par catégorie :</label>
        <select id="categoryFilter" class="zonify-category-filter" multiple="multiple" data-placeholder="Sélectionner des catégories">
            <?php foreach ($categories_for_js as $category) : ?>
            <option value="<?php echo esc_attr($category['slug']); ?>"><?php echo esc_html($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <!-- Ajout du filtre par région avec Select2 -->
        <?php if (!empty($regions_for_js)) : ?>
        <label for="regionFilter" style="margin-top: 15px; display: block;">Filtrer par région :</label>
        <select id="regionFilter" class="zonify-region-filter" multiple="multiple" data-placeholder="Sélectionner des régions">
            <?php foreach ($regions_for_js as $region) : ?>
            <option value="<?php echo esc_attr($region['slug']); ?>"><?php echo esc_html($region['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        
        <div class="zonify-filter-buttons">
            <button id="applyFilter" class="zonify-filter-button">Appliquer</button>
            <button id="resetFilter" class="zonify-filter-button zonify-filter-button-reset">Réinitialiser</button>
        </div>
    </div>

    <div id="map" style="height: 500px;"></div>

    <script>
    jQuery(document).ready(function($) {
        // Initialisation de Select2 pour les catégories
        $('#categoryFilter').select2({
            width: '100%',
            theme: 'classic',
            placeholder: "Sélectionner des catégories",
            allowClear: true
        });
        
        // Initialisation de Select2 pour les régions
        $('#regionFilter').select2({
            width: '100%',
            theme: 'classic',
            placeholder: "Sélectionner des régions",
            allowClear: true
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('zonify_map', 'zonify_frontend_shortcode');
