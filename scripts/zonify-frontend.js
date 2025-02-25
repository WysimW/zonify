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
    } else if (provider === 'custom') {
        tileLayerUrl = options.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = 'Personnalisé';
    } else {
        // Par défaut cartodb_light
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
            layer.on('click', function() {
                var content = '<div class="popup-container" style="font-family:' + options.popup_font_family + '; font-size:' + options.popup_font_size + '; color:' + options.popup_font_color + ';">';
                content += '<h2>' + (feature.properties.nom_commercial || 'Commercial') + '</h2>';
                content += '<p>' + (feature.properties.infos || '') + '</p>';

                // etc. (adresse, horaires, liens sociaux, etc.)
                if (feature.properties.address && parseInt(options.popup_show_address) === 1) {
                    content += '<p>Adresse : ' + feature.properties.address + '</p>';
                }
                // ...
                
                content += '</div>';
                L.popup()
                 .setLatLng(layer.getBounds().getCenter())
                 .setContent(content)
                 .openOn(map);
            });
        }
    }).addTo(map);
});
