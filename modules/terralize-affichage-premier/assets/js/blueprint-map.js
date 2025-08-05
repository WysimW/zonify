/**
 * Blueprint Map Enhancements
 * Fonctionnalités JavaScript pour améliorer l'interface de la carte
 * avec un style blueprint pour la cartographie.
 */

(function($) {
    'use strict';
    
    // Object qui contiendra toutes nos fonctions
    const BlueprintMap = {
        
        /**
         * Initialisation
         */
        init: function() {
            this.setupMapControls();
            this.enhancePopups();
            this.setupQuickFilters();
            this.createMapLegend();
            this.setupResponsiveUI();
        },
        
        /**
         * Crée de meilleurs contrôles pour la carte
         */
        setupMapControls: function() {
            // Attendre que la carte soit initialisée
            $(document).on('terralize_map_ready', function(event, map) {
                // Créer un conteneur de contrôles personnalisés en haut à droite
                const controlsContainer = $('<div class="map-controls"></div>');
                $('#map-container').prepend(controlsContainer);
                
                // Bouton pour centrer la carte
                const centerButton = $(
                    '<button class="map-control-btn" id="center-map" title="Centrer la carte">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>' +
                    'Centrer' +
                    '</button>'
                );
                
                // Bouton pour activer le mode plein écran
                const fullscreenButton = $(
                    '<button class="map-control-btn" id="fullscreen-map" title="Plein écran">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>' +
                    'Plein écran' +
                    '</button>'
                );
                
                // Bouton pour afficher/masquer la légende
                const legendButton = $(
                    '<button class="map-control-btn" id="toggle-legend" title="Afficher/masquer la légende">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>' +
                    'Légende' +
                    '</button>'
                );
                
                // Ajout des boutons au conteneur
                controlsContainer.append(centerButton);
                controlsContainer.append(fullscreenButton);
                controlsContainer.append(legendButton);
                
                // Gestionnaires d'événements pour les boutons
                centerButton.on('click', function() {
                    // Centre la carte (utilise les coordonnées par défaut de l'application)
                    const defaultCenter = window.terralizeMapData && window.terralizeMapData.defaultCenter 
                        ? window.terralizeMapData.defaultCenter 
                        : [46.227638, 2.213749]; // Centre de la France par défaut
                    
                    const defaultZoom = window.terralizeMapData && window.terralizeMapData.defaultZoom
                        ? window.terralizeMapData.defaultZoom
                        : 6;
                        
                    map.setView(defaultCenter, defaultZoom);
                });
                
                fullscreenButton.on('click', function() {
                    // Toggle le mode plein écran pour le conteneur de carte
                    const mapContainer = document.getElementById('map-container');
                    
                    if (!document.fullscreenElement) {
                        if (mapContainer.requestFullscreen) {
                            mapContainer.requestFullscreen();
                        } else if (mapContainer.mozRequestFullScreen) {
                            mapContainer.mozRequestFullScreen();
                        } else if (mapContainer.webkitRequestFullscreen) {
                            mapContainer.webkitRequestFullscreen();
                        } else if (mapContainer.msRequestFullscreen) {
                            mapContainer.msRequestFullscreen();
                        }
                        $(this).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path></svg> Quitter');
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.mozCancelFullScreen) {
                            document.mozCancelFullScreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                        $(this).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg> Plein écran');
                    }
                });
                
                legendButton.on('click', function() {
                    $('.map-legend').toggleClass('visible');
                });
            });
        },
        
        /**
         * Améliore l'affichage des popups
         */
        enhancePopups: function() {
            $(document).on('terralize_popup_opened', function(event, popup) {
                // Ajouter des classes et styles aux popups pour améliorer l'affichage
                const popupContent = popup._contentNode;
                const popupContainer = popup._container;
                
                // Assurer que nos styles CSS sont appliqués
                $(popupContainer).addClass('enhanced-popup');
                
                // Ajouter une ombre portée et une transition fluide
                $(popupContainer).css({
                    'box-shadow': '0 3px 14px rgba(0, 27, 71, 0.15)',
                    'transition': 'transform 0.2s ease'
                });
                
                // Animation d'entrée
                $(popupContainer).css('transform', 'translateY(10px)');
                setTimeout(function() {
                    $(popupContainer).css('transform', 'translateY(0)');
                }, 10);
            });
        },
        
        /**
         * Crée un système de filtres rapides pour la carte
         */
        setupQuickFilters: function() {
            // Créer le conteneur de filtres rapides
            const filtersContainer = $('<div class="quick-filters blueprint-grid"></div>');
            $('#map-container').before(filtersContainer);
            
            // En-tête
            filtersContainer.append('<h3>Filtres rapides</h3>');
            
            // Conteneur de boutons
            const buttonsContainer = $('<div class="filter-buttons"></div>');
            filtersContainer.append(buttonsContainer);
            
            // Vérifier si les données de types de panneaux sont disponibles
            if (window.terralizeMapData && window.terralizeMapData.panelTypes) {
                const types = window.terralizeMapData.panelTypes;
                
                // Bouton "Tous"
                buttonsContainer.append('<button class="filter-btn active" data-type="all">Tous</button>');
                
                // Boutons pour chaque type
                $.each(types, function(index, type) {
                    const label = type.charAt(0).toUpperCase() + type.slice(1);
                    buttonsContainer.append('<button class="filter-btn" data-type="' + type + '">' + label + '</button>');
                });
                
                // Gestionnaire d'événements pour les boutons de filtre
                $('.filter-btn').on('click', function() {
                    const type = $(this).data('type');
                    
                    // Mettre à jour l'état actif des boutons
                    $('.filter-btn').removeClass('active');
                    $(this).addClass('active');
                    
                    // Déclencher l'événement de filtre
                    $(document).trigger('terralize_filter_markers', { type: type });
                });
                
                // Hook l'événement de filtre
                $(document).on('terralize_filter_markers', function(event, data) {
                    // Cette fonction sera implémentée par Terralize
                    // Mais nous pouvons ajouter un visuel de chargement
                    $('#map').addClass('filtering');
                    
                    // Réinitialiser l'état de filtrage après un délai
                    setTimeout(function() {
                        $('#map').removeClass('filtering');
                    }, 500);
                });
            }
        },
        
        /**
         * Crée une légende pour la carte
         */
        createMapLegend: function() {
            // Vérifier si les données de types sont disponibles
            if (window.terralizeMapData && window.terralizeMapData.panelTypes) {
                // Créer le conteneur de légende
                const legendContainer = $('<div class="map-legend"></div>');
                $('#map-container').append(legendContainer);
                
                // En-tête
                legendContainer.append('<h4>Types de panneaux</h4>');
                
                // Liste des types
                const legendList = $('<ul class="legend-list"></ul>');
                legendContainer.append(legendList);
                
                // Couleurs associées aux types (correspondant aux classes CSS dans les marqueurs)
                const typeColors = {
                    'mural': '#70c141',
                    'pre-implantations': '#E04D00',
                    '4x3': '#2c5aa0',
                    '8x3': '#1e3c68',
                    'déroulant': '#9C27B0',
                    'totem': '#FF9800'
                };
                
                // Ajouter chaque type à la légende
                $.each(window.terralizeMapData.panelTypes, function(index, type) {
                    const label = type.charAt(0).toUpperCase() + type.slice(1).replace('-', ' ');
                    const color = typeColors[type] || '#333';
                    
                    const legendItem = $(
                        '<li class="legend-item">' +
                        '<span class="legend-color" style="background-color: ' + color + '"></span>' +
                        '<span class="legend-label">' + label + '</span>' +
                        '</li>'
                    );
                    
                    legendList.append(legendItem);
                });
                
                // Bouton pour fermer la légende
                const closeButton = $('<button class="legend-close">×</button>');
                legendContainer.append(closeButton);
                
                closeButton.on('click', function() {
                    legendContainer.removeClass('visible');
                });
                
                // Style CSS pour la légende
                $('<style>', {
                    text: `
                        .map-legend {
                            position: absolute;
                            bottom: 20px;
                            right: 20px;
                            background: white;
                            border-radius: 8px;
                            padding: 15px;
                            box-shadow: 0 3px 10px rgba(0, 27, 71, 0.1);
                            z-index: 1000;
                            width: 200px;
                            display: none;
                            border: 1px solid var(--terralize-border);
                        }
                        
                        .map-legend.visible {
                            display: block;
                            animation: fadeIn 0.3s ease;
                        }
                        
                        .map-legend h4 {
                            margin: 0 0 10px;
                            color: var(--terralize-primary);
                            font-size: 1em;
                            padding-bottom: 8px;
                            border-bottom: 1px solid var(--terralize-border);
                        }
                        
                        .legend-list {
                            margin: 0;
                            padding: 0;
                            list-style: none;
                        }
                        
                        .legend-item {
                            display: flex;
                            align-items: center;
                            margin-bottom: 6px;
                        }
                        
                        .legend-color {
                            width: 12px;
                            height: 12px;
                            border-radius: 50%;
                            margin-right: 8px;
                            border: 1px solid rgba(0,0,0,0.1);
                        }
                        
                        .legend-label {
                            font-size: 0.9em;
                            color: var(--terralize-text);
                        }
                        
                        .legend-close {
                            position: absolute;
                            top: 10px;
                            right: 10px;
                            background: none;
                            border: none;
                            color: #999;
                            cursor: pointer;
                            font-size: 1.2em;
                            padding: 0;
                            width: 20px;
                            height: 20px;
                            line-height: 1;
                            transition: color 0.2s ease;
                        }
                        
                        .legend-close:hover {
                            color: var(--terralize-primary);
                        }
                        
                        @keyframes fadeIn {
                            from { opacity: 0; transform: translateY(10px); }
                            to { opacity: 1; transform: translateY(0); }
                        }
                        
                        .quick-filters {
                            background: white;
                            border-radius: 8px;
                            padding: 15px;
                            margin-bottom: 20px;
                            box-shadow: 0 3px 10px rgba(0, 27, 71, 0.1);
                        }
                        
                        .quick-filters h3 {
                            margin: 0 0 15px;
                            color: var(--terralize-primary);
                            font-size: 1.1em;
                            position: relative;
                            padding-bottom: 8px;
                        }
                        
                        .quick-filters h3::after {
                            content: '';
                            position: absolute;
                            left: 0;
                            bottom: 0;
                            width: 40px;
                            height: 3px;
                            background: var(--terralize-secondary);
                            border-radius: 3px;
                        }
                        
                        .filter-buttons {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 10px;
                        }
                        
                        .filter-btn {
                            background: var(--terralize-light);
                            border: 1px solid var(--terralize-border);
                            border-radius: 6px;
                            padding: 6px 12px;
                            font-size: 0.9em;
                            cursor: pointer;
                            transition: all 0.2s ease;
                            color: var(--terralize-text);
                        }
                        
                        .filter-btn:hover {
                            border-color: var(--terralize-primary);
                            color: var(--terralize-primary);
                        }
                        
                        .filter-btn.active {
                            background: var(--terralize-primary);
                            color: white;
                            border-color: var(--terralize-primary);
                        }
                        
                        #map.filtering {
                            opacity: 0.7;
                            transition: opacity 0.3s ease;
                        }
                    `
                }).appendTo('head');
            }
        },
        
        /**
         * Ajuste l'interface pour le responsive
         */
        setupResponsiveUI: function() {
            const adjustUI = function() {
                const windowWidth = $(window).width();
                
                if (windowWidth < 768) {
                    // Style pour mobile
                    $('.map-controls').addClass('mobile');
                    $('.quick-filters').addClass('mobile');
                    
                    // Déplacer les contrôles en bas
                    if ($('.map-controls').hasClass('mobile') && !$('.map-controls').hasClass('moved')) {
                        $('.map-controls').addClass('moved').appendTo('#map');
                    }
                } else {
                    // Style pour desktop
                    $('.map-controls').removeClass('mobile moved');
                    $('.quick-filters').removeClass('mobile');
                    
                    // Remettre les contrôles à leur place
                    if ($('.map-controls.moved').length) {
                        $('.map-controls').prependTo('#map-container');
                    }
                }
            };
            
            // Exécuter au chargement et au redimensionnement
            $(window).on('load resize', adjustUI);
            
            // Style CSS pour le responsive
            $('<style>', {
                text: `
                    @media screen and (max-width: 768px) {
                        .map-controls.mobile {
                            position: absolute;
                            bottom: 20px;
                            left: 50%;
                            transform: translateX(-50%);
                            z-index: 1000;
                            background: white;
                            border-radius: 8px;
                            padding: 8px;
                            box-shadow: 0 3px 10px rgba(0, 27, 71, 0.2);
                            display: flex;
                            justify-content: center;
                        }
                        
                        .map-controls.mobile .map-control-btn {
                            padding: 6px 10px;
                            font-size: 0.8em;
                        }
                        
                        .quick-filters.mobile h3 {
                            font-size: 1em;
                        }
                        
                        .quick-filters.mobile .filter-btn {
                            padding: 4px 8px;
                            font-size: 0.8em;
                        }
                    }
                `
            }).appendTo('head');
        }
    };
    
    // Initialiser lorsque le document est prêt
    $(document).ready(function() {
        BlueprintMap.init();
    });
    
})(jQuery);