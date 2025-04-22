<?php
// reglage/settings-page.php
if (! defined('ABSPATH')) exit;

function terralize_settings_page()
{
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';

    // Traitement de la soumission du formulaire
    if (isset($_POST['terralize_settings_submit']) && check_admin_referer('terralize_settings_nonce')) {
        // Options Back Office
        update_option('terralize_tile_provider', sanitize_text_field($_POST['terralize_tile_provider']));
        update_option('terralize_tile_custom_url', sanitize_text_field($_POST['terralize_tile_custom_url']));
        update_option('terralize_zone_fill_color', sanitize_text_field($_POST['terralize_zone_fill_color']));
        update_option('terralize_zone_border_color', sanitize_text_field($_POST['terralize_zone_border_color']));
        update_option('terralize_zone_opacity', floatval($_POST['terralize_zone_opacity']));
        update_option('terralize_map_zoom', intval($_POST['terralize_map_zoom']));
        update_option('terralize_map_center_lat', sanitize_text_field($_POST['terralize_map_center_lat']));
        update_option('terralize_map_center_lng', sanitize_text_field($_POST['terralize_map_center_lng']));
        update_option('terralize_always_show_all_zones', isset($_POST['terralize_always_show_all_zones']) ? 0 : 0);

        // Options Front Office
        update_option('terralize_tile_provider_front', sanitize_text_field($_POST['terralize_tile_provider_front']));
        update_option('terralize_tile_custom_url_front', sanitize_text_field($_POST['terralize_tile_custom_url_front']));
        update_option('terralize_zone_fill_color_front', sanitize_text_field($_POST['terralize_zone_fill_color_front']));
        update_option('terralize_zone_border_color_front', sanitize_text_field($_POST['terralize_zone_border_color_front']));
        update_option('terralize_zone_opacity_front', floatval($_POST['terralize_zone_opacity_front']));
        update_option('terralize_map_zoom_front', intval($_POST['terralize_map_zoom_front']));
        update_option('terralize_map_center_lat_front', sanitize_text_field($_POST['terralize_map_center_lat_front']));
        update_option('terralize_map_center_lng_front', sanitize_text_field($_POST['terralize_map_center_lng_front']));
        // Options Popups
        update_option('terralize_popup_show_address', isset($_POST['terralize_popup_show_address']) ? 1 : 0);
        update_option('terralize_popup_show_hours', isset($_POST['terralize_popup_show_hours']) ? 1 : 0);
        update_option('terralize_popup_show_social', isset($_POST['terralize_popup_show_social']) ? 1 : 0);
        update_option('terralize_popup_font_family', sanitize_text_field($_POST['terralize_popup_font_family']));
        update_option('terralize_popup_font_size', sanitize_text_field($_POST['terralize_popup_font_size']));
        update_option('terralize_popup_font_color', sanitize_text_field($_POST['terralize_popup_font_color']));
        update_option('terralize_popup_enable_email_btn', isset($_POST['terralize_popup_enable_email_btn']) ? 1 : 0);
        update_option('terralize_popup_enable_phone_btn', isset($_POST['terralize_popup_enable_phone_btn']) ? 1 : 0);
        update_option('terralize_popup_enable_contact_btn', isset($_POST['terralize_popup_enable_contact_btn']) ? 1 : 0);
        update_option('terralize_contact_page_url', sanitize_text_field($_POST['terralize_contact_page_url']));

        // NOUVEAU : Enregistrement des réglages Geocoder Front
        update_option('terralize_geocoder_mode_front', sanitize_text_field($_POST['terralize_geocoder_mode_front']));
        update_option('terralize_geocoder_position_front', sanitize_text_field($_POST['terralize_geocoder_position_front']));
        echo '<div class="updated"><p>Les réglages ont été sauvegardés.</p></div>';
    }
?>
    <div class="wrap">
        <div class="terralize-header">
            <div class="terralize-header-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Terralize Icon" class="terralize-icon" />
                <h1 class="terralize-title">Terralize by MBS</h1>
            </div>
        </div>
        <h1>Réglages Terralize</h1>
        <form method="post">
            <?php wp_nonce_field('terralize_settings_nonce'); ?>
            <div id="terralize-tabs">
                <ul>
                    <li><a href="#terralize-back">Carte Back Office</a></li>
                    <li><a href="#terralize-front">Carte Front Office</a></li>
                    <li><a href="#terralize-popups">Popups</a></li>
                </ul>
                <div id="terralize-back">
                    <?php include_once plugin_dir_path(__FILE__) . 'tab-back-office.php'; ?>
                </div>
                <div id="terralize-front">
                    <?php include_once plugin_dir_path(__FILE__) . 'tab-front-office.php'; ?>
                </div>
                <div id="terralize-popups">
                    <?php include_once plugin_dir_path(__FILE__) . 'tab-popups.php'; ?>
                </div>
            </div>
            <?php submit_button('Sauvegarder les réglages'); ?>
            <input type="hidden" name="terralize_settings_submit" value="1" />
        </form>
    </div>
<?php
}
