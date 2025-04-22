<?php
// reglage/tab-back-office.php
if ( ! defined( 'ABSPATH' ) ) exit;

// Récupérer les options Back Office avec des valeurs par défaut
$tile_provider       = get_option('terralize_tile_provider', 'cartodb_light');
$tile_custom_url     = get_option('terralize_tile_custom_url', '');
$zone_fill_color     = get_option('terralize_zone_fill_color', '#3388ff');
$zone_border_color   = get_option('terralize_zone_border_color', '#3388ff');
$zone_opacity        = get_option('terralize_zone_opacity', 0.5);
$map_zoom            = get_option('terralize_map_zoom', 9);
$map_center_lat      = get_option('terralize_map_center_lat', '50.5');
$map_center_lng      = get_option('terralize_map_center_lng', '2.5');
?>

<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="terralize_tile_provider">Fournisseur de tuiles</label></th>
        <td>
            <select name="terralize_tile_provider" id="terralize_tile_provider">
                <option value="cartodb_light" <?php selected($tile_provider, 'cartodb_light'); ?>>CartoDB Positron (Light)</option>
                <option value="cartodb_dark" <?php selected($tile_provider, 'cartodb_dark'); ?>>CartoDB Dark Matter (Dark)</option>
                <option value="osm" <?php selected($tile_provider, 'osm'); ?>>OpenStreetMap Standard</option>
                <option value="opentopo" <?php selected($tile_provider, 'opentopo'); ?>>OpenTopoMap Standard</option>
                <option value="esri_topo" <?php selected($tile_provider, 'esri_topo'); ?>>ESRI World Topo</option>
                <option value="custom" <?php selected($tile_provider, 'custom'); ?>>URL personnalisée</option>
            </select>
        </td>
    </tr>
    <tr valign="top" id="custom_tile_url_row_back" <?php if($tile_provider !== 'custom') echo 'style="display:none;"'; ?>>
        <th scope="row"><label for="terralize_tile_custom_url">URL personnalisée</label></th>
        <td>
            <input type="text" name="terralize_tile_custom_url" id="terralize_tile_custom_url" value="<?php echo esc_attr($tile_custom_url); ?>" class="regular-text" />
            <p class="description">Entrez l'URL complète de vos tuiles personnalisées.</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="terralize_zone_fill_color">Couleur de fond des zones</label></th>
        <td>
            <input type="text" name="terralize_zone_fill_color" id="terralize_zone_fill_color" value="<?php echo esc_attr($zone_fill_color); ?>" class="regular-text" />
            <p class="description">Exemple : #3388ff</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="terralize_zone_border_color">Couleur des contours des zones</label></th>
        <td>
            <input type="text" name="terralize_zone_border_color" id="terralize_zone_border_color" value="<?php echo esc_attr($zone_border_color); ?>" class="regular-text" />
            <p class="description">Exemple : #3388ff</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="terralize_zone_opacity">Opacité des zones</label></th>
        <td>
            <input type="number" step="0.1" min="0" max="1" name="terralize_zone_opacity" id="terralize_zone_opacity" value="<?php echo esc_attr($zone_opacity); ?>" class="small-text" />
            <p class="description">Valeur entre 0 (transparent) et 1 (opaque)</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="terralize_map_zoom">Niveau de zoom par défaut</label></th>
        <td>
            <input type="number" name="terralize_map_zoom" id="terralize_map_zoom" value="<?php echo esc_attr($map_zoom); ?>" class="small-text" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="terralize_map_center_lat">Latitude du centre par défaut</label></th>
        <td>
            <input type="text" name="terralize_map_center_lat" id="terralize_map_center_lat" value="<?php echo esc_attr($map_center_lat); ?>" class="regular-text" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="terralize_map_center_lng">Longitude du centre par défaut</label></th>
        <td>
            <input type="text" name="terralize_map_center_lng" id="terralize_map_center_lng" value="<?php echo esc_attr($map_center_lng); ?>" class="regular-text" />
        </td>
    </tr>
    <?php
$always_show_all_zones = get_option('terralize_always_show_all_zones', 0);
?>

</table>
