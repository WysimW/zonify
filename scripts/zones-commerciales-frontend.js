document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de la carte côté front
    var map = L.map('map').setView([46.603354, 1.888334], 6);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Ajout des zones à partir du GeoJSON injecté
    L.geoJSON(zonesData, {
        onEachFeature: function(feature, layer) {
            layer.on('click', function() {
                // Création d'un contenu HTML personnalisé pour la popup
                var content = '<div class="popup-container">' +
                                '<h2>' + feature.properties.nom_commercial + '</h2>' +
                                '<p>' + feature.properties.infos + '</p>' +
                                (feature.properties.email ? '<p>Email : <a href="mailto:' + feature.properties.email + '">' + feature.properties.email + '</a></p>' : '') +
                                (feature.properties.telephone ? '<p>Téléphone : <a href="tel:' + feature.properties.telephone + '">' + feature.properties.telephone + '</a></p>' : '') +
                                '<a href="/contact" class="btn-contact">Contacter</a>' +
                              '</div>';
                L.popup()
                 .setLatLng(layer.getBounds().getCenter())
                 .setContent(content)
                 .openOn(map);
            });
        }
    }).addTo(map);
});
