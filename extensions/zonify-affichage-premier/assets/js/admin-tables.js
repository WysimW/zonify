/**
 * Script pour améliorer les tableaux d'administration des CPT
 * Ce script applique les classes CSS appropriées pour les badges de type et statut
 */

(function($) {
    'use strict';
    
    // Attendre que le document soit prêt
    $(document).ready(function() {
        // Style pour la liste des POIs (panneaux d'affichage)
        if ($('body').hasClass('post-type-poi')) {
            // Ajouter des badges pour les types de panneaux
            $('.column-panel_type').each(function() {
                var text = $(this).text().trim();
                if (text) {
                    var className = 'type-' + text.toLowerCase().replace(' ', '-');
                    $(this).html('<span class="' + className + '">' + text + '</span>');
                }
            });
            
            // Ajouter des badges pour les statuts
            $('.column-panel_status').each(function() {
                var text = $(this).text().trim();
                if (text) {
                    var className = 'status-' + text.toLowerCase().replace(' ', '-').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    $(this).html('<span class="' + className + '">' + text + '</span>');
                }
            });
        }
        
        // Style pour la liste des zones
        if ($('body').hasClass('post-type-zone')) {
            // Mettre en forme les colonnes spécifiques
            $('.column-zone_id, .column-zone_type').each(function() {
                var text = $(this).text().trim();
                if (text) {
                    $(this).html('<span class="badge-pill">' + text + '</span>');
                }
            });
        }
        
        // Améliorer l'affichage des titres des tableaux
        $('.wp-list-table .page-title-action').addClass('button-add-new');
        
        // Style pour les filtres des tableaux
        $('.tablenav .actions select').addClass('select-filter');
        
        // Améliorer l'apparence des messages de notification
        $('.updated, .error, .notice').each(function() {
            // Ajouter une icône appropriée selon le type de message
            var $this = $(this);
            var icon = '';
            
            if ($this.hasClass('updated') || $this.hasClass('notice-success')) {
                icon = '<span class="dashicons dashicons-yes-alt notice-icon success"></span>';
            } else if ($this.hasClass('error') || $this.hasClass('notice-error')) {
                icon = '<span class="dashicons dashicons-dismiss notice-icon error"></span>';
            } else if ($this.hasClass('notice-warning')) {
                icon = '<span class="dashicons dashicons-warning notice-icon warning"></span>';
            } else if ($this.hasClass('notice-info')) {
                icon = '<span class="dashicons dashicons-info notice-icon info"></span>';
            }
            
            if (icon) {
                $this.prepend(icon);
            }
        });
    });
    
    // Style CSS supplémentaire injecté directement
    $('<style>', {
        text: `
            .badge-pill {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.8em;
                font-weight: 500;
                background-color: var(--zonify-light);
                color: var(--zonify-dark);
            }
            
            .notice-icon {
                margin-right: 10px;
                font-size: 1.2em;
                vertical-align: middle;
            }
            
            .notice-icon.success {
                color: #28a745;
            }
            
            .notice-icon.error {
                color: #dc3545;
            }
            
            .notice-icon.warning {
                color: #ffc107;
            }
            
            .notice-icon.info {
                color: #17a2b8;
            }
            
            .button-add-new {
                margin-left: 15px !important;
                background-color: var(--zonify-secondary) !important;
                color: white !important;
                border-color: var(--zonify-secondary) !important;
            }
            
            .button-add-new:hover {
                background-color: #5da834 !important;
                border-color: #5da834 !important;
                color: white !important;
            }
            
            .select-filter {
                min-width: 150px !important;
            }
        `
    }).appendTo('head');
    
})(jQuery);