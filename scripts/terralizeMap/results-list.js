// Gestion de l'affichage et de la pagination des résultats
import { showFeaturePopup } from './popups.js';

export function setupResultsList(map, options) {
    const mapId = options.map_id;
    let geoJSONLayer = null;
    let allResults = [];
    let currentPage = 1;
    const resultsPerPage = 5;

    // DEBUGGING: Afficher les options reçues
    console.log("SETUP RESULTS LIST - options:", {
        mapId: options.map_id,
        zonesDataLength: options.zonesData ? 
            (options.zonesData.features ? options.zonesData.features.length : 
             (Array.isArray(options.zonesData) ? options.zonesData.length : "format inconnu")) 
            : "pas de données"
    });

    // Vérifier et utiliser directement options.zonesData s'il est présent
    let data = null;
    
    if (options.zonesData) {
        console.log("Données trouvées dans options.zonesData");
        data = options.zonesData;
    } else {
        console.log("Pas de données dans options.zonesData");
        data = { type: "FeatureCollection", features: [] };
    }
    
    // Stocker les données initiales dans l'état global pour compatibilité
    window.terralizemap.state = window.terralizemap.state || {};
    window.terralizemap.state.allZonesData = window.terralizemap.state.allZonesData || {};
    window.terralizemap.state.allZonesData[mapId] = data;
    
    // DEBUGGING: Vérifier le stockage des données
    console.log("DONNÉES STOCKÉES:", {
        source: options.zonesData ? "options" : "vide",
        format: data.type === "FeatureCollection" ? "FeatureCollection" : "Autre format",
        featuresLength: data.features ? data.features.length : "pas de features"
    });

    // Afficher toutes les données à l'initialisation
    const initialFilters = {
        categories: [],
        region: '',
        type: 'all',
        searchQuery: ''
    };
    console.log("DISPATCH INITIAL FILTER EVENT:", { mapId, filters: initialFilters });
    
    // MODIFICATION CRITIQUE: Créer et afficher directement le layer GeoJSON
    // sans attendre l'événement de filtrage
    console.log("Création immédiate du layer GeoJSON avec les données");
    
    let geoJsonInput = data;
    if (Array.isArray(data)) {
        geoJsonInput = { type: 'FeatureCollection', features: data };
    }
    
    console.log("CREATION DIRECTE GeoJSON:", {
        format: typeof geoJsonInput,
        isFeatureCollection: geoJsonInput.type === 'FeatureCollection',
        featuresLength: geoJsonInput.features ? geoJsonInput.features.length : "N/A",
        sampleFeature: geoJsonInput.features && geoJsonInput.features.length > 0 ? 
                       JSON.stringify(geoJsonInput.features[0]).substring(0, 100) : "Aucun"
    });
    
    try {
        // Créer et ajouter immédiatement la couche GeoJSON sans attendre un filtre
        geoJSONLayer = L.geoJSON(geoJsonInput, {
            style: function(feature) {
                if (feature.properties.type !== 'poi') {
                    return {
                        color: feature.properties.border_color || options.zone_border_color || '#3388ff',
                        fillColor: feature.properties.fill_color || options.zone_fill_color || '#3388ff',
                        fillOpacity: parseFloat(options.zone_opacity || 0.5),
                        weight: 2
                    };
                }
            },
            pointToLayer: function(feature, latlng) {
                // console.log("CRÉATION MARKER POUR FEATURE:", feature.properties.title || "Sans titre");
                if (feature.properties && feature.properties.type === 'poi') {
                    return L.marker(latlng, {
                        icon: L.icon({
                            iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/poi.png',
                            iconSize: [30, 40],
                            iconAnchor: [15, 40],
                            popupAnchor: [0, -35]
                        })
                    });
                }
                return L.marker(latlng);
            },
            onEachFeature: function(feature, layer) {
                layer.on('click', function() {
                    showFeaturePopup(map, feature, layer, options);
                });
            }
        }).addTo(map);
        
        console.log("Layer GeoJSON créé et ajouté à la carte:", !!geoJSONLayer);
        
        // AJOUT: Générer et afficher les résultats pour la sidebar
        console.log("Préparation des résultats pour la sidebar...");
        let displayedResults = [];
        
        if (geoJsonInput.features && geoJsonInput.features.length > 0) {
            geoJsonInput.features.forEach(function(feature) {
                if (feature.properties) {
                    if (feature.properties.type === 'zone') {
                        displayedResults.push({
                            id: feature.properties.id,
                            name: feature.properties.nom_commercial || feature.properties.title,
                            type: 'zone',
                            region: feature.properties.regions && feature.properties.regions.length > 0 
                                ? feature.properties.regions.map(r => r.name).join(', ') 
                                : 'Non spécifiée',
                            products: feature.properties.categories && feature.properties.categories.length > 0 
                                ? feature.properties.categories.map(c => c.name).join(', ') 
                                : 'Non spécifiés',
                            phone: feature.properties.telephone || ''
                        });
                    } else if (feature.properties.type === 'poi') {
                        displayedResults.push({
                            id: feature.properties.id,
                            name: feature.properties.title,
                            type: 'poi',
                            categories: feature.properties.categories && feature.properties.categories.length > 0 
                                ? feature.properties.categories.map(c => c.name).join(', ') 
                                : 'Non spécifiées'
                        });
                    }
                }
            });
        }
        
        // Stocker les résultats affichés
        window.terralizemap.state.displayedResults = window.terralizemap.state.displayedResults || {};
        window.terralizemap.state.displayedResults[mapId] = displayedResults;
        
        // Mettre à jour les compteurs de résultats
        updateResultsCount(displayedResults.length);
        
        // Afficher les résultats dans la sidebar
        console.log("Mise à jour de la sidebar avec", displayedResults.length, "résultats");
        updateResultsList(displayedResults);
        
        // Ajuster la vue
        if (geoJsonInput.features && geoJsonInput.features.length > 0 && 
            geoJSONLayer && typeof geoJSONLayer.getBounds === 'function') {
            try {
                console.log("Ajustement de la vue...");
                map.fitBounds(geoJSONLayer.getBounds());
                console.log("Vue ajustée");
            } catch (e) {
                console.error("Impossible d'ajuster la vue:", e);
            }
        }
    } catch (error) {
        console.error("ERREUR lors de la création du layer GeoJSON:", error);
    }
    
    // Continuer avec l'écouteur de filtre pour les filtres futurs
    const event = new CustomEvent('terralize:filter_data', {
        detail: { mapId, filters: initialFilters }
    });
    document.dispatchEvent(event);

    // Écoute l'événement de filtrage
    document.addEventListener('terralize:filter_data', function(e) {
        console.log("EVENT FILTER REÇU:", e.detail);
        if (!e.detail || e.detail.mapId !== mapId) {
            console.log("EVENT IGNORÉ: mauvais mapId");
            return;
        }
        
        const filters = e.detail.filters;
        const allZonesData = window.terralizemap.state.allZonesData[mapId] || { type: 'FeatureCollection', features: [] };
        console.log("FILTRAGE DÉMARRÉ:", { 
            filtres: filters,
            donnéesSource: allZonesData.features ? allZonesData.features.length : 'Données non structurées'
        });
        
        // --- Filtrage ---
        let filteredData = JSON.parse(JSON.stringify(allZonesData));
        let displayedResults = [];
        
        // Préparation des filtres
        let selectedRegions = [];
        if (filters.region && filters.region !== '') selectedRegions = [filters.region];
        
        let normalizedSearchQuery = '';
        if (filters.searchQuery) normalizedSearchQuery = filters.searchQuery.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        
        // Vérification si nous avons besoin de filtrer
        if (filters.categories.length > 0 || selectedRegions.length > 0 || filters.type !== 'all' || normalizedSearchQuery) {
            // Vérifier que filteredData a une structure GeoJSON valide
            if (filteredData && filteredData.features && Array.isArray(filteredData.features)) {
                // CORRECTION: Filtrer le tableau features plutôt que filteredData directement
                filteredData.features = filteredData.features.filter(function(feature) {
                    let matchesCategory = true, matchesRegion = true, matchesType = true, matchesSearch = true;
                    
                    if (filters.categories.length > 0) {
                        if (!feature.properties.categories || feature.properties.categories.length === 0) {
                            matchesCategory = false;
                        } else {
                            matchesCategory = feature.properties.categories.some(function(category) {
                                return filters.categories.includes(category.slug);
                            });
                        }
                    }
                    
                    if (selectedRegions.length > 0) {
                        if (!feature.properties.regions || feature.properties.regions.length === 0) {
                            matchesRegion = false;
                        } else {
                            matchesRegion = feature.properties.regions.some(function(region) {
                                return selectedRegions.includes(region.slug);
                            });
                        }
                    }
                    
                    if (filters.type !== 'all') {
                        matchesType = feature.properties.type === filters.type;
                    }
                    
                    if (normalizedSearchQuery) {
                        let found = false;
                        let searchableFields = [feature.properties.title || '', feature.properties.nom_commercial || '', feature.properties.infos || ''];
                        for (let i = 0; i < searchableFields.length; i++) {
                            let normalized = searchableFields[i].toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                            if (normalized.includes(normalizedSearchQuery)) { found = true; break; }
                        }
                        matchesSearch = found;
                    }
                    
                    return matchesCategory && matchesRegion && matchesType && matchesSearch;
                });
            } else {
                console.error("Structure de données invalide:", filteredData);
                // Créer une structure vide au cas où
                filteredData = { type: 'FeatureCollection', features: [] };
            }
        }
        
        // Préparer les résultats pour l'affichage dans la liste
        if (filteredData.features && filteredData.features.length > 0) {
            filteredData.features.forEach(function(feature) {
                if (feature.properties) {
                    if (feature.properties.type === 'zone') {
                        displayedResults.push({
                            id: feature.properties.id,
                            name: feature.properties.nom_commercial || feature.properties.title,
                            type: 'zone',
                            region: feature.properties.regions && feature.properties.regions.length > 0 ? feature.properties.regions.map(r => r.name).join(', ') : 'Non spécifiée',
                            products: feature.properties.categories && feature.properties.categories.length > 0 ? feature.properties.categories.map(c => c.name).join(', ') : 'Non spécifiés',
                            phone: feature.properties.telephone || ''
                        });
                    } else if (feature.properties.type === 'poi') {
                        displayedResults.push({
                            id: feature.properties.id,
                            name: feature.properties.title,
                            type: 'poi',
                            categories: feature.properties.categories && feature.properties.categories.length > 0 ? feature.properties.categories.map(c => c.name).join(', ') : 'Non spécifiées'
                        });
                    }
                }
            });
        }
        
        window.terralizemap.state.displayedResults[mapId] = displayedResults;
        updateResultsCount(displayedResults.length);
        updateResultsList(displayedResults);
        
        // Créer et ajouter la nouvelle couche GeoJSON
        if (geoJSONLayer) map.removeLayer(geoJSONLayer);
        
        console.log("CRÉATION GeoJSON:", {
            format: typeof filteredData,
            isFeatureCollection: filteredData.type === 'FeatureCollection',
            featuresLength: filteredData.features ? filteredData.features.length : "N/A",
            firstFeature: filteredData.features && filteredData.features.length > 0 ? 
                          JSON.stringify(filteredData.features[0]).substring(0, 100) : "Aucun"
        });
        
        try {
            geoJSONLayer = L.geoJSON(filteredData, {
                style: function(feature) {
                    if (feature.properties.type !== 'poi') {
                        if (feature.properties.border_color || feature.properties.fill_color) {
                            return {
                                color: feature.properties.border_color || options.zone_border_color || '#3388ff',
                                fillColor: feature.properties.fill_color || options.zone_fill_color || '#3388ff',
                                fillOpacity: parseFloat(options.zone_opacity || 0.5),
                                weight: 2
                            };
                        }
                        return {
                            color: options.zone_border_color || '#3388ff',
                            fillColor: options.zone_fill_color || '#3388ff',
                            fillOpacity: parseFloat(options.zone_opacity || 0.5),
                            weight: 2
                        };
                    }
                },
                pointToLayer: function(feature, latlng) {
                    console.log("CRÉATION POI:", { latLng: latlng, feature: feature });
                    if (feature.properties && feature.properties.type === 'poi') {
                        return L.marker(latlng, {
                            icon: L.icon({
                                iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/poi.png',
                                iconSize: [30, 40],
                                iconAnchor: [15, 40],
                                popupAnchor: [0, -35]
                            })
                        });
                    }
                    return L.marker(latlng);
                },
                onEachFeature: function(feature, layer) {
                    layer.on('click', function() {
                        showFeaturePopup(map, feature, layer, options);
                    });
                }
            }).addTo(map);
            
            console.log("LAYER AJOUTÉ:", geoJSONLayer);
            
            // Ajuster la vue si nécessaire
            if (filteredData.features && filteredData.features.length > 0 && 
                geoJSONLayer && typeof geoJSONLayer.getBounds === 'function') {
                try { 
                    const bounds = geoJSONLayer.getBounds();
                    console.log("BOUNDS:", bounds);
                    map.fitBounds(bounds); 
                } catch (e) {
                    console.error("ERREUR BOUNDS:", e);
                }
            }
        } catch (error) {
            console.error("ERREUR LAYER GeoJSON:", error);
        }
        // Sauvegarder les filtres actuels
        window.terralizemap.state.currentFilters = window.terralizemap.state.currentFilters || {};
        window.terralizemap.state.currentFilters[mapId] = filters;
    });

    function updateResultsList(results) {
        allResults = results;
        const resultsListEl = document.getElementById('results-list-' + mapId);
        if (!resultsListEl) return;
        if (results.length === 0) {
            resultsListEl.innerHTML = '<p class="no-results">Aucun résultat ne correspond à vos critères.</p>';
            currentPage = 1;
            updatePagination(results);
            return;
        }
        currentPage = 1;
        displayResultsPage(1);
        updatePagination(results);
    }
    function renderResultsList(pageResults) {
        const resultsListEl = document.getElementById('results-list-' + mapId);
        if (!resultsListEl) return;
        let html = '';
        pageResults.forEach(function(result) {
            if (result.type === 'zone') {
                html += `
            <div class="result-item commercial-item">
                <div class="result-badge zone">Zone</div>
                <h4>${result.name || 'Sans nom'}</h4>
                <div class="result-details">
                    <p><strong>Zone:</strong> ${result.region || 'Non spécifiée'}</p>
                    <p><strong>Services:</strong> ${result.products || 'Non spécifiés'}</p>
                    ${result.phone ? `<p><strong>Contact:</strong> <a href="tel:${result.phone.replace(/\s/g, '')}">${result.phone}</a></p>` : ''}
                </div>
                <button class="locate-on-map" data-id="${result.id}">
                    Localiser sur la carte
                </button>
            </div>
            `;
            } else if (result.type === 'poi') {
                html += `
            <div class="result-item poi-item">
                <div class="result-badge poi">Point de vente</div>
                <h4>${result.name || 'Sans nom'}</h4>
                <div class="result-details">
                    <p><strong>Catégories:</strong> ${result.categories || 'Non spécifiées'}</p>
                </div>
                <button class="locate-on-map" data-id="${result.id}">
                    Localiser sur la carte
                </button>
            </div>
            `;
            }
        });
        resultsListEl.innerHTML = html;
        document.querySelectorAll('.locate-on-map').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                // Implémentation de la localisation sur la carte
                locateItemOnMap(id);
            });
        });
    }

    // Fonction pour localiser un élément sur la carte
    function locateItemOnMap(id) {
        console.log("Localisation de l'élément avec ID:", id);
        
        // Récupérer les données GeoJSON actuelles
        const currentData = geoJSONLayer.toGeoJSON();
        
        if (!currentData || !currentData.features) {
            console.error("Aucune donnée GeoJSON disponible");
            return;
        }
        
        // Trouver la feature correspondant à l'ID
        const feature = currentData.features.find(f => 
            f.properties && f.properties.id === id
        );
        
        if (!feature) {
            console.error("Élément non trouvé avec l'ID:", id);
            return;
        }
        
        // Trouver la couche Leaflet correspondante
        let targetLayer = null;
        
        geoJSONLayer.eachLayer(function(layer) {
            if (layer.feature && layer.feature.properties && layer.feature.properties.id === id) {
                targetLayer = layer;
            }
        });
        
        if (!targetLayer) {
            console.error("Couche non trouvée pour l'ID:", id);
            return;
        }
        
        // Centrer la carte sur la feature
        if (feature.geometry.type === 'Point') {
            // Pour les points (POI), centrer sur les coordonnées
            const coords = feature.geometry.coordinates;
            map.setView([coords[1], coords[0]], 15);
        } else {
            // Pour les polygones (zones), ajuster la vue aux limites
            map.fitBounds(targetLayer.getBounds());
        }
        
        // Ouvrir la popup
        showFeaturePopup(map, feature, targetLayer, options);
        
        // Ajouter un effet visuel pour mettre en évidence l'élément
        if (feature.properties.type !== 'poi') {
            // Pour les zones (polygones)
            if (targetLayer.setStyle) {
                const originalStyle = {
                    color: feature.properties.border_color || options.zone_border_color || '#3388ff',
                    fillColor: feature.properties.fill_color || options.zone_fill_color || '#3388ff',
                    fillOpacity: parseFloat(options.zone_opacity || 0.5),
                    weight: 2
                };
                
                // Style de mise en évidence
                targetLayer.setStyle({
                    weight: 4,
                    color: '#ff4500',
                    dashArray: '',
                    fillOpacity: 0.7
                });
                
                // Rétablir le style d'origine après un délai
                setTimeout(function() {
                    targetLayer.setStyle(originalStyle);
                }, 2000);
            }
        } else {
            // Pour les POI (marqueurs)
            if (targetLayer._icon) {
                // Ajouter une classe pour animation CSS
                targetLayer._icon.classList.add('highlight-marker');
                
                // Retirer la classe après un délai
                setTimeout(function() {
                    targetLayer._icon.classList.remove('highlight-marker');
                }, 2000);
            }
        }
    }

    function displayResultsPage(page) {
        const totalPages = Math.max(1, Math.ceil(allResults.length / resultsPerPage));
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;
        const startIndex = (page - 1) * resultsPerPage;
        const endIndex = Math.min(startIndex + resultsPerPage, allResults.length);
        const pageResults = allResults.slice(startIndex, endIndex);
        renderResultsList(pageResults);
        updatePagination(allResults);
    }
    function updatePagination(totalResults) {
        const totalPages = Math.max(1, Math.ceil(totalResults.length / resultsPerPage));
        const prevButton = document.getElementById('prev-page-' + mapId);
        const nextButton = document.getElementById('next-page-' + mapId);
        if (prevButton) {
            prevButton.disabled = currentPage <= 1;
            prevButton.onclick = function() {
                if (currentPage > 1) displayResultsPage(currentPage - 1);
            };
        }
        if (nextButton) {
            nextButton.disabled = currentPage >= totalPages;
            nextButton.onclick = function() {
                if (currentPage < totalPages) displayResultsPage(currentPage + 1);
            };
        }
        // Mettre à jour les compteurs
        updateElementText('current-page-' + mapId, currentPage);
        updateElementText('total-pages-' + mapId, totalPages);
    }
    function updateResultsCount(count) {
        const resultsCounter = document.getElementById('results-counter-' + mapId);
        const resultsCount = document.getElementById('results-count-' + mapId);
        const tabResultsCount = document.getElementById('tab-results-count-' + mapId);
        if (resultsCounter) resultsCounter.textContent = '(' + count + ')';
        if (resultsCount) resultsCount.textContent = '(' + count + ')';
        if (tabResultsCount) tabResultsCount.textContent = count;
    }
    function updateElementText(id, text) {
        const element = document.getElementById(id);
        if (element) element.textContent = text;
    }
} 