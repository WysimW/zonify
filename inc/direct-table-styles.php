<?php
/**
 * Style direct pour les boutons des tableaux
 * Ce fichier injecte directement les styles CSS dans l'admin
 */

// Sécurité - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajoute les styles directement dans l'en-tête des pages admin
 */
function zonify_inject_table_button_styles() {
    // S'assurer que nous sommes sur une page d'administration
    if (!is_admin()) {
        return;
    }
    
    // CSS inline pour les boutons et éléments de formulaire
    $styles = "
    /* Style général des boutons dans les tableaux */
    .wp-core-ui .button,
    .wp-core-ui .button-secondary,
    input[type='submit'].button-secondary,
    .tablenav #post-query-submit,
    .tablenav .actions .button,
    .bulkactions .button {
        background: white !important;
        border: 1px solid #cfd8e3 !important;
        color: #333f4d !important;
        padding: 6px 12px !important;
        height: auto !important;
        border-radius: 6px !important;
        font-size: 0.9em !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        box-shadow: 0 1px 3px rgba(0, 27, 71, 0.1) !important;
        transition: all 0.2s ease !important;
        text-shadow: none !important;
        line-height: normal !important;
    }

    .wp-core-ui .button:hover,
    .wp-core-ui .button-secondary:hover,
    input[type='submit'].button-secondary:hover,
    .tablenav #post-query-submit:hover,
    .tablenav .actions .button:hover,
    .bulkactions .button:hover {
        background: #f0f5fa !important;
        border-color: #2c5aa0 !important;
        color: #2c5aa0 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 3px 6px rgba(0, 27, 71, 0.1) !important;
    }

    /* Boutons d'action (Apply, Filter) */
    .tablenav #post-query-submit,
    .tablenav .actions .button,
    input[name='filter_action'],
    .button.action {
        background: #2c5aa0 !important;
        color: white !important;
        border-color: #2c5aa0 !important;
    }

    .tablenav #post-query-submit:hover,
    .tablenav .actions .button:hover,
    input[name='filter_action']:hover,
    .button.action:hover {
        background: #1e3c68 !important;
        color: white !important;
        border-color: #1e3c68 !important;
    }

    /* Sélecteurs (Bulk actions, Filters) */
    .tablenav .actions select,
    .bulkactions select,
    .wp-admin select,
    .postbox select {
        height: 36px !important;
        padding: 0 30px 0 12px !important;
        border: 1px solid #cfd8e3 !important;
        border-radius: 6px !important;
        background-color: white !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        color: #333f4d !important;
        font-size: 0.9em !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
        min-width: 140px !important;
    }

    .tablenav .actions select:focus,
    .bulkactions select:focus,
    .wp-admin select:focus,
    .postbox select:focus {
        border-color: #2c5aa0 !important;
        box-shadow: 0 0 0 2px rgba(44, 90, 160, 0.2) !important;
        outline: none !important;
    }

    /* Zone des actions de masse */
    .tablenav .bulkactions {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    /* Ajustement pour le bouton de filtrage */
    .filter-items .button {
        margin-left: 8px !important;
        vertical-align: middle !important;
    }

    /* Style pour le bouton d'ajout (vert) */
    .page-title-action {
        background: #70c141 !important;
        border: 1px solid #70c141 !important;
        color: white !important;
        padding: 8px 16px !important;
        height: auto !important;
        border-radius: 6px !important;
        font-size: 0.9em !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        box-shadow: 0 1px 3px rgba(0, 27, 71, 0.1) !important;
        transition: all 0.2s ease !important;
        text-shadow: none !important;
        margin-left: 15px !important;
    }

    .page-title-action:hover {
        background: #5da834 !important;
        border-color: #5da834 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 3px 6px rgba(0, 27, 71, 0.1) !important;
        color: white !important;
    }

    /* Style pour le champ de recherche */
    .search-box input[type='search'] {
        height: 36px !important;
        width: 250px !important;
        padding: 0 15px !important;
        border: 1px solid #cfd8e3 !important;
        border-radius: 6px !important;
        background-color: white !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        color: #333f4d !important;
        font-size: 0.9em !important;
        transition: all 0.2s ease !important;
    }

    .search-box input[type='search']:focus {
        border-color: #2c5aa0 !important;
        box-shadow: 0 0 0 2px rgba(44, 90, 160, 0.2) !important;
        outline: none !important;
    }

    .search-box input[type='submit'] {
        background: #2c5aa0 !important;
        border: none !important;
        color: white !important;
        height: 36px !important;
        padding: 0 15px !important;
        border-radius: 6px !important;
        font-size: 0.9em !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        text-shadow: none !important;
        box-shadow: 0 1px 3px rgba(0, 27, 71, 0.1) !important;
        transition: all 0.2s ease !important;
        margin-left: 8px !important;
    }

    .search-box input[type='submit']:hover {
        background: #1e3c68 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 3px 6px rgba(0, 27, 71, 0.1) !important;
    }
    ";
    
    // Ajouter le CSS directement dans l'en-tête
    echo '<style type="text/css">' . $styles . '</style>';
}
add_action('admin_head', 'zonify_inject_table_button_styles');