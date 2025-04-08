// zonify-tracing.js

document.addEventListener('DOMContentLoaded', function() {

    // 1) Récupération du mode always_show_all_zones depuis zonifyMapVars
    var alwaysShow = (typeof zonifyMapVars.alwaysShow !== 'undefined' && zonifyMapVars.alwaysShow == 1);

    // 2) Configuration de la carte depuis zonifyMapVars
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

    // 3) Création de la carte Leaflet
    var map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);

    // 4) Définir les styles par défaut et la surbrillance
    var defaultStyle = {
        color: zonifyMapVars.zone_border_color || '#3388ff',
        fillColor: zonifyMapVars.zone_fill_color || '#3388ff',
        fillOpacity: parseFloat(zonifyMapVars.zone_opacity || 0.5),
        weight: 2
    };
    var highlightStyle = { color: 'red', weight: 3 };
    
    // Fonction pour obtenir le style d'une zone en fonction du commercial
    function getZoneStyle(feature) {
        // Si pas de propriétés, utiliser le style par défaut
        if (!feature || !feature.properties) {
            return defaultStyle;
        }
        
        // Récupérer les couleurs personnalisées du commercial
        var borderColor = feature.properties.border_color;
        var fillColor = feature.properties.fill_color;
        
        // Si aucune couleur personnalisée n'est définie, utiliser le style par défaut
        if (!borderColor && !fillColor) {
            return defaultStyle;
        }
        
        // Créer un style personnalisé
        return {
            color: borderColor || defaultStyle.color,
            fillColor: fillColor || defaultStyle.fillColor,
            fillOpacity: defaultStyle.fillOpacity,
            weight: defaultStyle.weight
        };
    }

    // 5) Création des FeatureGroups pour l'édition et pour servir de guide au snapping
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);
    var guideLayers = new L.FeatureGroup();
    map.addLayer(guideLayers);

    // 6) Ajouter le contrôle Leaflet.Draw
    var drawControl = new L.Control.Draw({
        edit: { featureGroup: drawnItems },
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

    // 7) Récupération du select multi pour les commerciaux et les régions
    var commercialSelect = document.getElementById('commercial-select');
    var regionSelect = document.getElementById('region-select');
    if (!commercialSelect) {
        console.error("L'élément #commercial-select est introuvable.");
    }
    if (!regionSelect) {
        console.error("L'élément #region-select est introuvable.");
    }

    // 8) Lorsqu'un nouveau polygone est créé, afficher un popup pour sélectionner le commercial et la région
    map.on(L.Draw.Event.CREATED, function(e) {
        var layer = e.layer;
        if (!layer.feature) {
            layer.feature = { type: "Feature", properties: {} };
        }
        // Déterminer le centre du polygone pour positionner le popup
        var center = layer.getBounds ? layer.getBounds().getCenter() : layer.getLatLng();

        // Construction du contenu du popup
        var popupContent = '<div id="popup-save-zone">';
        popupContent += '<label for="popup-commercial-select">Sélectionnez un commercial :</label><br/>';
        popupContent += '<select id="popup-commercial-select">';
        // Utiliser une variable globale "commercialsData" si disponible, sinon récupérer les options du select principal
        if (window.commercialsData && Array.isArray(window.commercialsData)) {
            window.commercialsData.forEach(function(comm) {
                popupContent += '<option value="' + comm.id + '">' + comm.title + '</option>';
            });
        } else if (commercialSelect) {
            Array.from(commercialSelect.options).forEach(function(opt) {
                if (opt.value !== "0" && opt.value !== "") {
                    popupContent += '<option value="' + opt.value + '">' + opt.text + '</option>';
                }
            });
        }
        popupContent += '</select><br/>';
        
        // Ajout du sélecteur de région dans le popup
        popupContent += '<label for="popup-region-select">Sélectionnez une région :</label><br/>';
        popupContent += '<select id="popup-region-select">';
        popupContent += '<option value="">-- Sélectionnez une région --</option>';
        // Si des régions sont disponibles via les variables JavaScript
        if (zonifyMapVars.regions && Array.isArray(zonifyMapVars.regions)) {
            zonifyMapVars.regions.forEach(function(region) {
                popupContent += '<option value="' + region.id + '">' + region.name + '</option>';
            });
        } else if (regionSelect) {
            // Sinon, récupérer depuis le select principal
            Array.from(regionSelect.options).forEach(function(opt) {
                if (opt.value !== "0" && opt.value !== "") {
                    popupContent += '<option value="' + opt.value + '">' + opt.text + '</option>';
                }
            });
        }
        popupContent += '</select><br/>';
        
        popupContent += '<button id="popup-save-btn" type="button">Enregistrer la zone</button>';
        popupContent += '</div>';

        // Afficher le popup
        L.popup().setLatLng(center).setContent(popupContent).openOn(map);

        // Attendre que le popup soit affiché puis attacher l'événement sur le bouton
        setTimeout(function() {
            var btn = document.getElementById('popup-save-btn');
            if (btn) {
                btn.addEventListener('click', function() {
                    var select = document.getElementById('popup-commercial-select');
                    var regionSelect = document.getElementById('popup-region-select');
                    if (select && select.value) {
                        layer.feature.properties.commercial_id = parseInt(select.value);
                        
                        // Stocker la région sélectionnée (si présente)
                        if (regionSelect && regionSelect.value) {
                            layer.feature.properties.region_id = parseInt(regionSelect.value);
                        }
                        
                        map.closePopup();
                        drawnItems.addLayer(layer);
                        // Activer le snapping pour le polygone (si applicable)
                        if ((layer instanceof L.Polygon || layer instanceof L.Polyline) && typeof L.Handler.PolylineSnap === 'function') {
                            layer.snapediting = new L.Handler.PolylineSnap(map, layer, { snapDistance: 20 });
                            layer.snapediting.addGuideLayer(guideLayers);
                            layer.snapediting.enable();
                        } else {
                            console.warn("L.Handler.PolylineSnap n'est pas défini. Le snapping n'est pas activé.");
                        }
                        guideLayers.addLayer(layer);

                        // Enregistrement via AJAX de la zone créée
                        var zoneGeoJSON = layer.toGeoJSON().geometry;
                        var requestData = {
                            action: 'save_zone',
                            zone_data: JSON.stringify(zoneGeoJSON),
                            commercial_id: layer.feature.properties.commercial_id,
                            _ajax_nonce: zonifyMapVars.nonce
                        };
                        
                        // Ajouter la région s'il y en a une
                        if (layer.feature.properties.region_id) {
                            requestData.region_id = layer.feature.properties.region_id;
                        }
                        
                        fetch(zonifyMapVars.ajax_url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                            body: new URLSearchParams(requestData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Zone sauvegardée : " + data.data.message);
                                if (data.data.zone_id) {
                                    layer.feature.properties.zone_id = data.data.zone_id;
                                }
                            } else {
                                alert("Erreur lors de la sauvegarde : " + data.data);
                            }
                        })
                        .catch(error => console.error("Erreur AJAX save_zone:", error));
                    } else {
                        alert("Veuillez sélectionner un commercial.");
                    }
                });
            }
        }, 300);
    });

    // 9) Fonction AJAX pour charger les zones via get_zone
    function loadZonesAndRender(comId, regionId, always) {
        const requestData = {
            action: 'get_zone',
            commercial_id: comId,
            _ajax_nonce: zonifyMapVars.nonce
        };
        
        // Ajouter la région si fournie
        if (regionId) {
            requestData.region_id = regionId;
        }
        
        fetch(zonifyMapVars.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams(requestData)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.data.debug && data.data.debug.length > 0) {
                    console.warn("Debug get_zone(" + comId + ", " + regionId + "):", data.data.debug);
                }
                var zoneData = data.data.zone_data || [];
                drawnItems.clearLayers();
                guideLayers.clearLayers();
                let newLayer = L.geoJSON(zoneData, {
                    style: getZoneStyle,
                    onEachFeature: function(feature, layer) {
                        if (!layer.feature) layer.feature = feature;
                        drawnItems.addLayer(layer);
                        attachPopup(layer);
                        if ((layer instanceof L.Polygon || layer instanceof L.Polyline) && typeof L.Handler.PolylineSnap === 'function') {
                            layer.snapediting = new L.Handler.PolylineSnap(map, layer, { snapDistance: 20 });
                            layer.snapediting.addGuideLayer(guideLayers);
                            layer.snapediting.enable();
                        } else {
                            console.warn("L.Handler.PolylineSnap n'est pas défini pour ce layer.");
                        }
                        guideLayers.addLayer(layer);
                    }
                }).addTo(map);
                if (zoneData.length > 0) {
                    map.fitBounds(newLayer.getBounds());
                }
            } else {
                console.error("Erreur get_zone(" + comId + ", " + regionId + "):", data.data);
            }
        })
        .catch(err => console.error("Erreur AJAX get_zone(" + comId + ", " + regionId + "):", err));
    }

    // 10) Au chargement, afficher toutes les zones (commercial_id = 0, region_id = 0)
    loadZonesAndRender(0, 0, alwaysShow);

    // 11) Au changement du multi‑select commercial
    jQuery(function($) {
        $('#commercial-select').select2({
            placeholder: "Sélectionnez un ou plusieurs commerciaux",
            allowClear: true,
            width: 'resolve'
        });
        
        // Initialisation de Select2 pour les régions
        $('#region-select').select2({
            placeholder: "Sélectionnez une ou plusieurs régions",
            allowClear: true,
            width: 'resolve'
        });

        // S'assurer que le script capte les changements Select2 pour les commerciaux
        $('#commercial-select').on('change', function() {
            filterAndLoadZones();
        });
        
        // S'assurer que le script capte les changements Select2 pour les régions
        $('#region-select').on('change', function() {
            filterAndLoadZones();
        });
        
        // Fonction pour filtrer et charger les zones selon les sélections
        function filterAndLoadZones() {
            const selectedCommercials = $('#commercial-select').val(); // tableau ou null
            const selectedRegions = $('#region-select').val(); // tableau ou null

            let commercialId = '';
            if (!selectedCommercials || selectedCommercials.length === 0 || selectedCommercials.includes("0")) {
                commercialId = '0';
            } else {
                commercialId = selectedCommercials.join(',');
            }
            
            let regionId = '';
            if (!selectedRegions || selectedRegions.length === 0 || selectedRegions.includes("0")) {
                regionId = '0';
            } else {
                regionId = selectedRegions.join(',');
            }

            if (!alwaysShow) {
                loadZonesAndRender(commercialId, regionId, false);
            } else {
                drawnItems.eachLayer(function(layer) {
                    // Vérification du commercial
                    let commercialMatch = false;
                    if (!selectedCommercials || selectedCommercials.includes("0") || selectedCommercials.length === 0) {
                        commercialMatch = true;
                    } else if (layer.feature && layer.feature.properties.commercial_id &&
                        selectedCommercials.includes(layer.feature.properties.commercial_id.toString())) {
                        commercialMatch = true;
                    }
                    
                    // Vérification de la région
                    let regionMatch = false;
                    if (!selectedRegions || selectedRegions.includes("0") || selectedRegions.length === 0) {
                        regionMatch = true;
                    } else if (layer.feature && layer.feature.properties.region_ids) {
                        // Vérifier si l'une des régions de la zone correspond à l'une des régions sélectionnées
                        const layerRegionIds = layer.feature.properties.region_ids.map(id => id.toString());
                        if (selectedRegions.some(id => layerRegionIds.includes(id))) {
                            regionMatch = true;
                        }
                    }
                    
                    // Appliquer le style en fonction des résultats de filtrage
                    if (commercialMatch && regionMatch) {
                        if (layer.setStyle) layer.setStyle(highlightStyle);
                        if (layer.getBounds) map.fitBounds(layer.getBounds());
                    } else {
                        if (layer.setStyle) layer.setStyle(defaultStyle);
                    }
                });
            }
        }
    });

    // 12) Bouton "Sauvegarder" (pour sauvegarder toutes les zones)
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
                var zid = 0, comid = 0, regionIds = [];
                if (layer.feature && layer.feature.properties) {
                    zid = layer.feature.properties.zone_id || 0;
                    comid = layer.feature.properties.commercial_id || 0;
                    regionIds = layer.feature.properties.region_ids || [];
                }
                polygonsData.push({
                    zone_id: zid,
                    geometry: geo,
                    commercial_id: comid,
                    region_ids: regionIds
                });
            });
            console.log("DEBUG: polygonsData =>", polygonsData);
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
                } else {
                    console.error("Erreur save :", data.data);
                    alert("Erreur : " + data.data);
                }
            })
            .catch(err => console.error("Erreur AJAX save :", err));
        });
    }

    // 13) Suppression via le popup
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
                    removeZoneLayerFromMap(zoneId);
                } else {
                    alert("Erreur suppression zone : " + data.data);
                }
            })
            .catch(err => console.error("Erreur AJAX delete_zone :", err));
        }
    });

    // 14) Fonction pour retirer la couche correspondante
    function removeZoneLayerFromMap(zoneId) {
        drawnItems.eachLayer(function(layer) {
            if (layer.feature && layer.feature.properties &&
                layer.feature.properties.zone_id == zoneId) {
                drawnItems.removeLayer(layer);
                guideLayers.removeLayer(layer);
            }
        });
    }

    // 15) Fonction d'attache du popup
    function attachPopup(layer) {
        var props = layer.feature.properties || {};
        var zId = props.zone_id || 0;
        var popupHtml = `<div><strong>Zone #${zId}</strong>`;
        
        // Ajouter les noms des régions s'ils sont disponibles
        if (props.region_names && props.region_names.length > 0) {
            popupHtml += `<br/><em>Régions : ${props.region_names.join(', ')}</em>`;
        }
        
        if (zId > 0 && zonifyMapVars.edit_zone_base) {
            let editUrl = `${zonifyMapVars.edit_zone_base}?post=${zId}&action=edit`;
            popupHtml += `<br/><a href="${editUrl}" target="_blank">Éditer cette zone</a>`;
        }
        if (zId > 0) {
            popupHtml += `<br/><a href="#" class="delete-zone-btn" data-zoneid="${zId}">Supprimer</a>`;
        }
        popupHtml += `</div>`;
        layer.bindPopup(popupHtml);
    }

     // 16) Gestion de l'édition des zones : Sauvegarder automatiquement lorsqu'un point est déplacé
     map.on('draw:edited', function(e) {
        e.layers.eachLayer(function(layer) {
            var zoneGeoJSON = layer.toGeoJSON().geometry;
            var commercialId = layer.feature.properties.commercial_id || 0;
            var zoneId = layer.feature.properties.zone_id || 0;
            var regionIds = layer.feature.properties.region_ids || [];
            
            const requestData = {
                action: 'save_zone',
                zone_data: JSON.stringify(zoneGeoJSON),
                commercial_id: commercialId,
                zone_id: zoneId,
                _ajax_nonce: zonifyMapVars.nonce
            };
            
            // Ajouter les régions s'il y en a
            if (regionIds.length > 0) {
                requestData.region_ids = JSON.stringify(regionIds);
            }
            
            fetch(zonifyMapVars.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: new URLSearchParams(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("Zone mise à jour : " + data.data.message);
                    if (data.data.zone_id) {
                        layer.feature.properties.zone_id = data.data.zone_id;
                    }
                } else {
                    console.error("Erreur lors de la mise à jour : " + data.data);
                }
            })
            .catch(error => console.error("Erreur AJAX update_zone:", error));
        });
    });

    console.log("L.Handler.PolylineSnap :", L.Handler.PolylineSnap);
});

jQuery(document).ready(function($) {
    $('#commercial-select').select2({
        placeholder: "Sélectionnez un ou plusieurs commerciaux",
        allowClear: true,
        width: 'resolve'
    });
    
    $('#region-select').select2({
        placeholder: "Sélectionnez une ou plusieurs régions",
        allowClear: true,
        width: 'resolve'
    });
});
