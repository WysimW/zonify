document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les options du Front Office
    var options = zonifyFrontendOptions || {};
    console.log(options);
    var provider = options.tile_provider || 'cartodb_light';
    var tileLayerUrl, attribution;
    
    if (provider === 'cartodb_dark'){
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    } else if (provider === 'osm'){
        tileLayerUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = '© OpenStreetMap contributors';
    } else if (provider === 'custom'){
        tileLayerUrl = options.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = 'Personnalisé';
    } else {
        // Par défaut, "cartodb_light"
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    }
    
    // Utiliser les autres options pour la carte
    var zoom = options.map_zoom;
    var centerLat = parseFloat(options.map_center_lat);
    var centerLng = parseFloat(options.map_center_lng);
    
    // Initialiser la carte
    var map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);
        
    // Définition du style par défaut pour les zones, en utilisant vos options
    var defaultStyle = {
        color: options.zone_border_color,
        fillColor: options.zone_fill_color,
        fillOpacity: options.zone_opacity,
        weight: 2
    };
    
    // Ajout des zones à partir du GeoJSON injecté
    L.geoJSON(zonesData, {
        style: defaultStyle,
        onEachFeature: function(feature, layer) {
            layer.on('click', function() {
                // Construction du contenu de la popup
                var content = '<div class="popup-container" style="font-family:' + options.popup_font_family + '; font-size:' + options.popup_font_size + '; color:' + options.popup_font_color + ';">' +
                              '<h2>' + feature.properties.nom_commercial + '</h2>' +
                              '<p>' + feature.properties.infos + '</p>';
                
                if (feature.properties.address && options.popup_show_address==1) {
                    content += '<p>Adresse : ' + feature.properties.address + '</p>';
                }
                if (feature.properties.opening_hours && options.popup_show_hours==1) {
                    content += '<p>Horaires : ' + feature.properties.opening_hours + '</p>';
                }
                if (feature.properties.social_links && options.popup_show_social==1) {
                    var links = feature.properties.social_links.split(',');
                    content += '<p>Réseaux sociaux : ';
                    links.forEach(function(link) {
                        var trimmed = link.trim();
                        if (trimmed) {
                            content += '<a href="' + trimmed + '" target="_blank">' + trimmed + '</a> ';
                        }
                    });
                    content += '</p>';
                }
                
                // Utiliser les options pour afficher ou non l'e-mail
                if (feature.properties.email && options.popup_enable_email_btn==1) {
                    content += '<p>Email : <a href="mailto:' + feature.properties.email + '">' + feature.properties.email + '</a></p>';
                }
                // Utiliser les options pour afficher ou non le téléphone
                if (feature.properties.telephone && options.popup_enable_phone_btn==1) {
                    content += '<p>Téléphone : <a href="tel:' + feature.properties.telephone + '">' + feature.properties.telephone + '</a></p>';
                }
                if (options.popup_enable_contact_btn) {
                    content += '<a href="/contact" class="btn-contact">Contacter</a>';
                }
                
                if (options.popup_enable_contact_btn==1) {
                    content += '<a href="/contact" class="btn-contact">Contacter</a>';
                }
                content += '</div>';
                
                L.popup()
                 .setLatLng(layer.getBounds().getCenter())
                 .setContent(content)
                 .openOn(map);
            });
        }
    }).addTo(map);
});
