document.addEventListener('DOMContentLoaded', function() {
    var options = zonifyFrontendOptions || {};
    console.log("Options : ", options);

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
            container.style.display = 'block'; // on l’affiche
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

    // 5) Chargement des polygones (zonesData)
    L.geoJSON(zonesData, {
        style: defaultStyle,
        onEachFeature: function(feature, layer) {
            // Au clic sur la zone
            layer.on('click', function() {
                // Construction du contenu de la popup
                // On peut appliquer un style "inline" basé sur popup_font_* si on veut
                var content = '<div class="popup-container" style="'
                              + 'font-family:' + (options.popup_font_family || 'Arial,sans-serif') + ';'
                              + ' font-size:' + (options.popup_font_size || '14px') + ';'
                              + ' color:' + (options.popup_font_color || '#333') + ';">';
    
                // Nom du commercial (feature.properties.nom_commercial)
                content += '<h2>' + (feature.properties.nom_commercial || 'Commercial') + '</h2>';
    
                // Info / présentation (feature.properties.infos)
                if (feature.properties.infos) {
                    content += '<p>' + feature.properties.infos + '</p>';
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
                    content += '<p><a href="/contact" class="btn-contact">Contacter</a></p>';
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
});
