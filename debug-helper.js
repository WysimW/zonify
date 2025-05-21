/**
 * Script de débogage pour Terralize Map - Popup et AJAX
 */
(function() {
    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        console.log("=== TERRALIZE DEBUG HELPER CHARGÉ ===");
        
        // Créer un élément de débogage dans le DOM
        const debugContainer = document.createElement('div');
        debugContainer.className = 'terralize-debug-container';
        debugContainer.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            z-index: 10000;
            font-family: monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
        `;
        
        // Ajouter un titre
        const title = document.createElement('h3');
        title.textContent = 'Terralize Debug';
        title.style.cssText = 'margin: 0 0 10px; border-bottom: 1px solid #fff; padding-bottom: 5px;';
        debugContainer.appendChild(title);
        
        // Créer un conteneur pour les messages
        const messagesContainer = document.createElement('div');
        messagesContainer.id = 'terralize-debug-messages';
        debugContainer.appendChild(messagesContainer);
        
        // Créer des boutons de test
        const buttonContainer = document.createElement('div');
        buttonContainer.style.cssText = 'margin-top: 10px; display: flex; gap: 5px; flex-wrap: wrap;';
        
        // Bouton pour tester l'API AJAX
        const testAjaxBtn = document.createElement('button');
        testAjaxBtn.textContent = 'Test AJAX';
        testAjaxBtn.style.cssText = 'padding: 5px 10px; background: #2196f3; border: none; color: white; cursor: pointer; border-radius: 3px;';
        testAjaxBtn.onclick = testAjaxEndpoint;
        buttonContainer.appendChild(testAjaxBtn);
        
        // Bouton pour effacer les logs
        const clearBtn = document.createElement('button');
        clearBtn.textContent = 'Effacer logs';
        clearBtn.style.cssText = 'padding: 5px 10px; background: #f44336; border: none; color: white; cursor: pointer; border-radius: 3px;';
        clearBtn.onclick = function() {
            document.getElementById('terralize-debug-messages').innerHTML = '';
        };
        buttonContainer.appendChild(clearBtn);
        
        debugContainer.appendChild(buttonContainer);
        
        // Ajouter le conteneur au body
        document.body.appendChild(debugContainer);
        
        // Intercepter console.log et afficher dans notre élément de débogage
        const originalConsoleLog = console.log;
        console.log = function() {
            // Appeler la fonction originale
            originalConsoleLog.apply(console, arguments);
            
            // Ajouter au conteneur de débogage si cela concerne Popup ou AJAX
            const args = Array.from(arguments);
            const message = args.join(' ');
            
            if (message.includes('[Popup]') || message.includes('[Ajax]') || message.includes('TERRALIZE')) {
                addDebugMessage(message);
            }
        };
        
        // Intercepter console.error également
        const originalConsoleError = console.error;
        console.error = function() {
            // Appeler la fonction originale
            originalConsoleError.apply(console, arguments);
            
            // Ajouter au conteneur de débogage si cela concerne Popup ou AJAX avec style rouge
            const args = Array.from(arguments);
            const message = args.join(' ');
            
            if (message.includes('[Popup]') || message.includes('[Ajax]') || message.includes('TERRALIZE')) {
                addDebugMessage(message, 'error');
            }
        };
        
        // Fonction pour ajouter un message de débogage au conteneur
        function addDebugMessage(message, type = 'info') {
            const messagesEl = document.getElementById('terralize-debug-messages');
            if (!messagesEl) return;
            
            const msgEl = document.createElement('div');
            msgEl.className = 'debug-message ' + type;
            msgEl.style.cssText = `
                margin-bottom: 5px;
                padding: 3px 5px;
                border-left: 3px solid ${type === 'error' ? '#f44336' : '#2196f3'};
                background: ${type === 'error' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(33, 150, 243, 0.1)'};
            `;
            
            const time = new Date().toLocaleTimeString();
            msgEl.innerHTML = `<span style="color: #aaa; font-size: 10px;">${time}</span> ${message}`;
            
            messagesEl.appendChild(msgEl);
            messagesEl.scrollTop = messagesEl.scrollHeight; // Auto-scroll
        }
        
        // Fonction pour tester l'endpoint AJAX
        function testAjaxEndpoint() {
            addDebugMessage('Test de l\'endpoint AJAX...');
            
            // Choix d'un ID de commercial (valeur de test)
            const commercialId = prompt('Entrez l\'ID du commercial à tester:', '1');
            if (!commercialId) return;
            
            const siteUrl = window.location.origin;
            const ajaxUrl = `${siteUrl}/wp-admin/admin-ajax.php?action=get_commercial_fields&commercial_id=${commercialId}`;
            
            addDebugMessage(`URL: ${ajaxUrl}`);
            
            // Faire la requête AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', ajaxUrl, true);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    addDebugMessage(`Statut de la réponse: ${xhr.status}`);
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            addDebugMessage(`Réponse parsée: ${JSON.stringify(response).substring(0, 100)}...`);
                            
                            // Afficher plus de détails
                            if (response.success && response.data) {
                                addDebugMessage(`Commercial: ${response.data.title}`);
                                addDebugMessage(`Nombre de champs: ${Object.keys(response.data.fields || {}).length}`);
                            } else {
                                addDebugMessage(`Erreur dans la réponse: ${JSON.stringify(response)}`, 'error');
                            }
                        } catch(e) {
                            addDebugMessage(`Erreur de parsing JSON: ${e}`, 'error');
                            addDebugMessage(`Réponse brute: ${xhr.responseText.substring(0, 100)}...`, 'error');
                        }
                    } else {
                        addDebugMessage(`Erreur HTTP: ${xhr.status}`, 'error');
                    }
                }
            };
            
            xhr.onerror = function() {
                addDebugMessage('Erreur réseau lors de la requête', 'error');
            };
            
            xhr.send();
        }
        
        // Ajouter un message initial
        addDebugMessage('Débogueur initialisé. Cliquez sur "Test AJAX" pour tester l\'endpoint.');
    });
})();
