<?php 
/**
 * Template de popup pour les marqueurs de carte
 * 
 * Ce fichier définit le HTML utilisé pour les popups des marqueurs sur la carte
 */

// Sécurité - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

class TERRALIZE_AP_Panel_Popup_Template {
    /**
     * Génère le contenu HTML pour la popup d'un marqueur de panneau d'affichage
     */
    public static function generate_popup_content($panel_id) {
        $panel = get_post($panel_id);
        
        if (!$panel || $panel->post_type !== 'poi') {
            return '<div class="marker-popup"><p>Panneau non trouvé</p></div>';
        }
        
        // Récupérer les métadonnées du panneau
        $reference = get_post_meta($panel_id, 'panel_reference', true);
        $type = get_post_meta($panel_id, 'panel_type', true);
        $width = get_post_meta($panel_id, 'panel_width', true);
        $height = get_post_meta($panel_id, 'panel_height', true);
        $surface = get_post_meta($panel_id, 'panel_surface', true);
        $address = get_post_meta($panel_id, 'panel_address', true);
        $city = get_post_meta($panel_id, 'panel_city_name', true);
        $postal_code = get_post_meta($panel_id, 'panel_postal_code', true);
        $visibility = get_post_meta($panel_id, 'panel_visibility', true);
        $status = get_post_meta($panel_id, 'panel_status', true);
        
        // Formater le titre du panneau
        $panel_title = !empty($reference) ? esc_html($reference) : esc_html($panel->post_title);
        
        // Formater le type de panneau
        $type_label = ucfirst(str_replace('-', ' ', $type));
        
        // Formater les dimensions
        $dimensions = '';
        if (!empty($width) && !empty($height)) {
            $dimensions = "{$width} x {$height} cm";
            if (!empty($surface)) {
                $dimensions .= " ({$surface} m²)";
            }
        }
        
        // Formater l'adresse complète
        $full_address = array();
        if (!empty($address)) $full_address[] = $address;
        if (!empty($postal_code) || !empty($city)) {
            $location = implode(' ', array_filter([$postal_code, $city]));
            if (!empty($location)) $full_address[] = $location;
        }
        $address_text = implode(', ', $full_address);
        
        // Statut avec badge coloré
        $status_class = '';
        $status_label = 'Inconnu';
        
        switch ($status) {
            case 'disponible':
                $status_class = 'status-disponible';
                $status_label = 'Disponible';
                break;
            case 'reserve':
            case 'réservé':
                $status_class = 'status-reserve';
                $status_label = 'Réservé';
                break;
            case 'loue':
            case 'loué':
                $status_class = 'status-loue';
                $status_label = 'Loué';
                break;
            case 'maintenance':
                $status_class = 'status-maintenance';
                $status_label = 'Maintenance';
                break;
        }
        
        // Construire le HTML de la popup avec notre style blueprint
        $html = '<div class="marker-popup">';
        
        // En-tête avec référence et type
        $html .= '<h3>' . $panel_title . '</h3>';
        
        $html .= '<div class="marker-popup-details">';
        
        // Informations principales avec style blueprint
        $html .= '<div class="marker-popup-detail-group">';
        $html .= '<p><span class="marker-popup-label">Type :</span> ' . esc_html($type_label) . '</p>';
        if (!empty($dimensions)) {
            $html .= '<p><span class="marker-popup-label">Dimensions :</span> ' . esc_html($dimensions) . '</p>';
        }
        $html .= '</div>';
        
        // Localisation
        if (!empty($address_text)) {
            $html .= '<div class="marker-popup-detail-group">';
            $html .= '<p><span class="marker-popup-label">Adresse :</span> ' . esc_html($address_text) . '</p>';
            $html .= '</div>';
        }
        
        // Statut avec badge
        $html .= '<div class="marker-popup-detail-group ' . $status_class . '">';
        $html .= '<p><span class="marker-popup-label">Statut :</span> <span class="status-badge">' . esc_html($status_label) . '</span></p>';
        $html .= '</div>';
        
        $html .= '</div>'; // Fin marker-popup-details
        
        // Boutons d'action
        $html .= '<div class="marker-popup-buttons">';
        $html .= '<a href="' . get_edit_post_link($panel_id) . '" class="marker-popup-btn marker-popup-btn-primary">Modifier</a>';
        $html .= '<a href="' . get_permalink($panel_id) . '" class="marker-popup-btn marker-popup-btn-secondary">Voir détails</a>';
        $html .= '</div>';
        
        $html .= '</div>'; // Fin marker-popup
        
        return $html;
    }
    
    /**
     * Hook pour remplacer le contenu de popup standard
     */
    public static function replace_popup_content($content, $panel_id) {
        return self::generate_popup_content($panel_id);
    }
}

// Appliquer notre template de popup personnalisé
add_filter('terralize_marker_popup_content', array('TERRALIZE_AP_Panel_Popup_Template', 'replace_popup_content'), 10, 2);