document.addEventListener('DOMContentLoaded', function() {

    // 1) Récup. de always_show_all_zones
    // (Défini via wp_localize_script('zonify-script', 'alwaysShowAllZones', $some_value);)
    var alwaysShow = (typeof alwaysShowAllZones !== 'undefined' && alwaysShowAllZones == 1);

    // Récup. de vars de config (définies dans zonifyMapVars)
    var provider = zonifyMapVars.tile_provider || 'cartodb_light';
    var tileLayerUrl, attribution;

    if (provider === 'cartodb_dark') {
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    } else if (provider === 'osm') {
        tileLayerUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = '© OpenStreetMap contributors';
    } else if (provider === 'custom') {
        tileLayerUrl = zonifyMapVars.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = 'Personnalisé';
    } else {
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    }

    var zoom = zonifyMapVars.map_zoom || 9;
    var centerLat = parseFloat(zonifyMapVars.map_center_lat) || 50.5;
    var centerLng = parseFloat(zonifyMapVars.map_center_lng) || 2.5;

    // Initialisation de la carte
    var map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);

    var defaultStyle = {
        color: zonifyMapVars.zone_border_color || '#3388ff',
        fillColor: zonifyMapVars.zone_fill_color || '#3388ff',
        fillOpacity: parseFloat(zonifyMapVars.zone_opacity || 0.5),
        weight: 2
    };
    var highlightStyle = { color: 'red', weight: 3 };

    // Groupe pour Leaflet.draw
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Contrôle de dessin
    var drawControl = new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: {
            polygon: true,
            rectangle: false,
            polyline: false,
            circle: false,
            marker: false,
            circlemarker: false
        }
    });
    map.addControl(drawControl);

    // Event CREATION polygone
    map.on(L.Draw.Event.CREATED, function(e) {
        var layer = e.layer;
        if (!layer.feature) {
            layer.feature = { type: "Feature", properties: {} };
        }
        var selectedCom = document.getElementById('commercial-select').value;
        if (selectedCom) {
            layer.feature.properties.commercial_id = parseInt(selectedCom);
        }
        drawnItems.addLayer(layer);
        attachPopup(layer);
    });

    // 2) Au chargement : si alwaysShow=1 => on charge toutes les zones & pas de refiltrage.
    //                  si alwaysShow=0 => on loadZones(0) => (toutes) par défaut
    if (alwaysShow) {
        loadZonesAndRender(0, true);  // true => mode "alwaysShow" => on stocke tout & surlignera plus tard
    } else {
        loadZonesAndRender(0, false); // charge toutes les zones comme point de départ
    }

    // 3) Changement de commercial
    var commercialSelect = document.getElementById('commercial-select');
    commercialSelect.addEventListener('change', function() {
        var selectedId = parseInt(this.value || 0);

        if (alwaysShow) {
            // On ne refait pas de requête : on surligne
            if (selectedId == 0) {
                // "Aucun" => retirer la surbrillance
                drawnItems.eachLayer(function(layer) {
                    layer.setStyle && layer.setStyle(defaultStyle);
                });
            } else {
                // Surbriller seulement celles du commercial
                drawnItems.eachLayer(function(layer) {
                    if (layer.feature && layer.feature.properties.commercial_id == selectedId) {
                        layer.setStyle && layer.setStyle(highlightStyle);
                    } else {
                        layer.setStyle && layer.setStyle(defaultStyle);
                    }
                });
            }
        } else {
            // Mode "vue par commercial" => fetch
            // Si selectedId=0 => on affiche toutes (pas de filter)
            loadZonesAndRender(selectedId, false);
        }
    });

    // 4) Bouton "Sauvegarder"
    var saveBtn = document.getElementById('save-zone');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            var layers = drawnItems.getLayers();
            if (layers.length === 0) {
                if (!confirm("Aucune zone sur la carte. Supprimer toutes les zones ?")) {
                    return;
                }
            }
            var polygonsData = [];
            layers.forEach(function(layer) {
                var geo = layer.toGeoJSON().geometry;
                var zid = 0, comid = 0;
                if (layer.feature && layer.feature.properties) {
                    zid = layer.feature.properties.zone_id || 0;
                    comid = layer.feature.properties.commercial_id || 0;
                }
                polygonsData.push({
                    zone_id: zid,
                    geometry: geo,
                    commercial_id: comid
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
                    alert(data.data);
                    console.log("Zones sauvegardées :", data.data);
                } else {
                    console.error("Erreur save :", data.data);
                    alert("Erreur : " + data.data);
                }
            })
            .catch(err => console.error("Erreur AJAX save :", err));
        });
    }

    // 5) Suppression
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-zone-btn')) {
            e.preventDefault();
            var zoneId = e.target.dataset.zoneid;
            if (!confirm("Supprimer la zone #" + zoneId + " ?")) {
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
                    removeZoneLayerFromMap(zoneId);
                } else {
                    alert("Erreur : " + data.data);
                }
            })
            .catch(err => console.error("Erreur AJAX delete_zone :", err));
        }
    });

    // ----------------------------------------------------------------
    // FONCTIONS
    // ----------------------------------------------------------------

    // Chargement via AJAX get_zone
    // param comId => 0 => toutes, >0 => commercial spécifique
    // param always => indique si on est en alwaysShowAllZones=1 (pour stocker tout)
    function loadZonesAndRender(comId, always) {
        fetch(zonifyMapVars.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
                action: 'get_zone',
                commercial_id: comId,
                _ajax_nonce: zonifyMapVars.nonce
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                var zoneData = data.data.zone_data || [];
                // On efface tout
                drawnItems.clearLayers();

                // On réaffiche
                let newLayer = L.geoJSON(zoneData, {
                    style: defaultStyle,
                    onEachFeature: function(feature, layer) {
                        if (!layer.feature) layer.feature = feature;
                        drawnItems.addLayer(layer);
                        attachPopup(layer);
                    }
                }).addTo(map);

                // Fit bounds
                if (zoneData.length > 0) {
                    map.fitBounds(newLayer.getBounds());
                }

                // Si always==true et commercial choisi >0 => on surbrille
                // Mais la question est : on refait un appel AJAX ou pas ?
                // Dans le flux "alwaysShowAllZones=1", on disait pas d'appel
                // => On peut adapter la logique si besoin
            } else {
                console.error("Erreur get_zone("+comId+") :", data.data);
            }
        })
        .catch(err => console.error("Erreur AJAX get_zone("+comId+") :", err));
    }

    function removeZoneLayerFromMap(zoneId) {
        drawnItems.eachLayer(function(layer) {
            if (layer.feature && layer.feature.properties &&
                layer.feature.properties.zone_id == zoneId) {
                drawnItems.removeLayer(layer);
            }
        });
    }

    function attachPopup(layer) {
        var props = layer.feature.properties || {};
        var zId = props.zone_id || 0;
        var popupHtml = `<div><strong>Zone #${zId}</strong>`;

        if (zId>0 && zonifyMapVars.edit_zone_base) {
            var editUrl = `${zonifyMapVars.edit_zone_base}?post=${zId}&action=edit`;
            popupHtml += `<br/><a href="${editUrl}" target="_blank">Éditer cette zone</a>`;
        }
        if (zId>0) {
            popupHtml += `<br/><a href="#" class="delete-zone-btn" data-zoneid="${zId}">Supprimer</a>`;
        }
        popupHtml += `</div>`;

        layer.bindPopup(popupHtml);
    }

});
