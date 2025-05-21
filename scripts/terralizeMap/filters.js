// Gestion des filtres de la carte
export function setupFilters(map, options) {
    const mapId = options.map_id;
    // Initialiser Select2 pour le filtre de catégories
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('#categoryFilter-' + mapId).select2({
            placeholder: "Sélectionnez une ou plusieurs catégories",
            allowClear: true,
            theme: 'classic',
            width: '100%',
            closeOnSelect: false,
            language: {
                noResults: function() { return "Aucune catégorie trouvée"; },
                searching: function() { return "Recherche..."; }
            }
        });
    }
    // Gestionnaires d'événements pour les boutons de filtres
    const applyFilterBtn = document.getElementById('applyFilter-' + mapId);
    const resetFilterBtn = document.getElementById('resetFilter-' + mapId);
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            let selectedCategories = [];
            if (typeof jQuery !== 'undefined') {
                if (jQuery('#categoryFilter-' + mapId).length) {
                    selectedCategories = jQuery('#categoryFilter-' + mapId).val() || [];
                }
            }
            const regionFilter = document.getElementById('region-filter-' + mapId) ? document.getElementById('region-filter-' + mapId).value : '';
            const typeFilter = document.getElementById('type-filter-' + mapId) ? document.getElementById('type-filter-' + mapId).value : 'all';
            const searchQuery = document.getElementById('search-filter-' + mapId) ? document.getElementById('search-filter-' + mapId).value : '';
            
            console.log("Filtres appliqués:", {
                categories: selectedCategories,
                region: regionFilter,
                type: typeFilter,
                searchQuery: searchQuery
            });
            
            // Utiliser la fonction exposée sur le namespace global
            window.terralizemap.filterData(mapId, {
                categories: selectedCategories,
                region: regionFilter,
                type: typeFilter,
                searchQuery: searchQuery
            });
        });
    }
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            if (typeof jQuery !== 'undefined') {
                if (jQuery('#categoryFilter-' + mapId).length) {
                    jQuery('#categoryFilter-' + mapId).val(null).trigger('change');
                }
            }
            if (document.getElementById('region-filter-' + mapId)) {
                document.getElementById('region-filter-' + mapId).value = '';
            }
            if (document.getElementById('type-filter-' + mapId)) {
                document.getElementById('type-filter-' + mapId).value = 'all';
            }
            if (document.getElementById('search-filter-' + mapId)) {
                document.getElementById('search-filter-' + mapId).value = '';
            }
            
            console.log("Filtres réinitialisés");
            
            // Réinitialiser la carte et les résultats via resetFilters
            window.terralizemap.resetFilters(mapId);
        });
    }
    
    // Compatibilité: exposer la fonction de filtrage sur le namespace global
    window.terralizemap.filterAndRenderData = function(selectedCategories = [], regionFilter = '', typeFilter = 'all', searchQuery = '') {
        // Cette fonction est maintenue pour la rétrocompatibilité
        console.log("Utilisation de filterAndRenderData via API publique");
        
        const event = new CustomEvent('terralize:filter_data', {
            detail: {
                mapId: mapId,
                filters: {
                    categories: selectedCategories,
                    region: regionFilter,
                    type: typeFilter,
                    searchQuery: searchQuery
                }
            }
        });
        document.dispatchEvent(event);
    };
} 