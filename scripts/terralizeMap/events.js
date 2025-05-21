// Gestion des événements personnalisés (CustomEvent)
export function setupEvents(map, options) {
    const mapId = options.map_id;
    // Exposer filterData sur le namespace global
    window.terralizemap.filterData = function(targetMapId, filterOptions) {
        if (!window.maps || !window.maps[targetMapId]) {
            console.error("Carte non trouvée pour filterData:", targetMapId);
            return false;
        }
        filterOptions = filterOptions || {};
        const currentFilters = window.terralizemap.state.currentFilters[targetMapId] || {
            categories: [], region: '', type: 'all', searchQuery: ''
        };
        const selectedCategories = filterOptions.categories !== undefined ? filterOptions.categories : currentFilters.categories;
        const regionFilter = filterOptions.region !== undefined ? filterOptions.region : currentFilters.region;
        const typeFilter = filterOptions.type !== undefined ? filterOptions.type : currentFilters.type;
        const searchQuery = filterOptions.searchQuery !== undefined ? filterOptions.searchQuery : currentFilters.searchQuery;
        const event = new CustomEvent('terralize:filter_data', {
            detail: {
                mapId: targetMapId,
                filters: {
                    categories: selectedCategories,
                    region: regionFilter,
                    type: typeFilter,
                    searchQuery: searchQuery
                }
            }
        });
        document.dispatchEvent(event);
        return true;
    };
    // Exposer resetFilters sur le namespace global
    window.terralizemap.resetFilters = function(targetMapId) {
        if (!window.maps || !window.maps[targetMapId]) {
            console.error("Carte non trouvée pour resetFilters:", targetMapId);
            return false;
        }
        window.terralizemap.state.currentFilters[targetMapId] = {
            categories: [], region: '', type: 'all', searchQuery: ''
        };
        const event = new CustomEvent('terralize:reset_filters', {
            detail: { mapId: targetMapId }
        });
        document.dispatchEvent(event);
        return true;
    };
    // Écouter l'événement de reset pour relancer le filtrage et réinitialiser la vue
    document.addEventListener('terralize:reset_filters', function(e) {
        if (!e.detail || !e.detail.mapId) return;
        const mapId = e.detail.mapId;
        const map = window.maps[mapId];
        if (!map) {
            console.error("Carte non trouvée pour l'événement reset_filters:", mapId);
            return;
        }
        window.terralizemap.state.currentFilters[mapId] = {
            categories: [], region: '', type: 'all', searchQuery: ''
        };
        // Relancer le filtrage
        const event = new CustomEvent('terralize:filter_data', {
            detail: {
                mapId: mapId,
                filters: window.terralizemap.state.currentFilters[mapId]
            }
        });
        document.dispatchEvent(event);
        // Réinitialiser la vue
        const centerLat = parseFloat(options.map_center_lat || 50.5);
        const centerLng = parseFloat(options.map_center_lng || 2.5);
        const zoom = parseInt(options.map_zoom || 9);
        map.setView([centerLat, centerLng], zoom);
    });
} 