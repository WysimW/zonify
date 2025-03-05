<?php
// reglage/tab-back-office.php
if ( ! defined( 'ABSPATH' ) ) exit;

// Récupérer les options Back Office avec des valeurs par défaut
$tile_provider       = get_option('zonify_tile_provider', 'cartodb_light');
$tile_custom_url     = get_option('zonify_tile_custom_url', '');
$zone_fill_color     = get_option('zonify_zone_fill_color', '#3388ff');
$zone_border_color   = get_option('zonify_zone_border_color', '#3388ff');
$zone_opacity        = get_option('zonify_zone_opacity', 0.5);
$map_zoom            = get_option('zonify_map_zoom', 9);
$map_center_lat      = get_option('zonify_map_center_lat', '50.5');
$map_center_lng      = get_option('zonify_map_center_lng', '2.5');
?>

<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="zonify_tile_provider">Fournisseur de tuiles</label></th>
        <td>
            <select name="zonify_tile_provider" id="zonify_tile_provider">
                <option value="cartodb_light" <?php selected($tile_provider, 'cartodb_light'); ?>>CartoDB Positron (Light)</option>
                <option value="cartodb_dark" <?php selected($tile_provider, 'cartodb_dark'); ?>>CartoDB Dark Matter (Dark)</option>
                <option value="osm" <?php selected($tile_provider, 'osm'); ?>>OpenStreetMap Standard</option>
                <option value="custom" <?php selected($tile_provider, 'custom'); ?>>URL personnalisée</option>
            </select>
        </td>
    </tr>
    <tr valign="top" id="custom_tile_url_row_back" <?php if($tile_provider !== 'custom') echo 'style="display:none;"'; ?>>
        <th scope="row"><label for="zonify_tile_custom_url">URL personnalisée</label></th>
        <td>
            <input type="text" name="zonify_tile_custom_url" id="zonify_tile_custom_url" value="<?php echo esc_attr($tile_custom_url); ?>" class="regular-text" />
            <p class="description">Entrez l'URL complète de vos tuiles personnalisées.</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_zone_fill_color">Couleur de fond des zones</label></th>
        <td>
            <input type="text" name="zonify_zone_fill_color" id="zonify_zone_fill_color" value="<?php echo esc_attr($zone_fill_color); ?>" class="regular-text" />
            <p class="description">Exemple : #3388ff</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_zone_border_color">Couleur des contours des zones</label></th>
        <td>
            <input type="text" name="zonify_zone_border_color" id="zonify_zone_border_color" value="<?php echo esc_attr($zone_border_color); ?>" class="regular-text" />
            <p class="description">Exemple : #3388ff</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_zone_opacity">Opacité des zones</label></th>
        <td>
            <input type="number" step="0.1" min="0" max="1" name="zonify_zone_opacity" id="zonify_zone_opacity" value="<?php echo esc_attr($zone_opacity); ?>" class="small-text" />
            <p class="description">Valeur entre 0 (transparent) et 1 (opaque)</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_map_zoom">Niveau de zoom par défaut</label></th>
        <td>
            <input type="number" name="zonify_map_zoom" id="zonify_map_zoom" value="<?php echo esc_attr($map_zoom); ?>" class="small-text" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_map_center_lat">Latitude du centre par défaut</label></th>
        <td>
            <input type="text" name="zonify_map_center_lat" id="zonify_map_center_lat" value="<?php echo esc_attr($map_center_lat); ?>" class="regular-text" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_map_center_lng">Longitude du centre par défaut</label></th>
        <td>
            <input type="text" name="zonify_map_center_lng" id="zonify_map_center_lng" value="<?php echo esc_attr($map_center_lng); ?>" class="regular-text" />
        </td>
    </tr>
    <?php
$always_show_all_zones = get_option('zonify_always_show_all_zones', 0);
?>
<tr valign="top">
    <th scope="row"><label for="zonify_always_show_all_zones">Toujours afficher tous les polygones ?</label></th>
    <td>
        <input type="checkbox" name="zonify_always_show_all_zones" id="zonify_always_show_all_zones" value="1" <?php checked($always_show_all_zones, 1); ?> />
        <p class="description">
            Si coché, la carte affichera tous les polygones en permanence, même si cela peut affecter les performances.
        </p>
    </td>
</tr>
</table>
