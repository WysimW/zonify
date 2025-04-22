/**
 * JavaScript pour les fonctionnalités administratives des panneaux d'affichage
 */
jQuery(document).ready(function($) {
    // Fonction pour calculer la surface automatiquement en m²
    function calculateSurface() {
        var width = parseInt($('#panel_width').val()) || 0;
        var height = parseInt($('#panel_height').val()) || 0;
        
        // Calcul de la surface en m² (convertir de cm² en m²)
        var surface = (width * height) / 10000;
        
        // Arrondir à 2 décimales
        surface = Math.round(surface * 100) / 100;
        
        // Mettre à jour le champ de surface
        $('#panel_surface').val(surface);
    }
    
    // Déclencher le calcul quand largeur ou hauteur change
    $('#panel_width, #panel_height').on('input', function() {
        calculateSurface();
    });
    
    // Bouton pour remplir l'adresse depuis la carte
    $('#locate-panel').on('click', function() {
        var geojson = $('#poi_geojson').val();
        
        if (!geojson) {
            alert('Veuillez d\'abord positionner le panneau sur la carte.');
            return;
        }
        
        try {
            var geoData = JSON.parse(geojson);
            var lat = geoData.coordinates[1];
            var lng = geoData.coordinates[0];
            
            // Appel à l'API de géocodage inverse (OpenStreetMap Nominatim)
            $.ajax({
                url: 'https://nominatim.openstreetmap.org/reverse',
                data: {
                    format: 'json',
                    lat: lat,
                    lon: lng,
                    zoom: 18,
                    addressdetails: 1
                },
                success: function(data) {
                    if (data && data.address) {
                        // Remplir les champs d'adresse avec les données récupérées
                        var address = data.address;
                        
                        // Construire l'adresse complète
                        var fullAddress = [];
                        if (address.road) fullAddress.push(address.road);
                        if (address.house_number) fullAddress.push(address.house_number);
                        
                        $('#panel_address').val(fullAddress.join(' '));
                        $('#panel_postal_code').val(address.postcode || '');
                        $('#panel_city_name').val(address.city || address.town || address.village || '');
                        $('#panel_department').val(address.county || '');
                        $('#panel_region').val(address.state || '');
                    } else {
                        alert('Aucune information d\'adresse trouvée pour cette position.');
                    }
                },
                error: function() {
                    alert('Erreur lors de la récupération de l\'adresse.');
                }
            });
        } catch (e) {
            alert('Erreur: Format GeoJSON invalide.');
        }
    });
});