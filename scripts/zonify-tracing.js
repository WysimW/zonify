document.addEventListener('DOMContentLoaded', function() {

    // 1. Configuration de la carte
    var provider = zonifyMapVars.tile_provider || 'cartodb_light';
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

    

    var zoom = zonifyMapVars.map_zoom || 9;
    var centerLat = parseFloat(zonifyMapVars.map_center_lat) || 50.5;
    var centerLng = parseFloat(zonifyMapVars.map_center_lng) || 2.5;

    // Création de la carte Leaflet
    var map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);

    // 2. Styles
    var defaultStyle = {
        color: zonifyMapVars.zone_border_color || '#3388ff',
        fillColor: zonifyMapVars.zone_fill_color || '#3388ff',
        fillOpacity: parseFloat(zonifyMapVars.zone_opacity || 0.5),
        weight: 2
    };
    var highlightStyle = {
        color: 'red',
        weight: 3
    };

    // 3. Groupe pour l'édition Leaflet.draw
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // 4. Charger les zones existantes (zonesAdminData)
    //    Chaque zone aura un popup avec "Éditer" et "Supprimer" (si zone_id > 0)
    var allZonesLayer = L.geoJSON(zonesAdminData, {
        style: defaultStyle,
        onEachFeature: function(feature, layer) {
            // S'assurer qu'on stocke la feature
            if (!layer.feature) {
                layer.feature = feature;
            }
            // Ajouter au groupe d'édition
            drawnItems.addLayer(layer);

            // Créer un popup
            var zoneId = feature.properties.zone_id || 0;
            var popupHtml = `<div><strong>Zone #${zoneId}</strong>`;
            
            // Lien d'édition du post "zone"
            if (zoneId > 0 && zonifyMapVars.edit_zone_base) {
                var editUrl = `${zonifyMapVars.edit_zone_base}?post=${zoneId}&action=edit`;
                popupHtml += `<br/><a href="${editUrl}" target="_blank">Accéder à la fiche de cette zone</a>`;
            }

            // Bouton de suppression
            if (zoneId > 0) {
                popupHtml += `<br/><a href="#" class="delete-zone-btn" data-zoneid="${zoneId}">Supprimer cette zone</a>`;
            }

            popupHtml += `</div>`;

            layer.bindPopup(popupHtml);
        }
    }).addTo(map);

    // 5. Surbrillance si on change de commercial dans la liste
    var commercialSelect = document.getElementById('commercial-select');
    commercialSelect.addEventListener('change', function() {
        var selectedId = this.value;
        // Remettre le style par défaut
        allZonesLayer.eachLayer(function(layer) {
            layer.setStyle(defaultStyle);
        });
        if (!selectedId) return;

        allZonesLayer.eachLayer(function(layer) {
            var props = layer.feature.properties;
            if (props && props.commercial_id == selectedId) {
                layer.setStyle(highlightStyle);
                if (layer.getBounds) {
                    map.fitBounds(layer.getBounds());
                }
            }
        });
    });

    // 6. Contrôleur Leaflet.draw
    var drawControl = new L.Control.Draw({
        edit: {
            featureGroup: drawnItems
        },
        draw: {
            polygon: true,
            polyline: false,
            rectangle: false,
            circle: false,
            marker: false,
            circlemarker: false
        }
    });
    map.addControl(drawControl);

    // 7. Création d'un nouveau polygone
    map.on(L.Draw.Event.CREATED, function(e) {
        var layer = e.layer;
        if (!layer.feature) {
            layer.feature = { type: "Feature", properties: {} };
        }
        // Assigner le commercial sélectionné s'il existe
        var selectedCom = commercialSelect.value;
        if (selectedCom) {
            layer.feature.properties.commercial_id = parseInt(selectedCom);
        }
        // Pas de zone_id => nouveau
        drawnItems.addLayer(layer);
    });

    // 8. Sauvegarder toutes les zones
    document.getElementById('save-zones').addEventListener('click', function() {
        var layers = drawnItems.getLayers();
        if (layers.length === 0) {
            if (!confirm("Aucune zone sur la carte. Supprimer toutes les zones ?")) {
                return;
            }
        }

        // Construire la liste
        var polygonsData = [];
        layers.forEach(function(layer) {
            var geo = layer.toGeoJSON().geometry;
            var zid = 0;
            var oldCom = 0;
            if (layer.feature && layer.feature.properties) {
                if (layer.feature.properties.zone_id) {
                    zid = layer.feature.properties.zone_id;
                }
                if (layer.feature.properties.commercial_id) {
                    oldCom = parseInt(layer.feature.properties.commercial_id);
                }
            }
            polygonsData.push({
                zone_id: zid,
                geometry: geo,
                commercial_id: oldCom
            });
        });

        fetch(zonifyMapVars.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
                action: 'save_multiple_zones',
                zones: JSON.stringify(polygonsData),
                _ajax_nonce: zonifyMapVars.nonce
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                console.log("Zones sauvegardées :", data.data);
                alert(data.data);
            } else {
                console.error("Erreur sauvegarde :", data.data);
                alert("Erreur : " + data.data);
            }
        })
        .catch(err => {
            console.error("Erreur AJAX :", err);
        });
    });

    // 9. Écouter le clic "Supprimer cette zone" dans le popup
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-zone-btn')) {
            e.preventDefault();
            var zoneId = e.target.dataset.zoneid;
            if (!confirm("Voulez-vous vraiment supprimer la zone #" + zoneId + " ?")) {
                return;
            }
            fetch(zonifyMapVars.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: new URLSearchParams({
                    action: 'delete_zone',
                    zone_id: zoneId,
                    _ajax_nonce: zonifyMapVars.nonce
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert("Zone supprimée côté serveur.");
                    // Retirer la couche de la carte
                    removeZoneLayerFromMap(zoneId);
                } else {
                    alert("Erreur suppression zone : " + data.data);
                }
            })
            .catch(err => console.error("Erreur AJAX delete_zone :", err));
        }
    });

    // Fonction pour retirer la couche correspondante
    function removeZoneLayerFromMap(zoneId) {
        drawnItems.eachLayer(function(layer) {
            if (layer.feature && layer.feature.properties && layer.feature.properties.zone_id == zoneId) {
                drawnItems.removeLayer(layer);
            }
        });
    }
});
