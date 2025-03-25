document.addEventListener('DOMContentLoaded', function() {
    // ...
    var toggleBtn = document.getElementById('toggle-view-mode');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            // On déclenche l’appel AJAX
            fetch(zonifyMapVars.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: new URLSearchParams({
                    action: 'toggle_always_show',
                    // si besoin : _ajax_nonce: zonifyMapVars.nonce
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // data.data.always_show_all_zones => la nouvelle valeur (0 ou 1)
                    alert("Option basculée : now = " + data.data.always_show_all_zones);
                    // On peut recharger la page pour appliquer la nouvelle logique 
                    // (ou adapter un rechargement dynamique selon votre code)
                    location.reload();
                } else {
                    console.error("Erreur toggle : ", data.data);
                    alert("Erreur toggle : " + data.data);
                }
            })
            .catch(err => console.error("Erreur AJAX toggle_always_show :", err));
        });
    }
    // ...
});
