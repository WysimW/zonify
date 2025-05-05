<?php
if (!defined('ABSPATH')) exit;

// Récupérer les paramètres de style
$style_settings = get_option('terralize_style_settings', array());

// Définir les valeurs par défaut
$defaults = array(
    'button_border_radius' => '4px',
    'button_border_width' => '1px',
    'button_border_style' => 'solid',
    'button_border_color' => '#cccccc',
    'button_background_color' => '#ffffff',
    'button_text_color' => '#333333',
    'button_hover_background' => '#f5f5f5',
    'button_hover_text' => '#000000',
    'sidebar_background' => '#ffffff',
    'sidebar_border_color' => '#dddddd',
    'popup_background' => '#ffffff',
    'popup_border_radius' => '4px',
    'popup_border_color' => '#dddddd',
    'popup_border_width' => '1px',
    'popup_border_style' => 'solid',
    'popup_shadow' => '0 2px 4px rgba(0,0,0,0.1)'
);

// Fusionner avec les valeurs par défaut
$style_settings = wp_parse_args($style_settings, $defaults);

// Générer le CSS
header('Content-Type: text/css');
?>
:root {
    /* Couleurs des boutons */
    --terralize-button-border-radius: <?php echo esc_attr($style_settings['button_border_radius']); ?>;
    --terralize-button-border-width: <?php echo esc_attr($style_settings['button_border_width']); ?>;
    --terralize-button-border-style: <?php echo esc_attr($style_settings['button_border_style']); ?>;
    --terralize-button-border-color: <?php echo esc_attr($style_settings['button_border_color']); ?>;
    --terralize-button-background-color: <?php echo esc_attr($style_settings['button_background_color']); ?>;
    --terralize-button-text-color: <?php echo esc_attr($style_settings['button_text_color']); ?>;
    --terralize-button-hover-background: <?php echo esc_attr($style_settings['button_hover_background']); ?>;
    --terralize-button-hover-text: <?php echo esc_attr($style_settings['button_hover_text']); ?>;

    /* Couleurs de la sidebar */
    --terralize-sidebar-background: <?php echo esc_attr($style_settings['sidebar_background']); ?>;
    --terralize-sidebar-border-color: <?php echo esc_attr($style_settings['sidebar_border_color']); ?>;

    /* Couleurs des popups */
    --terralize-popup-background: <?php echo esc_attr($style_settings['popup_background']); ?>;
    --terralize-popup-border-radius: <?php echo esc_attr($style_settings['popup_border_radius']); ?>;
    --terralize-popup-border-color: <?php echo esc_attr($style_settings['popup_border_color']); ?>;
    --terralize-popup-border-width: <?php echo esc_attr($style_settings['popup_border_width']); ?>;
    --terralize-popup-border-style: <?php echo esc_attr($style_settings['popup_border_style']); ?>;
    --terralize-popup-shadow: <?php echo esc_attr($style_settings['popup_shadow']); ?>;
} 