/**
 * Script JavaScript pour la carte des panneaux d'affichage - Extension Terralize Affichage Premier
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
        fillColor: "#70c141",
        color: "#70c141",
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
        var selectedCities = filters.cities || [];
        var selectedDepartment = filters.department || '';
        var selectedRegion = filters.region || '';
        var selectedType = filters.type || '';
        var selectedSupport = filters.support || '';
        var selectedFormat = filters.format || '';
        var selectedStatus = filters.status || '';
        var minWidth = filters.minWidth || 0;
        var maxWidth = filters.maxWidth || 0;
        var minHeight = filters.minHeight || 0;
        var maxHeight = filters.maxHeight || 0;

        console.log("Filtres appliqués:", filters);

        // Clone des données pour ne pas modifier l'original
        var filteredData = JSON.parse(JSON.stringify(panneauxData));
        var displayedResults = [];
        var invalidCoordinatesPOIs = [];

        // Vérifier les POIs sans coordonnées valides
        var validGeoData = filteredData.filter(function(feature) {
            // Vérifier que la géométrie existe et que les coordonnées sont valides
            if (!feature.geometry || 
                !feature.geometry.coordinates || 
                !Array.isArray(feature.geometry.coordinates) || 
                feature.geometry.coordinates.length < 2 ||
                !feature.geometry.coordinates[0] || 
                !feature.geometry.coordinates[1]) {
                
                // Stocker les POIs invalides pour information
                invalidCoordinatesPOIs.push({
                    id: feature.properties.id,
                    title: feature.properties.title
                });
                
                return false;
            }
            return true;
        });

        console.log("POIs avec coordonnées valides: " + validGeoData.length + " sur " + filteredData.length);
        
        if (invalidCoordinatesPOIs.length > 0) {
            console.warn("POIs sans coordonnées valides:", invalidCoordinatesPOIs);
        }
        
        // Continuer avec les données valides uniquement
        filteredData = validGeoData;

        // Normaliser la recherche (convertir en minuscules, supprimer les accents)
        var normalizedSearchQuery = '';
        if (searchQuery) {
            normalizedSearchQuery = searchQuery.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        // Debug des données avant filtrage pour comprendre leur structure
        if (filteredData.length > 0) {
            console.log("Exemple de données à filtrer:", filteredData[0].properties);
        }

        // Appliquer les filtres
        filteredData = filteredData.filter(function(feature) {
            let matchesSearch = true;
            let matchesCategories = true;
            let matchesCities = true;
            let matchesDepartment = true;
            let matchesRegion = true;
            let matchesType = true;
            let matchesSupport = true;
            let matchesStatus = true;
            let matchesFormat = true;
            let matchesDimensions = true;
            
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
                    'department', 'region', 'panel_type', 'support_type', 'notes'
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
            
            // Filtre par villes (multi-select)
            if (selectedCities.length > 0) {
                matchesCities = false;
                
                // Vérifier d'abord dans la taxonomie poi_city
                if (feature.properties.cities && feature.properties.cities.length > 0) {
                    matchesCities = feature.properties.cities.some(function(city) {
                        return selectedCities.includes(city.slug);
                    });
                }
                
                // Si pas trouvé dans la taxonomie, vérifier dans le champ city_name
                if (!matchesCities && feature.properties.city_name) {
                    matchesCities = selectedCities.includes(feature.properties.city_name);
                }
            }
            
            // Filtre par département (depuis panel_departement)
            if (selectedDepartment && feature.properties.department) {
                matchesDepartment = normalizeText(feature.properties.department) === normalizeText(selectedDepartment);
            }
            
            // Filtre par région (depuis panel_region)
            if (selectedRegion && feature.properties.region) {
                matchesRegion = normalizeText(feature.properties.region) === normalizeText(selectedRegion);
            }
            
            // Filtre par type de panneau (panel_type)
            if (selectedType && feature.properties.panel_type) {
                matchesType = normalizeText(feature.properties.panel_type) === normalizeText(selectedType);
            }
            
            // Filtre par type de support (panel_support converti en support_type)
            if (selectedSupport) {
                var panelSupport = feature.properties.support_type || feature.properties.panel_support || '';
                matchesSupport = normalizeText(panelSupport) === normalizeText(selectedSupport);
            }
            
            // Filtre par statut (panel_disponibilite converti en status)
            if (selectedStatus && (feature.properties.status || feature.properties.panel_disponibilite)) {
                var panelStatus = feature.properties.status || feature.properties.panel_disponibilite || '';
                matchesStatus = normalizeText(panelStatus) === normalizeText(selectedStatus);
            }
            
            // Filtre par format (panel_format_standard ou panel_format)
            if (selectedFormat) {
                matchesFormat = false;
                
                // Vérifier dans l'ordre: panel_format_standard, panel_format, format
                // Utiliser une variable directe pour une meilleure lisibilité
                var formatValue = normalizeText(selectedFormat);
                
                // Format standard est prioritaire - c'est celui qui est utilisé dans la metabox
                if (feature.properties.panel_format_standard && 
                    normalizeText(feature.properties.panel_format_standard) === formatValue) {
                    matchesFormat = true;
                }
                // Ensuite essayer panel_format  
                else if (feature.properties.panel_format && 
                    normalizeText(feature.properties.panel_format) === formatValue) {
                    matchesFormat = true;
                }
                // Enfin essayer le champ format qui pourrait être utilisé comme alternative
                else if (feature.properties.format && 
                    normalizeText(feature.properties.format) === formatValue) {
                    matchesFormat = true;
                }
                
                // Afficher un log pour debugging
                if (!matchesFormat && (feature.properties.panel_format_standard || feature.properties.panel_format || feature.properties.format)) {
                    console.log("Format non correspondant:", {
                        filter: formatValue,
                        panel_format_standard: feature.properties.panel_format_standard,
                        panel_format: feature.properties.panel_format,
                        format: feature.properties.format,
                        id: feature.properties.id,
                        title: feature.properties.title
                    });
                }
            }
            
            // Filtre par dimensions
            if ((minWidth > 0 || maxWidth > 0 || minHeight > 0 || maxHeight > 0) && 
                (feature.properties.width || feature.properties.height || feature.properties.panel_width || feature.properties.panel_height)) {
                
                const width = parseInt(feature.properties.width || feature.properties.panel_width) || 0;
                const height = parseInt(feature.properties.height || feature.properties.panel_height) || 0;
                
                if (minWidth > 0 && width < minWidth) matchesDimensions = false;
                if (maxWidth > 0 && width > maxWidth) matchesDimensions = false;
                if (minHeight > 0 && height < minHeight) matchesDimensions = false;
                if (maxHeight > 0 && height > maxHeight) matchesDimensions = false;
            }
            
            // Un panneau doit correspondre à tous les filtres pour être affiché
            const matches = matchesSearch && matchesCategories && matchesCities && 
                        matchesDepartment && matchesRegion && matchesType && 
                        matchesSupport && matchesStatus && matchesFormat && matchesDimensions;
                        
            // Si le panneau correspond aux filtres, l'ajouter aux résultats à afficher
            if (matches) {
                // Pour déterminer le format, chercher dans plusieurs champs possibles
                let format = feature.properties.format || feature.properties.panel_format_standard || feature.properties.panel_format || '';
                if (!format && feature.properties.surface) {
                    const surface = parseFloat(feature.properties.surface);
                    if (!isNaN(surface)) {
                        if (surface >= 11 && surface <= 12.5) format = '12M2';
                        else if (surface >= 7.5 && surface < 11) format = '8M2';
                        else if (surface >= 5.5 && surface < 7.5) format = '6M2';
                        else if (surface >= 3.5 && surface < 5.5) format = '4M2';
                        else if (surface >= 1.75 && surface < 3.5) format = '2M2';
                        else if (surface >= 1.25 && surface < 1.75) format = '1,5M2';
                    }
                }
                
                // Récupérer le statut depuis le bon champ
                let status = feature.properties.status || feature.properties.panel_disponibilite || '';
                
                displayedResults.push({
                    id: feature.properties.id,
                    title: feature.properties.title,
                    reference: feature.properties.reference || '',
                    panel_type: feature.properties.panel_type || '',
                    support_type: feature.properties.support_type || feature.properties.panel_support || '',
                    format: format,
                    surface: feature.properties.surface || '',
                    dimensions: (feature.properties.width && feature.properties.height) ? 
                              `${feature.properties.width}×${feature.properties.height} cm` : 
                              (feature.properties.panel_width && feature.properties.panel_height) ?
                              `${feature.properties.panel_width}×${feature.properties.panel_height} cm` : '',
                    address: getFullAddress(feature.properties),
                    city: feature.properties.city_name || '',
                    department: feature.properties.department || '',
                    region: feature.properties.region || '',
                    status: status,
                    image: feature.properties.image || '',
                    visibility_from: feature.properties.visibility_from || '',
                    visibility_to: feature.properties.visibility_to || '',
                    visibility_angle: feature.properties.visibility_angle || '',
                    visibility_distance: feature.properties.visibility_distance || '',
                    panel_traffic: feature.properties.panel_traffic || '',
                    visibility_note: feature.properties.visibility_note || ''
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
                // Utiliser l'icône SVG personnalisée pour tous les panneaux
                var icon = L.icon({
                    iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/sucette_panneau_pin.svg',
                    iconSize: [30, 40],
                    iconAnchor: [15, 40],
                    popupAnchor: [0, -35]
                });
                return L.marker(latlng, { icon: icon });
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
        content += '<h3 style="margin: 0 0 10px; color: #70c141;">' + panel.title;
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
        
        // Section 1: Détails techniques
        content += '<div class="details-section" style="margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 10px;">';
        content += '<h4 style="margin: 0 0 8px; color: #70c141; font-size: 15px;">Informations techniques</h4>';
        
        // Type et support
        if (panel.panel_type) {
            content += '<p style="margin: 3px 0;"><strong>Type:</strong> ' + panel.panel_type + '</p>';
        }
        if (panel.support_type || panel.panel_support) {
            content += '<p style="margin: 3px 0;"><strong>Support:</strong> ' + (panel.support_type || panel.panel_support || '') + '</p>';
        }
        
        // Dimensions et format
        if ((panel.width && panel.height) || (panel.panel_width && panel.panel_height)) {
            var width = panel.width || panel.panel_width;
            var height = panel.height || panel.panel_height;
            content += '<p style="margin: 3px 0;"><strong>Dimensions:</strong> ' + width + '×' + height + ' cm';
            if (panel.surface) {
                content += ' (' + panel.surface + ' m²)';
            }
            content += '</p>';
        } else if (panel.surface) {
            content += '<p style="margin: 3px 0;"><strong>Surface:</strong> ' + panel.surface + ' m²</p>';
        }
        
        // Format standard
        if (panel.format || panel.panel_format || panel.panel_format_standard) {
            content += '<p style="margin: 3px 0;"><strong>Format:</strong> ' + (panel.panel_format_standard || panel.format || panel.panel_format || '') + '</p>';
        }
        
        // Annonceur actuel et date de fin
        if (panel.panel_annonceur) {
            content += '<p style="margin: 3px 0;"><strong>Annonceur:</strong> ' + panel.panel_annonceur + '</p>';
        }
        if (panel.panel_date_fin) {
            content += '<p style="margin: 3px 0;"><strong>Fin d\'engagement:</strong> ' + panel.panel_date_fin + '</p>';
        }
        content += '</div>';
        
        // Section 2: Localisation
        content += '<div class="details-section" style="margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 10px;">';
        content += '<h4 style="margin: 0 0 8px; color: #70c141; font-size: 15px;">Localisation</h4>';
        
        // Adresse complète
        if (parseInt(options.popup_show_address) === 1) {
            var address = getFullAddress(panel);
            if (address) {
                content += '<p style="margin: 3px 0;"><strong>Adresse:</strong> ' + address + '</p>';
            }
        }
        
        // Département et région
        if (panel.department || panel.panel_departement) {
            content += '<p style="margin: 3px 0;"><strong>Département:</strong> ' + (panel.department || panel.panel_departement) + '</p>';
        }
        if (panel.region || panel.panel_region) {
            content += '<p style="margin: 3px 0;"><strong>Région:</strong> ' + (panel.region || panel.panel_region) + '</p>';
        }
        
        // Coordonnées GPS
        if (panel.panel_latitude && panel.panel_longitude) {
            content += '<p style="margin: 3px 0;"><strong>Coordonnées GPS:</strong> ' + panel.panel_latitude + ', ' + panel.panel_longitude + '</p>';
        }
        content += '</div>';
        
        // Section 3: Visibilité
        content += '<div class="details-section">';
        content += '<h4 style="margin: 0 0 8px; color: #70c141; font-size: 15px;">Visibilité</h4>';
        
        // Informations de visibilité
        if (panel.visibility_from || panel.visibility_to) {
            content += '<p style="margin: 3px 0;"><strong>Visibilité:</strong> ';
            if (panel.visibility_from) content += 'En venant de ' + panel.visibility_from;
            if (panel.visibility_from && panel.visibility_to) content += ', ';
            if (panel.visibility_to) content += 'En allant à ' + panel.visibility_to;
            content += '</p>';
        }
        
        if (panel.visibility_angle) {
            content += '<p style="margin: 3px 0;"><strong>Angle de visibilité:</strong> ' + panel.visibility_angle + '°</p>';
        }
        
        if (panel.visibility_distance) {
            content += '<p style="margin: 3px 0;"><strong>Distance de visibilité:</strong> ' + panel.visibility_distance + ' m</p>';
        }
        
        if (panel.panel_traffic) {
            content += '<p style="margin: 3px 0;"><strong>Trafic quotidien:</strong> ' + panel.panel_traffic + ' passages</p>';
        }
        
        // Notes sur la visibilité
        if (panel.visibility_note) {
            content += '<p style="margin: 3px 0;"><strong>Notes:</strong> ' + panel.visibility_note + '</p>';
        }
        content += '</div>';
        

        
        content += '</div>'; // Fin panel-details
        
        // Bouton pour contacter / en savoir plus
        content += '<div class="panel-actions" style="margin-top: 15px; text-align: center;">' +
            '<a href="/contact/?panel_id=' + panel.id + 
            '&panel_ref=' + encodeURIComponent(panel.reference || '') + 
            '&panel_type=' + encodeURIComponent(panel.panel_type || '') +
            '&panel_address=' + encodeURIComponent(getFullAddress(panel) || '') +
            '&panel_city=' + encodeURIComponent(panel.city_name || '') +
            '&panel_format=' + encodeURIComponent(panel.panel_format_standard || panel.format || panel.panel_format || '') +
            '" class="panel-contact-btn" style="' +
            'background-color: #70c141; color: white; padding: 8px 15px; text-decoration: none; ' +
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
            
            // Section technique
            html += `<div class="result-section">`;
            
            // Référence
            if (result.reference) {
                html += `<p><strong>Réf.:</strong> ${result.reference}</p>`;
            }
            
            // Type et support
            if (result.panel_type) {
                html += `<p><strong>Type:</strong> ${result.panel_type}</p>`;
            }
            
            if (result.support_type) {
                html += `<p><strong>Support:</strong> ${result.support_type}</p>`;
            }
            
            // Dimensions et format
            if (result.dimensions) {
                html += `<p><strong>Dimensions:</strong> ${result.dimensions}</p>`;
            }
            
            if (result.surface) {
                html += `<p><strong>Surface:</strong> ${result.surface} m²</p>`;
            }
            
            if (result.format) {
                html += `<p><strong>Format:</strong> ${result.format}</p>`;
            }
            html += `</div>`;
            
            // Section localisation
            html += `<div class="result-section">`;
            // Adresse
            if (result.address) {
                html += `<p><strong>Adresse:</strong> ${result.address}</p>`;
            } else if (result.city) {
                html += `<p><strong>Ville:</strong> ${result.city}</p>`;
            }
            
            // Département et région
            if (result.department) {
                html += `<p><strong>Département:</strong> ${result.department}</p>`;
            }
            
            if (result.region) {
                html += `<p><strong>Région:</strong> ${result.region}</p>`;
            }
            html += `</div>`;
            
            // Section visibilité (nouvelle section)
            if (result.visibility_from || result.visibility_to || result.visibility_angle || 
                result.visibility_distance || result.panel_traffic || result.visibility_note) {
                html += `<div class="result-section">
                    <h5>Visibilité</h5>`;
                
                // Visibilité directionnelle
                if (result.visibility_from || result.visibility_to) {
                    html += `<p><strong>Direction:</strong> `;
                    if (result.visibility_from) html += `de ${result.visibility_from}`;
                    if (result.visibility_from && result.visibility_to) html += ` `;
                    if (result.visibility_to) html += `vers ${result.visibility_to}`;
                    html += `</p>`;
                }
                
                // Angle et distance
                if (result.visibility_angle) {
                    html += `<p><strong>Angle:</strong> ${result.visibility_angle}°</p>`;
                }
                
                if (result.visibility_distance) {
                    html += `<p><strong>Distance:</strong> ${result.visibility_distance} m</p>`;
                }
                
                // Trafic quotidien
                if (result.panel_traffic) {
                    html += `<p><strong>Trafic:</strong> ${result.panel_traffic} passages/jour</p>`;
                }
                
                // Notes
                if (result.visibility_note) {
                    html += `<p><strong>Notes:</strong> ${result.visibility_note}</p>`;
                }
                
                html += `</div>`;
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
                
                html += `<div class="result-section status-section">
                    <p><strong>Statut:</strong> <span class="status-badge" style="background-color:${statusColor}; color: white; 
                    padding: 2px 8px; border-radius: 12px; font-size: 12px; display: inline-block;">
                    ${result.status}</span></p>
                </div>`;
            }
            
            html += `</div>
                <button class="locate-on-map" data-id="${result.id}">
                    Localiser sur la carte
                </button>
                <a href="/contact/?panel_id=${result.id}' + 
                    '&panel_ref=' + encodeURIComponent(result.reference || '') +
                    '&panel_type=' + encodeURIComponent(result.panel_type || '') +
                    '&panel_address=' + encodeURIComponent(result.address || '') +
                    '&panel_city=' + encodeURIComponent(result.city || '') +
                    '&panel_format=' + encodeURIComponent(result.format || '') +
                '" class="contact-button">
                    Contacter / Réserver
                </a>
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

        jQuery('#city-filter').select2({
            placeholder: "Sélectionnez une ou plusieurs villes",
            allowClear: true,
            width: '100%',
            closeOnSelect: false
        });

        jQuery('#department-filter, #region-filter, #type-filter, #support-filter, #format-filter, #status-filter').select2({
            width: '100%',
            placeholder: "Sélectionner...",
            allowClear: true
        });
    }

    // 14) Gestionnaires d'événements pour les filtres
    var applyFilterBtn = document.getElementById('apply-filter');
    var resetFilterBtn = document.getElementById('reset-filter');
    
    // Fonction pour obtenir la valeur d'un élément DOM de manière sécurisée
    function getElementValue(id) {
        var element = document.getElementById(id);
        return element ? element.value : '';
    }
    
    // Fonction pour obtenir une valeur numérique d'un élément DOM de manière sécurisée
    function getElementNumericValue(id) {
        var element = document.getElementById(id);
        return element && element.value ? parseInt(element.value) : 0;
    }
    
    // Appliquer les filtres
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            // Récupérer les valeurs de filtres
            var selectedCategories = [];
            if (typeof jQuery !== 'undefined' && jQuery('#category-filter').length) {
                selectedCategories = jQuery('#category-filter').val() || [];
            }
            
            var selectedCities = [];
            if (typeof jQuery !== 'undefined' && jQuery('#city-filter').length) {
                selectedCities = jQuery('#city-filter').val() || [];
            }
            
            var filters = {
                search: getElementValue('search-filter'),
                categories: selectedCategories,
                cities: selectedCities,
                department: getElementValue('department-filter'),
                region: getElementValue('region-filter'),
                type: getElementValue('type-filter'),
                support: getElementValue('support-filter'),
                format: getElementValue('format-filter'),
                status: getElementValue('status-filter'),
                minWidth: getElementNumericValue('min-width'),
                maxWidth: getElementNumericValue('max-width'),
                minHeight: getElementNumericValue('min-height'),
                maxHeight: getElementNumericValue('max-height')
            };

            console.log("Application des filtres:", filters);
            // Appliquer les filtres
            filterAndRenderData(filters);
        });
    }

    // Réinitialiser les filtres
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            // Réinitialiser les valeurs de filtres de manière sécurisée
            var searchFilter = document.getElementById('search-filter');
            if (searchFilter) searchFilter.value = '';
            
            var minWidth = document.getElementById('min-width');
            if (minWidth) minWidth.value = '';
            
            var maxWidth = document.getElementById('max-width');
            if (maxWidth) maxWidth.value = '';
            
            var minHeight = document.getElementById('min-height');
            if (minHeight) minHeight.value = '';
            
            var maxHeight = document.getElementById('max-height');
            if (maxHeight) maxHeight.value = '';
            
            if (typeof jQuery !== 'undefined') {
                if (jQuery('#category-filter').length) jQuery('#category-filter').val(null).trigger('change');
                if (jQuery('#city-filter').length) jQuery('#city-filter').val(null).trigger('change');
                if (jQuery('#department-filter').length) jQuery('#department-filter').val(null).trigger('change');
                if (jQuery('#region-filter').length) jQuery('#region-filter').val(null).trigger('change');
                if (jQuery('#type-filter').length) jQuery('#type-filter').val(null).trigger('change');
                if (jQuery('#support-filter').length) jQuery('#support-filter').val(null).trigger('change');
                if (jQuery('#format-filter').length) jQuery('#format-filter').val(null).trigger('change');
                if (jQuery('#status-filter').length) jQuery('#status-filter').val(null).trigger('change');
            } else {
                var cityFilter = document.getElementById('city-filter');
                if (cityFilter) cityFilter.value = '';
                
                var departmentFilter = document.getElementById('department-filter');
                if (departmentFilter) departmentFilter.value = '';
                
                var regionFilter = document.getElementById('region-filter');
                if (regionFilter) regionFilter.value = '';
                
                var typeFilter = document.getElementById('type-filter');
                if (typeFilter) typeFilter.value = '';
                
                var supportFilter = document.getElementById('support-filter');
                if (supportFilter) supportFilter.value = '';
                
                var formatFilter = document.getElementById('format-filter');
                if (formatFilter) formatFilter.value = '';
                
                var statusFilter = document.getElementById('status-filter');
                if (statusFilter) statusFilter.value = '';
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