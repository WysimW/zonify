<?php

// reglage/reglage.php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once plugin_dir_path(__FILE__) . 'settings-page.php';
require_once plugin_dir_path(__FILE__) . 'settings-enqueue.php';

// Ajout des nouvelles options de style
add_action('admin_init', 'terralize_register_style_settings');
function terralize_register_style_settings() {
    register_setting('terralize_options', 'terralize_style_settings', array(
        'default' => array(
            'button_border_radius' => '8px',
            'button_border_width' => '1px',
            'button_border_style' => 'solid',
            'button_border_color' => '#D9D9D9',
            'button_background_color' => '#2E5039',
            'button_text_color' => '#FFFFFF',
            'button_hover_background' => '#7DA37B',
            'button_hover_text' => '#FFFFFF',
            'sidebar_background' => '#FFFFFF',
            'sidebar_border_color' => '#D9D9D9',
            'popup_background' => '#FFFFFF',
            'popup_border_radius' => '8px',
            'popup_border_color' => '#D9D9D9',
            'popup_border_width' => '1px',
            'popup_border_style' => 'solid',
            'popup_shadow' => '0 2px 8px rgba(0,0,0,0.1)'
        )
    ));
}
