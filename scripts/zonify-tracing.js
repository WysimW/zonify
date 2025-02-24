document.addEventListener('DOMContentLoaded', function() {

    // Récupération du thème via zonifyMapVars.tile_provider par exemple
    var provider = zonifyMapVars.tile_provider || 'cartodb_light';
    var tileLayerUrl, attribution;
    
    // Définir l'URL des tuiles et l'attribution en fonction du fournisseur
    if (provider === 'cartodb_dark'){
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    } else if (provider === 'osm'){
        tileLayerUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = '© OpenStreetMap contributors';
    } else if (provider === 'custom'){
        // Pour l'option custom, on suppose que l'URL personnalisée a été renseignée
        tileLayerUrl = zonifyMapVars.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = 'Personnalisé';
    } else {
        // Par défaut, "cartodb_light"
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    }
    
    // Utilisation des autres options pour configurer la carte
    var zoom = zonifyMapVars.map_zoom;
    var centerLat = parseFloat(zonifyMapVars.map_center_lat);
    var centerLng = parseFloat(zonifyMapVars.map_center_lng);
    
    var map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);
    
    // Exemple d'utilisation des couleurs et opacité dans un style par défaut pour les zones
    var defaultStyle = {
        color: zonifyMapVars.zone_border_color,
        fillColor: zonifyMapVars.zone_fill_color,
        fillOpacity: zonifyMapVars.zone_opacity,
        weight: 2
    };

    // Groupe pour stocker les zones (pour édition éventuelle)
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);
    
    // Charger toutes les zones actives et les afficher avec le style par défaut
    var allZonesLayer = L.geoJSON(zonesAdminData, {
        style: defaultStyle,
        onEachFeature: function(feature, layer) {
            layer.bindPopup('<strong>' + feature.properties.commercial_title + '</strong>');
            drawnItems.addLayer(layer);
        }
    }).addTo(map);
    
    // Lorsqu'un commercial est sélectionné, mettre en surbrillance sa zone (s'il existe)
    document.getElementById('commercial-select').addEventListener('change', function(){
        var selectedId = this.value;
        // Parcourir chaque couche dans allZonesLayer pour mettre à jour son style
        allZonesLayer.eachLayer(function(layer) {
            if (layer.feature.properties.commercial_id == selectedId) {
                layer.setStyle(highlightStyle);
                // Optionnel : centrer la carte sur la zone sélectionnée
                map.fitBounds(layer.getBounds());
            } else {
                layer.setStyle(defaultStyle);
            }
        });
    });
    
    // Gestion de dessin d'une nouvelle zone (pour le commercial actuellement sélectionné)
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
    
    // Lorsqu'une nouvelle zone est dessinée, l'ajouter au groupe
    map.on(L.Draw.Event.CREATED, function (event) {
        var layer = event.layer;
        drawnItems.addLayer(layer);
    });
    
    // Bouton pour sauvegarder/modifier la zone du commercial sélectionné
    document.getElementById('save-zone').addEventListener('click', function(){
        var commercialSelect = document.getElementById('commercial-select');
        var commercialId = commercialSelect ? commercialSelect.value : '';
        if (!commercialId) {
            alert("Veuillez sélectionner un commercial.");
            return;
        }
        var layers = drawnItems.getLayers();
        if(layers.length === 0) {
           alert("Aucune zone dessinée pour sauvegarder.");
           return;
        }
        // Ici, on suppose qu'il n'y a qu'une zone par commercial.
        // Vous pouvez adapter la logique si nécessaire.
        var layer = layers[0];
        var geojson = layer.toGeoJSON();
        fetch(zonifyMapVars.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams({
                action: 'save_zone',
                zone_data: JSON.stringify(geojson.geometry),
                commercial_id: commercialId,
                _ajax_nonce: zonifyMapVars.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                console.log('Zone mise à jour avec succès');
                alert("Zone mise à jour avec succès.");
            } else {
                console.error('Erreur lors de la mise à jour de la zone :', data.data);
                alert("Erreur lors de la mise à jour de la zone.");
            }
        })
        .catch(error => console.error('Erreur AJAX :', error));
    });
});
