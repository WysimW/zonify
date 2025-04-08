/**
 * Script JavaScript pour la carte des panneaux d'affichage - Extension Zonify Affichage Premier
 */
document.addEventListener('DOMContentLoaded', function() {
    var options = mapOptions || {};
    
    // Initialisation des variables globales
    var map;
    var geoJSONLayer;
    var allResults = [];
    var currentPage = 1;
    var resultsPerPage = 5;
    
    // 1) Choix du provider de tuiles
    var provider = options.tile_provider || 'cartodb_light';
    var tileLayerUrl, attribution;

    switch (provider) {
        case 'cartodb_dark':
            tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
            break;
        case 'osm':
            tileLayerUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            attribution = '© OpenStreetMap contributors';
            break;
        case 'opentopo':
            tileLayerUrl = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
            attribution = '© OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)';
            break;
        case 'esri_topo':
            tileLayerUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}';
            attribution = 'Tiles © Esri — Source: Esri, USGS, NOAA';
            break;
        case 'custom':
            tileLayerUrl = options.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            attribution = 'Personnalisé';
            break;
        default:
            // Par défaut: CartoDB Light
            tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
            attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    }

    // 2) Initialisation de la carte
    var zoom = options.map_zoom || 6;
    var centerLat = parseFloat(options.map_center_lat || 46.2276);
    var centerLng = parseFloat(options.map_center_lng || 2.2137);

    map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, {
        attribution: attribution,
        maxZoom: 19
    }).addTo(map);

    // 3) Ajouter le geocoder pour la recherche d'adresses
    var geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        position: 'topleft',
        placeholder: 'Rechercher une adresse...',
        errorMessage: 'Adresse introuvable'
    }).on('markgeocode', function(e) {
        var bbox = e.geocode.bbox;
        var poly = L.polygon([
            bbox.getSouthEast(),
            bbox.getNorthEast(),
            bbox.getNorthWest(),
            bbox.getSouthWest()
        ]);
        map.fitBounds(poly.getBounds());
    }).addTo(map);

    // 4) Styles pour les marqueurs de panneaux
    var markerStyle = {
        radius: 8,
        fillColor: "#FF5500",
        color: "#FF5500",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.6
    };

    // 5) Filtrer et afficher les données sur la carte
    function filterAndRenderData(filters) {
        // Supprimer la couche existante si elle existe
        if (geoJSONLayer) {
            map.removeLayer(geoJSONLayer);
        }

        // Initialiser ou utiliser les filtres par défaut
        filters = filters || {};
        var searchQuery = filters.search || '';
        var selectedCategories = filters.categories || [];
        var selectedCity = filters.city || '';
        var selectedDepartment = filters.department || '';
        var selectedType = filters.type || '';
        var selectedStatus = filters.status || '';
        var minSurface = parseFloat(filters.minSurface) || 0;

        // Clone des données pour ne pas modifier l'original
        var filteredData = JSON.parse(JSON.stringify(panneauxData));
        var displayedResults = [];

        // Normaliser la recherche (convertir en minuscules, supprimer les accents)
        var normalizedSearchQuery = '';
        if (searchQuery) {
            normalizedSearchQuery = searchQuery.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        // Appliquer les filtres
        filteredData = filteredData.filter(function(feature) {
            let matchesSearch = true;
            let matchesCategories = true;
            let matchesCity = true;
            let matchesDepartment = true;
            let matchesType = true;
            let matchesStatus = true;
            let matchesSurface = true;
            
            // Fonction pour normaliser le texte (minuscules, sans accents)
            function normalizeText(text) {
                if (!text) return '';
                return text.toString().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            }
            
            // Filtre par recherche textuelle
            if (normalizedSearchQuery) {
                matchesSearch = false;
                
                // Vérifier dans différents champs
                const fields = [
                    'title', 'reference', 'address', 'postal_code', 'city_name', 
                    'department', 'region', 'panel_type', 'notes'
                ];
                
                for (const field of fields) {
                    if (feature.properties[field] && 
                        normalizeText(feature.properties[field]).includes(normalizedSearchQuery)) {
                        matchesSearch = true;
                        break;
                    }
                }
                
                // Vérifier dans les catégories
                if (!matchesSearch && feature.properties.categories && feature.properties.categories.length > 0) {
                    for (const cat of feature.properties.categories) {
                        if (normalizeText(cat.name).includes(normalizedSearchQuery)) {
                            matchesSearch = true;
                            break;
                        }
                    }
                }
                
                // Vérifier dans les villes
                if (!matchesSearch && feature.properties.cities && feature.properties.cities.length > 0) {
                    for (const city of feature.properties.cities) {
                        if (normalizeText(city.name).includes(normalizedSearchQuery)) {
                            matchesSearch = true;
                            break;
                        }
                    }
                }
            }
            
            // Filtre par catégories
            if (selectedCategories.length > 0) {
                if (!feature.properties.categories || feature.properties.categories.length === 0) {
                    matchesCategories = false;
                } else {
                    matchesCategories = feature.properties.categories.some(function(category) {
                        return selectedCategories.includes(category.slug);
                    });
                }
            }
            
            // Filtre par ville (taxonomie)
            if (selectedCity) {
                if (!feature.properties.cities || feature.properties.cities.length === 0) {
                    matchesCity = false;
                } else {
                    matchesCity = feature.properties.cities.some(function(city) {
                        return city.slug === selectedCity;
                    });
                }
            }
            
            // Filtre par département
            if (selectedDepartment && feature.properties.department) {
                matchesDepartment = feature.properties.department === selectedDepartment;
            }
            
            // Filtre par type de panneau
            if (selectedType && feature.properties.panel_type) {
                matchesType = feature.properties.panel_type === selectedType;
            }
            
            // Filtre par statut
            if (selectedStatus && feature.properties.status) {
                matchesStatus = feature.properties.status === selectedStatus;
            }
            
            // Filtre par surface minimale
            if (minSurface > 0 && feature.properties.surface) {
                var surface = parseFloat(feature.properties.surface);
                matchesSurface = !isNaN(surface) && surface >= minSurface;
            }
            
            // Un panneau doit correspondre à tous les filtres pour être affiché
            const matches = matchesSearch && matchesCategories && matchesCity && 
                        matchesDepartment && matchesType && matchesStatus && matchesSurface;
                        
            // Si le panneau correspond aux filtres, l'ajouter aux résultats à afficher
            if (matches) {
                displayedResults.push({
                    id: feature.properties.id,
                    title: feature.properties.title,
                    reference: feature.properties.reference || '',
                    panel_type: feature.properties.panel_type || '',
                    surface: feature.properties.surface || '',
                    dimensions: (feature.properties.width && feature.properties.height) ? 
                              `${feature.properties.width}×${feature.properties.height} cm` : '',
                    address: getFullAddress(feature.properties),
                    city: feature.properties.city_name || '',
                    department: feature.properties.department || '',
                    status: feature.properties.status || '',
                    image: feature.properties.image || ''
                });
            }
            
            return matches;
        });
        
        // Mettre à jour le nombre de résultats et la liste des résultats
        updateResultsCount(displayedResults.length);
        updateResultsList(displayedResults);

        // Créer et ajouter la nouvelle couche GeoJSON
        geoJSONLayer = L.geoJSON(filteredData, {
            pointToLayer: function(feature, latlng) {
                // Si le POI a une icône personnalisée, l'utiliser
                if (feature.properties.icon) {
                    var icon = L.icon({
                        iconUrl: feature.properties.icon.url,
                        iconSize: [feature.properties.icon.width, feature.properties.icon.height],
                        iconAnchor: [feature.properties.icon.anchor_x, feature.properties.icon.anchor_y]
                    });
                    return L.marker(latlng, { icon: icon });
                } else {
                    // Sinon, utiliser le marqueur circulaire par défaut
                    return L.circleMarker(latlng, markerStyle);
                }
            },
            onEachFeature: function(feature, layer) {
                // Au clic sur le panneau
                layer.on('click', function() {
                    // Construire le contenu de la popup
                    var content = createPopupContent(feature.properties);
                    
                    // Ouvrir la popup
                    L.popup()
                        .setLatLng(layer.getLatLng())
                        .setContent(content)
                        .openOn(map);
                });
            }
        }).addTo(map);

        // Ajuster la vue si nécessaire
        if (filteredData.length > 0) {
            try {
                if (geoJSONLayer && typeof geoJSONLayer.getBounds === 'function') {
                    map.fitBounds(geoJSONLayer.getBounds());
                }
            } catch (e) {
                console.error("Impossible d'ajuster la vue:", e);
            }
        }
    }

    // 6) Générer le contenu de la popup pour un panneau
    function createPopupContent(panel) {
        var content = '<div class="panel-popup" style="' +
            'font-family:' + (options.popup_font_family || 'Arial,sans-serif') + ';' +
            ' font-size:' + (options.popup_font_size || '14px') + ';' +
            ' color:' + (options.popup_font_color || '#333') + ';">';
        
        // Titre avec référence
        content += '<h3 style="margin: 0 0 10px; color: #FF5500;">' + panel.title;
        if (panel.reference) {
            content += ' <span style="font-size: 0.8em; opacity: 0.8;">(Réf: ' + panel.reference + ')</span>';
        }
        content += '</h3>';
        
        // Image si disponible
        if (panel.image) {
            content += '<div class="panel-image" style="text-align: center; margin-bottom: 10px;">' +
                       '<img src="' + panel.image + '" alt="' + panel.title + '" style="max-width: 100%; max-height: 150px; border-radius: 4px;">' +
                       '</div>';
        }
        
        content += '<div class="panel-details" style="margin-top: 10px;">';
        
        // Type et dimensions
        if (panel.panel_type) {
            content += '<p><strong>Type:</strong> ' + panel.panel_type + '</p>';
        }
        
        if (panel.width && panel.height) {
            content += '<p><strong>Dimensions:</strong> ' + panel.width + '×' + panel.height + ' cm';
            if (panel.surface) {
                content += ' (' + panel.surface + ' m²)';
            }
            content += '</p>';
        } else if (panel.surface) {
            content += '<p><strong>Surface:</strong> ' + panel.surface + ' m²</p>';
        }
        
        // Adresse complète si activée
        if (parseInt(options.popup_show_address) === 1) {
            var address = getFullAddress(panel);
            if (address) {
                content += '<p><strong>Adresse:</strong> ' + address + '</p>';
            }
        }
        
        // Visibilité
        if (panel.visibility) {
            content += '<p><strong>Visibilité:</strong> ' + panel.visibility + '</p>';
        }
        
        // Statut
        if (panel.status) {
            var statusLabel = panel.status;
            var statusColor = '#777';
            
            switch (panel.status.toLowerCase()) {
                case 'disponible':
                    statusColor = '#28a745';
                    break;
                case 'reserve':
                case 'réservé':
                    statusColor = '#ffc107';
                    break;
                case 'loue':
                case 'loué':
                    statusColor = '#dc3545';
                    break;
                case 'maintenance':
                    statusColor = '#17a2b8';
                    break;
            }
            
            content += '<p><strong>Statut:</strong> <span style="color:' + statusColor + '; font-weight: bold;">' + 
                      statusLabel + '</span></p>';
        }
        
        // Notes
        if (panel.notes) {
            content += '<p><strong>Notes:</strong> ' + panel.notes + '</p>';
        }
        
        // Afficher les catégories
        if (panel.categories && panel.categories.length > 0) {
            content += '<p><strong>Catégories:</strong> ';
            panel.categories.forEach(function(cat, index) {
                content += cat.name;
                if (index < panel.categories.length - 1) {
                    content += ', ';
                }
            });
            content += '</p>';
        }
        
        content += '</div>'; // Fin panel-details
        
        // Bouton pour contacter / en savoir plus
        content += '<div class="panel-actions" style="margin-top: 15px; text-align: center;">' +
            '<a href="/contact?panel=' + panel.id + '" class="panel-contact-btn" style="' +
            'background-color: #FF5500; color: white; padding: 8px 15px; text-decoration: none; ' +
            'border-radius: 4px; display: inline-block; font-weight: bold;">' +
            'Contacter / Réserver</a>' +
            '</div>';
        
        content += '</div>'; // Fin panel-popup
        
        return content;
    }

    // 7) Construire une adresse complète à partir des propriétés du panneau
    function getFullAddress(panel) {
        var addressParts = [];
        
        if (panel.address) {
            addressParts.push(panel.address);
        }
        
        if (panel.postal_code || panel.city_name) {
            var cityPart = [];
            if (panel.postal_code) cityPart.push(panel.postal_code);
            if (panel.city_name) cityPart.push(panel.city_name);
            addressParts.push(cityPart.join(' '));
        }
        
        return addressParts.join(', ');
    }

    // 8) Mettre à jour le compteur de résultats
    function updateResultsCount(count) {
        var resultsCountEl = document.getElementById('results-count');
        var tabResultsCount = document.getElementById('tab-results-count');
        var resultsCounter = document.getElementById('results-counter');

        if (resultsCountEl) {
            resultsCountEl.textContent = `(${count})`;
        }

        if (tabResultsCount) {
            tabResultsCount.textContent = count;
        }

        if (resultsCounter) {
            resultsCounter.textContent = `(${count})`;
        }
    }

    // 9) Mettre à jour la liste des résultats
    function updateResultsList(results) {
        // Stocker tous les résultats pour la pagination
        allResults = results;

        var resultsListEl = document.getElementById('results-list');
        if (!resultsListEl) return;

        if (results.length === 0) {
            resultsListEl.innerHTML = '<p class="no-results">Aucun panneau ne correspond à vos critères.</p>';
            // Réinitialiser la pagination
            currentPage = 1;
            updatePagination(results);
            return;
        }

        // Reset à la première page lors d'une nouvelle recherche
        currentPage = 1;

        // Afficher la première page des résultats
        displayResultsPage(1);

        // Mettre à jour les contrôles de pagination
        updatePagination(results);
    }

    // 10) Afficher une page spécifique de résultats
    function displayResultsPage(page) {
        // Vérifier si la page est valide
        var totalPages = Math.max(1, Math.ceil(allResults.length / resultsPerPage));
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;

        currentPage = page;

        // Calculer les index de début et fin
        var startIndex = (page - 1) * resultsPerPage;
        var endIndex = Math.min(startIndex + resultsPerPage, allResults.length);

        // Récupérer la sous-liste des résultats pour cette page
        var pageResults = allResults.slice(startIndex, endIndex);

        // Mettre à jour le DOM avec cette page de résultats
        renderResultsList(pageResults);

        // Mettre à jour les contrôles de pagination
        updatePagination(allResults);
    }

    // 11) Afficher la liste des résultats dans le panneau latéral
    function renderResultsList(pageResults) {
        var resultsListEl = document.getElementById('results-list');
        if (!resultsListEl) return;

        var html = '';
        pageResults.forEach(function(result) {
            // Classe CSS basée sur le statut
            var statusClass = '';
            if (result.status) {
                statusClass = 'status-' + result.status.toLowerCase().replace(/[^a-z0-9]/g, '');
            }
            
            html += `
            <div class="result-item panel-item ${statusClass}">
                <h4>${result.title || 'Sans titre'}</h4>`;
                
            // Image miniature si disponible
            if (result.image) {
                html += `<div class="result-image">
                    <img src="${result.image}" alt="${result.title}" />
                </div>`;
            }
                
            html += `<div class="result-details">`;
            
            // Référence
            if (result.reference) {
                html += `<p><strong>Réf.:</strong> ${result.reference}</p>`;
            }
            
            // Type et dimensions
            if (result.panel_type) {
                html += `<p><strong>Type:</strong> ${result.panel_type}</p>`;
            }
            
            if (result.dimensions) {
                html += `<p><strong>Dimensions:</strong> ${result.dimensions}`;
                if (result.surface) {
                    html += ` (${result.surface} m²)`;
                }
                html += `</p>`;
            } else if (result.surface) {
                html += `<p><strong>Surface:</strong> ${result.surface} m²</p>`;
            }
            
            // Adresse
            if (result.address) {
                html += `<p><strong>Adresse:</strong> ${result.address}</p>`;
            } else if (result.city) {
                html += `<p><strong>Ville:</strong> ${result.city}</p>`;
            }
            
            // Statut avec badge coloré
            if (result.status) {
                var statusColor = '#777';
                switch (result.status.toLowerCase()) {
                    case 'disponible': statusColor = '#28a745'; break;
                    case 'reserve':
                    case 'réservé': statusColor = '#ffc107'; break;
                    case 'loue':
                    case 'loué': statusColor = '#dc3545'; break;
                    case 'maintenance': statusColor = '#17a2b8'; break;
                }
                
                html += `<p><strong>Statut:</strong> <span class="status-badge" style="background-color:${statusColor};">
                    ${result.status}</span></p>`;
            }
            
            html += `</div>
                <button class="locate-on-map" data-id="${result.id}">
                    Localiser sur la carte
                </button>
            </div>`;
        });

        resultsListEl.innerHTML = html;

        // Ajouter les écouteurs d'événements pour la localisation sur la carte
        document.querySelectorAll('.locate-on-map').forEach(function(button) {
            button.addEventListener('click', function() {
                var id = parseInt(this.getAttribute('data-id'));

                // Trouver l'élément correspondant dans les données
                var feature = panneauxData.find(f => f.properties.id === id);
                if (feature && geoJSONLayer) {
                    // Trouver la couche correspondante dans la couche GeoJSON
                    geoJSONLayer.eachLayer(function(layer) {
                        if (layer.feature && layer.feature.properties.id === id) {
                            // Centrer la carte sur cette couche
                            if (layer.getLatLng) {
                                map.setView(layer.getLatLng(), 15);
                            }

                            // Simuler un clic sur la couche pour ouvrir la popup
                            layer.fire('click');
                        }
                    });
                }
            });
        });
    }

    // 12) Mettre à jour les contrôles de pagination
    function updatePagination(totalResults) {
        var totalPages = Math.max(1, Math.ceil(totalResults.length / resultsPerPage));

        // Mettre à jour les compteurs
        document.getElementById('current-page').textContent = currentPage;
        document.getElementById('total-pages').textContent = totalPages;

        // Activer/désactiver les boutons de pagination
        var prevButton = document.getElementById('prev-page');
        var nextButton = document.getElementById('next-page');

        if (prevButton) {
            prevButton.disabled = currentPage <= 1;
        }

        if (nextButton) {
            nextButton.disabled = currentPage >= totalPages;
        }
    }

    // 13) Initialiser Select2 pour les filtres
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('#category-filter').select2({
            placeholder: "Sélectionnez une ou plusieurs catégories",
            allowClear: true,
            width: '100%',
            closeOnSelect: false
        });

        jQuery('#city-filter, #department-filter, #type-filter, #status-filter').select2({
            width: '100%',
            placeholder: "Sélectionner...",
            allowClear: true
        });
    }

    // 14) Gestionnaires d'événements pour les filtres
    var applyFilterBtn = document.getElementById('apply-filter');
    var resetFilterBtn = document.getElementById('reset-filter');
    var surfaceFilter = document.getElementById('surface-filter');
    var surfaceValue = document.getElementById('surface-value');
    
    // Mise à jour du texte de la valeur de surface
    if (surfaceFilter && surfaceValue) {
        surfaceFilter.addEventListener('input', function() {
            surfaceValue.textContent = this.value;
        });
    }

    // Appliquer les filtres
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            // Récupérer les valeurs de filtres
            var selectedCategories = [];
            if (typeof jQuery !== 'undefined') {
                selectedCategories = jQuery('#category-filter').val() || [];
            }
            
            var filters = {
                search: document.getElementById('search-filter').value,
                categories: selectedCategories,
                city: document.getElementById('city-filter').value,
                department: document.getElementById('department-filter').value,
                type: document.getElementById('type-filter').value,
                status: document.getElementById('status-filter').value,
                minSurface: document.getElementById('surface-filter').value
            };

            // Appliquer les filtres
            filterAndRenderData(filters);
        });
    }

    // Réinitialiser les filtres
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            // Réinitialiser les valeurs de filtres
            document.getElementById('search-filter').value = '';
            
            if (typeof jQuery !== 'undefined') {
                jQuery('#category-filter').val(null).trigger('change');
                jQuery('#city-filter').val(null).trigger('change');
                jQuery('#department-filter').val(null).trigger('change');
                jQuery('#type-filter').val(null).trigger('change');
                jQuery('#status-filter').val(null).trigger('change');
            }
            
            // Réinitialiser le curseur de surface
            if (surfaceFilter) {
                surfaceFilter.value = 0;
            }
            if (surfaceValue) {
                surfaceValue.textContent = '0';
            }

            // Afficher toutes les données
            filterAndRenderData();
        });
    }

    // 15) Gestion des boutons de pagination
    var prevPageBtn = document.getElementById('prev-page');
    var nextPageBtn = document.getElementById('next-page');

    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                displayResultsPage(currentPage - 1);
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', function() {
            var totalPages = Math.ceil(allResults.length / resultsPerPage);
            if (currentPage < totalPages) {
                displayResultsPage(currentPage + 1);
            }
        });
    }

    // 16) Gestion des onglets et contrôles de l'interface
    // Gestion des onglets
    document.querySelectorAll('.tab-btn').forEach(function(tab) {
        tab.addEventListener('click', function() {
            // Désactiver tous les onglets et panels
            document.querySelectorAll('.tab-btn').forEach(function(t) {
                t.classList.remove('active');
            });
            document.querySelectorAll('.sidebar-panel').forEach(function(p) {
                p.classList.remove('active');
            });

            // Activer l'onglet cliqué et le panneau correspondant
            this.classList.add('active');
            var tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-panel').classList.add('active');
        });
    });

    // Gestion de l'accordéon des filtres
    document.querySelectorAll('.accordion-header').forEach(function(header) {
        header.addEventListener('click', function() {
            // Toggle de la classe active pour l'élément parent
            var accordionItem = this.parentNode;
            accordionItem.classList.toggle('active');

            // Changer l'icône
            var icon = this.querySelector('.accordion-icon');
            if (accordionItem.classList.contains('active')) {
                icon.textContent = '-';
            } else {
                icon.textContent = '+';
            }
        });
    });

    // Boutons de contrôle
    var toggleFiltersBtn = document.getElementById('toggle-filters');
    var toggleResultsBtn = document.getElementById('toggle-results');
    var expandMapBtn = document.getElementById('expand-map');
    var sidebar = document.querySelector('.map-sidebar');
    var mapContainer = document.querySelector('.map-container-wrapper');

    if (toggleFiltersBtn) {
        toggleFiltersBtn.addEventListener('click', function() {
            // Activer l'onglet filtres
            document.querySelector('.tab-btn[data-tab="filters"]').click();

            // Ajouter la classe 'sidebar-visible' si pas déjà présente
            if (!sidebar.classList.contains('sidebar-visible')) {
                sidebar.classList.add('sidebar-visible');
                mapContainer.classList.add('sidebar-open');
            }
        });
    }

    if (toggleResultsBtn) {
        toggleResultsBtn.addEventListener('click', function() {
            // Activer l'onglet résultats
            document.querySelector('.tab-btn[data-tab="results"]').click();

            // Ajouter la classe 'sidebar-visible' si pas déjà présente
            if (!sidebar.classList.contains('sidebar-visible')) {
                sidebar.classList.add('sidebar-visible');
                mapContainer.classList.add('sidebar-open');
            }
        });
    }

    if (expandMapBtn) {
        expandMapBtn.addEventListener('click', function() {
            // Toggle mode plein écran pour la carte
            sidebar.classList.toggle('sidebar-visible');
            mapContainer.classList.toggle('sidebar-open');

            // Mettre à jour le texte du bouton
            if (sidebar.classList.contains('sidebar-visible')) {
                this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                Plein écran
                `;
            } else {
                this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                Réduire
                `;
            }

            // Redimensionner la carte après changement d'affichage
            setTimeout(function() {
                map.invalidateSize();
            }, 300);
        });
    }

    // Fermeture des panneaux
    document.querySelectorAll('.panel-close-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            sidebar.classList.remove('sidebar-visible');
            mapContainer.classList.remove('sidebar-open');

            // Redimensionner la carte après changement d'affichage
            setTimeout(function() {
                map.invalidateSize();
            }, 300);
        });
    });

    // 17) Initialiser la carte avec toutes les données
    filterAndRenderData();
});