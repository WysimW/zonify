// Initialisation de la carte Leaflet
export function initMap(options) {
    console.log("INIT_MAP - DÉBUT avec options:", {
        mapId: options.map_id,
        provider: options.tile_provider || 'cartodb_light'
    });
    
    try {
        // 1) Choix du provider de tuiles
        const provider = options.tile_provider || 'cartodb_light';
        let tileLayerUrl, attribution;

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
            tileLayerUrl = options.tile_custom_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            attribution = 'Personnalisé';
        } else {
            tileLayerUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
            attribution = '&copy; OpenStreetMap contributors &copy; CARTO';
        }

        // 2) Initialisation de la carte
        const mapId = options.map_id;
        const zoom = options.map_zoom || 9;
        const centerLat = parseFloat(options.map_center_lat || 50.5);
        const centerLng = parseFloat(options.map_center_lng || 2.5);

        console.log("INIT_MAP - Création de la carte Leaflet:", {
            mapId: mapId,
            element: document.getElementById(mapId) ? "OK" : "NON TROUVÉ",
            centerLat: centerLat,
            centerLng: centerLng,
            zoom: zoom
        });
        
        // VÉRIFICATION CRITIQUE: L'élément DOM existe-t-il?
        const mapElement = document.getElementById(mapId);
        if (!mapElement) {
            console.error(`ERREUR CRITIQUE: Élément DOM avec id=${mapId} introuvable!`);
            throw new Error(`Élément DOM avec id=${mapId} introuvable!`);
        }

        const map = L.map(mapId).setView([centerLat, centerLng], zoom);
        L.tileLayer(tileLayerUrl, { attribution }).addTo(map);
        
        console.log("INIT_MAP - Tuiles ajoutées, correction taille carte...");

        // Correction du problème de carte grise en forçant un invalidateSize après chargement
        setTimeout(() => {
            map.invalidateSize(true);
            console.log("INIT_MAP - invalidateSize appelé");
        }, 300);

        // Stocker l'instance de carte dans window.maps pour y accéder ailleurs
        if (!window.maps) window.maps = {};
        window.maps[mapId] = map;
        console.log("INIT_MAP - Carte stockée dans window.maps");

        // Exposer une fonction pour obtenir l'instance de carte
        window.terralizemap = window.terralizemap || {};
        window.terralizemap.getMapInstance = function(id) {
            return window.maps[id] || null;
        };
        
        console.log("INIT_MAP - TERMINÉ avec succès");
        return map;
    } catch (error) {
        console.error("ERREUR DANS INIT_MAP:", error);
        throw error; // Relancer l'erreur
    }
} 