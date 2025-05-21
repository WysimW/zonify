// Gestion des éléments de l'interface utilisateur
export function setupUI(map, options) {
    const mapId = options.map_id;
    console.log("Initialisation des éléments UI pour la carte", mapId);
    
    // Initialisation des accordéons
    initAccordions(mapId);
    
    // Ajout de l'écouteur pour les réinitialisations de DOM
    document.addEventListener('terralize:dom_updated', function(e) {
        if (e.detail && e.detail.mapId === mapId) {
            console.log("Réinitialisation des accordéons après mise à jour du DOM");
            initAccordions(mapId);
        }
    });
    
    // Exposer la fonction d'initialisation des accordéons
    window.terralizemap.initAccordions = function(targetMapId) {
        if (targetMapId === mapId || !targetMapId) {
            initAccordions(mapId);
            return true;
        }
        return false;
    };
}

// Fonction d'initialisation des accordéons
function initAccordions(mapId) {
    console.log("Initialisation des accordéons pour", mapId);
    
    // D'abord, supprimer tous les gestionnaires d'événements existants
    document.querySelectorAll('.accordion-item .accordion-header').forEach(function(header) {
        // Cloner pour supprimer tous les gestionnaires
        var newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);
    });
    
    // Forcer tous les accordéons à l'état inactif par défaut
    document.querySelectorAll('.accordion-item').forEach(function(item) {
        // MODIFICATION: Tous les accordéons sont fermés au chargement
        // Supprimer la classe active pour tous les accordéons
        item.classList.remove('active');
        
        // S'assurer que le contenu est fermé
        var content = item.querySelector('.accordion-content');
        if (content) {
            content.style.maxHeight = "0px";
            content.style.overflow = "hidden";
        }
        
        // Mettre à jour l'icône
        var iconSpan = item.querySelector('.accordion-header .accordion-icon');
        if (iconSpan) {
            iconSpan.textContent = '+';
        }
    });
    
    // Ajouter les écouteurs d'événements pour chaque accordéon
    document.querySelectorAll('.accordion-item').forEach(function(item) {
        var header = item.querySelector('.accordion-header');
        var content = item.querySelector('.accordion-content');
        
        if (header && content) {
            // Ajouter le gestionnaire d'événement
            header.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle la classe active
                var isActive = item.classList.contains('active');
                item.classList.toggle('active');
                
                // Mettre à jour l'icône
                var iconSpan = header.querySelector('.accordion-icon');
                if (iconSpan) {
                    iconSpan.textContent = !isActive ? '-' : '+';
                }
                
                // Animer l'ouverture/fermeture
                if (!isActive) {
                    content.style.maxHeight = content.scrollHeight + "px";
                    console.log("Ouverture accordéon, hauteur:", content.scrollHeight);
                } else {
                    content.style.maxHeight = "0px";
                    console.log("Fermeture accordéon");
                }
                
                console.log("Toggle accordéon:", item, !isActive);
            });
        }
    });
    
    console.log("Accordéons initialisés");
} 