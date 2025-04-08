document.addEventListener('DOMContentLoaded', function() {
    var options = zonifyFrontendOptions || {};
    console.log("Options : ", options);
    
    // Afficher les données pour le debug
    console.log("Données des zones et POI:", zonesData);

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
    }
     else if (provider === 'custom') {
        tileLayerUrl = zonifyMapVars.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = 'Personnalisé';
    }  
    else {
        // Par défaut
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    }

    // 2) Initialisation de la carte
    var zoom = options.map_zoom || 9;
    var centerLat = parseFloat(options.map_center_lat || 50.5);
    var centerLng = parseFloat(options.map_center_lng || 2.5);

    var map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);

    // 3) Mode de placement du geocoder
    if (options.geocoder_mode === 'on_map') {
        // Contrôle par défaut (en haut à gauche)
        L.Control.geocoder({
            defaultMarkGeocode: false
        })
        .on('markgeocode', function(e) {
            var bbox = e.geocode.bbox;
            var poly = L.polygon([
                bbox.getSouthEast(),
                bbox.getNorthEast(),
                bbox.getNorthWest(),
                bbox.getSouthWest()
            ]);
            map.fitBounds(poly.getBounds());
        })
        .addTo(map);

    } else if (options.geocoder_mode === 'on_map_custom_position') {
        // Contrôle dans la carte, position personnalisée
        var pos = options.geocoder_position || 'topleft';
        L.Control.geocoder({
            defaultMarkGeocode: false,
            position: pos
        })
        .on('markgeocode', function(e) {
            var bbox = e.geocode.bbox;
            var poly = L.polygon([
                bbox.getSouthEast(),
                bbox.getNorthEast(),
                bbox.getNorthWest(),
                bbox.getSouthWest()
            ]);
            map.fitBounds(poly.getBounds());
        })
        .addTo(map);

    } else if (options.geocoder_mode === 'outside_map') {
        // On affiche le conteneur en dehors de la carte
        var container = document.getElementById('outsideSearchContainer');
        if (container) {
            container.style.display = 'block'; // on l'affiche
        }

        // On crée un geocoder "brut" (Nominatim)
        var geocoder = L.Control.Geocoder.nominatim();

        var searchInput = document.getElementById('searchInput');
        var searchBtn   = document.getElementById('searchBtn');

        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                var query = searchInput.value.trim();
                if (!query) return;

                geocoder.geocode(query, function(results) {
                    if (!results || !results.length) {
                        alert("Aucun résultat pour : " + query);
                        return;
                    }
                    var r = results[0];
                    if (r.bbox) {
                        var bbox = r.bbox;
                        var southWest = L.latLng(bbox[0], bbox[1]);
                        var northEast = L.latLng(bbox[2], bbox[3]);
                        var bounds = L.latLngBounds(southWest, northEast);
                        map.fitBounds(bounds);
                    } else if (r.center) {
                        map.setView(r.center, 13);
                    }
                });
            });
        }
    }

    // 4) Style par défaut pour les polygones
    var defaultStyle = {
        color: options.zone_border_color || '#3388ff',
        fillColor: options.zone_fill_color || '#3388ff',
        fillOpacity: parseFloat(options.zone_opacity || 0.5),
        weight: 2
    };
    
    // Définition de styles différents pour zones et POIs
    var poiStyle = {
        radius: 8,
        fillColor: "#ff7800",
        color: "#000",
        weight: 1,
        opacity: 1,
        fillOpacity: 0.8
    };
    
    // Variable globale pour stocker les couches GeoJSON
    var geoJSONLayer;
    
    // Fonction pour filtrer et afficher les données selon les catégories et régions sélectionnées
    function filterAndRenderData(selectedCategories = [], selectedRegions = []) {
        // Supprimer la couche existante si elle existe
        if (geoJSONLayer) {
            map.removeLayer(geoJSONLayer);
        }
        
        // Clone des données pour ne pas modifier l'original
        var filteredData = JSON.parse(JSON.stringify(zonesData));
        
        console.log("Nombre d'éléments avant filtrage:", filteredData.length);
        console.log("Catégories sélectionnées:", selectedCategories);
        console.log("Régions sélectionnées:", selectedRegions);
        
        // Vérifier les types d'éléments pour le debug
        var poiCount = 0;
        var zoneCount = 0;
        filteredData.forEach(function(item) {
            if (item.properties && item.properties.type === 'poi') {
                poiCount++;
            } else if (item.properties && item.properties.type === 'zone') {
                zoneCount++;
            }
        });
        console.log("Nombre de POI:", poiCount);
        console.log("Nombre de zones:", zoneCount);
        
        // Appliquer les filtres si nécessaire
        if (selectedCategories.length > 0 || selectedRegions.length > 0) {
            filteredData = filteredData.filter(function(feature) {
                var matchesCategory = true;
                var matchesRegion = true;
                
                // Vérifier les catégories si elles sont sélectionnées
                if (selectedCategories.length > 0) {
                    // Si pas de catégories, ne pas afficher
                    if (!feature.properties.categories || feature.properties.categories.length === 0) {
                        matchesCategory = false;
                    } else {
                        // Vérifier si l'élément possède au moins une des catégories sélectionnées
                        matchesCategory = feature.properties.categories.some(function(category) {
                            return selectedCategories.includes(category.slug);
                        });
                    }
                }
                
                // Vérifier les régions si elles sont sélectionnées
                if (selectedRegions.length > 0) {
                    // Si pas de régions, ne pas afficher
                    if (!feature.properties.regions || feature.properties.regions.length === 0) {
                        matchesRegion = false;
                    } else {
                        // Vérifier si l'élément possède au moins une des régions sélectionnées
                        matchesRegion = feature.properties.regions.some(function(region) {
                            return selectedRegions.includes(region.slug);
                        });
                    }
                }
                
                // L'élément doit correspondre aux deux filtres pour être affiché
                return matchesCategory && matchesRegion;
            });
        }
        
        console.log("Nombre d'éléments après filtrage:", filteredData.length);
        
        // Créer et ajouter la nouvelle couche GeoJSON
        geoJSONLayer = L.geoJSON(filteredData, {
            style: function(feature) {
                // On n'applique le style que pour les polygones (zones)
                if (feature.properties.type !== 'poi') {
                    return defaultStyle;
                }
            },
            pointToLayer: function(feature, latlng) {
                console.log("pointToLayer appelé pour:", feature);
                // Si c'est un POI (type point), on utilise un cercleMarker
                if (feature.properties && feature.properties.type === 'poi') {
                    console.log("Création d'un POI à la position:", latlng);
                    return L.circleMarker(latlng, poiStyle);
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
                        // Popup pour les points d'intérêt
                        content += '<h2>' + (feature.properties.title || 'Point d\'intérêt') + '</h2>';
                        
                        // Afficher les catégories du POI
                        if (feature.properties.categories && feature.properties.categories.length > 0) {
                            content += '<p><strong>Catégories :</strong> ';
                            feature.properties.categories.forEach(function(cat, index) {
                                content += cat.name;
                                if (index < feature.properties.categories.length - 1) {
                                    content += ', ';
                                }
                            });
                            content += '</p>';
                        }
                        
                        // Afficher les régions du POI
                        if (feature.properties.regions && feature.properties.regions.length > 0) {
                            content += '<p><strong>Régions :</strong> ';
                            feature.properties.regions.forEach(function(reg, index) {
                                content += reg.name;
                                if (index < feature.properties.regions.length - 1) {
                                    content += ', ';
                                }
                            });
                            content += '</p>';
                        }
                    } else {
                        // Popup pour les zones
                        content += '<h2>' + (feature.properties.nom_commercial || 'Commercial') + '</h2>';
                        
                        // Info / présentation
                        if (feature.properties.infos) {
                            content += '<p>' + feature.properties.infos + '</p>';
                        }
                        
                        // Afficher les catégories de la zone
                        if (feature.properties.categories && feature.properties.categories.length > 0) {
                            content += '<p><strong>Catégories :</strong> ';
                            feature.properties.categories.forEach(function(cat, index) {
                                content += cat.name;
                                if (index < feature.properties.categories.length - 1) {
                                    content += ', ';
                                }
                            });
                            content += '</p>';
                        }
                        
                        // Afficher les régions de la zone
                        if (feature.properties.regions && feature.properties.regions.length > 0) {
                            content += '<p><strong>Régions :</strong> ';
                            feature.properties.regions.forEach(function(reg, index) {
                                content += reg.name;
                                if (index < feature.properties.regions.length - 1) {
                                    content += ', ';
                                }
                            });
                            content += '</p>';
                        }
                        
                        // Adresse
                        if (feature.properties.address 
                            && parseInt(options.popup_show_address) === 1) 
                        {
                            content += '<p><strong>Adresse :</strong> ' + feature.properties.address + '</p>';
                        }
                        
                        // Horaires d'ouverture
                        if (feature.properties.opening_hours 
                            && parseInt(options.popup_show_hours) === 1) 
                        {
                            content += '<p><strong>Horaires :</strong> ' + feature.properties.opening_hours + '</p>';
                        }
                        
                        // Liens sociaux (social_links séparés par des virgules)
                        if (feature.properties.social_links 
                            && parseInt(options.popup_show_social) === 1) 
                        {
                            var links = feature.properties.social_links.split(',');
                            content += '<p><strong>Réseaux sociaux :</strong> ';
                            links.forEach(function(link) {
                                var trimmed = link.trim();
                                if (trimmed) {
                                    content += '<a href="' + trimmed + '" target="_blank">'
                                            + trimmed + '</a> ';
                                }
                            });
                            content += '</p>';
                        }
                        
                        // Email
                        if (feature.properties.email 
                            && parseInt(options.popup_enable_email_btn) === 1) 
                        {
                            content += '<p><strong>Email :</strong> '
                                    + '<a href="mailto:' + feature.properties.email + '">'
                                    + feature.properties.email + '</a></p>';
                        }
                        
                        // Téléphone
                        if (feature.properties.telephone 
                            && parseInt(options.popup_enable_phone_btn) === 1) 
                        {
                            content += '<p><strong>Téléphone :</strong> '
                                    + '<a href="tel:' + feature.properties.telephone + '">'
                                    + feature.properties.telephone + '</a></p>';
                        }
                        
                        // Bouton "contacter" (exemple menant vers /contact)
                        if (parseInt(options.popup_enable_contact_btn) === 1) {
                            var commercialId = feature.properties.commercial_id || 0;
                            var contactUrl = options.contact_page_url || '/contact';
                            content += '<p><a href="' + contactUrl + '?commercial_id=' + commercialId + '" class="btn-contact">Contacter</a></p>';
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
    
    // Initialiser Select2 pour le filtre de catégories
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('#categoryFilter').select2({
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
    
    // Gestionnaires d'événements pour le filtrage
    var applyFilterBtn = document.getElementById('applyFilter');
    var resetFilterBtn = document.getElementById('resetFilter');
    var categoryFilter = document.getElementById('categoryFilter');
    var regionFilter = document.getElementById('region-filter'); // Corrigé : ID correct du filtre région
    
    // Utiliser jQuery pour récupérer les valeurs de Select2
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            var selectedCategories = [];
            var selectedRegions = [];
            
            if (typeof jQuery !== 'undefined') {
                // Récupérer les catégories sélectionnées
                if (jQuery('#categoryFilter').length) {
                    selectedCategories = jQuery('#categoryFilter').val() || [];
                }
                
                // Récupérer les régions sélectionnées - Corrigé : ID correct et gestion de valeur unique
                if (jQuery('#region-filter').length) {
                    var regionVal = jQuery('#region-filter').val();
                    // Si une seule région est sélectionnée et qu'elle n'est pas vide, la mettre dans un tableau
                    if (regionVal && regionVal !== '') {
                        selectedRegions = Array.isArray(regionVal) ? regionVal : [regionVal];
                    }
                }
            } else {
                // Fallback si jQuery n'est pas disponible
                if (categoryFilter) {
                    for (var i = 0; i < categoryFilter.selectedOptions.length; i++) {
                        selectedCategories.push(categoryFilter.selectedOptions[i].value);
                    }
                }
                
                if (regionFilter) {
                    // Si une seule région est sélectionnée
                    if (regionFilter.value && regionFilter.value !== '') {
                        selectedRegions.push(regionFilter.value);
                    }
                }
            }
            
            console.log("Catégories sélectionnées :", selectedCategories);
            console.log("Régions sélectionnées :", selectedRegions);
            
            filterAndRenderData(selectedCategories, selectedRegions);
        });
    }
    
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            // Réinitialiser les sélections avec Select2
            if (typeof jQuery !== 'undefined') {
                if (jQuery('#categoryFilter').length) {
                    jQuery('#categoryFilter').val(null).trigger('change');
                }
                
                if (jQuery('#region-filter').length) { // Corrigé : ID correct
                    jQuery('#region-filter').val('').trigger('change');
                }
            } else {
                // Fallback si jQuery n'est pas disponible
                if (categoryFilter) {
                    for (var i = 0; i < categoryFilter.options.length; i++) {
                        categoryFilter.options[i].selected = false;
                    }
                }
                
                if (regionFilter) {
                    regionFilter.value = '';
                }
            }
            
            // Afficher toutes les données
            filterAndRenderData();
        });
    }
    
    // Afficher initialement toutes les données
    filterAndRenderData();
});
