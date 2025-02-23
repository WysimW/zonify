document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([50.5, 2.5], 9);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }).addTo(map);
    
    
    
    
    
    // Définition des styles
    var defaultStyle = { color: '#3388ff', weight: 2 };
    var highlightStyle = { color: 'red', weight: 3 };

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
        fetch(zoneVars.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams({
                action: 'save_zone',
                zone_data: JSON.stringify(geojson.geometry),
                commercial_id: commercialId,
                _ajax_nonce: zoneVars.nonce
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
