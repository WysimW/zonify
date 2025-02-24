<?php
function zonify_frontend_shortcode() {
    // Récupérer les commerciaux ayant une zone enregistrée
    $args = array(
        'post_type'      => 'zone',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);
    $zones = array(); // Contiendra un tableau de Features
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $geojson = get_post_meta(get_the_ID(), 'zone_geojson', true);
            $comm_id = get_post_meta(get_the_ID(), 'zone_commercial_id', true);

            if ($geojson) {
                // Récupérer les infos du commercial lié
                $nom_commercial = '';
                $infos = '';
                $email = '';
                $telephone = '';
                $address = '';
                $opening_hours = '';
                $social_links = '';

                if ($comm_id) {
                    $nom_commercial = get_the_title($comm_id);
                    // On peut récupérer l'excerpt si besoin, ou un champ "infos" perso
                    $infos = get_the_excerpt($comm_id);
                    $email = get_post_meta($comm_id, 'commercial_email', true);
                    $telephone = get_post_meta($comm_id, 'commercial_telephone', true);
                    $address = get_post_meta($comm_id, 'commercial_address', true);
                    $opening_hours = get_post_meta($comm_id, 'commercial_opening_hours', true);
                    $social_links = get_post_meta($comm_id, 'commercial_social_links', true);
                }

                $zones[] = array(
                    'type'       => 'Feature',
                    'properties' => array(
                        'nom_commercial' => $nom_commercial,
                        'infos'          => $infos,
                        'email'          => $email,
                        'telephone'      => $telephone,
                        'address'        => $address,
                        'opening_hours'  => $opening_hours,
                        'social_links'   => $social_links
                    ),
                    'geometry'   => json_decode($geojson, true)
                );
            }
        }
        wp_reset_postdata();
    }
    
    // Enqueue des scripts et styles pour le front-end
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    wp_enqueue_script('zonify-frontend', plugin_dir_url(__FILE__) . '../scripts/zonify-frontend.js', array('leaflet-js'), '1.0', true);
    wp_enqueue_style('zonify-frontend-css', plugin_dir_url(__FILE__) . '../assets/css/zc-frontend.css', array('leaflet-css'), '1.0');

    // Passage des zones au script front-end
    wp_localize_script('zonify-frontend', 'zonesData', $zones);
    
    // Regrouper les options Front Office pour la carte et les popups
    $front_options = array(
        'tile_provider'    => get_option('zonify_tile_provider_front', 'cartodb_light'),
        'tile_custom_url'  => get_option('zonify_tile_custom_url_front', ''),
        'zone_fill_color'  => get_option('zonify_zone_fill_color_front', '#3388ff'),
        'zone_border_color'=> get_option('zonify_zone_border_color_front', '#3388ff'),
        'zone_opacity'     => floatval(get_option('zonify_zone_opacity_front', 0.5)),
        'map_zoom'         => intval(get_option('zonify_map_zoom_front', 9)),
        'map_center_lat'   => get_option('zonify_map_center_lat_front', '50.5'),
        'map_center_lng'   => get_option('zonify_map_center_lng_front', '2.5')
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
    
    // Fusionner les options en un seul tableau
    $combined_options = array_merge($front_options, $popup_options);
    
    wp_localize_script('zonify-frontend', 'zonifyFrontendOptions', $combined_options);
    
    ob_start();
    ?>
    <div id="map" style="height: 500px;"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('zonify_map', 'zonify_frontend_shortcode');
