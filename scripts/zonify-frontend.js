document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les options du Front Office
    var options = zonifyFrontendOptions || {};
    console.log("Options : ", options);

    // Choix du fournisseur de tuiles
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
        // Par défaut "cartodb_light"
        tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
    }

    // Initialisation de la carte
    var zoom = options.map_zoom || 9;
    var centerLat = parseFloat(options.map_center_lat || 50.5);
    var centerLng = parseFloat(options.map_center_lng || 2.5);

    var map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer(tileLayerUrl, { attribution: attribution }).addTo(map);

    // Style par défaut
    var defaultStyle = {
        color: options.zone_border_color || '#3388ff',
        fillColor: options.zone_fill_color || '#3388ff',
        fillOpacity: parseFloat(options.zone_opacity || 0.5),
        weight: 2
    };

    // Chargement des polygones
    L.geoJSON(zonesData, {
        style: defaultStyle,
        onEachFeature: function(feature, layer) {
            layer.on('click', function() {
                // Création du contenu de popup
                var content = '<div class="popup-container" style="font-family:' + options.popup_font_family + '; font-size:' + options.popup_font_size + '; color:' + options.popup_font_color + ';">';
                content += '<h2>' + (feature.properties.nom_commercial || 'Commercial') + '</h2>';
                content += '<p>' + (feature.properties.infos || '') + '</p>';

                // Adresse
                if (feature.properties.address && parseInt(options.popup_show_address) === 1) {
                    content += '<p>Adresse : ' + feature.properties.address + '</p>';
                }
                // Horaires
                if (feature.properties.opening_hours && parseInt(options.popup_show_hours) === 1) {
                    content += '<p>Horaires : ' + feature.properties.opening_hours + '</p>';
                }
                // Réseaux sociaux
                if (feature.properties.social_links && parseInt(options.popup_show_social) === 1) {
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
                // Email
                if (feature.properties.email && parseInt(options.popup_enable_email_btn) === 1) {
                    content += '<p>Email : <a href="mailto:' + feature.properties.email + '">' + feature.properties.email + '</a></p>';
                }
                // Téléphone
                if (feature.properties.telephone && parseInt(options.popup_enable_phone_btn) === 1) {
                    content += '<p>Téléphone : <a href="tel:' + feature.properties.telephone + '">' + feature.properties.telephone + '</a></p>';
                }
                // Bouton contact
                if (parseInt(options.popup_enable_contact_btn) === 1) {
                    content += '<p><a href="/contact" class="btn-contact">Contacter</a></p>';
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
