// Gestion des popups Leaflet
/**
 * Affiche une popup pour une feature (zone ou poi) sur la carte.
 * @param {L.Map} map - Instance de la carte
 * @param {Object} feature - Feature GeoJSON
 * @param {L.Layer} layer - Layer Leaflet
 * @param {Object} options - Options de la carte
 *
 * Exemple d'utilisation :
 *   import { showFeaturePopup } from './popups.js';
 *   ...
 *   onEachFeature: function(feature, layer) {
 *     layer.on('click', function() {
 *       showFeaturePopup(map, feature, layer, options);
 *     });
 *   }
 */
export function showFeaturePopup(map, feature, layer, options) {
    try {
        // Amélioration du débogage des champs personnalisés
        console.log("[Popup] Affichage du popup pour:", feature.properties.title);
        
        if (feature.properties.custom_fields) {
            // Ajout de debugging plus détaillé pour les champs
            console.log("[Popup] Analyse des champs personnalisés:", Object.keys(feature.properties.custom_fields).length, "champs trouvés");
            
            // Vérification des zones d'affichage configurées
            const zones = {};
            Object.entries(feature.properties.custom_fields).forEach(([key, field]) => {
                const zone = field.display_zone || 'info';
                zones[zone] = (zones[zone] || 0) + 1;
            });
            console.log("[Popup] Répartition des champs par zone:", zones);
        } else {
            console.warn("[Popup] ATTENTION: Aucun champ personnalisé trouvé pour ce POI");
            
            // Ajouter des champs d'exemple pour le débogage
            feature.properties.custom_fields = {
                "address": {
                    "label": "Adresse",
                    "value": "123 Rue d'Exemple, 75000 Paris",
                    "type": "text",
                    "display_zone": "location"
                },
                "phone": {
                    "label": "Téléphone",
                    "value": "01 23 45 67 89",
                    "type": "text",
                    "display_zone": "contact"
                },
                "email": {
                    "label": "Email",
                    "value": "exemple@domaine.com",
                    "type": "email",
                    "display_zone": "contact"
                },
                "website": {
                    "label": "Site web",
                    "value": "https://www.exemple.com",
                    "type": "url",
                    "display_zone": "contact"
                },
                "opening_hours": {
                    "label": "Horaires d'ouverture",
                    "value": "Lun-Ven: 9h-18h, Sam: 10h-16h",
                    "type": "textarea",
                    "display_zone": "info"
                },
                "description": {
                    "label": "Description",
                    "value": "<p>Ceci est une description d'exemple pour tester l'affichage des champs personnalisés.</p>",
                    "type": "wysiwyg",
                    "display_zone": "info"
                }
            };
            console.log("[Popup] Champs d'exemple ajoutés pour test");
        }
        
        let content = '';
        
        if (feature.properties.type === 'poi') {
            // Popup pour un POI
            content = '<div class="popup-container">';
            
            // Titre du POI
            content += '<h3>' + (feature.properties.title || 'Point d\'intérêt') + '</h3>';
            
            // Préparer les champs pour chaque zone
            let headerFields = {};
            let infoFields = {};
            let contactFields = {};
            let locationFields = {};
            
            // Trier les champs par zone d'affichage
            if (feature.properties.custom_fields && Object.keys(feature.properties.custom_fields).length > 0) {
                Object.entries(feature.properties.custom_fields).forEach(([key, field]) => {
                    if (field && field.value) {
                        const displayZone = field.display_zone || 'info';
                        
                        // Distribuer le champ dans la zone appropriée
                        switch (displayZone) {
                            case 'header':
                                headerFields[key] = field;
                                break;
                            case 'contact':
                                contactFields[key] = field;
                                break;
                            case 'location':
                                locationFields[key] = field;
                                break;
                            case 'info':
                            default:
                                infoFields[key] = field;
                                break;
                        }
                    }
                });
            }
            
            // Rapport de debugging sur la répartition des champs
            console.log("[Popup] Répartition finale des champs:", {
                header: Object.keys(headerFields).length,
                contact: Object.keys(contactFields).length,
                info: Object.keys(infoFields).length,
                location: Object.keys(locationFields).length
            });
            
            // Afficher les images du header
            if (Object.keys(headerFields).length > 0) {
                console.log("[Popup] Affichage des champs header:", Object.keys(headerFields));
                Object.entries(headerFields).forEach(([key, field]) => {
                    if (field.type === 'image' && field.value) {
                        // On suppose que la valeur est un ID d'image pour WP et qu'on a déjà l'URL dans image_url
                        if (feature.properties.image_url) {
                            content += '<div class="panel-image">' +
                                '<img src="' + feature.properties.image_url + '" alt="' + feature.properties.title + '">' +
                                '<div class="image-zoom-icon" onclick="window.openImageLightbox(\'' + feature.properties.image_url + '\')" title="Agrandir l\'image">' +
                                '<i class="fas fa-search-plus"></i>' +
                                '</div>' +
                                '</div>';
                        }
                    }
                });
            } else if (feature.properties.image_url) {
                // Fallback: Si pas de champs header mais une image_url existe
                console.log("[Popup] Utilisation de l'image par défaut");
                content += '<div class="panel-image">' +
                    '<img src="' + feature.properties.image_url + '" alt="' + feature.properties.title + '">' +
                    '<div class="image-zoom-icon" onclick="window.openImageLightbox(\'' + feature.properties.image_url + '\')" title="Agrandir l\'image">' +
                    '<i class="fas fa-search-plus"></i>' +
                    '</div>' +
                    '</div>';
            }
            
            // Système d'onglets sans script inline
            content += 
                '<div class="panel-tabs" style="margin-bottom: 15px;">' +
                                '<div class="tab-headers">' +                '<div class="tab-btn active" data-tab="info-content">Informations</div>' +                '<div class="tab-btn" data-tab="contact-content">Contact</div>' +                '</div>' +
                '<div class="tab-contents">';
            
            // Onglet Informations
                            content += '<div class="tab-content" id="info-content">';
            
            // Section Informations générales
            let hasInfo = Object.keys(infoFields).length > 0;
            content += '<div class="popup-section info">';
            content += '<h4>Informations</h4>';
            
            if (hasInfo) {
                console.log("[Popup] Affichage des champs info:", Object.keys(infoFields));
                Object.entries(infoFields).forEach(([key, field]) => {
                    if (field.value) {
                        // Adapter l'affichage selon le type de champ
                        if (field.type === 'wysiwyg' || field.type === 'textarea') {
                            content += '<div class="info-' + key + '">';
                            content += '<p><strong>' + field.label + '</strong></p>';
                            content += '<div class="info-text">' + field.value + '</div>';
                            content += '</div>';
                        } else if (field.type === 'checkbox') {
                            if (field.value === '1') {
                                content += '<p><strong>' + field.label + '</strong> <span class="checkbox-mark">✓</span></p>';
                            }
                        } else {
                            content += '<p><strong>' + field.label + ':</strong> ' + field.value + '</p>';
                        }
                    }
                });
            } else {
                content += '<p><em>Aucune information supplémentaire disponible</em></p>';
            }
            
            // Intégrer les champs de localisation dans l'onglet Informations
            if (Object.keys(locationFields).length > 0) {
                content += '<h4 class="location-title">Localisation</h4>';
                console.log("[Popup] Affichage des champs location:", Object.keys(locationFields));
                Object.entries(locationFields).forEach(([key, field]) => {
                    if (field.value) {
                        content += '<p><strong>' + field.label + ':</strong> ' + field.value + '</p>';
                    }
                });
            }

            // Coordonnées GPS
            if (feature.geometry && feature.geometry.coordinates) {
                const lat = feature.geometry.coordinates[1];
                const lng = feature.geometry.coordinates[0];
                content += '<p><strong>Coordonnées GPS:</strong> ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</p>';
            }
        
            // Afficher les catégories si disponibles
            if (feature.properties.categories && feature.properties.categories.length > 0) {
                content += '<div class="popup-tags">';
                feature.properties.categories.forEach(function(cat) {
                    content += '<span class="popup-tag">' + cat.name + '</span>';
                });
                content += '</div>';
            }
        
            content += '</div>'; // Fin des infos
            content += '</div>'; // Fin info-content
        
            // Onglet Contact
            content += '<div class="tab-content" id="contact-content">';
            
            // Section Contact
            let hasContactInfo = Object.keys(contactFields).length > 0;
            content += '<div class="popup-section contact-info">';
            content += '<h4>Contact</h4>';
            
            if (hasContactInfo) {
                console.log("[Popup] Affichage des champs contact:", Object.keys(contactFields));
                Object.entries(contactFields).forEach(([key, field]) => {
                    if (field.value) {
                        switch (field.type) {
                            case 'email':
                                content += '<p><i class="fas fa-envelope"></i> <a href="mailto:' + field.value + '">' + 
                                    field.value + '</a></p>';
                                break;
                            case 'url':
                            case 'website':
                                content += '<p><i class="fas fa-globe"></i> <a href="' + field.value + 
                                    '" target="_blank">Visiter le site web</a></p>';
                                break;
                            case 'phone':
                                content += '<p><i class="fas fa-phone"></i> <a href="tel:' + field.value + '">' + 
                                    field.value + '</a></p>';
                                break;
                            default:
                                content += '<p><i class="fas fa-info-circle"></i> <strong>' + field.label + ':</strong> ' + field.value + '</p>';
                                break;
                        }
                    }
                });
            } else {
                content += '<p><em>Aucune information de contact disponible</em></p>';
            }
            
            content += '</div>'; // Fin popup-section contact-info
            
            content += '</div>'; // Fin contact-content
            content += '</div>'; // Fin tab-contents
            content += '</div>'; // Fin panel-tabs
            
            // Section pour les itinéraires (toujours visible, indépendamment des onglets)
            content += '<div class="popup-section routes-section">';
            
            // Distance et itinéraire si l'utilisateur est localisé
            if (window.userLatLng) {
                const poiLatlng = L.latLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]);
                const distance = window.userLatLng.distanceTo(poiLatlng);
                content += '<p><i class="fas fa-map-marker-alt"></i> Distance: ' + Math.round(distance/1000) + ' km</p>';
                content += '<button class="get-directions-btn" data-lat="' + window.userLatLng.lat + '" data-lng="' + window.userLatLng.lng + '" data-poi-lat="' + poiLatlng.lat + '" data-poi-lng="' + poiLatlng.lng + '">Calculer l\'itinéraire <i class="fas fa-directions"></i></button>';
            } else {
                const poiLatlng = L.latLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]);
                content += '<button class="locate-and-route-btn" data-poi-lat="' + poiLatlng.lat + '" data-poi-lng="' + poiLatlng.lng + '">Me localiser et calculer l\'itinéraire <i class="fas fa-map-marker-alt"></i></button>';
            }
            
            content += '</div>'; // Fin routes-section
        
            // Bouton Contact dans le bas de la popup
            const contactUrl = options.contact_page_url || '/contact';
            content += '<div class="panel-actions">' +
                    '<a href="' + contactUrl + '?poi_id=' + feature.properties.id + '" class="btn-contact">Contacter</a>' +
                '</div>';
        
            content += '</div>'; // Fin popup-container
        
        } else {
            // Popup pour une zone commerciale - Utiliser la même structure modulaire avec 2 onglets
            content = '<div class="popup-container">';
            content += '<h2 style="margin-top: 0; color: #333;">' + (feature.properties.title || 'Zone') + '</h2>';
            
            const comm_id = feature.properties.commercial_id || 0;
            let hasCommercial = comm_id > 0;
            
            // Vérifier si on a des champs personnalisés du commercial
            if (hasCommercial && feature.properties.commercial_custom_fields) {
                console.log("[Popup] Zone liée au commercial ID:", comm_id, "avec", Object.keys(feature.properties.commercial_custom_fields).length, "champs");
                
                // Préparer les champs pour chaque zone (même approche que pour les POI)
                let headerFields = {};
                let infoFields = {};
                let contactFields = {};
                let locationFields = {};
                
                // Trier les champs par zone d'affichage
                if (Object.keys(feature.properties.commercial_custom_fields).length > 0) {
                    Object.entries(feature.properties.commercial_custom_fields).forEach(([key, field]) => {
                        if (field && field.value) {
                            const displayZone = field.display_zone || 'info';
                            
                            // Distribuer le champ dans la zone appropriée
                            switch (displayZone) {
                                case 'header':
                                    headerFields[key] = field;
                                    break;
                                case 'contact':
                                    contactFields[key] = field;
                                    break;
                                case 'location':
                                    locationFields[key] = field;
                                    break;
                                case 'info':
                                default:
                                    infoFields[key] = field;
                                    break;
                            }
                        }
                    });
                }
                
                // Rapport de debugging sur la répartition des champs
                console.log("[Popup] Répartition finale des champs pour commercial:", {
                    header: Object.keys(headerFields).length,
                    contact: Object.keys(contactFields).length,
                    info: Object.keys(infoFields).length,
                    location: Object.keys(locationFields).length
                });
                
                // Afficher les images du header
                if (Object.keys(headerFields).length > 0) {
                    console.log("[Popup] Affichage des champs header pour commercial:", Object.keys(headerFields));
                    Object.entries(headerFields).forEach(([key, field]) => {
                        if (field.type === 'image' && field.value) {
                            content += '<div class="panel-image">' +
                                '<img src="' + field.value + '" alt="Image du commercial">' +
                                '<div class="image-zoom-icon" onclick="window.openImageLightbox(\'' + field.value + '\')" title="Agrandir l\'image">' +
                                '<i class="fas fa-search-plus"></i>' +
                                '</div>' +
                                '</div>';
                        }
                    });
                }
                
                // Système d'onglets sans script inline (avec seulement 2 onglets)
                content += '<div class="panel-tabs">' +
                    '<div class="tab-headers">' +
                    '<div class="tab-btn active" data-tab="comm-info-content">Informations</div>' +
                    '<div class="tab-btn" data-tab="comm-contact-content">Contact</div>' +
                    '</div>' +
                    '<div class="tab-contents">';
                
                // Onglet Informations
                content += '<div class="tab-content" id="comm-info-content">';
                
                // Section Informations générales
                content += '<div class="popup-section info">';
                content += '<h4>Informations</h4>';
                
                if (Object.keys(infoFields).length > 0) {
                    console.log("[Popup] Affichage des champs info pour commercial:", Object.keys(infoFields));
                    Object.entries(infoFields).forEach(([key, field]) => {
                        if (field.value) {
                            if (field.type === 'wysiwyg' || field.type === 'textarea') {
                                content += '<div class="info-' + key + '">';
                                content += '<p><strong>' + field.label + '</strong></p>';
                                content += '<div class="info-text">' + field.value + '</div>';
                                content += '</div>';
                            } else if (field.type === 'checkbox') {
                                if (field.value === '1') {
                                    content += '<p><strong>' + field.label + '</strong> <span class="checkbox-mark">✓</span></p>';
                                }
                            } else {
                                content += '<p><strong>' + field.label + ':</strong> ' + field.value + '</p>';
                            }
                        }
                    });
                } else {
                    // Fallback sur les anciennes propriétés si disponibles
                    if (feature.properties.infos) {
                        content += '<p>' + feature.properties.infos + '</p>';
                    } else {
                        content += '<p><em>Aucune information supplémentaire disponible</em></p>';
                    }
                }
                
                // Intégrer les champs de localisation dans l'onglet Informations
                if (Object.keys(locationFields).length > 0) {
                    content += '<h4 class="location-title">Localisation</h4>';
                    console.log("[Popup] Affichage des champs location pour commercial:", Object.keys(locationFields));
                    Object.entries(locationFields).forEach(([key, field]) => {
                        if (field.value) {
                            content += '<p><strong>' + field.label + ':</strong> ' + field.value + '</p>';
                        }
                    });
                } else {
                    // Fallback sur les anciennes propriétés si disponibles
                    if (feature.properties.address) {
                        content += '<h4 style="margin-top: 15px;">Localisation</h4>';
                        content += '<p><strong>Adresse:</strong> ' + feature.properties.address + '</p>';
                    }
                    
                    if (feature.properties.opening_hours) {
                        content += '<p><strong>Horaires:</strong> ' + feature.properties.opening_hours + '</p>';
                    }
                }
                
                // Afficher les catégories
                if (feature.properties.categories && feature.properties.categories.length > 0) {
                    content += '<div class="popup-tags">';
                    feature.properties.categories.forEach(function(cat) {
                        content += '<span class="popup-tag">' + cat.name + '</span>';
                    });
                    content += '</div>';
                }
                
                // Afficher les régions
                if (feature.properties.regions && feature.properties.regions.length > 0) {
                    content += '<div class="popup-tags">';
                    feature.properties.regions.forEach(function(reg) {
                        content += '<span class="popup-tag">' + reg.name + '</span>';
                    });
                    content += '</div>';
                }
                
                content += '</div>'; // Fin section info
                content += '</div>'; // Fin onglet info
                
                // Onglet Contact
                content += '<div class="tab-content" id="comm-contact-content">';
                
                // Section Contact
                content += '<div class="popup-section contact-info">';
                content += '<h4>Contact</h4>';
                
                if (Object.keys(contactFields).length > 0) {
                    console.log("[Popup] Affichage des champs contact pour commercial:", Object.keys(contactFields));
                    Object.entries(contactFields).forEach(([key, field]) => {
                        if (field.value) {
                            if (field.type === 'email') {
                                content += '<p><i class="fas fa-envelope"></i> <a href="mailto:' + field.value + '">' + field.value + '</a></p>';
                            } else if (field.type === 'url' || field.type === 'website') {
                                content += '<p><i class="fas fa-globe"></i> <a href="' + field.value + '" target="_blank">Visiter le site web</a></p>';
                            } else if (field.type === 'text' && field.label.toLowerCase().includes('téléphone')) {
                                content += '<p><i class="fas fa-phone"></i> <a href="tel:' + field.value + '">' + field.value + '</a></p>';
                            } else {
                                content += '<p><i class="fas fa-info-circle"></i> <strong>' + field.label + ':</strong> ' + field.value + '</p>';
                            }
                        }
                    });
                } else {
                    if (feature.properties.email) {
                        content += '<p><i class="fas fa-envelope"></i> <a href="mailto:' + feature.properties.email + '">' + feature.properties.email + '</a></p>';
                    }
                    if (feature.properties.telephone) {
                        content += '<p><i class="fas fa-phone"></i> <a href="tel:' + feature.properties.telephone + '">' + feature.properties.telephone + '</a></p>';
                    }
                    if (!feature.properties.email && !feature.properties.telephone) {
                        content += '<p><em>Aucune information de contact disponible</em></p>';
                    }
                }
                
                content += '</div>'; // Fin section contact
                content += '</div>'; // Fin onglet contact
                content += '</div>'; // Fin tab-contents
                content += '</div>'; // Fin panel-tabs
                
            } else {
                // Affichage classique si pas de commercial lié ou pas de champs
                if (feature.properties.infos) {
                    content += '<p>' + feature.properties.infos + '</p>';
                }
                
                // Afficher les catégories
                if (feature.properties.categories && feature.properties.categories.length > 0) {
                    content += '<div class="popup-tags">';
                    feature.properties.categories.forEach(function(cat) {
                        content += '<span class="popup-tag">' + cat.name + '</span>';
                    });
                    content += '</div>';
                }
                
                // Afficher les régions
                if (feature.properties.regions && feature.properties.regions.length > 0) {
                    content += '<div class="popup-tags">';
                    feature.properties.regions.forEach(function(reg) {
                        content += '<span class="popup-tag">' + reg.name + '</span>';
                    });
                    content += '</div>';
                }
            }
            
            // Bouton de contact (toujours présent)
            if (options && options.popup_enable_contact_btn && parseInt(options.popup_enable_contact_btn) === 1) {
                const contactUrl = options.contact_page_url || '/contact';
                                content += '<div class="panel-actions">';                content += '<a href="' + contactUrl + '?zone_id=' + feature.properties.id + (hasCommercial ? '&commercial_id=' + comm_id : '') + '" class="btn-contact">Contacter</a>';                content += '</div>';
            }
            
            content += '</div>'; // Fin popup-container
        }

        // Déterminer la position de la popup
        let popupLatLng;
        try {
            if (layer.getBounds) {
                popupLatLng = layer.getBounds().getCenter();
            } else if (layer.getLatLng) {
                popupLatLng = layer.getLatLng();
            } else {
                popupLatLng = map.getCenter();
            }
        } catch (err) {
            console.error("[Popup] Erreur lors de la détermination de la position de la popup:", err);
            popupLatLng = map.getCenter();
        }

        // Créer et ouvrir la popup avec des options améliorées
        const popup = L.popup({
            maxWidth: 350,
            minWidth: 250,
            className: 'terralize-custom-popup',
            closeButton: true,
            autoClose: true,
            closeOnEscapeKey: true
        })
            .setLatLng(popupLatLng)
            .setContent(content)
            .openOn(map);
        
        // Attacher les gestionnaires d'événements après ouverture de la popup
        window.setTimeout(function() {
            try {
                console.log("[Popup] Initialisation des événements de la popup");
                
                // Gestionnaire pour les boutons d'onglets
                const tabButtons = document.querySelectorAll('.popup-container .tab-btn');
                console.log("[Popup] Nombre de boutons d'onglets trouvés:", tabButtons.length);
                
                tabButtons.forEach(function(button) {
                    try {
                        button.addEventListener('click', function(e) {
                            try {
                                // Récupérer l'ID du contenu à afficher
                                const tabId = this.getAttribute('data-tab');
                                console.log("[Popup] Changement d'onglet vers:", tabId);
                                
                                // Réinitialiser tous les boutons
                                document.querySelectorAll('.popup-container .tab-btn').forEach(function(btn) {
                                    btn.classList.remove('active');
                                });
                                
                                // Activer le bouton et le contenu sélectionnés
                                this.classList.add('active');
                                
                                const tabContent = document.getElementById(tabId);
                                if (tabContent) {
                                    // Masquer tous les contenus d'onglets
                                    document.querySelectorAll('.popup-container .tab-content').forEach(function(content) {
                                        content.style.display = 'none';
                                    });
                                    // Afficher le contenu de l'onglet sélectionné
                                    tabContent.style.display = 'block';
                                } else {
                                    console.warn("[Popup] Contenu d'onglet non trouvé:", tabId);
                                }
                            } catch (err) {
                                console.error("[Popup] Erreur dans le gestionnaire de clic d'onglet:", err);
                            }
                        });
                        console.log("[Popup] Écouteur d'événement ajouté pour le bouton d'onglet:", button.getAttribute('data-tab'));
                    } catch (err) {
                        console.error("[Popup] Erreur lors de l'ajout d'un écouteur d'événement:", err);
                    }
                });
                
                // Gestion des boutons d'itinéraire avec try/catch
                try {
                    const getDirectionsBtn = document.querySelector('.get-directions-btn');
                    if (getDirectionsBtn) {
                        getDirectionsBtn.addEventListener('click', function() {
                            const startLat = this.getAttribute('data-lat');
                            const startLng = this.getAttribute('data-lng');
                            const endLat = this.getAttribute('data-poi-lat');
                            const endLng = this.getAttribute('data-poi-lng');
                            console.log("[Popup] Calcul d'itinéraire:", startLat, startLng, "→", endLat, endLng);
                            window.open(`https://www.google.com/maps/dir/?api=1&origin=${startLat},${startLng}&destination=${endLat},${endLng}&travelmode=driving`);
                        });
                    }
                    
                    const locateAndRouteBtn = document.querySelector('.locate-and-route-btn');
                    if (locateAndRouteBtn) {
                        locateAndRouteBtn.addEventListener('click', function() {
                            const poiLat = this.getAttribute('data-poi-lat');
                            const poiLng = this.getAttribute('data-poi-lng');
                            console.log("[Popup] Localisation pour itinéraire vers:", poiLat, poiLng);
                            
                            map.locate({setView: true, maxZoom: 16, enableHighAccuracy: true});
                            map.once('locationfound', function(e) {
                                window.userLatLng = e.latlng;
                                const startLat = e.latlng.lat;
                                const startLng = e.latlng.lng;
                                console.log("[Popup] Position trouvée:", startLat, startLng);
                                window.open(`https://www.google.com/maps/dir/?api=1&origin=${startLat},${startLng}&destination=${poiLat},${poiLng}&travelmode=driving`);
                            });
                            map.once('locationerror', function(err) {
                                console.error("[Popup] Erreur de localisation:", err);
                                alert('Impossible de vous localiser : ' + err.message);
                            });
                        });
                    }
                } catch (err) {
                    console.error("[Popup] Erreur lors de la configuration des boutons d'itinéraire:", err);
                }
                
                console.log("[Popup] Initialisation des événements terminée avec succès");
            } catch (err) {
                console.error("[Popup] Erreur globale lors de l'initialisation des événements:", err);
            }
        }, 100);
    } catch (err) {
        console.error("[Popup] Erreur critique dans la fonction showFeaturePopup:", err);
    }
}

