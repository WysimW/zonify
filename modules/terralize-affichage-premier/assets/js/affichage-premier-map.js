/**
 * Script JavaScript pour la carte des panneaux d'affichage - Extension Terralize Affichage Premier
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation de la carte v1');

    var options = window.mapOptions || {};
    console.log('Options de la carte:', options);
    
    // Initialisation des variables globales
    var allResults = [];
    var currentPage = 1;
    var resultsPerPage = 5;
    var geoJSONLayer;
    window.maps = window.maps || {};
    
    // Fonction pour afficher une image en lightbox
    window.openImageLightbox = function(imgUrl) {
        // Créer l'overlay pour la lightbox s'il n'existe pas déjà
        var lightboxOverlay = document.getElementById('image-lightbox-overlay');
        if (!lightboxOverlay) {
            lightboxOverlay = document.createElement('div');
            lightboxOverlay.id = 'image-lightbox-overlay';
            lightboxOverlay.className = 'lightbox-overlay';
            lightboxOverlay.innerHTML = `
                <div class="lightbox-container">
                    <div class="lightbox-content">
                        <img id="lightbox-image" src="" alt="Image agrandie" />
                    </div>
                    <button class="lightbox-close">×</button>
                </div>
            `;
            document.body.appendChild(lightboxOverlay);
            
            // Ajouter les écouteurs d'événements pour fermer la lightbox
            lightboxOverlay.addEventListener('click', function(event) {
                if (event.target === lightboxOverlay || event.target.className === 'lightbox-close') {
                    lightboxOverlay.style.display = 'none';
                }
            });
            
            // Ajouter les styles CSS pour la lightbox s'ils n'existent pas déjà
            if (!document.getElementById('lightbox-styles')) {
                var style = document.createElement('style');
                style.id = 'lightbox-styles';
                style.textContent = `
                    .lightbox-overlay {
                        display: none;
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.8);
                        z-index: 10000;
                        justify-content: center;
                        align-items: center;
                    }
                    .lightbox-container {
                        position: relative;
                        max-width: 90%;
                        max-height: 90%;
                    }
                    .lightbox-content {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
                    .lightbox-content img {
                        max-width: 100%;
                        max-height: 80vh;
                        object-fit: contain;
                        border: 2px solid white;
                        border-radius: 4px;
                    }
                    .lightbox-close {
                        position: absolute;
                        top: -30px;
                        right: -30px;
                        background: transparent;
                        border: none;
                        color: white;
                        font-size: 30px;
                        cursor: pointer;
                    }
                    .image-zoom-icon {
                        position: absolute;
                        bottom: 10px;
                        right: 10px;
                        background-color: rgba(255, 255, 255, 0.8);
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        cursor: pointer;
                        color: #333;
                        font-size: 16px;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                        transition: transform 0.2s ease;
                        z-index: 1;
                    }
                    .image-zoom-icon:hover {
                        transform: scale(1.1);
                        background-color: rgba(255, 255, 255, 1);
                    }
                    .panel-image {
                        position: relative;
                    }
                    .result-image {
                        position: relative;
                    }
                `;
                document.head.appendChild(style);
            }
        }
        
        // Mettre à jour l'image dans la lightbox
        var lightboxImage = document.getElementById('lightbox-image');
        if (lightboxImage) {
            lightboxImage.src = imgUrl;
            
            // Afficher la lightbox
            lightboxOverlay.style.display = 'flex';
        }
    };

    // Définir la fonction switchTab dans le scope global pour les onglets des popups
    window.switchTab = function(btn, tabId) {
        var btns = document.querySelectorAll('.panel-popup .tab-btn');
        for(var i=0; i<btns.length; i++) {
            btns[i].classList.remove('active');
            btns[i].style.fontWeight = 'normal';
            btns[i].style.borderBottomColor = 'transparent';
            btns[i].style.color = '#333';
        }
        btn.classList.add('active');
        btn.style.fontWeight = 'bold';
        btn.style.borderBottomColor = '#70c141';
        btn.style.color = '#70c141';
        
        var contents = document.querySelectorAll('.panel-popup .tab-content');
        for(var j=0; j<contents.length; j++) {
            contents[j].style.display = 'none';
        }
        document.getElementById(tabId).style.display = 'block';
    };

    // Récupérer l'ID de la carte depuis les options
    var mapId = options.map_id || '';
    console.log("ID de la carte:", mapId);

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

    // Trouver tous les conteneurs de carte sur la page
    var mapContainers = document.querySelectorAll('.affichage-premier-map');
    
    // Définir filterAndRenderData en dehors de la boucle pour qu'elle soit accessible globalement
    function filterAndRenderData(filters, currentMap, mapId) {
        // S'assurer que currentMap est défini
        if (!currentMap && mapId) {
            currentMap = window.maps[mapId];
        }
        
        if (!currentMap) {
            console.error("Carte non définie pour le filtrage des données");
            return;
        }
        
        // Supprimer la couche existante si elle existe
        if (geoJSONLayer) {
            currentMap.removeLayer(geoJSONLayer);
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
        var radiusKm = filters.radiusKm ? parseFloat(filters.radiusKm) : 0;
        var centerCity = filters.centerCity || null;
        
        // Nouveaux filtres pour la géolocalisation
        var userLocation = filters.userLocation || null;
        var userLocationRadiusKm = filters.userLocationRadiusKm ? parseFloat(filters.userLocationRadiusKm) : 0;

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
            let matchesRadius = true;
            let matchesUserLocationRadius = true;
            
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
            
            // Filtre par rayon kilométrique autour d'une ville
            if (radiusKm > 0 && centerCity && centerCity.latitude && centerCity.longitude && feature.geometry && feature.geometry.coordinates) {
                // Coordonnées de la ville centrale
                const centerLat = parseFloat(centerCity.latitude);
                const centerLng = parseFloat(centerCity.longitude);
                
                // Coordonnées du panneau
                const panelLat = feature.geometry.coordinates[1];
                const panelLng = feature.geometry.coordinates[0];
                
                // Calculer la distance
                const distance = calculateDistance(centerLat, centerLng, panelLat, panelLng);
                
                // Vérifier si le panneau est dans le rayon spécifié
                if (distance > radiusKm) {
                    matchesRadius = false;
                }
            }
            
            // Filtre par rayon kilométrique autour de la position utilisateur
            if (userLocationRadiusKm > 0 && userLocation && userLocation.latitude && userLocation.longitude && 
                feature.geometry && feature.geometry.coordinates) {
                
                // Coordonnées de l'utilisateur
                const userLat = parseFloat(userLocation.latitude);
                const userLng = parseFloat(userLocation.longitude);
                
                // Coordonnées du panneau
                const panelLat = feature.geometry.coordinates[1];
                const panelLng = feature.geometry.coordinates[0];
                
                // Calculer la distance
                const distance = calculateDistance(userLat, userLng, panelLat, panelLng);
                
                // Log pour débogage
                if (feature.properties.id && feature.properties.title) {
                    console.log("Panneau: " + feature.properties.title + 
                                ", Distance: " + distance.toFixed(2) + " km, " +
                                "Rayon max: " + userLocationRadiusKm + " km, " +
                                "Inclus: " + (distance <= userLocationRadiusKm));
                }
                
                // Vérifier si le panneau est dans le rayon spécifié
                if (distance > userLocationRadiusKm) {
                    matchesUserLocationRadius = false;
                }
            }
            
            // Un panneau doit correspondre à tous les filtres pour être affiché
            const matches = matchesSearch && matchesCategories && matchesCities && 
                        matchesDepartment && matchesRegion && matchesType && 
                        matchesSupport && matchesStatus && matchesFormat && matchesDimensions && matchesRadius && matchesUserLocationRadius;
                        
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
                    photo_url: feature.properties.photo_url || '',
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
        updateResultsCount(displayedResults.length, mapId);
        updateResultsList(displayedResults, mapId);

        // Créer et ajouter la nouvelle couche GeoJSON
        geoJSONLayer = L.geoJSON(filteredData, {
            pointToLayer: function(feature, latlng) {
                // Récupérer les options d'icônes depuis les paramètres du module Affichage Premier
                var iconOptions = window.terralizeAPIconOptions || {};
                var iconUrl = iconOptions.poi_icon_url || '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/sucette_panneau_pin.svg';
                var iconSize = parseInt(iconOptions.poi_icon_size) || 30;
                var iconAnchorX = parseInt(iconOptions.poi_icon_anchor_x) || 15;
                var iconAnchorY = parseInt(iconOptions.poi_icon_anchor_y) || 40;
                
                // Calculer les dimensions de l'icône (maintenir le ratio 3:4 pour l'icône par défaut)
                var iconHeight = Math.round(iconSize * 1.33); // Ratio 30:40 = 1:1.33
                
                var icon = L.icon({
                    iconUrl: iconUrl,
                    iconSize: [iconSize, iconHeight],
                    iconAnchor: [iconAnchorX, iconAnchorY],
                    popupAnchor: [0, -iconAnchorY + 5] // Ajuster la popup selon l'ancrage
                });
                return L.marker(latlng, { icon: icon });
            },
            onEachFeature: function(feature, layer) {
                layer.on('click', function() {
                    var content = createPopupContent(feature.properties);
                    L.popup()
                        .setLatLng(layer.getLatLng())
                        .setContent(content)
                        .openOn(currentMap);
                });
            }
        }).addTo(currentMap);

        // Ajuster la vue si nécessaire
        if (filteredData.length > 0) {
            try {
                if (geoJSONLayer && typeof geoJSONLayer.getBounds === 'function') {
                    currentMap.fitBounds(geoJSONLayer.getBounds());
                }
            } catch (e) {
                console.error("Impossible d'ajuster la vue:", e);
            }
        }
    }

    // Fonction pour mettre à jour le compteur de résultats
    function updateResultsCount(count, mapId) {
        var resultsCountEl = document.getElementById('results-count-' + mapId);
        var tabResultsCount = document.getElementById('tab-results-count-' + mapId);
        var resultsCounter = document.getElementById('results-counter-' + mapId);

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

    // Fonction pour mettre à jour la liste des résultats
    function updateResultsList(results, mapId) {
        allResults = results;
        var resultsListEl = document.getElementById('results-list-' + mapId);
        if (!resultsListEl) return;

        if (results.length === 0) {
            resultsListEl.innerHTML = '<p class="no-results">Aucun panneau ne correspond à vos critères.</p>';
            currentPage = 1;
            updatePagination(results, mapId);
            return;
        }

        currentPage = 1;
        displayResultsPage(1, mapId);
        updatePagination(results, mapId);
    }

    mapContainers.forEach(function(container) {
        var mapId = container.id;
        if (!mapId) {
            console.error('Conteneur de carte sans ID trouvé');
            return;
        }

        // Initialiser la carte pour ce conteneur
        // Désactivation du contrôle de zoom par défaut afin de pouvoir le replacer en bas à droite
        var currentMap = L.map(mapId, { zoomControl: false }).setView([centerLat, centerLng], zoom);
        
        // Stocker la référence de la carte dans un objet global
        window.maps = window.maps || {};
        window.maps[mapId] = currentMap;
        
        console.log("Carte créée avec ID:", mapId);
        
        // Corriger le problème de carte grise en forçant un invalidateSize après chargement
        setTimeout(function() {
            currentMap.invalidateSize(true);
        }, 300);

        // Fonction pour mettre à jour la taille de la carte
        function updateMapSize(mapId) {
            var map = window.maps[mapId];
            if (map && typeof map.invalidateSize === 'function') {
                setTimeout(function() {
                    map.invalidateSize(true);
                }, 300);
            }
        }

        // Fonction pour ouvrir la sidebar
        function openSidebar(tabName, mapId) {
            var sidebar = document.querySelector('.map-sidebar[data-map-id="' + mapId + '"]');
            var mapContainer = document.querySelector('.map-container-wrapper[data-map-id="' + mapId + '"]');
            
            if (!sidebar || !mapContainer) {
                console.error('Éléments de sidebar non trouvés pour mapId:', mapId);
                return;
            }

            mapContainer.classList.add('sidebar-open');
            sidebar.classList.add('sidebar-visible');
            
            // Activer l'onglet spécifié, ou 'filters' par défaut
            var targetTab = tabName || 'filters';
            var tabBtn = document.querySelector('.tab-btn[data-tab="' + targetTab + '"][data-map-id="' + mapId + '"]');
            if (tabBtn) {
                tabBtn.click();
            }
            
            var expandMapBtn = document.getElementById('expand-map-' + mapId);
            if (expandMapBtn) {
                expandMapBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                Réduire
                `;
            }
            
            updateMapSize(mapId);
            updateSidebarToggle();
        }

        L.tileLayer(tileLayerUrl, {
            attribution: attribution,
            maxZoom: 19
        }).addTo(currentMap);

        // Ajouter les contrôles de zoom en bas à droite (plus ergonomique)
        L.control.zoom({ position: 'bottomright' }).addTo(currentMap);

        // Ajouter le geocoder pour la recherche d'adresses
        var geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            // Déplacement du champ de recherche en bas à droite pour garder le coin supérieur gauche libre
            position: 'bottomright',
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
            currentMap.fitBounds(poly.getBounds());
        }).addTo(currentMap);

        // Initialiser la carte avec toutes les données
        filterAndRenderData({}, currentMap, mapId);

        // Gestion des boutons de contrôle pour cette instance
        var toggleFiltersBtn = document.getElementById('toggle-filters-' + mapId);
        var toggleResultsBtn = document.getElementById('toggle-results-' + mapId);
        var expandMapBtn = document.getElementById('expand-map-' + mapId);
        var sidebar = document.querySelector('.map-sidebar[data-map-id="' + mapId + '"]');
        var mapContainer = document.querySelector('.map-container-wrapper[data-map-id="' + mapId + '"]');

        if (toggleFiltersBtn) {
            toggleFiltersBtn.addEventListener('click', function() {
                openSidebar('filters', mapId);
                // Forcer un redimensionnement de la carte après que la sidebar soit ouverte
                updateMapSize(mapId);
                updateSidebarToggle();
            });
        }

        if (toggleResultsBtn) {
            toggleResultsBtn.addEventListener('click', function() {
                openSidebar('results', mapId);
                // Forcer un redimensionnement de la carte après que la sidebar soit ouverte
                updateMapSize(mapId);
                updateSidebarToggle();
            });
        }

        if (expandMapBtn) {
            expandMapBtn.addEventListener('click', function() {
                if (this.textContent.trim() === 'Réduire') {
                    closeSidebar(mapId);
                } else {
                    openSidebar(null, mapId);
                }
                // Forcer un redimensionnement de la carte après changement d'état
                updateMapSize(mapId);
                updateSidebarToggle();
            });
        }

        /* ------------------------------------------------------------------
         *  Bouton flèche pour ouvrir / fermer la sidebar
         * ----------------------------------------------------------------*/
        var sidebarToggleBtn = document.createElement('button');
        sidebarToggleBtn.id = 'sidebar-toggle-' + mapId;
        sidebarToggleBtn.className = 'sidebar-toggle-btn';
        sidebarToggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        mapContainer.appendChild(sidebarToggleBtn);

        // Met à jour l'orientation de la flèche selon l'état de la sidebar
        function updateSidebarToggle() {
            if (mapContainer.classList.contains('sidebar-open')) {
                sidebarToggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            } else {
                sidebarToggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            }
        }

        // État initial
        updateSidebarToggle();

        // Gestion du clic sur la flèche
        sidebarToggleBtn.addEventListener('click', function() {
            if (mapContainer.classList.contains('sidebar-open')) {
                closeSidebar(mapId);
            } else {
                openSidebar(null, mapId);
            }
            // Mettre à jour l'icône après le basculement
            updateSidebarToggle();
        });
    });

    // Fonction pour fermer la sidebar
    function closeSidebar(mapId) {
        var sidebar = document.querySelector('.map-sidebar[data-map-id="' + mapId + '"]');
        var mapContainer = document.querySelector('.map-container-wrapper[data-map-id="' + mapId + '"]');
        
        if (!sidebar || !mapContainer) {
            console.error('Éléments de sidebar non trouvés pour mapId:', mapId);
            return;
        }

        mapContainer.classList.remove('sidebar-open');
        sidebar.classList.remove('sidebar-visible');
        
        var expandMapBtn = document.getElementById('expand-map-' + mapId);
        if (expandMapBtn) {
            expandMapBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
            Plein écran
            `;
        }
        
        updateMapSize(mapId);
        updateSidebarToggle();
    }

    // 6) Générer le contenu de la popup pour un panneau
    function createPopupContent(panel) {
        var content = '<div class="panel-popup" style="' +
            'font-family:' + (options.popup_font_family || 'Arial,sans-serif') + ';' +
            ' font-size:' + (options.popup_font_size || '14px') + ';' +
            ' color:' + (options.popup_font_color || '#333') + ';' +
            ' width: 320px;">';
        
        // Image en haut de la popup, avant le titre
        if (panel.image) {
            content += '<div class="panel-image" style="text-align: center; margin-bottom: 15px; position: relative;">' +
                       '<img src="' + panel.image + '" alt="' + panel.title + '" style="max-width: 100%; max-height: 180px; border-radius: 4px; object-fit: cover;">' +
                       '<div class="image-zoom-icon" onclick="openImageLightbox(\'' + panel.image + '\')" title="Agrandir l\'image">' +
                       '<i class="fas fa-search-plus"></i>' +
                       '</div>' +
                       '</div>';
        } else if (panel.photo_url) {
            // Utiliser la propriété photo_url si disponible comme alternative
            content += '<div class="panel-image" style="text-align: center; margin-bottom: 15px; position: relative;">' +
                       '<img src="' + panel.photo_url + '" alt="' + panel.title + '" style="max-width: 100%; max-height: 180px; border-radius: 4px; object-fit: cover;">' +
                       '<div class="image-zoom-icon" onclick="openImageLightbox(\'' + panel.photo_url + '\')" title="Agrandir l\'image">' +
                       '<i class="fas fa-search-plus"></i>' +
                       '</div>' +
                       '</div>';
        } else {
            // Placeholder pour l'image si aucune image n'est disponible
            content += '<div class="panel-image" style="text-align: center; margin-bottom: 15px; background-color: #f5f5f5; border-radius: 4px; padding: 30px; height: 100px; display: flex; align-items: center; justify-content: center;">' +
                       '<span style="color: #999;">Aucune image disponible</span>' +
                       '</div>';
        }
        
        // Titre après l'image, sans référence
        content += '<h3 style="margin: 0 0 10px; color: #70c141;">' + panel.title + '</h3>';
        
        // Système d'onglets avec onclick directement sur les boutons
        content += '<div class="panel-tabs" style="margin-bottom: 15px;">';
        content += '<div class="tab-headers" style="display: flex; border-bottom: 1px solid #ddd; margin-bottom: 10px;">';
        
        // Boutons des onglets avec onclick - seulement 2 onglets maintenant
        content += '<div class="tab-btn active" onclick="switchTab(this, \'tech-content\')" ' +
                  'style="flex: 1; text-align: center; padding: 8px; cursor: pointer; font-weight: bold; border-bottom: 3px solid #70c141; color: #70c141;">Technique</div>';
        
        content += '<div class="tab-btn" onclick="switchTab(this, \'location-content\')" ' +
                  'style="flex: 1; text-align: center; padding: 8px; cursor: pointer; font-weight: normal; border-bottom: 3px solid transparent;">Localisation</div>';
        
        content += '</div>'; // Fin tab-headers
        
        // Contenu des onglets
        content += '<div class="tab-contents">';
        
        // Onglet 1: Infos techniques
        content += '<div class="tab-content active" id="tech-content" style="display: block;">';
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
        
        // Référence (déplacée dans l'onglet technique)
        if (panel.reference) {
            content += '<p style="margin: 3px 0;"><strong>Référence:</strong> ' + panel.reference + '</p>';
        }
        
        content += '</div>'; // Fin tech-content
        
        // Onglet 2: Localisation (fusion de localisation et visibilité)
        content += '<div class="tab-content" id="location-content" style="display: none;">';
        content += '<h4 style="margin: 0 0 8px; color: #70c141; font-size: 15px;">Localisation et visibilité</h4>';
        
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
        
        content += '</div>'; // Fin location-content
        
        content += '</div>'; // Fin tab-contents
        content += '</div>'; // Fin panel-tabs
        
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
            'Contacter / Réserver</a>';

        // Bouton d'édition pour les administrateurs
        if (window.isAdminUser) {
            content += '<a href="/wp-admin/post.php?post=' + panel.id + '&action=edit" ' +
                       'class="panel-edit-btn" target="_blank" style="' +
                       'background-color: #2271b1; color: white; padding: 8px 15px; text-decoration: none; ' +
                       'border-radius: 4px; display: inline-block; margin-left: 10px; font-weight: bold;">' +
                       '<i class="fas fa-edit"></i> Éditer</a>';
        }
        
        content += '</div>';
        
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

    // 8) Afficher une page spécifique de résultats
    function displayResultsPage(page, mapId) {
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
        renderResultsList(pageResults, mapId);

        // Mettre à jour les contrôles de pagination
        updatePagination(allResults, mapId);
    }

    // 9) Afficher la liste des résultats dans le panneau latéral
    function renderResultsList(pageResults, mapId) {
        var resultsListEl = document.getElementById('results-list-' + mapId);
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
                    <div class="image-zoom-icon" onclick="openImageLightbox('${result.image}')" title="Agrandir l'image">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>`;
            } else if (result.photo_url) {
                html += `<div class="result-image">
                    <img src="${result.photo_url}" alt="${result.title}" />
                    <div class="image-zoom-icon" onclick="openImageLightbox('${result.photo_url}')" title="Agrandir l'image">
                        <i class="fas fa-search-plus"></i>
                    </div>
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
                <button class="locate-on-map" data-id="${result.id}" data-map-id="${mapId}">
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
                var mapId = this.getAttribute('data-map-id');
                
                if (!mapId) {
                    console.error("ID de carte non défini pour la localisation");
                    return;
                }
                
                var currentMap = window.maps[mapId];
                if (!currentMap) {
                    console.error("Carte non trouvée pour l'ID:", mapId);
                    return;
                }

                // Trouver l'élément correspondant dans les données
                var feature = panneauxData.find(f => f.properties.id === id);
                if (feature && geoJSONLayer) {
                    // Trouver la couche correspondante dans la couche GeoJSON
                    geoJSONLayer.eachLayer(function(layer) {
                        if (layer.feature && layer.feature.properties.id === id) {
                            // Centrer la carte sur cette couche
                            if (layer.getLatLng) {
                                currentMap.setView(layer.getLatLng(), 15);
                            }

                            // Simuler un clic sur la couche pour ouvrir la popup
                            layer.fire('click');
                        }
                    });
                }
            });
        });
    }

    // 10) Mettre à jour les contrôles de pagination
    function updatePagination(totalResults, mapId) {
        var totalPages = Math.max(1, Math.ceil(totalResults.length / resultsPerPage));

        // Mettre à jour les compteurs
        document.getElementById('current-page-' + mapId).textContent = currentPage;
        document.getElementById('total-pages-' + mapId).textContent = totalPages;

        // Activer/désactiver les boutons de pagination
        var prevButton = document.getElementById('prev-page-' + mapId);
        var nextButton = document.getElementById('next-page-' + mapId);

        if (prevButton) {
            prevButton.disabled = currentPage <= 1;
        }

        if (nextButton) {
            nextButton.disabled = currentPage >= totalPages;
        }
    }

    // 11) Initialiser Select2 pour les filtres
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        // Utiliser le mapId correct pour les sélecteurs
        jQuery('#category-filter-' + mapId).select2({
            placeholder: "Sélectionnez une ou plusieurs catégories",
            allowClear: true,
            width: '100%',
            closeOnSelect: false
        });

        jQuery('#city-filter-' + mapId).select2({
            placeholder: "Sélectionnez une ou plusieurs villes",
            allowClear: true,
            width: '100%',
            closeOnSelect: false
        });

        jQuery('#department-filter-' + mapId + ', #region-filter-' + mapId + ', #type-filter-' + mapId + ', #support-filter-' + mapId + ', #format-filter-' + mapId + ', #status-filter-' + mapId + ', #radius-filter-' + mapId).select2({
            width: '100%',
            placeholder: "Sélectionner...",
            allowClear: true
        });

        // Nouveaux sélecteurs pour le filtre de rayon
        jQuery('#center-city-filter-' + mapId).select2({
            width: '100%',
            placeholder: "Sélectionner une ville centrale",
            allowClear: true
        }).on('change', function() {
            // Réagir au changement de ville centrale
            var centerCityValue = jQuery(this).val();
            var mapId = this.id.replace('center-city-filter-', '');
            var currentMap = window.maps[mapId];
            
            // Supprimer les marqueurs de ville et cercles de rayon précédents
            if (window.cityMarker) {
                currentMap.removeLayer(window.cityMarker);
                window.cityMarker = null;
            }
            
            if (window.cityRadiusCircle) {
                currentMap.removeLayer(window.cityRadiusCircle);
                window.cityRadiusCircle = null;
            }
            
            if (!centerCityValue) return;
            
            // Parser les informations de la ville
            var centerCity = parseCenterCity(centerCityValue);
            if (!centerCity) return;
            
            console.log("Ville sélectionnée:", centerCity);
            
            // Centrer la carte sur la ville sélectionnée
            currentMap.setView([centerCity.latitude, centerCity.longitude], 13);
            
            // Ajouter un marqueur pour la ville sélectionnée
            window.cityMarker = L.marker([centerCity.latitude, centerCity.longitude], {
                icon: L.icon({
                    iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/city-building-svgrepo-com.svg',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -30]
                })
            }).addTo(currentMap);
            
            // Vérifier si un rayon est déjà sélectionné et l'afficher
            var radiusKm = jQuery('#radius-km-filter-' + mapId).val();
            if (radiusKm) {
                displayCityRadius(centerCity, radiusKm, currentMap);
            }
        });

        jQuery('#radius-km-filter-' + mapId).select2({
            width: '100%',
            placeholder: "Sélectionner un rayon",
            allowClear: true
        }).on('change', function() {
            var radiusKm = jQuery(this).val();
            var mapId = this.id.replace('radius-km-filter-', '');
            var currentMap = window.maps[mapId];
            
            // Supprimer le cercle existant s'il y en a un
            if (window.cityRadiusCircle) {
                currentMap.removeLayer(window.cityRadiusCircle);
                window.cityRadiusCircle = null;
            }
            
            // Vérifier si une ville est sélectionnée
            var centerCityValue = jQuery('#center-city-filter-' + mapId).val();
            if (!centerCityValue || !radiusKm) return;
            
            var centerCity = parseCenterCity(centerCityValue);
            if (!centerCity) return;
            
            // Afficher le cercle de rayon
            displayCityRadius(centerCity, radiusKm, currentMap);
        });
        
        // Initialiser Select2 pour tous les autres sélecteurs avec la classe select2-filter
        jQuery('.select2-filter').not('#category-filter-' + mapId + ', #city-filter-' + mapId + ', #department-filter-' + mapId + ', #region-filter-' + mapId + ', #type-filter-' + mapId + ', #support-filter-' + mapId + ', #format-filter-' + mapId + ', #status-filter-' + mapId + ', #radius-filter-' + mapId + ', #center-city-filter-' + mapId + ', #radius-km-filter-' + mapId).select2({
            width: '100%',
            placeholder: function() {
                return jQuery(this).attr('data-placeholder') || '';
            },
            allowClear: true
        });
        
        // Initialiser Select2 pour le rayon de géolocalisation
        jQuery('#geo-radius-filter-' + mapId).select2({
            width: '100%',
            minimumResultsForSearch: Infinity, // Désactiver la recherche pour ce sélecteur
            dropdownCssClass: 'geo-radius-dropdown'
        });
    }
    
    // Fonction pour afficher un cercle de rayon autour d'une ville
    function displayCityRadius(centerCity, radiusKm, map) {
        radiusKm = parseFloat(radiusKm);
        if (isNaN(radiusKm) || radiusKm <= 0) return;
        
        // Créer un cercle pour visualiser le rayon
        window.cityRadiusCircle = L.circle([centerCity.latitude, centerCity.longitude], {
            radius: radiusKm * 1000, // Convertir en mètres
            color: '#70c141',
            fillColor: '#70c141',
            fillOpacity: 0.1,
            weight: 2
        }).addTo(map);
        
        // Ajuster la vue pour voir tout le cercle
        map.fitBounds(window.cityRadiusCircle.getBounds());
    }

    // 12) Gestionnaires d'événements pour les filtres
    // Fonction pour appliquer les filtres
    document.querySelectorAll('[id^="apply-filter-"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Extraire l'ID de la carte à partir de l'ID du bouton
            var buttonId = this.id;
            var mapId = buttonId.replace('apply-filter-', '');
            
            var currentMap = window.maps[mapId];
            if (!currentMap) {
                console.error("Carte non trouvée pour l'ID:", mapId);
                return;
            }
            
            console.log("Application des filtres pour la carte:", mapId);
            
            // Récupérer les valeurs de filtres
            var selectedCategories = [];
            if (typeof jQuery !== 'undefined' && jQuery('#category-filter-' + mapId).length) {
                selectedCategories = jQuery('#category-filter-' + mapId).val() || [];
            }
            
            var selectedCities = [];
            if (typeof jQuery !== 'undefined' && jQuery('#city-filter-' + mapId).length) {
                selectedCities = jQuery('#city-filter-' + mapId).val() || [];
            }
            
            // Récupérer la ville centrale et le rayon
            var centerCityValue = getElementValue('center-city-filter-' + mapId);
            var centerCity = parseCenterCity(centerCityValue);
            var radiusKm = getElementValue('radius-km-filter-' + mapId);
            
            // Si une ville est sélectionnée et un rayon spécifié, afficher le cercle de rayon
            if (centerCity && radiusKm) {
                // Si un cercle existe déjà, le supprimer
                if (window.cityRadiusCircle) {
                    currentMap.removeLayer(window.cityRadiusCircle);
                    window.cityRadiusCircle = null;
                }
                
                // Afficher le cercle de rayon
                displayCityRadius(centerCity, radiusKm, currentMap);
            }
            
            var filters = {
                search: getElementValue('search-filter-' + mapId),
                categories: selectedCategories,
                cities: selectedCities,
                department: getElementValue('department-filter-' + mapId),
                region: getElementValue('region-filter-' + mapId),
                type: getElementValue('type-filter-' + mapId),
                support: getElementValue('support-filter-' + mapId),
                format: getElementValue('format-filter-' + mapId),
                status: getElementValue('status-filter-' + mapId),
                minWidth: getElementNumericValue('min-width-' + mapId),
                maxWidth: getElementNumericValue('max-width-' + mapId),
                minHeight: getElementNumericValue('min-height-' + mapId),
                maxHeight: getElementNumericValue('max-height-' + mapId),
                centerCity: centerCity,
                radiusKm: radiusKm
            };

            console.log("Application des filtres:", filters);
            // Appliquer les filtres
            filterAndRenderData(filters, currentMap, mapId);
        });
    });

    // Fonction pour réinitialiser les filtres
    document.querySelectorAll('[id^="reset-filter-"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Extraire l'ID de la carte à partir de l'ID du bouton
            var buttonId = this.id;
            var mapId = buttonId.replace('reset-filter-', '');
            
            var currentMap = window.maps[mapId];
            if (!currentMap) {
                console.error("Carte non trouvée pour l'ID:", mapId);
                return;
            }
            
            // Réinitialiser les valeurs de filtres de manière sécurisée
            var searchFilter = document.getElementById('search-filter-' + mapId);
            if (searchFilter) searchFilter.value = '';
            
            var minWidth = document.getElementById('min-width-' + mapId);
            if (minWidth) minWidth.value = '';
            
            var maxWidth = document.getElementById('max-width-' + mapId);
            if (maxWidth) maxWidth.value = '';
            
            var minHeight = document.getElementById('min-height-' + mapId);
            if (minHeight) minHeight.value = '';
            
            var maxHeight = document.getElementById('max-height-' + mapId);
            if (maxHeight) maxHeight.value = '';
            
            var centerCityFilter = document.getElementById('center-city-filter-' + mapId);
            if (centerCityFilter) centerCityFilter.value = '';
            
            var radiusKmFilter = document.getElementById('radius-km-filter-' + mapId);
            if (radiusKmFilter) radiusKmFilter.value = '';
            
            // Réinitialiser les contrôles de géolocalisation
            var geoRadiusContainer = document.getElementById('geo-radius-container-' + mapId);
            if (geoRadiusContainer) geoRadiusContainer.style.display = 'none';
            
            var locateMeBtn = document.getElementById('locate-me-btn-' + mapId);
            if (locateMeBtn) {
                locateMeBtn.innerHTML = '<i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> Me localiser';
                locateMeBtn.style.backgroundColor = '#70c141';
                locateMeBtn.disabled = false;
            }
            
            var geoStatus = document.getElementById('geo-status-' + mapId);
            if (geoStatus) {
                geoStatus.textContent = '';
            }
            
            // Supprimer le cercle et le marqueur de géolocalisation
            if (radiusCircle) {
                currentMap.removeLayer(radiusCircle);
                radiusCircle = null;
            }
            
            if (window.userMarker) {
                currentMap.removeLayer(window.userMarker);
                window.userMarker = null;
            }
            
            // Supprimer le cercle et le marqueur de ville
            if (window.cityRadiusCircle) {
                currentMap.removeLayer(window.cityRadiusCircle);
                window.cityRadiusCircle = null;
            }
            
            if (window.cityMarker) {
                currentMap.removeLayer(window.cityMarker);
                window.cityMarker = null;
            }
            
            // Supprimer la notification du filtre géographique
            var geoFilterInfo = document.getElementById('geo-filter-info-' + mapId);
            if (geoFilterInfo) geoFilterInfo.remove();
            
            if (typeof jQuery !== 'undefined') {
                if (jQuery('#category-filter-' + mapId).length) jQuery('#category-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#city-filter-' + mapId).length) jQuery('#city-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#department-filter-' + mapId).length) jQuery('#department-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#region-filter-' + mapId).length) jQuery('#region-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#type-filter-' + mapId).length) jQuery('#type-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#support-filter-' + mapId).length) jQuery('#support-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#format-filter-' + mapId).length) jQuery('#format-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#status-filter-' + mapId).length) jQuery('#status-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#radius-filter-' + mapId).length) jQuery('#radius-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#center-city-filter-' + mapId).length) jQuery('#center-city-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#radius-km-filter-' + mapId).length) jQuery('#radius-km-filter-' + mapId).val(null).trigger('change');
                if (jQuery('#geo-radius-filter-' + mapId).length) jQuery('#geo-radius-filter-' + mapId).val('5').trigger('change');
            } else {
                var cityFilter = document.getElementById('city-filter-' + mapId);
                if (cityFilter) cityFilter.value = '';
                
                var departmentFilter = document.getElementById('department-filter-' + mapId);
                if (departmentFilter) departmentFilter.value = '';
                
                var regionFilter = document.getElementById('region-filter-' + mapId);
                if (regionFilter) regionFilter.value = '';
                
                var typeFilter = document.getElementById('type-filter-' + mapId);
                if (typeFilter) typeFilter.value = '';
                
                var supportFilter = document.getElementById('support-filter-' + mapId);
                if (supportFilter) supportFilter.value = '';
                
                var formatFilter = document.getElementById('format-filter-' + mapId);
                if (formatFilter) formatFilter.value = '';
                
                var statusFilter = document.getElementById('status-filter-' + mapId);
                if (statusFilter) statusFilter.value = '';
                
                var radiusFilter = document.getElementById('radius-filter-' + mapId);
                if (radiusFilter) radiusFilter.value = '';
            }

            // Afficher toutes les données
            filterAndRenderData({}, currentMap, mapId);
        });
    });

    // 13) Gestion des boutons de pagination
    document.querySelectorAll('[id^="prev-page-"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Extraire l'ID de la carte à partir de l'ID du bouton
            var buttonId = this.id;
            var mapId = buttonId.replace('prev-page-', '');
            
            if (currentPage > 1) {
                displayResultsPage(currentPage - 1, mapId);
            }
        });
    });

    document.querySelectorAll('[id^="next-page-"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Extraire l'ID de la carte à partir de l'ID du bouton
            var buttonId = this.id;
            var mapId = buttonId.replace('next-page-', '');
            
            var totalPages = Math.ceil(allResults.length / resultsPerPage);
            if (currentPage < totalPages) {
                displayResultsPage(currentPage + 1, mapId);
            }
        });
    });

    // 14) Gestion des onglets et contrôles de l'interface
    // Gestion des onglets
    document.querySelectorAll('.tab-btn-' + mapId).forEach(function(tab) {
        tab.addEventListener('click', function() {
            // Désactiver tous les onglets et panels
            document.querySelectorAll('.tab-btn-' + mapId).forEach(function(t) {
                t.classList.remove('active');
            });
            document.querySelectorAll('.sidebar-panel-' + mapId).forEach(function(p) {
                p.classList.remove('active');
            });

            // Activer l'onglet cliqué et le panneau correspondant
            this.classList.add('active');
            var tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-panel-' + mapId).classList.add('active');
        });
    });

    // Gestion de l'accordéon des filtres
    document.querySelectorAll('.accordion-header-' + mapId).forEach(function(header) {
        header.addEventListener('click', function() {
            // Toggle de la classe active pour l'élément parent
            var accordionItem = this.parentNode;
            accordionItem.classList.toggle('active');

            // Changer l'icône
            var icon = this.querySelector('.accordion-icon-' + mapId);
            if (accordionItem.classList.contains('active')) {
                icon.textContent = '-';
            } else {
                icon.textContent = '+';
            }
        });
    });

    // Boutons de contrôle
    var toggleFiltersBtn = document.getElementById('toggle-filters-' + mapId);
    var toggleResultsBtn = document.getElementById('toggle-results-' + mapId);
    var expandMapBtn = document.getElementById('expand-map-' + mapId);
    var sidebar = document.querySelector('.map-sidebar[data-map-id="' + mapId + '"]');
    var mapContainer = document.querySelector('.map-container-wrapper[data-map-id="' + mapId + '"]');

    if (toggleFiltersBtn) {
        toggleFiltersBtn.addEventListener('click', function() {
            // Activer l'onglet filtres
            document.querySelector('.tab-btn-' + mapId + '[data-tab="filters"]').click();

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
            document.querySelector('.tab-btn-' + mapId + '[data-tab="results"]').click();

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
                currentMap.invalidateSize();
            }, 300);
        });
    }

    // Fermeture des panneaux
    document.querySelectorAll('.panel-close-btn-' + mapId).forEach(function(btn) {
        btn.addEventListener('click', function() {
            sidebar.classList.remove('sidebar-visible');
            mapContainer.classList.remove('sidebar-open');

            // Redimensionner la carte après changement d'affichage
            setTimeout(function() {
                currentMap.invalidateSize();
            }, 300);
        });
    });

    // 15) Initialiser la carte avec toutes les données
    // Vérifier si mapId est défini avant d'appeler filterAndRenderData
    if (mapId && window.maps && window.maps[mapId]) {
        filterAndRenderData({}, window.maps[mapId], mapId);
        
        // Forcer un redimensionnement après le chargement initial des données
        setTimeout(function() {
            if (window.maps[mapId]) {
                window.maps[mapId].invalidateSize(true);
            }
        }, 500);
    } else {
        console.error("Impossible d'initialiser les données - mapId ou window.maps non définis", {mapId, maps: window.maps});
    }

    // Variable pour stocker la position actuelle de l'utilisateur
    var userLocation = null;
    // Variable pour stocker le cercle de rayon
    var radiusCircle = null;

    // Gestionnaire d'événement pour le bouton "Me localiser"
    document.querySelectorAll('[id^="locate-me-btn-"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Extraire l'ID de la carte à partir de l'ID du bouton
            var buttonId = this.id;
            var mapId = buttonId.replace('locate-me-btn-', '');
            
            var currentMap = window.maps[mapId];
            if (!currentMap) {
                console.error("Carte non trouvée pour l'ID:", mapId);
                return;
            }
            
            console.log("Géolocalisation activée pour la carte:", mapId);
            
            // Vérifier si la géolocalisation est disponible
            if (!navigator.geolocation) {
                alert("La géolocalisation n'est pas prise en charge par votre navigateur.");
                return;
            }

            // Mise à jour visuelle du bouton pendant la géolocalisation
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation en cours...';
            
            // Afficher un message de statut
            var geoStatus = document.getElementById('geo-status-' + mapId);
            if (geoStatus) {
                geoStatus.textContent = "Recherche de votre position...";
            }

            // Demander la géolocalisation
            navigator.geolocation.getCurrentPosition(
                // Succès
                function(position) {
                    userLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    
                    console.log("Position trouvée :", userLocation);

                    // Afficher le panneau de rayon
                    var geoRadiusContainer = document.getElementById('geo-radius-container-' + mapId);
                    if (geoRadiusContainer) {
                        geoRadiusContainer.style.display = 'block';
                    }
                    
                    // Mise à jour du bouton
                    var locateMeBtn = document.getElementById('locate-me-btn-' + mapId);
                    if (locateMeBtn) {
                        locateMeBtn.disabled = false;
                        locateMeBtn.innerHTML = '<i class="fas fa-check"></i> Position trouvée';
                        locateMeBtn.style.backgroundColor = '#28a745';
                    }
                    
                    // Mise à jour du message de statut
                    if (geoStatus) {
                        geoStatus.textContent = "Position trouvée ! Utilisez le rayon pour filtrer les panneaux autour de vous.";
                        geoStatus.style.color = '#28a745';
                    }
                    
                    // Centrer la carte sur la position trouvée
                    currentMap.setView([userLocation.latitude, userLocation.longitude], 13);
                    
                    // Ajouter un marqueur pour indiquer la position de l'utilisateur
                    if (window.userMarker) {
                        currentMap.removeLayer(window.userMarker);
                    }
                    window.userMarker = L.marker([userLocation.latitude, userLocation.longitude], {
                        icon: L.icon({
                            iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/location-pin-svgrepo-com.svg',
                            iconSize: [32, 32],
                            iconAnchor: [16, 32],
                            popupAnchor: [0, -30]
                        })
                    }).addTo(currentMap);
                },
                // Erreur
                function(error) {
                    console.error("Erreur de géolocalisation:", error);
                    var locateMeBtn = document.getElementById('locate-me-btn-' + mapId);
                    if (locateMeBtn) {
                        locateMeBtn.disabled = false;
                        locateMeBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Me localiser';
                    }
                    
                    // Message d'erreur spécifique pour HTTP vs HTTPS
                    var errorMsg = "";
                    if (window.location.protocol === 'http:' && !window.location.hostname.match(/localhost|127.0.0.1/)) {
                        errorMsg = "La géolocalisation nécessite une connexion sécurisée (HTTPS). Sur un serveur local, utilisez localhost ou 127.0.0.1.";
                    } else {
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg = "Vous avez refusé l'accès à votre position.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg = "Votre position n'a pas pu être déterminée.";
                                break;
                            case error.TIMEOUT:
                                errorMsg = "La demande de géolocalisation a expiré.";
                                break;
                            default:
                                errorMsg = "Une erreur inconnue s'est produite.";
                        }
                    }
                    
                    // Afficher le message d'erreur
                    if (geoStatus) {
                        geoStatus.textContent = errorMsg;
                        geoStatus.style.color = '#dc3545';
                    } else {
                        alert(errorMsg);
                    }
                },
                // Options
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    });

    // Gestionnaire d'événement pour le bouton "Filtrer" par géolocalisation
    document.querySelectorAll('[id^="apply-geo-filter-"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Extraire l'ID de la carte à partir de l'ID du bouton
            var buttonId = this.id;
            var mapId = buttonId.replace('apply-geo-filter-', '');
            
            var currentMap = window.maps[mapId];
            if (!currentMap) {
                console.error("Carte non trouvée pour l'ID:", mapId);
                return;
            }
            
            var geoRadiusSelect = document.getElementById('geo-radius-filter-' + mapId);
            var geoRadiusKm = geoRadiusSelect ? geoRadiusSelect.value : '';
            console.log("Rayon sélectionné:", geoRadiusKm);
            filterByUserLocation(geoRadiusKm, mapId, currentMap);
        });
    });

    // Fonction pour filtrer les panneaux autour de la position de l'utilisateur
    function filterByUserLocation(radiusKm, mapId, currentMap) {
        if (!userLocation) {
            alert("Veuillez d'abord activer la géolocalisation.");
            return;
        }
        
        // Convertir en nombre
        radiusKm = parseFloat(radiusKm);
        
        // Retirer le cercle existant s'il y en a un
        if (radiusCircle) {
            currentMap.removeLayer(radiusCircle);
            radiusCircle = null;
        }
        
        if (!radiusKm || isNaN(radiusKm)) {
            // Si pas de rayon spécifié, réinitialiser les filtres pour voir tous les panneaux
            filterAndRenderData({}, currentMap, mapId);
            // Supprimer la notification si elle existe
            var existingInfo = document.getElementById('geo-filter-info-' + mapId);
            if (existingInfo) existingInfo.remove();
            return;
        }
        
        console.log("Filtrage avec rayon de " + radiusKm + " km autour de la position : ", userLocation);
        
        // Création d'un objet de filtres avec la position de l'utilisateur
        var filters = {
            userLocation: userLocation,
            userLocationRadiusKm: radiusKm
        };
        
        // Ajouter un cercle visuel sur la carte pour montrer le rayon
        radiusCircle = L.circle([userLocation.latitude, userLocation.longitude], {
            radius: radiusKm * 1000, // en mètres
            color: '#70c141',
            fillColor: '#70c141',
            fillOpacity: 0.1,
            weight: 2
        }).addTo(currentMap);
        
        // Ajuster la vue de la carte pour voir tout le cercle
        currentMap.fitBounds(radiusCircle.getBounds());
        
        // Mettre à jour un élément UI pour montrer que le filtre est actif
        var filterInfoElement = document.getElementById('geo-filter-info-' + mapId);
        if (!filterInfoElement) {
            filterInfoElement = document.createElement('div');
            filterInfoElement.id = 'geo-filter-info-' + mapId;
            filterInfoElement.className = 'geo-filter-notification';
            filterInfoElement.style.cssText = 'position: absolute; z-index: 1000; top: 10px; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.9); padding: 5px 15px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);';
            
            // S'assurer que l'élément parent existe
            var mapContainer = document.querySelector('.map-container');
            if (mapContainer) {
                mapContainer.appendChild(filterInfoElement);
            } else {
                // Fallback si .map-container n'est pas trouvé
                document.querySelector('#' + mapId).parentNode.appendChild(filterInfoElement);
            }
        }
        
        filterInfoElement.innerHTML = 'Filtre actif : Panneaux dans un rayon de ' + radiusKm + ' km autour de ma position <button id="clear-geo-filter-' + mapId + '" style="margin-left: 10px; background: #f44336; color: white; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer;">Effacer</button>';
        
        // S'assurer que l'écouteur d'événement est correctement attaché
        setTimeout(function() {
            var clearButton = document.getElementById('clear-geo-filter-' + mapId);
            if (clearButton) {
                // Supprimer les écouteurs précédents pour éviter les doublons
                clearButton.replaceWith(clearButton.cloneNode(true));
                // Réattacher l'écouteur
                document.getElementById('clear-geo-filter-' + mapId).addEventListener('click', function() {
                    // Supprimer le cercle de rayon si présent
                    if (radiusCircle) {
                        currentMap.removeLayer(radiusCircle);
                        radiusCircle = null;
                    }
                    // Réinitialiser les filtres
                    filterAndRenderData({}, currentMap, mapId);
                    // Supprimer la notification
                    filterInfoElement.remove();
                    
                    // Réinitialiser le statut de géolocalisation
                    var geoStatus = document.getElementById('geo-status-' + mapId);
                    if (geoStatus) {
                        geoStatus.textContent = "Position trouvée ! Utilisez le rayon pour filtrer les panneaux autour de vous.";
                        geoStatus.style.color = '#28a745';
                    }
                });
            }
        }, 50);
        
        // Mettre à jour le message de statut
        var geoStatus = document.getElementById('geo-status-' + mapId);
        if (geoStatus) {
            geoStatus.textContent = "Filtrage actif : affichage des panneaux dans un rayon de " + radiusKm + " km.";
            geoStatus.style.color = '#70c141';
        }
        
        // Appliquer le filtre
        filterAndRenderData(filters, currentMap, mapId);
    }

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
    
    // Fonction pour parser les informations de la ville centrale
    function parseCenterCity(centerCityValue) {
        if (!centerCityValue) return null;
        
        var parts = centerCityValue.split('|');
        if (parts.length >= 3) {
            return {
                id: parts[0],
                latitude: parts[1],
                longitude: parts[2]
            };
        }
        return null;
    }

    // Fonction pour calculer la distance entre deux points (en km)
    function calculateDistance(lat1, lon1, lat2, lon2) {
        if (!lat1 || !lon1 || !lat2 || !lon2) {
            console.error("Coordonnées invalides pour le calcul de distance", {lat1, lon1, lat2, lon2});
            return Number.MAX_VALUE; // Retourner une grande distance en cas d'erreur
        }
        
        var R = 6371; // Rayon de la terre en km
        var dLat = deg2rad(lat2 - lat1);
        var dLon = deg2rad(lon2 - lon1);
        var a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
            Math.sin(dLon/2) * Math.sin(dLon/2); 
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
        var d = R * c; // Distance en km
        return d;
    }
    
    function deg2rad(deg) {
        return deg * (Math.PI/180);
    }

    // Ajouter un écouteur pour le redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        // Redimensionner toutes les cartes lors du redimensionnement de la fenêtre
        if (window.maps) {
            Object.keys(window.maps).forEach(function(mapId) {
                updateMapSize(mapId);
            });
        }
    });
});