<?php
// reglage/settings-page.php
if ( ! defined( 'ABSPATH' ) ) exit;

function zonify_settings_page() {
    // Traitement de la soumission du formulaire
    if ( isset($_POST['zonify_settings_submit']) && check_admin_referer('zonify_settings_nonce') ) {
        // Options Back Office
        update_option('zonify_tile_provider', sanitize_text_field($_POST['zonify_tile_provider']));
        update_option('zonify_tile_custom_url', sanitize_text_field($_POST['zonify_tile_custom_url']));
        update_option('zonify_zone_fill_color', sanitize_text_field($_POST['zonify_zone_fill_color']));
        update_option('zonify_zone_border_color', sanitize_text_field($_POST['zonify_zone_border_color']));
        update_option('zonify_zone_opacity', floatval($_POST['zonify_zone_opacity']));
        update_option('zonify_map_zoom', intval($_POST['zonify_map_zoom']));
        update_option('zonify_map_center_lat', sanitize_text_field($_POST['zonify_map_center_lat']));
        update_option('zonify_map_center_lng', sanitize_text_field($_POST['zonify_map_center_lng']));
        // Options Front Office
        update_option('zonify_tile_provider_front', sanitize_text_field($_POST['zonify_tile_provider_front']));
        update_option('zonify_tile_custom_url_front', sanitize_text_field($_POST['zonify_tile_custom_url_front']));
        update_option('zonify_zone_fill_color_front', sanitize_text_field($_POST['zonify_zone_fill_color_front']));
        update_option('zonify_zone_border_color_front', sanitize_text_field($_POST['zonify_zone_border_color_front']));
        update_option('zonify_zone_opacity_front', floatval($_POST['zonify_zone_opacity_front']));
        update_option('zonify_map_zoom_front', intval($_POST['zonify_map_zoom_front']));
        update_option('zonify_map_center_lat_front', sanitize_text_field($_POST['zonify_map_center_lat_front']));
        update_option('zonify_map_center_lng_front', sanitize_text_field($_POST['zonify_map_center_lng_front']));
         // Options Popups
    update_option('zonify_popup_show_address', isset($_POST['zonify_popup_show_address']) ? 1 : 0);
    update_option('zonify_popup_show_hours', isset($_POST['zonify_popup_show_hours']) ? 1 : 0);
    update_option('zonify_popup_show_social', isset($_POST['zonify_popup_show_social']) ? 1 : 0);
    update_option('zonify_popup_font_family', sanitize_text_field($_POST['zonify_popup_font_family']));
    update_option('zonify_popup_font_size', sanitize_text_field($_POST['zonify_popup_font_size']));
    update_option('zonify_popup_font_color', sanitize_text_field($_POST['zonify_popup_font_color']));
    update_option('zonify_popup_enable_email_btn', isset($_POST['zonify_popup_enable_email_btn']) ? 1 : 0);
    update_option('zonify_popup_enable_phone_btn', isset($_POST['zonify_popup_enable_phone_btn']) ? 1 : 0);
    update_option('zonify_popup_enable_contact_btn', isset($_POST['zonify_popup_enable_contact_btn']) ? 1 : 0);
        echo '<div class="updated"><p>Les réglages ont été sauvegardés.</p></div>';
    }
    ?>
    <div class="wrap">
    <h1>Réglages Zonify</h1>
    <form method="post">
        <?php wp_nonce_field('zonify_settings_nonce'); ?>
        <div id="zonify-tabs">
            <ul>
                <li><a href="#zonify-back">Carte Back Office</a></li>
                <li><a href="#zonify-front">Carte Front Office</a></li>
                <li><a href="#zonify-popups">Popups</a></li>
            </ul>
            <div id="zonify-back">
                <?php include_once plugin_dir_path(__FILE__) . 'tab-back-office.php'; ?>
            </div>
            <div id="zonify-front">
                <?php include_once plugin_dir_path(__FILE__) . 'tab-front-office.php'; ?>
            </div>
            <div id="zonify-popups">
                <?php include_once plugin_dir_path(__FILE__) . 'tab-popups.php'; ?>
            </div>
        </div>
        <?php submit_button('Sauvegarder les réglages'); ?>
        <input type="hidden" name="zonify_settings_submit" value="1" />
    </form>
</div>
    <?php
}
