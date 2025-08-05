// Gestion de la géolocalisation utilisateur
export function setupUserLocation(map, options) {
    const mapId = options.map_id;
    // Ajout du contrôle de géolocalisation Leaflet
    L.control.locate({
        position: 'topleft',
        strings: {
            title: "Me localiser",
            popup: "Vous êtes ici",
            outsideMapBoundsMsg: "Vous semblez être en dehors des limites de la carte"
        },
        locateOptions: {
            maxZoom: 16,
            enableHighAccuracy: true,
            setView: true,
            watch: false
        },
        // Utiliser l'icône marker de Font Awesome (compatible avec Bricks)
        icon: 'fas fa-map-marker-alt',
        onLocationFound: function(e) {
            showUserLocation(map, e);
        }
    }).addTo(map);

    // Style CSS personnalisé pour l'icône de géolocalisation
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        .user-location-icon {
            background-image: url('/wp-content/plugins/zone-commercial-pluginwp/assets/svg/location-pin-svgrepo-com.svg');
            background-size: 20px 20px;
            background-repeat: no-repeat;
            background-position: center;
            width: 30px;
            height: 30px;
        }
    `;
    document.head.appendChild(styleElement);

    // Gestionnaire pour le bouton de localisation personnalisé dans la sidebar
    const locateMeBtn = document.getElementById('locateMe-' + mapId);
    if (locateMeBtn) {
        locateMeBtn.addEventListener('click', function() {
            window.terralizemap.locateUser(mapId);
        });
    }
    // Gestionnaire pour le bouton "Me localiser" de la sidebar des filtres
    const locateMeBtnFilter = document.getElementById('locate-me-btn-' + mapId);
    if (locateMeBtnFilter) {
        locateMeBtnFilter.addEventListener('click', function() {
            window.terralizemap.locateUser(mapId);
        });
    }
    // Capture l'événement pour appliquer le filtre géographique
    const applyGeoFilterBtn = document.getElementById('apply-geo-filter-' + mapId);
    if (applyGeoFilterBtn) {
        applyGeoFilterBtn.addEventListener('click', function() {
            if (!window.userLatLng) {
                alert("Veuillez d'abord vous localiser.");
                return;
            }
            const radiusSelect = document.getElementById('geo-radius-filter-' + mapId);
            const radius = parseInt(radiusSelect.value, 10);
            filterByDistance(map, options, window.userLatLng, radius * 1000);
        });
    }
    // Exposer locateUser sur le namespace global
    window.terralizemap.locateUser = function(targetMapId, latitude, longitude) {
        const targetMap = window.maps[targetMapId];
        if (!targetMap) {
            console.error("Carte non trouvée:", targetMapId);
            return false;
        }
        if (latitude && longitude) {
            // S'assurer que les coordonnées sont numériques
            const lat = parseFloat(latitude);
            const lng = parseFloat(longitude);
            
            if (isNaN(lat) || isNaN(lng)) {
                console.error("Coordonnées invalides:", latitude, longitude);
                return false;
            }
            
            const latlng = L.latLng(lat, lng);
            showUserLocation(targetMap, {
                latlng: latlng,
                accuracy: 100
            });
            return true;
        }
        
        // Configuration standard pour la géolocalisation
        targetMap.locate({ 
            setView: true, 
            maxZoom: 16, 
            enableHighAccuracy: true,
            watch: false
        });
        
        targetMap.once('locationfound', function(e) {
            console.log("Localisation trouvée:", e.latlng);
            showUserLocation(targetMap, e);
        });
        
        targetMap.once('locationerror', function(e) {
            console.error("Erreur de localisation:", e);
            alert("Impossible de vous localiser : " + e.message);
        });
        
        return true;
    };
    // Exposer filterByDistance sur le namespace global
    window.terralizemap.filterByDistance = function(center, radiusMeters) {
        if (!center || typeof radiusMeters !== 'number') {
            console.error("Paramètres invalides pour filterByDistance", center, radiusMeters);
            return false;
        }
        // Utilise la première carte si plusieurs
        const mapIds = Object.keys(window.maps || {});
        const activeMapId = mapIds.length > 0 ? mapIds[0] : null;
        const map = window.maps[activeMapId];
        if (!map) return false;
        filterByDistance(map, options, center, radiusMeters);
        return true;
    };
    // Gestion du bouton "POI le plus proche"
    const findNearestPoiBtn = document.getElementById('findNearestPoi-' + mapId);
    if (findNearestPoiBtn) {
        findNearestPoiBtn.addEventListener('click', function() {
            if (!window.userLatLng) {
                map.locate({ setView: true, maxZoom: 16, enableHighAccuracy: true });
                map.once('locationfound', function(e) {
                    window.userLatLng = e.latlng;
                    findAndDisplayNearestPOI(map, options, window.userLatLng);
                });
                map.once('locationerror', function(e) {
                    alert("Impossible de vous localiser : " + e.message);
                });
            } else {
                findAndDisplayNearestPOI(map, options, window.userLatLng);
            }
        });
    }
}

function showUserLocation(targetMap, locationEvent) {
    console.log("Affichage de la position utilisateur:", locationEvent);
    
    // Vérifier que locationEvent.latlng existe et est valide
    if (!locationEvent.latlng || 
        typeof locationEvent.latlng.lat === 'undefined' || 
        typeof locationEvent.latlng.lng === 'undefined') {
        console.error("Coordonnées invalides:", locationEvent);
        return null;
    }
    
    const radius = locationEvent.accuracy ? locationEvent.accuracy / 2 : 100;
    const userLatLng = locationEvent.latlng;
    
    // Stocker la position pour utilisation ultérieure
    window.userLatLng = userLatLng;
    
    console.log("Position utilisateur:", userLatLng.lat, userLatLng.lng);
    
    // Supprimer les marqueurs précédents si existants
    if (window.userLocationMarker) {
        targetMap.removeLayer(window.userLocationMarker);
    }
    if (window.userAccuracyCircle) {
        targetMap.removeLayer(window.userAccuracyCircle);
    }
    
    // Créer le marqueur de position utilisateur
    window.userLocationMarker = L.marker(userLatLng, {
        icon: L.icon({
            iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/location-pin-svgrepo-com.svg',
            iconSize: [32, 32],
            iconAnchor: [16, 32], // Pointe inférieure de l'icône
            popupAnchor: [0, -32], // Centre de la popup au-dessus de l'icône
            className: 'user-location-marker'
        })
    }).addTo(targetMap)
    .bindPopup("Vous êtes ici (précision de " + Math.round(radius) + " mètres)")
    .openPopup();
    
    // Cercle de précision
    window.userAccuracyCircle = L.circle(userLatLng, {
        radius: radius,
        color: '#4285F4',
        fillColor: '#4285F4',
        fillOpacity: 0.15
    }).addTo(targetMap);
    
    // Centrer la carte sur la position
    targetMap.setView(userLatLng, 14);
    
    // Supprimer les marqueurs après un délai
    setTimeout(function() {
        if (window.userLocationMarker) {
            targetMap.removeLayer(window.userLocationMarker);
            window.userLocationMarker = null;
        }
        if (window.userAccuracyCircle) {
            targetMap.removeLayer(window.userAccuracyCircle);
            window.userAccuracyCircle = null;
        }
    }, 30000);
    
    // Déclencher un événement personnalisé
    const event = new CustomEvent('terralize:user_located', {
        detail: {
            mapId: targetMap._container.id,
            latitude: userLatLng.lat,
            longitude: userLatLng.lng,
            accuracy: radius
        }
    });
    document.dispatchEvent(event);
    
    return userLatLng;
}

function filterByDistance(map, options, center, radiusMeters) {
    const mapId = options.map_id;
    const allZonesData = window.terralizemap.state.allZonesData[mapId] || [];
    let withinRadius = [];
    allZonesData.forEach(function(feature) {
        let coordinates;
        const isPoint = feature.geometry.type === 'Point';
        if (isPoint) {
            coordinates = L.latLng(
                feature.geometry.coordinates[1],
                feature.geometry.coordinates[0]
            );
            const distance = center.distanceTo(coordinates);
            if (distance <= radiusMeters) {
                withinRadius.push(feature);
            }
        } else if (feature.geometry.type === 'Polygon') {
            let inRadius = false;
            feature.geometry.coordinates[0].forEach(function(coord) {
                const point = L.latLng(coord[1], coord[0]);
                const distance = center.distanceTo(point);
                if (distance <= radiusMeters) {
                    inRadius = true;
                }
            });
            if (inRadius) {
                withinRadius.push(feature);
            }
        }
    });
    if (window.radiusCircle) {
        map.removeLayer(window.radiusCircle);
    }
    window.radiusCircle = L.circle(center, {
        radius: radiusMeters,
        color: '#ff7800',
        fillColor: '#ff7800',
        fillOpacity: 0.2,
        weight: 1
    }).addTo(map);
    map.fitBounds(window.radiusCircle.getBounds());
    // Mettre à jour les résultats (fonction utilitaire à déplacer si besoin)
    if (typeof updateResultsCount === 'function') {
        updateResultsCount(withinRadius.length);
    }
    // Appliquer le filtre (fonction à brancher selon l'architecture)
    // ...
}

function findAndDisplayNearestPOI(map, options, userLatLng) {
    if (window.nearestMarker) {
        map.removeLayer(window.nearestMarker);
    }
    const nearestPOI = findNearestPOI(map, options, userLatLng);
    if (nearestPOI) {
        window.nearestMarker = L.marker(nearestPOI.latlng, {
            icon: L.icon({
                iconUrl: '/wp-content/plugins/zone-commercial-pluginwp/assets/svg/poi.png',
                iconSize: [30, 40],
                iconAnchor: [15, 40],
                popupAnchor: [0, -35],
                className: 'nearest-poi-marker'
            })
        }).addTo(map)
        .bindPopup(`
            <div class="nearest-poi-popup">
                <h3>${nearestPOI.properties.title}</h3>
                <p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg> Distance: ${Math.round(nearestPOI.distance)} mètres</p>
                <button class="get-directions-btn" data-lat="${userLatLng.lat}" data-lng="${userLatLng.lng}" data-poi-lat="${nearestPOI.latlng.lat}" data-poi-lng="${nearestPOI.latlng.lng}">
                    Calculer l'itinéraire
                </button>
            </div>
        `).openPopup();
        const bounds = L.latLngBounds([userLatLng, nearestPOI.latlng]);
        map.fitBounds(bounds, { padding: [50, 50] });
        document.querySelector('.get-directions-btn').addEventListener('click', function() {
            const startLat = this.getAttribute('data-lat');
            const startLng = this.getAttribute('data-lng');
            const endLat = this.getAttribute('data-poi-lat');
            const endLng = this.getAttribute('data-poi-lng');
            window.open(`https://www.google.com/maps/dir/?api=1&origin=${startLat},${startLng}&destination=${endLat},${endLng}&travelmode=driving`);
        });
        setTimeout(function() {
            if (window.nearestMarker) {
                map.removeLayer(window.nearestMarker);
                window.nearestMarker = null;
            }
        }, 30000);
    } else {
        alert("Aucun point d'intérêt n'a été trouvé à proximité.");
    }
}

function findNearestPOI(map, options, userLatLng) {
    const mapId = options.map_id;
    const allZonesData = window.terralizemap.state.allZonesData[mapId] || [];
    let nearestPOI = null;
    let minDistance = Infinity;
    allZonesData.forEach(function(feature) {
        if (feature.properties.type === 'poi' && feature.geometry.type === 'Point') {
            const poiLatLng = L.latLng(
                feature.geometry.coordinates[1],
                feature.geometry.coordinates[0]
            );
            const distance = userLatLng.distanceTo(poiLatLng);
            if (distance < minDistance) {
                minDistance = distance;
                nearestPOI = {
                    ...feature,
                    latlng: poiLatLng,
                    distance: distance
                };
            }
        }
    });
    return nearestPOI;
} 