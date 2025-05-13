document.addEventListener('DOMContentLoaded', function() {
    // Gestionnaires d'événements pour les filtres
    var applyFilterBtn = document.getElementById('apply-filter');
    var resetFilterBtn = document.getElementById('reset-filter');

    // Fonction pour récupérer les valeurs des filtres
    function getFilterValues() {
        return {
            search: document.getElementById('search-filter').value,
            department: document.getElementById('department-filter').value,
            centerCity: document.getElementById('center-city-filter').value,
            radiusKm: document.getElementById('radius-km-filter').value,
            type: document.getElementById('type-filter').value,
            support: document.getElementById('support-filter').value,
            format: document.getElementById('format-filter').value
        };
    }

    // Fonction pour filtrer les données
    function filterAndRenderData(filters) {
        console.log("Filtrage des données avec:", filters);
        
        // Filtrer les panneaux selon les critères
        var filteredPanneaux = window.panneauxData.filter(function(panneau) {
            // Filtre de recherche
            if (filters.search && !panneau.properties.title.toLowerCase().includes(filters.search.toLowerCase()) &&
                !panneau.properties.reference.toLowerCase().includes(filters.search.toLowerCase()) &&
                !panneau.properties.address.toLowerCase().includes(filters.search.toLowerCase())) {
                return false;
            }

            // Filtre département
            if (filters.department && panneau.properties.department !== filters.department) {
                return false;
            }

            // Filtre type
            if (filters.type && panneau.properties.panel_type !== filters.type) {
                return false;
            }

            // Filtre support
            if (filters.support && panneau.properties.panel_support !== filters.support) {
                return false;
            }

            // Filtre format
            if (filters.format && panneau.properties.panel_format !== filters.format) {
                return false;
            }

            return true;
        });

        // Mettre à jour l'affichage
        updateResultsList(filteredPanneaux);
        updateMapMarkers(filteredPanneaux);
    }

    // Fonction pour mettre à jour les marqueurs sur la carte
    function updateMapMarkers(panneaux) {
        if (!window.map) return;

        // Supprimer tous les marqueurs existants
        if (window.markers) {
            window.markers.forEach(function(marker) {
                window.map.removeLayer(marker);
            });
        }
        window.markers = [];

        // Ajouter les nouveaux marqueurs
        panneaux.forEach(function(panneau) {
            var marker = L.marker([panneau.geometry.coordinates[1], panneau.geometry.coordinates[0]])
                .bindPopup(createPopupContent(panneau));
            marker.addTo(window.map);
            window.markers.push(marker);
        });
    }

    // Fonction pour créer le contenu du popup
    function createPopupContent(panneau) {
        return `
            <div class="popup-content">
                <h3>${panneau.properties.title}</h3>
                <p><strong>Référence:</strong> ${panneau.properties.reference}</p>
                <p><strong>Adresse:</strong> ${panneau.properties.address}</p>
                <p><strong>Type:</strong> ${panneau.properties.panel_type}</p>
                <p><strong>Format:</strong> ${panneau.properties.panel_format}</p>
            </div>
        `;
    }

    // Fonction pour mettre à jour la liste des résultats
    function updateResultsList(panneaux) {
        var resultsList = document.getElementById('results-list');
        var resultsCount = document.getElementById('results-count');
        
        if (!resultsList || !resultsCount) return;

        resultsCount.textContent = `(${panneaux.length})`;

        if (panneaux.length === 0) {
            resultsList.innerHTML = '<p class="no-results">Aucun résultat ne correspond à vos critères.</p>';
            return;
        }

        var html = '';
        panneaux.forEach(function(panneau) {
            html += `
                <div class="result-item">
                    <h3>${panneau.properties.title}</h3>
                    <p><strong>Référence:</strong> ${panneau.properties.reference}</p>
                    <p><strong>Adresse:</strong> ${panneau.properties.address}</p>
                    <p><strong>Type:</strong> ${panneau.properties.panel_type}</p>
                    <p><strong>Format:</strong> ${panneau.properties.panel_format}</p>
                </div>
            `;
        });

        resultsList.innerHTML = html;
    }

    // Gestionnaire pour le bouton Appliquer
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            var filters = getFilterValues();
            filterAndRenderData(filters);
        });
    }

    // Gestionnaire pour le bouton Réinitialiser
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            // Réinitialiser tous les champs de filtre
            document.getElementById('search-filter').value = '';
            document.getElementById('department-filter').value = '';
            document.getElementById('center-city-filter').value = '';
            document.getElementById('radius-km-filter').value = '';
            document.getElementById('type-filter').value = '';
            document.getElementById('support-filter').value = '';
            document.getElementById('format-filter').value = '';

            // Réinitialiser Select2 si présent
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                jQuery('.select2-filter').val(null).trigger('change');
            }

            // Réafficher toutes les données
            filterAndRenderData({});
        });
    }

    // Initialiser l'affichage avec toutes les données
    if (window.panneauxData) {
        filterAndRenderData({});
    }
}); 