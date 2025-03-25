<?php
// reglage/tab-front-office.php
if ( ! defined( 'ABSPATH' ) ) exit;

// Récupérer les options Front Office avec des valeurs par défaut
$tile_provider_front       = get_option('zonify_tile_provider_front', 'cartodb_light');
$tile_custom_url_front     = get_option('zonify_tile_custom_url_front', '');
$zone_fill_color_front     = get_option('zonify_zone_fill_color_front', '#3388ff');
$zone_border_color_front   = get_option('zonify_zone_border_color_front', '#3388ff');
$zone_opacity_front        = get_option('zonify_zone_opacity_front', 0.5);
$map_zoom_front            = get_option('zonify_map_zoom_front', 9);
$map_center_lat_front      = get_option('zonify_map_center_lat_front', '50.5');
$map_center_lng_front      = get_option('zonify_map_center_lng_front', '2.5');

// Réglages geocoder
$geocoder_mode_front     = get_option('zonify_geocoder_mode_front', 'on_map');
$geocoder_position_front = get_option('zonify_geocoder_position_front', 'topleft');
?>

<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="zonify_tile_provider_front">Fournisseur de tuiles</label></th>
        <td>
            <select name="zonify_tile_provider_front" id="zonify_tile_provider_front">
                <option value="cartodb_light" <?php selected($tile_provider_front, 'cartodb_light'); ?>>CartoDB Positron (Light)</option>
                <option value="cartodb_dark" <?php selected($tile_provider_front, 'cartodb_dark'); ?>>CartoDB Dark Matter (Dark)</option>
                <option value="osm" <?php selected($tile_provider_front, 'osm'); ?>>OpenStreetMap Standard</option>
                <option value="opentopo" <?php selected($tile_provider, 'opentopo'); ?>>OpenTopoMap Standard</option>
                <option value="esri_topo" <?php selected($tile_provider, 'esri_topo'); ?>>ESRI World Topo</option>
                <option value="custom" <?php selected($tile_provider_front, 'custom'); ?>>URL personnalisée</option>
            </select>
        </td>
    </tr>
    <tr valign="top" id="custom_tile_url_row_front" <?php if($tile_provider_front !== 'custom') echo 'style="display:none;"'; ?>>
        <th scope="row"><label for="zonify_tile_custom_url_front">URL personnalisée</label></th>
        <td>
            <input type="text" name="zonify_tile_custom_url_front" id="zonify_tile_custom_url_front" value="<?php echo esc_attr($tile_custom_url_front); ?>" class="regular-text" />
            <p class="description">Entrez l'URL complète de vos tuiles personnalisées.</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_zone_fill_color_front">Couleur de fond des zones</label></th>
        <td>
            <input type="text" name="zonify_zone_fill_color_front" id="zonify_zone_fill_color_front" value="<?php echo esc_attr($zone_fill_color_front); ?>" class="regular-text" />
            <p class="description">Exemple : #3388ff</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_zone_border_color_front">Couleur des contours des zones</label></th>
        <td>
            <input type="text" name="zonify_zone_border_color_front" id="zonify_zone_border_color_front" value="<?php echo esc_attr($zone_border_color_front); ?>" class="regular-text" />
            <p class="description">Exemple : #3388ff</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_zone_opacity_front">Opacité des zones</label></th>
        <td>
            <input type="number" step="0.1" min="0" max="1" name="zonify_zone_opacity_front" id="zonify_zone_opacity_front" value="<?php echo esc_attr($zone_opacity_front); ?>" class="small-text" />
            <p class="description">Valeur entre 0 (transparent) et 1 (opaque)</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_map_zoom_front">Niveau de zoom par défaut</label></th>
        <td>
            <input type="number" name="zonify_map_zoom_front" id="zonify_map_zoom_front" value="<?php echo esc_attr($map_zoom_front); ?>" class="small-text" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_map_center_lat_front">Latitude du centre par défaut</label></th>
        <td>
            <input type="text" name="zonify_map_center_lat_front" id="zonify_map_center_lat_front" value="<?php echo esc_attr($map_center_lat_front); ?>" class="regular-text" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_map_center_lng_front">Longitude du centre par défaut</label></th>
        <td>
            <input type="text" name="zonify_map_center_lng_front" id="zonify_map_center_lng_front" value="<?php echo esc_attr($map_center_lng_front); ?>" class="regular-text" />
        </td>
    </tr>
      <!-- Réglages geocoder : mode -->
      <tr valign="top">
        <th scope="row"><label for="zonify_geocoder_mode_front">Mode de placement du champ de recherche</label></th>
        <td>
            <select name="zonify_geocoder_mode_front" id="zonify_geocoder_mode_front">
                <option value="on_map" <?php selected($geocoder_mode_front, 'on_map'); ?>>Dans la carte (position par défaut)</option>
                <option value="on_map_custom_position" <?php selected($geocoder_mode_front, 'on_map_custom_position'); ?>>Dans la carte (choisir position)</option>
                <option value="outside_map" <?php selected($geocoder_mode_front, 'outside_map'); ?>>Hors de la carte (champ de recherche manuel)</option>
            </select>
            <p class="description">
                - "Dans la carte (position par défaut)" : le contrôle Leaflet Geocoder apparaîtra en haut à gauche.<br/>
                - "Dans la carte (choisir position)" : vous pourrez choisir "topright", "bottomleft", etc.<br/>
                - "Hors de la carte" : vous devrez insérer un champ de recherche manuel en HTML.
            </p>
        </td>
    </tr>

    <!-- Réglages geocoder : position -->
    <tr valign="top" id="geocoder_position_row_front" <?php if ($geocoder_mode_front !== 'on_map_custom_position') echo 'style="display:none;"'; ?>>
        <th scope="row"><label for="zonify_geocoder_position_front">Position du geocoder</label></th>
        <td>
            <select name="zonify_geocoder_position_front" id="zonify_geocoder_position_front">
                <option value="topleft" <?php selected($geocoder_position_front, 'topleft'); ?>>topleft</option>
                <option value="topright" <?php selected($geocoder_position_front, 'topright'); ?>>topright</option>
                <option value="bottomleft" <?php selected($geocoder_position_front, 'bottomleft'); ?>>bottomleft</option>
                <option value="bottomright" <?php selected($geocoder_position_front, 'bottomright'); ?>>bottomright</option>
            </select>
            <p class="description">
                Choisissez la position "Leaflet" : topleft, topright, bottomleft, ou bottomright.
            </p>
        </td>
    </tr>

</table>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modeSelect = document.getElementById('zonify_geocoder_mode_front');
    var positionRow = document.getElementById('geocoder_position_row_front');

    function togglePositionRow() {
        if (modeSelect.value === 'on_map_custom_position') {
            positionRow.style.display = '';
        } else {
            positionRow.style.display = 'none';
        }
    }

    modeSelect.addEventListener('change', togglePositionRow);
});
</script>