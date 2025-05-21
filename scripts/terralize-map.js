/**
 * Tracteur Zone - Script de gestion de la carte pour le shortcode
 * Ce script initialise et gère une carte Leaflet pour afficher les zones commerciales et points d'intérêt
 */

// Nouvelle version modulaire de la carte Terralize
// Toute la logique est désormais répartie dans le dossier terralizeMap/
console.log("DÉMARRAGE DU MODULE terralizeMap");

// Importer le module principal (doit être au niveau supérieur du module)
import { initTerralizeMap } from './terralizeMap/index.js';
console.log("Import réussi!");

try {
    // Initialiser le namespace global avant tout import
    console.log("Initialisation du namespace window.terralizemap");
    window.terralizemap = window.terralizemap || {};
    
    // Variables globales pour stocker les données et états entre les appels de fonctions
    window.terralizemap.state = {
        allZonesData: {},       // Stockage par ID de carte
        displayedResults: {},   // Stockage par ID de carte
        currentFilters: {},     // Filtres actuels par ID de carte
        userLatLng: null,       // Position de l'utilisateur (commune à toutes les cartes)
        radiusCircle: null      // Cercle de rayon (par carte)
    };
    
    // Exemple d'initialisation (à adapter selon ton intégration WordPress)
    document.addEventListener('DOMContentLoaded', function() {
        console.log("DOM CHARGÉ - Initialisation de la carte");
        
        // Récupérer les options nécessaires depuis le contexte global WP
        const mapId = typeof tracteurZoneMapId !== 'undefined' ? tracteurZoneMapId : '';
        if (!mapId) {
            console.error("Erreur: tracteurZoneMapId est undefined");
            return;
        }
        console.log("MapID trouvé:", mapId);
        
        // DEBUGGING: Vérifier les données
        console.log("DEBUG zonesData:", typeof zonesData, Array.isArray(zonesData) ? zonesData.length : "N/A");
        console.log("DEBUG premier élément:", zonesData && zonesData.length > 0 ? zonesData[0] : "Aucun élément");
        
        const options = typeof terralizeFrontendOptions !== 'undefined' ? terralizeFrontendOptions : {};
        options.map_id = mapId;
        
        // SI LES DONNÉES SONT VIDES, AJOUTER DES DONNÉES DE TEST
        const testData = {
            type: "FeatureCollection",
            features: [
                {
                    type: "Feature",
                    properties: {
                        id: 1,
                        title: "Zone Test Module",
                        type: "zone",
                        nom_commercial: "Zone Test Module",
                        infos: "Zone ajoutée directement dans le module JS pour tester l'affichage"
                    },
                    geometry: {
                        type: "Polygon",
                        coordinates: [
                            [
                                [2.3, 48.85],
                                [2.32, 48.85],
                                [2.32, 48.86],
                                [2.3, 48.86],
                                [2.3, 48.85]
                            ]
                        ]
                    }
                },
                {
                    type: "Feature",
                    properties: {
                        id: 2,
                        title: "POI Test Module",
                        type: "poi"
                    },
                    geometry: {
                        type: "Point",
                        coordinates: [2.31, 48.855]
                    }
                }
            ]
        };
        
        // Utiliser soit les données réelles, soit les données de test
        options.zonesData = (typeof zonesData !== 'undefined' && zonesData && 
                            ((Array.isArray(zonesData) && zonesData.length > 0) || 
                            (zonesData.features && zonesData.features.length > 0))) 
                            ? zonesData 
                            : testData;
                            
        console.log("OPTIONS FINALES:", {
            map_id: options.map_id,
            zonesDataLength: options.zonesData.features ? options.zonesData.features.length : "Sans features",
            tileProvider: options.tile_provider
        });
        
        try {
            // Initialiser la carte modulaire
            console.log("Appel à initTerralizeMap()");
            initTerralizeMap(options);
            console.log("Carte initialisée avec succès");
        } catch (error) {
            console.error("ERREUR LORS DE L'INITIALISATION DE LA CARTE:", error);
        }
    });
} catch (error) {
    console.error("ERREUR CRITIQUE DANS L'IMPORT OU L'INITIALISATION:", error);
}

