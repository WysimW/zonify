<?php
/**
 * Chargement spécifique des styles pour les tableaux CPT
 * Ce fichier s'assure que les styles des tableaux CPT sont correctement appliqués
 */

// Sécurité - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajoute le JavaScript inline pour transformer les colonnes en badges
 */
function zap_add_cpt_table_scripts() {
    // S'assurer que nous sommes sur une page d'administration
    if (!is_admin()) {
        return;
    }

    // Déterminer si nous sommes sur une page de liste (edit.php)
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'edit') {
        return;
    }

    // Script pour ajouter les classes aux colonnes des tableaux
    $script = "
    jQuery(document).ready(function($) {
        // Style pour la liste des POIs (panneaux d'affichage)
        if ($('body').hasClass('post-type-poi')) {
            // Ajouter des badges pour les types de panneaux
            $('.column-panel_type').each(function() {
                var text = $(this).text().trim();
                if (text) {
                    var className = 'type-' + text.toLowerCase().replace(' ', '-');
                    $(this).html('<span class=\"' + className + '\">' + text + '</span>');
                }
            });
            
            // Ajouter des badges pour les statuts
            $('.column-panel_status').each(function() {
                var text = $(this).text().trim();
                if (text) {
                    var className = 'status-' + text.toLowerCase().replace(' ', '-').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    $(this).html('<span class=\"' + className + '\">' + text + '</span>');
                }
            });
        }
        
        // Style pour la liste des zones
        if ($('body').hasClass('post-type-zone')) {
            // Mettre en forme les colonnes spécifiques
            $('.column-zone_id, .column-zone_type').each(function() {
                var text = $(this).text().trim();
                if (text) {
                    $(this).html('<span class=\"badge-pill\">' + text + '</span>');
                }
            });
        }
    });";

    // CSS inline pour les badges et autres éléments de l'interface des tableaux
    $styles = "
    /* Styles pour les badges dans les tableaux */
    .widefat.wp-list-table {
        border: none !important;
        box-shadow: 0 3px 10px rgba(0, 27, 71, 0.1) !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        background-color: white !important;
        margin-top: 20px !important;
    }

    .widefat.wp-list-table thead th {
        background-color: #f0f5fa !important;
        padding: 12px 15px !important;
        border-bottom: 2px solid #2c5aa0 !important;
        color: #1e3c68 !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        font-size: 0.85em !important;
        letter-spacing: 0.5px !important;
    }

    .widefat.wp-list-table tbody tr:hover {
        background-color: rgba(44, 90, 160, 0.05) !important;
    }

    .widefat.wp-list-table td {
        padding: 15px !important;
        vertical-align: middle !important;
        color: #333f4d !important;
    }

    /* Badges pour type de panneau */
    .post-type-poi .column-panel_type span,
    .post-type-poi .column-panel_status span {
        display: inline-block !important;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        font-size: 0.8em !important;
        font-weight: 500 !important;
        text-transform: uppercase !important;
    }

    /* Couleurs types */
    .column-panel_type span.type-mural {
        background-color: rgba(112, 193, 65, 0.2) !important;
        color: #70c141 !important;
    }

    .column-panel_type span.type-pre-enseigne {
        background-color: rgba(224, 77, 0, 0.2) !important;
        color: #E04D00 !important;
    }

    .column-panel_type span.type-4x3 {
        background-color: rgba(44, 90, 160, 0.2) !important;
        color: #2c5aa0 !important;
    }

    .column-panel_type span.type-8x3 {
        background-color: rgba(30, 60, 104, 0.2) !important;
        color: #1e3c68 !important;
    }

    .column-panel_type span.type-deroulant {
        background-color: rgba(156, 39, 176, 0.2) !important;
        color: #9C27B0 !important;
    }

    .column-panel_type span.type-totem {
        background-color: rgba(255, 152, 0, 0.2) !important;
        color: #FF9800 !important;
    }

    /* Couleurs statuts */
    .column-panel_status span.status-disponible {
        background-color: rgba(40, 167, 69, 0.2) !important;
        color: #28a745 !important;
    }

    .column-panel_status span.status-reserve,
    .column-panel_status span.status-réservé {
        background-color: rgba(255, 193, 7, 0.2) !important;
        color: #856404 !important;
    }

    .column-panel_status span.status-loue,
    .column-panel_status span.status-loué {
        background-color: rgba(220, 53, 69, 0.2) !important;
        color: #dc3545 !important;
    }

    .column-panel_status span.status-maintenance {
        background-color: rgba(23, 162, 184, 0.2) !important;
        color: #17a2b8 !important;
    }

    /* Badge pour zones */
    .badge-pill {
        display: inline-block !important;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        font-size: 0.8em !important;
        font-weight: 500 !important;
        background-color: #f0f5fa !important;
        color: #1e3c68 !important;
    }

    /* Messages d'erreur et de succès */
    .notice, div.updated, div.error {
        margin: 15px 0 !important;
        padding: 10px 15px !important;
        border-radius: 6px !important;
        border-top: none !important;
        border-right: none !important;
        border-bottom: none !important;
        border-left-width: 4px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
    }

    /* Bouton d'ajout */
    .page-title-action {
        background: #70c141 !important;
        border: 1px solid #70c141 !important;
        color: white !important;
        padding: 7px 15px !important;
        height: auto !important;
        border-radius: 6px !important;
        font-size: 0.9em !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        box-shadow: 0 1px 3px rgba(0, 27, 71, 0.1) !important;
        transition: all 0.2s ease !important;
        text-shadow: none !important;
    }

    .page-title-action:hover {
        background: #5da834 !important;
        border-color: #5da834 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 3px 6px rgba(0, 27, 71, 0.1) !important;
    }
    ";

    // Ajouter le script et les styles en ligne
    wp_add_inline_script('jquery', $script);
    wp_add_inline_style('wp-admin', $styles);
}
add_action('admin_enqueue_scripts', 'zap_add_cpt_table_scripts');