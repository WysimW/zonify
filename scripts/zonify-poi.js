document.addEventListener('DOMContentLoaded', function() {
    // Récupération des options depuis zonifyPoiVars (à définir via wp_localize_script)
    var provider = zonifyPoiVars.tile_provider || 'cartodb_light';
    
    var tileLayerUrl, attribution;

    // Choix du provider de tuiles
    if (provider === 'osm') {
        tileLayerUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = '© OpenStreetMap contributors';
    } else if (provider === 'opentopo') {
        tileLayerUrl = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
        attribution = '© OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)';
    } else if (provider === 'esri_topo') {
        tileLayerUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}';
        attribution = 'Tiles © Esri — Source: Esri, USGS, NOAA';
    } else if (provider === 'custom') {
        tileLayerUrl = zonifyPoiVars.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = 'Personnalisé';
    } else {
        // Par défaut (CartoDB light)
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    }

    var zoom = zonifyPoiVars.map_zoom || 9;
    var centerLat = parseFloat(zonifyPoiVars.map_center_lat) || 50.5;
    var centerLng = parseFloat(zonifyPoiVars.map_center_lng) || 2.5;

    // Création de la carte dans le conteneur avec l'id "poi-map"
    var map = L.map('poi-map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);

    // Groupe pour stocker les markers (POI)
    var drawnMarkers = new L.FeatureGroup();
    map.addLayer(drawnMarkers);

    // Chargement des POI existants s'ils sont définis via wp_localize_script
    if (typeof poisData !== 'undefined' && Array.isArray(poisData)) {
        poisData.forEach(function(poi) {
            if (poi.geometry && poi.geometry.type === "Point") {
                // Attention : GeoJSON pour un point est de la forme [lng, lat]
                var lat = poi.geometry.coordinates[1];
                var lng = poi.geometry.coordinates[0];
                var marker = L.marker([lat, lng]);
                if (poi.properties && poi.properties.title) {
                    marker.bindPopup("<strong>" + poi.properties.title + "</strong>");
                }
                // Garder la structure GeoJSON dans le marker pour l'édition éventuelle
                marker.feature = poi;
                drawnMarkers.addLayer(marker);
            }
        });
    }

    // Initialisation du contrôle Leaflet Draw (uniquement pour les markers)
    var drawControl = new L.Control.Draw({
        edit: {
            featureGroup: drawnMarkers,
            edit: true,
            remove: true
        },
        draw: {
            marker: true,
            polygon: false,
            polyline: false,
            rectangle: false,
            circle: false,
            circlemarker: false
        }
    });
    map.addControl(drawControl);

    // Événement déclenché lors de la création d'un marker
    map.on(L.Draw.Event.CREATED, function(e) {
        var layer = e.layer;
        // Demande de renseignement du titre du POI via une prompt
        var poiTitle = prompt("Entrez le nom du point d'intérêt :");
        if (poiTitle) {
            layer.bindPopup("<strong>" + poiTitle + "</strong>");
            // Initialisation des propriétés du feature
            layer.feature = layer.feature || { type: "Feature", properties: {} };
            layer.feature.properties.title = poiTitle;
        }
        drawnMarkers.addLayer(layer);

        // Enregistrement du POI via AJAX
        var poiGeoJSON = layer.toGeoJSON().geometry;
        fetch(zonifyPoiVars.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
                action: 'save_poi',
                poi_data: JSON.stringify(poiGeoJSON),
                _ajax_nonce: zonifyPoiVars.nonce
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                alert("Point d'intérêt sauvegardé : " + data.data.message);
                if (data.data.poi_id) {
                    layer.feature.properties.poi_id = data.data.poi_id;
                }
            } else {
                alert("Erreur lors de la sauvegarde : " + data.data);
            }
        })
        .catch(function(error) {
            console.error("Erreur AJAX save_poi:", error);
        });
    });

    // Gestion de l'édition des POI
    map.on('draw:edited', function(e) {
        e.layers.eachLayer(function(layer) {
            var poiGeoJSON = layer.toGeoJSON().geometry;
            var poiId = (layer.feature && layer.feature.properties) ? layer.feature.properties.poi_id : 0;
            fetch(zonifyPoiVars.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: new URLSearchParams({
                    action: 'save_poi',
                    poi_data: JSON.stringify(poiGeoJSON),
                    poi_id: poiId,
                    _ajax_nonce: zonifyPoiVars.nonce
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    console.log("Point d'intérêt mis à jour : " + data.data.message);
                    if (data.data.poi_id) {
                        layer.feature.properties.poi_id = data.data.poi_id;
                    }
                } else {
                    console.error("Erreur lors de la mise à jour : " + data.data);
                }
            })
            .catch(function(error) {
                console.error("Erreur AJAX update_poi:", error);
            });
        });
    });

    // Gestion de la suppression des POI
    map.on('draw:deleted', function(e) {
        e.layers.eachLayer(function(layer) {
            var poiId = (layer.feature && layer.feature.properties) ? layer.feature.properties.poi_id : 0;
            if (poiId) {
                fetch(zonifyPoiVars.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams({
                        action: 'delete_poi',
                        poi_id: poiId,
                        _ajax_nonce: zonifyPoiVars.nonce
                    })
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        alert("Point d'intérêt supprimé.");
                    } else {
                        alert("Erreur lors de la suppression : " + data.data);
                    }
                })
                .catch(function(error) {
                    console.error("Erreur AJAX delete_poi:", error);
                });
            }
        });
    });
});
