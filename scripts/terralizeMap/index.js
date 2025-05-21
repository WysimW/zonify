// Point d'entrée du module terralizeMap
// On importe et initialise les sous-modules ici
console.log("CHARGEMENT INDEX: Import des modules...");

import { setupEvents } from './events.js';
import { setupUI } from './ui.js';
import { setupResultsList } from './results-list.js';
import { setupFilters } from './filters.js';
import { setupUserLocation } from './user-location.js';
import { initMap } from './map-init.js';
import { setupPopups } from './popups.js';

console.log("MODULES IMPORTÉS AVEC SUCCÈS");

/**
 * Point d'entrée principal pour initialiser la carte Terralize
 * @param {Object} options - Options de configuration de la carte
 */
export function initTerralizeMap(options) {
    const mapId = options.map_id;
    if (!mapId) {
        console.error("Erreur: ID de carte non défini");
        return;
    }
    
    console.log("Initialisation de la carte Terralize avec ID:", mapId);
    
    try {
        // 1. Initialiser la carte de base
        const map = initMap(options);
        
        // Stocker la référence à la carte
        window.maps = window.maps || {};
        window.maps[mapId] = map;
        
        // 2. Configurer l'interface utilisateur
        setupUI(map, options);
        
        // 3. Configurer les gestionnaires d'événements
        setupEvents(map, options);
        
        // 4. Initialiser la liste des résultats
        setupResultsList(map, options);
        
        // 5. Configurer les popups pour la carte
        setupPopups(map, options);
        
        // 6. Initialiser les filtres
        setupFilters(map, options);
        
        // 7. Initialiser les fonctionnalités de géolocalisation
        setupUserLocation(map, options);
        
        // Exposer l'API publique
        window.terralizemap = window.terralizemap || {};
        
        // Fonction pour obtenir l'instance de la carte
        window.terralizemap.getMapInstance = function(mapId) {
            return window.maps && window.maps[mapId];
        };
        
        console.log("Carte Terralize initialisée avec succès");
        
        return map;
    } catch (error) {
        console.error("Erreur lors de l'initialisation de la carte:", error);
        throw error;
    }
}

// Pour compatibilité globale (si besoin)
window.terralizeMap = window.terralizeMap || {};
window.terralizeMap.init = initTerralizeMap;
console.log("EXPORT initTerralizeMap terminé"); 