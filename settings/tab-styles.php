<?php
// Récupération des réglages de style existants
$style_settings = get_option('terralize_style_settings', array());
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
$style_settings = wp_parse_args($style_settings, $defaults);
?>

<div class="terralize-section">
    <h2>Styles des boutons</h2>
    <table class="form-table">
        <tr>
            <th scope="row">Rayon de la bordure</th>
            <td>
                <input type="text" name="button_border_radius" value="<?php echo esc_attr($style_settings['button_border_radius']); ?>" class="regular-text" />
                <p class="description">Ex: 4px, 50%, etc.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">Largeur de la bordure</th>
            <td>
                <input type="text" name="button_border_width" value="<?php echo esc_attr($style_settings['button_border_width']); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">Style de la bordure</th>
            <td>
                <select name="button_border_style">
                    <option value="solid" <?php selected($style_settings['button_border_style'], 'solid'); ?>>Plein</option>
                    <option value="dashed" <?php selected($style_settings['button_border_style'], 'dashed'); ?>>Tirets</option>
                    <option value="dotted" <?php selected($style_settings['button_border_style'], 'dotted'); ?>>Points</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Couleur de la bordure</th>
            <td>
                <input type="color" name="button_border_color" value="<?php echo esc_attr($style_settings['button_border_color']); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Couleur de fond</th>
            <td>
                <input type="color" name="button_background_color" value="<?php echo esc_attr($style_settings['button_background_color']); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Couleur du texte</th>
            <td>
                <input type="color" name="button_text_color" value="<?php echo esc_attr($style_settings['button_text_color']); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Couleur de fond au survol</th>
            <td>
                <input type="color" name="button_hover_background" value="<?php echo esc_attr($style_settings['button_hover_background']); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Couleur du texte au survol</th>
            <td>
                <input type="color" name="button_hover_text" value="<?php echo esc_attr($style_settings['button_hover_text']); ?>" />
            </td>
        </tr>
    </table>
</div>

<div class="terralize-section">
    <h2>Styles de la barre latérale</h2>
    <table class="form-table">
        <tr>
            <th scope="row">Couleur de fond</th>
            <td>
                <input type="color" name="sidebar_background" value="<?php echo esc_attr($style_settings['sidebar_background']); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Couleur de la bordure</th>
            <td>
                <input type="color" name="sidebar_border_color" value="<?php echo esc_attr($style_settings['sidebar_border_color']); ?>" />
            </td>
        </tr>
    </table>
</div>

<div class="terralize-section">
    <h2>Styles des popups</h2>
    <table class="form-table">
        <tr>
            <th scope="row">Couleur de fond</th>
            <td>
                <input type="color" name="popup_background" value="<?php echo esc_attr($style_settings['popup_background']); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Rayon de la bordure</th>
            <td>
                <input type="text" name="popup_border_radius" value="<?php echo esc_attr($style_settings['popup_border_radius']); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">Couleur de la bordure</th>
            <td>
                <input type="color" name="popup_border_color" value="<?php echo esc_attr($style_settings['popup_border_color']); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Largeur de la bordure</th>
            <td>
                <input type="text" name="popup_border_width" value="<?php echo esc_attr($style_settings['popup_border_width']); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">Style de la bordure</th>
            <td>
                <select name="popup_border_style">
                    <option value="solid" <?php selected($style_settings['popup_border_style'], 'solid'); ?>>Plein</option>
                    <option value="dashed" <?php selected($style_settings['popup_border_style'], 'dashed'); ?>>Tirets</option>
                    <option value="dotted" <?php selected($style_settings['popup_border_style'], 'dotted'); ?>>Points</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Ombre</th>
            <td>
                <input type="text" name="popup_shadow" value="<?php echo esc_attr($style_settings['popup_shadow']); ?>" class="regular-text" />
                <p class="description">Ex: 0 2px 4px rgba(0,0,0,0.1)</p>
            </td>
        </tr>
    </table>
</div> 