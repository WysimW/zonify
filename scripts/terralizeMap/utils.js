// Fonctions utilitaires pour terralizeMap
export function updateElementText(id, text) {
    const element = document.getElementById(id);
    if (element) element.textContent = text;
}
// Ajouter d'autres utilitaires ici au besoin 