export function setupPopups(map, options) {
    // Fonction d'initialisation pour la gestion des popups
    console.log("Configuration des popups pour la carte", map._container.id);
    
    // Initialiser globalement la fonction d'ouverture d'image en lightbox
    if (!window.openImageLightbox) {
        window.openImageLightbox = function(imgUrl) {
            // Créer l'overlay pour la lightbox s'il n'existe pas déjà
            var lightboxOverlay = document.getElementById('image-lightbox-overlay');
            if (!lightboxOverlay) {
                lightboxOverlay = document.createElement('div');
                lightboxOverlay.id = 'image-lightbox-overlay';
                lightboxOverlay.className = 'lightbox-overlay';
                lightboxOverlay.innerHTML = `
                    <div class="lightbox-container">
                        <div class="lightbox-content">
                            <img id="lightbox-image" src="" alt="Image agrandie" />
                        </div>
                        <button class="lightbox-close">×</button>
                    </div>
                `;
                document.body.appendChild(lightboxOverlay);
                
                // Ajouter les écouteurs d'événements pour fermer la lightbox
                lightboxOverlay.addEventListener('click', function(event) {
                    if (event.target === lightboxOverlay || event.target.className === 'lightbox-close') {
                        lightboxOverlay.style.display = 'none';
                    }
                });
            }
            
            // Mettre à jour l'image dans la lightbox
            var lightboxImage = document.getElementById('lightbox-image');
            if (lightboxImage) {
                lightboxImage.src = imgUrl;
                
                // Afficher la lightbox
                lightboxOverlay.style.display = 'flex';
            }
        };
    }
} 