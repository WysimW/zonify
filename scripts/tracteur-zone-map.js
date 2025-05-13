/**
 * Tracteur Zone - Script de gestion de la carte pour le shortcode
 * Ce script initialise et gère une carte Leaflet pour afficher les zones commerciales et points d'intérêt
 */

document.addEventListener('DOMContentLoaded', function() {
    // Récupérer l'ID unique de la carte passé par le shortcode
    var mapId = tracteurZoneMapId || '';
    if (!mapId) return; // Ne rien faire si l'ID n'est pas disponible
    
    // Récupérer les données passées par WordPress
    var options = terralizeFrontendOptions || {};
    var allZonesData = zonesData || [];
    
    console.log("Options de la carte:", options);
    console.log("Données des zones et POI:", allZonesData);

    // 1) Choix du provider de tuiles
    var provider = options.tile_provider || 'cartodb_light';
    var tileLayerUrl, attribution;

    if (provider === 'cartodb_dark') {
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    } else if (provider === 'osm') {
        tileLayerUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = '© OpenStreetMap contributors';
    } else if (provider === 'opentopo') {
        tileLayerUrl = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
        attribution = '© OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)';
    } else if (provider === 'esri_topo') {
        tileLayerUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}';
        attribution = 'Tiles © Esri — Source: Esri, USGS, NOAA';
    } else if (provider === 'custom') {
        tileLayerUrl = options.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = 'Personnalisé';
    } else {
        // Par défaut
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    }

    // 2) Initialisation de la carte
    var zoom = options.map_zoom || 9;
    var centerLat = parseFloat(options.map_center_lat || 50.5);
    var centerLng = parseFloat(options.map_center_lng || 2.5);

    var map = L.map(mapId).setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);
    
    // Correction du problème de carte grise en forçant un invalidateSize après chargement
    setTimeout(function() {
        map.invalidateSize(true);
    }, 300);

    // Ajout du contrôle de géolocalisation Leaflet
    L.control.locate({
        position: 'topleft',
        strings: {
            title: "Me localiser",
            popup: "Vous êtes ici",
            outsideMapBoundsMsg: "Vous semblez être en dehors des limites de la carte"
        },
        locateOptions: {
            maxZoom: 16,
            enableHighAccuracy: true
        },
        icon: 'fa fa-location-arrow'
    }).addTo(map);

    // Gestionnaire pour le bouton de localisation personnalisé dans la sidebar
    var locateMeBtn = document.getElementById('locateMe-' + mapId);
    if (locateMeBtn) {
        locateMeBtn.addEventListener('click', function() {
            map.locate({
                setView: true,
                maxZoom: 16,
                enableHighAccuracy: true
            });
            
            // Afficher un message lors de la localisation
            map.on('locationfound', function(e) {
                var radius = e.accuracy / 2;
                var userLatLng = e.latlng;
                
                // Créer un marqueur à la position de l'utilisateur
                var userMarker = L.marker(userLatLng, {
                    icon: L.icon({
                        iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/location-pin-svgrepo-com.svg',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -30],
                        className: 'user-location-marker'
                    })
                }).addTo(map)
                .bindPopup("Vous êtes ici (précision de " + Math.round(radius) + " mètres)").openPopup();
                
                // Afficher un cercle indiquant la précision
                var accuracyCircle = L.circle(userLatLng, {
                    radius: radius,
                    color: '#4285F4',
                    fillColor: '#4285F4',
                    fillOpacity: 0.15
                }).addTo(map);

                // Stocker la position de l'utilisateur pour une utilisation ultérieure
                window.userLatLng = userLatLng;
                
                // Supprimer les marqueurs après 30 secondes
                setTimeout(function() {
                    map.removeLayer(userMarker);
                    map.removeLayer(accuracyCircle);
                }, 30000);
            });
            
            // Gestion des erreurs de localisation
            map.on('locationerror', function(e) {
                alert("Impossible de vous localiser : " + e.message);
            });
        });
    }
    
    // Gestionnaire pour le bouton de recherche du POI le plus proche
    var findNearestPoiBtn = document.getElementById('findNearestPoi-' + mapId);
    if (findNearestPoiBtn) {
        findNearestPoiBtn.addEventListener('click', function() {
            // Vérifier si la position de l'utilisateur est déjà connue
            if (!window.userLatLng) {
                // Si non, demander la position
                map.locate({
                    setView: true,
                    maxZoom: 16,
                    enableHighAccuracy: true
                });
                
                map.once('locationfound', function(e) {
                    var userLatLng = e.latlng;
                    window.userLatLng = userLatLng;
                    findAndDisplayNearestPOI(userLatLng);
                });
                
                map.once('locationerror', function(e) {
                    alert("Impossible de vous localiser : " + e.message);
                });
            } else {
                // Si oui, utiliser cette position
                findAndDisplayNearestPOI(window.userLatLng);
            }
        });
    }
    
    // Fonction pour afficher le POI le plus proche
    function findAndDisplayNearestPOI(userLatLng) {
        // Supprimer le marqueur précédent du POI le plus proche s'il existe
        if (window.nearestMarker) {
            map.removeLayer(window.nearestMarker);
        }
        
        // Trouver le point d'intérêt le plus proche
        var nearestPOI = findNearestPOI(userLatLng);
        
        if (nearestPOI) {
            // Créer un marqueur pour le POI le plus proche
            window.nearestMarker = L.marker(nearestPOI.latlng, {
                icon: L.icon({
                    iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/poi.png',
                    iconSize: [30, 40],
                    iconAnchor: [15, 40],
                    popupAnchor: [0, -35],
                    className: 'nearest-poi-marker'
                })
            }).addTo(map)
            .bindPopup(`
                <div class="nearest-poi-popup">
                    <h3>${nearestPOI.properties.title}</h3>
                    <p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg> Distance: ${Math.round(nearestPOI.distance)} mètres</p>
                    <button class="get-directions-btn" data-lat="${userLatLng.lat}" data-lng="${userLatLng.lng}" data-poi-lat="${nearestPOI.latlng.lat}" data-poi-lng="${nearestPOI.latlng.lng}">
                        Calculer l'itinéraire
                    </button>
                </div>
            `).openPopup();

            // Adapter la vue pour montrer à la fois l'utilisateur et le POI
            var bounds = L.latLngBounds([userLatLng, nearestPOI.latlng]);
            map.fitBounds(bounds, { padding: [50, 50] });
            
            // Ajouter un gestionnaire pour le bouton d'itinéraire
            document.querySelector('.get-directions-btn').addEventListener('click', function() {
                var startLat = this.getAttribute('data-lat');
                var startLng = this.getAttribute('data-lng');
                var endLat = this.getAttribute('data-poi-lat');
                var endLng = this.getAttribute('data-poi-lng');
                
                // Ouvrir Google Maps avec l'itinéraire
                window.open(`https://www.google.com/maps/dir/?api=1&origin=${startLat},${startLng}&destination=${endLat},${endLng}&travelmode=driving`);
            });
            
            // Supprimer le marqueur après 30 secondes
            setTimeout(function() {
                if (window.nearestMarker) {
                    map.removeLayer(window.nearestMarker);
                    window.nearestMarker = null;
                }
            }, 30000);
        } else {
            alert("Aucun point d'intérêt n'a été trouvé à proximité.");
        }
    }

    // Fonction pour trouver le POI le plus proche
    function findNearestPOI(userLatLng) {
        var nearestPOI = null;
        var minDistance = Infinity;

        allZonesData.forEach(function(feature) {
            if (feature.properties.type === 'poi' && feature.geometry.type === 'Point') {
                var poiLatLng = L.latLng(
                    feature.geometry.coordinates[1],
                    feature.geometry.coordinates[0]
                );
                
                var distance = userLatLng.distanceTo(poiLatLng);
                
                if (distance < minDistance) {
                    minDistance = distance;
                    nearestPOI = {
                        ...feature,
                        latlng: poiLatLng,
                        distance: distance
                    };
                }
            }
        });

        return nearestPOI;
    }

    // Ajouter le geocoder sur la carte
    var geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        position: 'topleft',
        placeholder: 'Rechercher une adresse...'
    }).on('markgeocode', function(e) {
        var bbox = e.geocode.bbox;
        var poly = L.polygon([
            bbox.getSouthEast(),
            bbox.getNorthEast(),
            bbox.getNorthWest(),
            bbox.getSouthWest()
        ]);
        map.fitBounds(poly.getBounds());

        // Mise à jour des résultats après recherche
        updateResultsFromSearch(e.geocode);
    }).addTo(map);

    // 3) Styles par défaut
    var defaultStyle = {
        color: options.zone_border_color || '#3388ff',
        fillColor: options.zone_fill_color || '#3388ff',
        fillOpacity: parseFloat(options.zone_opacity || 0.5),
        weight: 2
    };

    var poiStyle = {
        radius: 8,
        fillColor: "#ff7800",
        color: "#000",
        weight: 1,
        opacity: 1,
        fillOpacity: 0.8
    };

    // 4) Variable pour stocker la couche GeoJSON
    var geoJSONLayer;

    // Variables globales pour la pagination
    var currentPage = 1;
    var resultsPerPage = 5; // Nombre de résultats par page
    var allResults = []; // Stocke tous les résultats pour la pagination

    // Fonctions de pagination
    function updatePagination(totalResults) {
        var totalPages = Math.max(1, Math.ceil(totalResults.length / resultsPerPage));

        // Mettre à jour les compteurs
        updateElementText('current-page-' + mapId, currentPage);
        updateElementText('total-pages-' + mapId, totalPages);

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

    // Fonction d'aide pour mettre à jour le texte d'un élément
    function updateElementText(id, text) {
        var element = document.getElementById(id);
        if (element) {
            element.textContent = text;
        }
    }

    // 5) Fonction pour filtrer et afficher les données
    function filterAndRenderData(selectedCategories = [], regionFilter = '', typeFilter = 'all', searchQuery = '') {
        // Supprimer la couche existante si elle existe
        if (geoJSONLayer) {
            map.removeLayer(geoJSONLayer);
        }

        // Clone des données pour ne pas modifier l'original
        var filteredData = JSON.parse(JSON.stringify(allZonesData));
        var displayedResults = [];

        // Préparation des filtres
        var selectedRegions = [];
        if (regionFilter && regionFilter !== '') {
            selectedRegions = [regionFilter]; // Convertir en tableau pour la compatibilité
        }

        // Normaliser la recherche (convertir en minuscules, supprimer les accents)
        var normalizedSearchQuery = '';
        if (searchQuery) {
            normalizedSearchQuery = searchQuery.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        // Appliquer les filtres
        if (selectedCategories.length > 0 || selectedRegions.length > 0 || typeFilter !== 'all' || normalizedSearchQuery) {
            filteredData = filteredData.filter(function(feature) {
                let matchesCategory = true;
                let matchesRegion = true;
                let matchesType = true;
                let matchesSearch = true;

                // Filtre par catégorie
                if (selectedCategories.length > 0) {
                    if (!feature.properties.categories || feature.properties.categories.length === 0) {
                        matchesCategory = false;
                    } else {
                        matchesCategory = feature.properties.categories.some(function(category) {
                            return selectedCategories.includes(category.slug);
                        });
                    }
                }

                // Filtre par région basé sur la taxonomie 'region'
                if (selectedRegions.length > 0) {
                    if (!feature.properties.regions || feature.properties.regions.length === 0) {
                        matchesRegion = false;
                    } else {
                        matchesRegion = feature.properties.regions.some(function(region) {
                            return selectedRegions.includes(region.slug);
                        });
                    }
                }

                // Filtre par type (zone ou poi)
                if (typeFilter !== 'all') {
                    matchesType = feature.properties.type === typeFilter;
                }

                // Filtre par recherche textuelle
                if (normalizedSearchQuery) {
                    // Chercher dans le titre et d'autres propriétés
                    var found = false;
                    var searchableFields = [
                        feature.properties.title || '',
                        feature.properties.nom_commercial || '',
                        feature.properties.infos || ''
                    ];

                    for (var i = 0; i < searchableFields.length; i++) {
                        var normalized = searchableFields[i].toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        if (normalized.includes(normalizedSearchQuery)) {
                            found = true;
                            break;
                        }
                    }

                    matchesSearch = found;
                }

                // L'élément doit correspondre à tous les filtres pour être affiché
                return matchesCategory && matchesRegion && matchesType && matchesSearch;
            });
        }

        console.log("Nombre d'éléments après filtrage:", filteredData.length);

        // Préparer les résultats pour l'affichage dans la liste
        if (filteredData.length > 0) {
            filteredData.forEach(function(feature) {
                if (feature.properties) {
                    // Format différent selon le type
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

        // Mettre à jour les compteurs
        updateElementText('results-count-' + mapId, '(' + displayedResults.length + ')');
        updateElementText('tab-results-count-' + mapId, displayedResults.length);
        updateElementText('results-counter-' + mapId, '(' + displayedResults.length + ')');

        // Mettre à jour la liste des résultats
        updateResultsList(displayedResults);

        // Créer et ajouter la nouvelle couche GeoJSON
        geoJSONLayer = L.geoJSON(filteredData, {
            style: function(feature) {
                // Si couleurs personnalisées pour cette zone
                if (feature.properties.type !== 'poi') {
                    if (feature.properties.border_color || feature.properties.fill_color) {
                        return {
                            color: feature.properties.border_color || defaultStyle.color,
                            fillColor: feature.properties.fill_color || defaultStyle.fillColor,
                            fillOpacity: defaultStyle.fillOpacity,
                            weight: defaultStyle.weight
                        };
                    }
                    return defaultStyle;
                }
            },
            pointToLayer: function(feature, latlng) {
                // Si c'est un POI (type point), on utilise une icône SVG personnalisée
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
                // Par défaut, on laisse Leaflet créer un marker standard
                return L.marker(latlng);
            },
            onEachFeature: function(feature, layer) {
                // Au clic sur la zone ou le POI
                layer.on('click', function() {
                    // Construction du contenu de la popup basé sur le type
                    var content = '<div class="popup-container" style="'
                                + 'font-family:' + (options.popup_font_family || 'Arial,sans-serif') + ';'
                                + ' font-size:' + (options.popup_font_size || '14px') + ';'
                                + ' color:' + (options.popup_font_color || '#333') + ';">';
                    
                    if (feature.properties.type === 'poi') {
                        // Popup pour les points d'intérêt (style nearest-poi-popup)
                        content = `
                        <div class="nearest-poi-popup">
                            <h3>${feature.properties.title || 'Point d\'intérêt'}</h3>`;
                            
                        // Afficher les catégories du POI
                        if (feature.properties.categories && feature.properties.categories.length > 0) {
                            content += '<p><strong>Catégories: </strong>';
                            content += feature.properties.categories.map(cat => cat.name).join(', ');
                            content += '</p>';
                        }
                        
                        // Afficher les régions du POI
                        if (feature.properties.regions && feature.properties.regions.length > 0) {
                            content += '<p><strong>Région: </strong>';
                            content += feature.properties.regions.map(reg => reg.name).join(', ');
                            content += '</p>';
                        }
                        
                        // Ajouter le bouton pour calculer l'itinéraire
                        // Déterminer si nous avons déjà la position de l'utilisateur
                        if (window.userLatLng) {
                            // Nous avons déjà la position de l'utilisateur
                            var poiLatlng = L.latLng(
                                feature.geometry.coordinates[1],
                                feature.geometry.coordinates[0]
                            );
                            var distance = window.userLatLng.distanceTo(poiLatlng);
                            
                            content += `<p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg> Distance: ${Math.round(distance)} mètres</p>`;
                            
                            content += `<button class="get-directions-btn" data-lat="${window.userLatLng.lat}" data-lng="${window.userLatLng.lng}" data-poi-lat="${poiLatlng.lat}" data-poi-lng="${poiLatlng.lng}">
                                Calculer l'itinéraire
                            </button>`;
                        } else {
                            // Nous n'avons pas encore la position de l'utilisateur
                            // Ajouter un bouton pour demander la localisation puis calculer l'itinéraire
                            var poiLatlng = L.latLng(
                                feature.geometry.coordinates[1],
                                feature.geometry.coordinates[0]
                            );
                            
                            content += `<button class="locate-and-route-btn" data-poi-lat="${poiLatlng.lat}" data-poi-lng="${poiLatlng.lng}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Me localiser et calculer l'itinéraire
                            </button>`;
                        }
                        
                        content += '</div>';
                    } else {
                        // Popup pour les zones
                        content += '<h2>' + (feature.properties.title || 'Zone') + '</h2>';
                        
                        // Afficher le nom commercial / commercial associé en sous-titre si disponible
                        if (feature.properties.nom_commercial) {
                            content += '<h3 style="margin-top: 5px; color: #555;">Commercial : ' + feature.properties.nom_commercial + '</h3>';
                        }
                        
                        // Info / présentation
                        if (feature.properties.infos) {
                            content += '<p>' + feature.properties.infos + '</p>';
                        }
                        
                        // Afficher les catégories de la zone
                        if (feature.properties.categories && feature.properties.categories.length > 0) {
                            content += '<div class="popup-tags">';
                            feature.properties.categories.forEach(function(cat) {
                                content += '<span class="popup-tag">' + cat.name + '</span>';
                            });
                            content += '</div>';
                        }
                        
                        // Afficher les régions de la zone
                        if (feature.properties.regions && feature.properties.regions.length > 0) {
                            content += '<div class="popup-tags">';
                            feature.properties.regions.forEach(function(reg) {
                                content += '<span class="popup-tag">' + reg.name + '</span>';
                            });
                            content += '</div>';
                        }
                        
                        // Informations de contact
                        content += '<div class="popup-contact-info">';
                        
                        // Adresse
                        if (feature.properties.address && parseInt(options.popup_show_address) === 1) {
                            content += '<p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>' + feature.properties.address + '</p>';
                        }
                        
                        // Horaires d'ouverture
                        if (feature.properties.opening_hours && parseInt(options.popup_show_hours) === 1) {
                            content += '<div class="popup-hours">';
                            content += '<p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>' + feature.properties.opening_hours + '</p>';
                            content += '</div>';
                        }
                        
                        // Liens sociaux
                        if (feature.properties.social_links && parseInt(options.popup_show_social) === 1) {
                            content += '<div class="popup-social">';
                            var links = feature.properties.social_links.split(',');
                            links.forEach(function(link) {
                                var trimmed = link.trim();
                                if (trimmed) {
                                    content += '<a href="' + trimmed + '" target="_blank">';
                                    content += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';
                                    content += trimmed + '</a>';
                                }
                            });
                            content += '</div>';
                        }
                        
                        // Email
                        if (feature.properties.email && parseInt(options.popup_enable_email_btn) === 1) {
                            content += '<p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>';
                            content += '<a href="mailto:' + feature.properties.email + '">' + feature.properties.email + '</a></p>';
                        }
                        
                        // Téléphone
                        if (feature.properties.telephone && parseInt(options.popup_enable_phone_btn) === 1) {
                            content += '<p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
                            content += '<a href="tel:' + feature.properties.telephone + '">' + feature.properties.telephone + '</a></p>';
                        }
                        
                        content += '</div>'; // Fin de popup-contact-info
                        
                        // Bouton "contacter"
                        if (parseInt(options.popup_enable_contact_btn) === 1) {
                            var commercialId = feature.properties.commercial_id || 0;
                            var contactUrl = options.contact_page_url || '/contact';
                            content += '<a href="' + contactUrl + '?commercial_id=' + commercialId + '" class="btn-contact">Contacter</a>';
                        }
                    }
                    
                    content += '</div>';
                    
                    // Ouvrir la popup centrée sur la bounding box du polygone (pour un polygon)
                    // ou sur la lat/lng si c'est un point
                    var popupLatLng;
                    if (layer.getBounds) {
                        popupLatLng = layer.getBounds().getCenter();
                    } else if (layer.getLatLng) {
                        popupLatLng = layer.getLatLng();
                    } else {
                        // fallback
                        popupLatLng = map.getCenter();
                    }
                    
                    L.popup()
                    .setLatLng(popupLatLng)
                    .setContent(content)
                    .openOn(map);
                    
                    // Ajouter le gestionnaire pour le bouton d'itinéraire standard
                    var getDirectionsBtn = document.querySelector('.get-directions-btn');
                    if (getDirectionsBtn) {
                        getDirectionsBtn.addEventListener('click', function() {
                            var startLat = this.getAttribute('data-lat');
                            var startLng = this.getAttribute('data-lng');
                            var endLat = this.getAttribute('data-poi-lat');
                            var endLng = this.getAttribute('data-poi-lng');
                            
                            // Ouvrir Google Maps avec l'itinéraire
                            window.open(`https://www.google.com/maps/dir/?api=1&origin=${startLat},${startLng}&destination=${endLat},${endLng}&travelmode=driving`);
                        });
                    }
                    
                    // Ajouter le gestionnaire pour le bouton combiné de localisation et itinéraire
                    var locateAndRouteBtn = document.querySelector('.locate-and-route-btn');
                    if (locateAndRouteBtn) {
                        locateAndRouteBtn.addEventListener('click', function() {
                            var poiLat = this.getAttribute('data-poi-lat');
                            var poiLng = this.getAttribute('data-poi-lng');
                            
                            // Localiser l'utilisateur
                            map.locate({
                                setView: true,
                                maxZoom: 16,
                                enableHighAccuracy: true
                            });
                            
                            // Lorsque la localisation est trouvée
                            map.once('locationfound', function(e) {
                                // Stocker la position de l'utilisateur
                                window.userLatLng = e.latlng;
                                
                                // Créer un marqueur temporaire pour l'utilisateur
                                var userMarker = L.marker(e.latlng, {
                                    icon: L.icon({
                                        iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/location-pin-svgrepo-com.svg',
                                        iconSize: [32, 32],
                                        iconAnchor: [16, 32],
                                        popupAnchor: [0, -30],
                                        className: 'user-location-marker'
                                    })
                                }).addTo(map);
                                
                                // Créer la popup avec le nouveau contenu incluant le bouton d'itinéraire
                                var poiLatLng = L.latLng(poiLat, poiLng);
                                var distance = window.userLatLng.distanceTo(poiLatLng);
                                
                                var updatedContent = document.querySelector('.nearest-poi-popup').innerHTML;
                                // Remplacer le bouton de localisation par le bouton d'itinéraire
                                updatedContent = updatedContent.replace(
                                    /<button class="locate-and-route-btn"[^>]*>.*?<\/button>/s,
                                    `<p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg> Distance: ${Math.round(distance)} mètres</p>
                                    <button class="get-directions-btn" data-lat="${window.userLatLng.lat}" data-lng="${window.userLatLng.lng}" data-poi-lat="${poiLat}" data-poi-lng="${poiLng}">
                                        Calculer l'itinéraire
                                    </button>`
                                );
                                
                                // Mettre à jour le contenu de la popup
                                var popupContent = '<div class="nearest-poi-popup">' + updatedContent + '</div>';
                                L.popup()
                                .setLatLng(poiLatLng)
                                .setContent(popupContent)
                                .openOn(map);
                                
                                // Ajouter le gestionnaire pour le nouveau bouton d'itinéraire
                                document.querySelector('.get-directions-btn').addEventListener('click', function() {
                                    var startLat = this.getAttribute('data-lat');
                                    var startLng = this.getAttribute('data-lng');
                                    var endLat = this.getAttribute('data-poi-lat');
                                    var endLng = this.getAttribute('data-poi-lng');
                                    
                                    // Ouvrir Google Maps avec l'itinéraire
                                    window.open(`https://www.google.com/maps/dir/?api=1&origin=${startLat},${startLng}&destination=${endLat},${endLng}&travelmode=driving`);
                                });
                                
                                // Adapter la vue pour montrer à la fois l'utilisateur et le POI
                                var bounds = L.latLngBounds([window.userLatLng, poiLatLng]);
                                map.fitBounds(bounds, { padding: [50, 50] });
                                
                                // Supprimer le marqueur après 30 secondes
                                setTimeout(function() {
                                    map.removeLayer(userMarker);
                                }, 30000);
                            });
                            
                            // Gérer l'erreur de localisation
                            map.once('locationerror', function(e) {
                                alert("Impossible de vous localiser : " + e.message);
                            });
                        });
                    }
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

    // Fonction pour mettre à jour la liste des résultats
    function updateResultsList(results) {
        // Stocker tous les résultats pour la pagination
        allResults = results;

        var resultsListEl = document.getElementById('results-list-' + mapId);
        if (!resultsListEl) return;

        if (results.length === 0) {
            resultsListEl.innerHTML = '<p class="no-results">Aucun résultat ne correspond à vos critères.</p>';
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

    // Fonction pour rendre uniquement les résultats de la page courante
    function renderResultsList(pageResults) {
        var resultsListEl = document.getElementById('results-list-' + mapId);
        if (!resultsListEl) return;

        var html = '';
        pageResults.forEach(function(result) {
            // Format différent selon le type (zone commerciale ou POI)
            if (result.type === 'zone') {
                // Format pour les zones commerciales
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
                // Format pour les points d'intérêt
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

        // Ajouter les écouteurs d'événements pour la localisation sur la carte
        document.querySelectorAll('.locate-on-map').forEach(function(button) {
            button.addEventListener('click', function() {
                var id = parseInt(this.getAttribute('data-id'));

                // Trouver l'élément correspondant dans les données
                var feature = allZonesData.find(f => f.properties.id === id);
                if (feature && geoJSONLayer) {
                    // Trouver la couche correspondante dans la couche GeoJSON
                    geoJSONLayer.eachLayer(function(layer) {
                        if (layer.feature && layer.feature.properties.id === id) {
                            // Centrer la carte sur cette couche
                            if (layer.getBounds) {
                                map.fitBounds(layer.getBounds());
                            } else if (layer.getLatLng) {
                                map.setView(layer.getLatLng(), 13);
                            }

                            // Simuler un clic sur la couche pour ouvrir la popup
                            layer.fire('click');
                        }
                    });
                }
            });
        });
    }

    // Fonction pour la mise à jour des résultats à partir d'une recherche geocoder
    function updateResultsFromSearch(result) {
        // Cette fonction serait à adapter pour récupérer les commerciaux proches du point recherché
        // Pour l'instant, on affiche tous les commerciaux dans les zones visibles
        var visibleResults = [];
        var bounds = map.getBounds();

        // Filtrer les zones visibles sur la carte
        allZonesData.forEach(function(feature) {
            if (feature.properties.type === 'zone') {
                // Vérifier si la zone est dans la vue actuelle (approximatif)
                var isVisible = false;
                try {
                    if (feature.geometry && feature.geometry.coordinates) {
                        // Pour les polygones
                        if (feature.geometry.type === 'Polygon') {
                            // Prendre le premier point comme approximation
                            var coord = feature.geometry.coordinates[0][0];
                            var latlng = L.latLng(coord[1], coord[0]);
                            isVisible = bounds.contains(latlng);
                        }
                        // Pour les points
                        else if (feature.geometry.type === 'Point') {
                            var coord = feature.geometry.coordinates;
                            var latlng = L.latLng(coord[1], coord[0]);
                            isVisible = bounds.contains(latlng);
                        }
                    }
                } catch (e) {
                    console.error("Erreur lors de la vérification de visibilité:", e);
                }

                if (isVisible) {
                    visibleResults.push({
                        id: feature.properties.id,
                        name: feature.properties.nom_commercial || feature.properties.title,
                        region: result.name || 'Région',
                        products: feature.properties.categories ? feature.properties.categories.map(c => c.name).join(', ') : '',
                        phone: feature.properties.telephone || '',
                        type: 'zone'
                    });
                }
            }
        });

        updateElementText('results-count-' + mapId, '(' + visibleResults.length + ')');
        updateElementText('tab-results-count-' + mapId, visibleResults.length);
        updateResultsList(visibleResults);
    }

    // Initialiser Select2 pour le filtre de catégories
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('#categoryFilter-' + mapId).select2({
            placeholder: "Sélectionnez une ou plusieurs catégories",
            allowClear: true,
            theme: 'classic',
            width: '100%',
            closeOnSelect: false,
            language: {
                noResults: function() {
                    return "Aucune catégorie trouvée";
                },
                searching: function() {
                    return "Recherche...";
                }
            }
        });
    }

    // Gestionnaires d'événements pour les boutons de pagination
    var prevPageBtn = document.getElementById('prev-page-' + mapId);
    var nextPageBtn = document.getElementById('next-page-' + mapId);

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

    // Gestionnaires d'événements pour le filtrage
    var applyFilterBtn = document.getElementById('applyFilter-' + mapId);
    var resetFilterBtn = document.getElementById('resetFilter-' + mapId);

    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            var selectedCategories = [];
            
            if (typeof jQuery !== 'undefined') {
                // Récupérer les catégories sélectionnées
                if (jQuery('#categoryFilter-' + mapId).length) {
                    selectedCategories = jQuery('#categoryFilter-' + mapId).val() || [];
                }
            }
            
            // Récupérer les autres valeurs de filtres
            var regionFilter = document.getElementById('region-filter-' + mapId) ? 
                               document.getElementById('region-filter-' + mapId).value : '';
            var typeFilter = document.getElementById('type-filter-' + mapId) ? 
                             document.getElementById('type-filter-' + mapId).value : 'all';
            var searchQuery = document.getElementById('search-filter-' + mapId) ? 
                              document.getElementById('search-filter-' + mapId).value : '';
            
            filterAndRenderData(selectedCategories, regionFilter, typeFilter, searchQuery);
        });
    }

    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            // Réinitialiser les filtres
            if (typeof jQuery !== 'undefined') {
                if (jQuery('#categoryFilter-' + mapId).length) {
                    jQuery('#categoryFilter-' + mapId).val(null).trigger('change');
                }
            }
            
            if (document.getElementById('region-filter-' + mapId)) {
                document.getElementById('region-filter-' + mapId).value = '';
            }
            
            if (document.getElementById('type-filter-' + mapId)) {
                document.getElementById('type-filter-' + mapId).value = 'all';
            }
            
            if (document.getElementById('search-filter-' + mapId)) {
                document.getElementById('search-filter-' + mapId).value = '';
            }
            
            // Réinitialiser la carte
            filterAndRenderData();
            
            // Reset la vue
            map.setView([centerLat, centerLng], zoom);
        });
    }

    // Gestionnaires pour l'interface
    // Gestion des onglets
    document.querySelectorAll('.tab-btn[data-map-id="' + mapId + '"]').forEach(function(tab) {
        tab.addEventListener('click', function() {
            // Récupérer l'identifiant de l'onglet et du panneau
            var tabId = this.getAttribute('data-tab');
            
            // Désactiver tous les onglets
            document.querySelectorAll('.tab-btn[data-map-id="' + mapId + '"]').forEach(function(t) {
                t.classList.remove('active');
            });
            
            // Désactiver tous les panneaux
            document.querySelectorAll('.sidebar-panel').forEach(function(panel) {
                panel.classList.remove('active');
            });
            
            // Activer l'onglet cliqué
            this.classList.add('active');
            
            // Activer le panneau correspondant
            var targetPanel = document.getElementById(tabId + '-panel-' + mapId);
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
            
            // Redimensionner la carte après changement d'onglet
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        });
    });

    // Fonction pour gérer correctement le redimensionnement de la carte
    function handleMapResize() {
        // Forcer la carte à se redimensionner après que les transitions CSS soient terminées
        setTimeout(function() {
            map.invalidateSize({
                animate: true,
                pan: true,
                debounceMoveend: true // paramètre supplémentaire pour améliorer la performance
            });
        }, 350); // Un peu plus que la durée de transition CSS (0.3s)
    }

    // Gestion de l'accordéon
    document.querySelectorAll('.accordion-header').forEach(function(header) {
        header.addEventListener('click', function() {
            // Toggle la classe active
            var item = this.parentNode;
            item.classList.toggle('active');
            
            // Changer l'icône
            var icon = this.querySelector('.accordion-icon');
            if (item.classList.contains('active')) {
                icon.textContent = '-';
            } else {
                icon.textContent = '+';
            }
        });
    });

    // Gestionnaires pour les contrôles de la carte
    var toggleFiltersBtn = document.getElementById('toggle-filters-' + mapId);
    var toggleResultsBtn = document.getElementById('toggle-results-' + mapId);
    var expandMapBtn = document.getElementById('expand-map-' + mapId);
    var sidebar = document.querySelector('.map-container-wrapper .map-sidebar');
    var mapMainContainer = document.querySelector('.map-main-container');

    if (toggleFiltersBtn && sidebar) {
        toggleFiltersBtn.addEventListener('click', function() {
            // Rendre le conteneur principal avec sidebar
            if (mapMainContainer) {
                mapMainContainer.classList.add('with-sidebar');
                mapMainContainer.classList.remove('without-sidebar');
            }
            
            // Afficher la sidebar si elle n'est pas visible
            sidebar.classList.add('sidebar-visible');
            
            // Activer l'onglet filtres
            document.querySelector('.tab-btn[data-tab="filters"][data-map-id="' + mapId + '"]').click();
            
            // Redimensionner la carte pour s'adapter
            handleMapResize();
        });
    }

    if (toggleResultsBtn && sidebar) {
        toggleResultsBtn.addEventListener('click', function() {
            // Rendre le conteneur principal avec sidebar
            if (mapMainContainer) {
                mapMainContainer.classList.add('with-sidebar');
                mapMainContainer.classList.remove('without-sidebar');
            }
            
            // Afficher la sidebar si elle n'est pas visible
            sidebar.classList.add('sidebar-visible');
            
            // Activer l'onglet résultats
            document.querySelector('.tab-btn[data-tab="results"][data-map-id="' + mapId + '"]').click();
            
            // Redimensionner la carte pour s'adapter
            handleMapResize();
        });
    }

    // Gestion des boutons de fermeture
    document.querySelectorAll('.panel-close-btn[data-map-id="' + mapId + '"]').forEach(function(closeBtn) {
        closeBtn.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.remove('sidebar-visible');
                
                // Définir le conteneur principal sans sidebar
                if (mapMainContainer) {
                    mapMainContainer.classList.add('without-sidebar');
                    mapMainContainer.classList.remove('with-sidebar');
                }
                
                // Redimensionner la carte après fermeture
                handleMapResize();
            }
        });
    });

    // Mode plein écran
    if (expandMapBtn) {
        expandMapBtn.addEventListener('click', function() {
            var container = document.querySelector('.tracteur-zone-map-container');
            if (container) {
                container.classList.toggle('fullscreen');
                
                // Mettre à jour le texte du bouton
                if (container.classList.contains('fullscreen')) {
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg> Normal';
                } else {
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg> Plein écran';
                }
                
                // Redimensionner la carte après le changement
                setTimeout(function() {
                    map.invalidateSize();
                }, 300);
            }
        });
    }

    // Afficher initialement toutes les données
    filterAndRenderData();
    
    // Forcer un redimensionnement après le chargement initial des données
    setTimeout(function() {
        map.invalidateSize(true);
    }, 500);
    
    // Ajouter un écouteur pour le redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        // Redimensionner la carte lors du redimensionnement de la fenêtre
        setTimeout(function() {
            map.invalidateSize(true);
        }, 200);
    });
}); 