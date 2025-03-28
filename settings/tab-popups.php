<?php
// reglage/tab-popups.php
if ( ! defined( 'ABSPATH' ) ) exit;

// Récupérer les options de popups avec des valeurs par défaut
$popup_show_address      = get_option('zonify_popup_show_address', 0);
$popup_show_hours        = get_option('zonify_popup_show_hours', 0);
$popup_show_social       = get_option('zonify_popup_show_social', 0);
$popup_font_family       = get_option('zonify_popup_font_family', 'Arial, sans-serif');
$popup_font_size         = get_option('zonify_popup_font_size', '14px');
$popup_font_color        = get_option('zonify_popup_font_color', '#333333');
$popup_enable_email_btn  = get_option('zonify_popup_enable_email_btn', 1);
$popup_enable_phone_btn  = get_option('zonify_popup_enable_phone_btn', 1);
$popup_enable_contact_btn= get_option('zonify_popup_enable_contact_btn', 0);
?>

<table class="form-table">
    <tr valign="top">
        <th scope="row">Afficher l'adresse</th>
        <td>
            <label for="zonify_popup_show_address">
                <input type="checkbox" name="zonify_popup_show_address" id="zonify_popup_show_address" value="1" <?php checked($popup_show_address, 1); ?> />
                Afficher l'adresse dans la popup
            </label>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Afficher les horaires</th>
        <td>
            <label for="zonify_popup_show_hours">
                <input type="checkbox" name="zonify_popup_show_hours" id="zonify_popup_show_hours" value="1" <?php checked($popup_show_hours, 1); ?> />
                Afficher les horaires d'ouverture
            </label>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Afficher les profils sociaux</th>
        <td>
            <label for="zonify_popup_show_social">
                <input type="checkbox" name="zonify_popup_show_social" id="zonify_popup_show_social" value="1" <?php checked($popup_show_social, 1); ?> />
                Afficher les liens vers les réseaux sociaux
            </label>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_popup_font_family">Police du texte</label></th>
        <td>
            <input type="text" name="zonify_popup_font_family" id="zonify_popup_font_family" value="<?php echo esc_attr($popup_font_family); ?>" class="regular-text" />
            <p class="description">Exemple : Arial, sans-serif</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_popup_font_size">Taille du texte</label></th>
        <td>
            <input type="text" name="zonify_popup_font_size" id="zonify_popup_font_size" value="<?php echo esc_attr($popup_font_size); ?>" class="small-text" />
            <p class="description">Exemple : 14px</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="zonify_popup_font_color">Couleur du texte</label></th>
        <td>
            <input type="text" name="zonify_popup_font_color" id="zonify_popup_font_color" value="<?php echo esc_attr($popup_font_color); ?>" class="regular-text" />
            <p class="description">Exemple : #333333</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Bouton Email</th>
        <td>
            <label for="zonify_popup_enable_email_btn">
                <input type="checkbox" name="zonify_popup_enable_email_btn" id="zonify_popup_enable_email_btn" value="1" <?php checked($popup_enable_email_btn, 1); ?> />
                Activer le bouton d'envoi d'e-mail
            </label>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Bouton Téléphone</th>
        <td>
            <label for="zonify_popup_enable_phone_btn">
                <input type="checkbox" name="zonify_popup_enable_phone_btn" id="zonify_popup_enable_phone_btn" value="1" <?php checked($popup_enable_phone_btn, 1); ?> />
                Activer le bouton d'appel téléphonique
            </label>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Bouton Contact</th>
        <td>
            <label for="zonify_popup_enable_contact_btn">
                <input type="checkbox" name="zonify_popup_enable_contact_btn" id="zonify_popup_enable_contact_btn" value="1" <?php checked($popup_enable_contact_btn, 1); ?> />
                Activer le bouton de formulaire de contact
            </label>
        </td>
    </tr>
    <tr valign="top">
    <th scope="row"><label for="zonify_contact_page_url">URL de la page de contact</label></th>
    <td>
        <input type="text" name="zonify_contact_page_url" id="zonify_contact_page_url" value="<?php echo esc_attr(get_option('zonify_contact_page_url', '/zonfiy-commercial-contact')); ?>" class="regular-text" />
        <p class="description">Indiquez l'URL relative de la page de contact. Exemples: /contact, /contact/commercial, /contact-commercial</p>
    </td>
</tr>

</table>
