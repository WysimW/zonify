<?php
/**
 * Page de documentation pour l'extension Terralize Affichage Premier
 * 
 * Fournit une documentation détaillée sur l'utilisation de l'extension
 * pour la gestion des panneaux d'affichage urbain.
 */

// Si ce fichier est appelé directement, abandon
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajouter la page de documentation à l'administration
 */
function terralize_ap_add_docs_page() {
    add_submenu_page(
        'terralize',
        'Documentation Affichage Premier',
        'Documentation',
        'manage_options',
        'terralize_affichage_docs',
        'terralize_ap_docs_page_content'
    );
}
add_action('admin_menu', 'terralize_ap_add_docs_page');

/**
 * Contenu de la page de documentation
 */
function terralize_ap_docs_page_content() {
    $icon_url = plugin_dir_url(dirname(__FILE__)) . 'assets/icons/icon.png';
    $plugin_version = TERRALIZE_AP_VERSION;
    ?>
    <div class="wrap terralize-docs">
        <!-- En-tête -->
        <div class="terralize-header">
            <div class="terralize-header-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Terralize Icon" class="terralize-icon" />
                <h1 class="terralize-title">Documentation Terralize Affichage Premier</h1>
            </div>
            <div class="terralize-header-right">
                <span class="terralize-version">Version <?php echo esc_html($plugin_version); ?></span>
            </div>
        </div>
        
        <!-- Navigation de la documentation -->
        <div class="terralize-docs-nav">
            <nav class="nav-tabs">
                <a href="#introduction" class="active">Introduction</a>
                <a href="#panneau-affichage">Gestion des panneaux</a>
                <a href="#carte-interactive">Carte interactive</a>
                <a href="#import-export">Import/Export</a>
                <a href="#taxonomies">Catégories et filtres</a>
                <a href="#contact-reservation">Formulaire de réservation</a>
                <a href="#parametres">Paramètres</a>
                <a href="#faq">FAQ</a>
            </nav>
        </div>
        
        <!-- Contenu principal de la documentation -->
        <div class="terralize-docs-content">
            <!-- Introduction -->
            <section id="introduction" class="doc-section active">
                <h2>Introduction à Terralize Affichage Premier</h2>
                <p>
                    L'extension Terralize Affichage Premier est spécialement conçue pour les entreprises d'affichage urbain. 
                    Elle permet de gérer votre parc de panneaux d'affichage et de les présenter sur une carte interactive.
                </p>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <h4>Gestion complète des panneaux</h4>
                        <p>Ajoutez, modifiez et organisez votre inventaire de panneaux d'affichage avec toutes les informations techniques.</p>
                    </div>
                    
                    <div class="feature-item">
                        <h4>Carte interactive</h4>
                        <p>Visualisez tous vos panneaux sur une carte dynamique avec filtres avancés par type, ville ou catégorie.</p>
                    </div>
                    
                    <div class="feature-item">
                        <h4>Import/Export</h4>
                        <p>Importez vos données existantes depuis CSV ou exportez vos panneaux au format CSV/GeoJSON.</p>
                    </div>
                    
                    <div class="feature-item">
                        <h4>Taxonomies personnalisées</h4>
                        <p>Organisez vos panneaux avec des catégories adaptées à votre activité.</p>
                    </div>
                </div>
            </section>
            
            <!-- Gestion des panneaux -->
            <section id="panneau-affichage" class="doc-section">
                <h2>Gestion des panneaux d'affichage</h2>
                
                <h3>Ajouter un nouveau panneau</h3>
                <ol>
                    <li>
                        Dans le menu administratif, cliquez sur <strong>Panneaux d'affichage</strong> puis sur <strong>Ajouter</strong>.
                    </li>
                    <li>
                        Remplissez les informations du panneau :
                        <ul>
                            <li><strong>Titre</strong> : Nom ou référence principale du panneau</li>
                            <li><strong>Référence</strong> : Code d'identification unique du panneau</li>
                            <li><strong>Type de panneau</strong> : 4x3, 8x3, Mural, Sucette, etc.</li>
                            <li><strong>Dimensions</strong> : Largeur et hauteur en centimètres</li>
                            <li><strong>Adresse et localisation</strong> : Position précise du panneau</li>
                            <li><strong>Visibilité</strong> : Informations sur l'angle de vue et la visibilité</li>
                            <li><strong>Statut de disponibilité</strong> : Disponible, Réservé, En maintenance, etc.</li>
                        </ul>
                    </li>
                    <li>
                        Positionnez précisément le panneau sur la carte en utilisant l'outil de géolocalisation.
                    </li>
                    <li>
                        Attribuez des catégories au panneau pour faciliter la recherche et le filtrage.
                    </li>
                    <li>
                        Ajoutez une ou plusieurs photos du panneau pour faciliter son identification.
                    </li>
                    <li>
                        Cliquez sur <strong>Publier</strong> pour enregistrer le panneau.
                    </li>
                </ol>
                
                <h3>Champs disponibles</h3>
                <table class="doc-table">
                    <tr>
                        <th>Champ</th>
                        <th>Description</th>
                        <th>Utilisation</th>
                    </tr>
                    <tr>
                        <td>Référence</td>
                        <td>Identifiant unique du panneau</td>
                        <td>Permet de retrouver facilement un panneau spécifique</td>
                    </tr>
                    <tr>
                        <td>Type</td>
                        <td>Catégorie technique du panneau</td>
                        <td>Classifie les panneaux par format technique</td>
                    </tr>
                    <tr>
                        <td>Dimensions</td>
                        <td>Largeur et hauteur en cm</td>
                        <td>Génère automatiquement la surface en m²</td>
                    </tr>
                    <tr>
                        <td>Adresse</td>
                        <td>Localisation précise</td>
                        <td>Peut être générée automatiquement depuis la carte</td>
                    </tr>
                    <tr>
                        <td>Statut</td>
                        <td>État de disponibilité</td>
                        <td>Détermine la couleur d'affichage sur la carte</td>
                    </tr>
                </table>
            </section>
            
            <!-- Carte interactive -->
            <section id="carte-interactive" class="doc-section">
                <h2>Carte interactive</h2>
                
                <h3>Affichage et navigation</h3>
                <p>
                    La carte interactive vous permet de visualiser l'ensemble de votre parc de panneaux d'affichage. 
                    Elle est disponible à la fois pour les administrateurs et sur le frontend via la page 
                    <code>/carte-des-panneaux/</code>.
                </p>
                
                <h4>Fonctionnalités principales :</h4>
                <ul>
                    <li><strong>Filtrage</strong> : Filtrez les panneaux par type, ville, département ou catégorie</li>
                    <li><strong>Recherche</strong> : Trouvez rapidement des panneaux par référence ou adresse</li>
                    <li><strong>Clusters</strong> : Regroupement automatique des panneaux proches pour une meilleure lisibilité</li>
                    <li><strong>Infobulles</strong> : Cliquez sur un panneau pour voir ses détails sans quitter la carte</li>
                    <li><strong>Mode plein écran</strong> : Visualisez la carte en mode plein écran pour plus de confort</li>
                </ul>
                
                <h3>Personnalisation de l'affichage</h3>
                <p>
                    Vous pouvez personnaliser l'affichage de la carte dans la section <strong>Paramètres → Carte des panneaux</strong> :
                </p>
                <ul>
                    <li>Zoom par défaut et position initiale</li>
                    <li>Styles de marqueurs et couleurs par type de panneau</li>
                    <li>Contenu des infobulles</li>
                    <li>Filtres disponibles pour les visiteurs</li>
                </ul>
            </section>
            
            <!-- Import/Export -->
            <section id="import-export" class="doc-section">
                <h2>Import et Export de données</h2>
                
                <h3>Importer des panneaux depuis CSV</h3>
                <p>
                    L'extension permet d'importer facilement un grand nombre de panneaux depuis un fichier CSV.
                </p>
                
                <h4>Procédure d'import :</h4>
                <ol>
                    <li>Préparez votre fichier CSV avec les colonnes suivantes :
                        <ul>
                            <li><code>reference</code> : Référence unique du panneau</li>
                            <li><code>titre</code> : Nom du panneau</li>
                            <li><code>type</code> : Type du panneau</li>
                            <li><code>latitude</code> et <code>longitude</code> : Coordonnées géographiques</li>
                            <li><code>adresse</code>, <code>code_postal</code>, <code>ville</code> : Informations de localisation</li>
                            <li><code>largeur</code> et <code>hauteur</code> : Dimensions en cm</li>
                            <li><code>statut</code> : Disponibilité du panneau</li>
                            <li><code>categories</code> : Liste de catégories séparées par des virgules</li>
                        </ul>
                    </li>
                    <li>Accédez à <strong>Terralize → Import/Export</strong> puis sélectionnez l'onglet <strong>Import Panneaux</strong></li>
                    <li>Téléversez votre fichier CSV et configurez les options de correspondance des colonnes</li>
                    <li>Lancez l'import et vérifiez le rapport de résultats</li>
                </ol>
                
                <h3>Exporter les données</h3>
                <p>
                    Vous pouvez exporter vos panneaux aux formats suivants :
                </p>
                <ul>
                    <li><strong>CSV</strong> : Pour édition ou sauvegarde dans un tableur</li>
                    <li><strong>GeoJSON</strong> : Format standard pour données géographiques, compatible avec la plupart des SIG</li>
                </ul>
                
                <h4>Accès à l'export :</h4>
                <p>
                    Les fonctions d'export sont disponibles dans <strong>Terralize → Import/Export</strong> ou directement 
                    depuis la page d'accueil de Terralize.
                </p>
            </section>
            
            <!-- Taxonomies et filtres -->
            <section id="taxonomies" class="doc-section">
                <h2>Catégories et filtres</h2>
                
                <h3>Gestion des catégories de panneaux</h3>
                <p>
                    L'extension crée automatiquement plusieurs taxonomies pour classer vos panneaux :
                </p>
                <ul>
                    <li><strong>Catégories de panneaux</strong> : Classification générale (Premium, Standard, Événementiel...)</li>
                    <li><strong>Types de support</strong> : Format technique (4x3, 8x3, Mural, Abri-bus...)</li>
                    <li><strong>Zones géographiques</strong> : Organisation par région, département, ville</li>
                </ul>
                
                <h3>Création et gestion des catégories</h3>
                <p>
                    Pour gérer les catégories de panneaux :
                </p>
                <ol>
                    <li>Accédez à <strong>Terralize → Catégories de panneaux</strong></li>
                    <li>Ajoutez de nouvelles catégories en précisant :
                        <ul>
                            <li>Nom et slug</li>
                            <li>Description</li>
                            <li>Catégorie parente (optionnel)</li>
                        </ul>
                    </li>
                    <li>Utilisez le système de hiérarchie pour créer des sous-catégories</li>
                </ol>
            </section>
            
            <!-- Formulaire de contact/réservation -->
            <section id="contact-reservation" class="doc-section">
                <h2>Formulaire de réservation de panneaux</h2>
                
                <h3>Présentation du système de réservation</h3>
                <p>
                    L'extension intègre un système complet de réservation de panneaux qui permet aux visiteurs d'envoyer 
                    des demandes de réservation directement depuis la carte interactive. Ce système comprend :
                </p>
                <ul>
                    <li>Un shortcode pour afficher un formulaire de contact/réservation</li>
                    <li>La transmission automatique des informations du panneau sélectionné</li>
                    <li>Un système d'administration pour gérer les demandes reçues</li>
                </ul>
                
                <h3>Utilisation du shortcode</h3>
                <p>
                    Pour intégrer le formulaire de réservation de panneaux sur une page, utilisez le shortcode suivant :
                </p>
                <pre>[terralize_panel_contact_form]</pre>
                
                <p>
                    Ce shortcode crée un formulaire qui récupère automatiquement les informations du panneau 
                    quand un visiteur clique sur "Contacter / Réserver" depuis la carte interactive.
                </p>
                
                <h3>Configuration de la page de contact</h3>
                <ol>
                    <li>
                        <strong>Créer une page Contact/Réservation</strong> :
                        <ul>
                            <li>Créez une nouvelle page dans WordPress</li>
                            <li>Utilisez le modèle "Contact - Réservation de panneaux" dans le thème Affichage Premier</li>
                            <li>Ou utilisez n'importe quel modèle et ajoutez le shortcode <code>[terralize_panel_contact_form]</code></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Définir le slug de la page</strong> :
                        <ul>
                            <li>Pour un fonctionnement optimal, définissez le slug de la page à <code>contact</code> ou <code>reservation</code></li>
                            <li>Si vous utilisez un autre slug, modifiez les liens dans le fichier <code>affichage-premier-map.js</code></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Personnalisation du contenu</strong> :
                        <ul>
                            <li>Vous pouvez ajouter du contenu supplémentaire avant ou après le shortcode</li>
                            <li>Si vous utilisez l'éditeur de blocs, placez le shortcode dans un bloc "Shortcode"</li>
                        </ul>
                    </li>
                </ol>
                
                <h3>Gestion des demandes de réservation</h3>
                <p>
                    Les demandes de réservation sont enregistrées sous forme de posts personnalisés dans WordPress :
                </p>
                <ul>
                    <li>
                        <strong>Administration</strong> : Accédez aux demandes via le menu "Demandes de réservation" dans le tableau de bord
                    </li>
                    <li>
                        <strong>Email de notification</strong> : Un email est envoyé à l'adresse configurée dans WordPress à chaque nouvelle demande
                    </li>
                    <li>
                        <strong>Détails enregistrés</strong> : Chaque demande inclut les informations du panneau et les coordonnées du contact
                    </li>
                </ul>
                
                <h3>Personnalisation du formulaire</h3>
                <p>
                    Vous pouvez personnaliser l'apparence du formulaire en modifiant les styles CSS :
                </p>
                <ul>
                    <li>
                        <strong>CSS du thème</strong> : Si vous utilisez le thème Affichage Premier, modifiez <code>/css/contact-reservation.css</code>
                    </li>
                    <li>
                        <strong>CSS personnalisé</strong> : Ajoutez des styles dans le Personnalisateur WordPress ou via un plugin CSS personnalisé
                    </li>
                </ul>
                
                <h3>Configuration avancée</h3>
                <p>
                    Pour personnaliser davantage le comportement du formulaire, modifiez le fichier <code>panel-contact-shortcode.php</code> :
                </p>
                <ul>
                    <li><strong>Champs additionnels</strong> : Ajoutez, modifiez ou supprimez des champs du formulaire</li>
                    <li><strong>Logique de validation</strong> : Personnalisez les règles de validation des données</li>
                    <li><strong>Emails automatiques</strong> : Configurez des réponses automatiques aux clients</li>
                </ul>
            </section>
            
            <!-- Paramètres -->
            <section id="parametres" class="doc-section">
                <h2>Paramètres de configuration</h2>
                
                <h3>Paramètres généraux</h3>
                <p>
                    Les paramètres de l'extension sont accessibles via <strong>Terralize → Paramètres</strong> dans l'onglet 
                    <strong>Affichage Premier</strong>.
                </p>
                
                <h4>Options disponibles :</h4>
                <ul>
                    <li><strong>Affichage carte</strong> : Configurez l'apparence et le comportement de la carte</li>
                    <li><strong>Types de panneaux</strong> : Gérez les types prédéfinis et leurs icônes</li>
                    <li><strong>Données à afficher</strong> : Sélectionnez les champs visibles dans les infobulles</li>
                    <li><strong>Options avancées</strong> : API keys et paramètres techniques</li>
                </ul>
                
                <h3>Personnalisation des icônes</h3>
                <p>
                    Vous pouvez personnaliser les icônes des marqueurs pour chaque type de panneau :
                </p>
                <ol>
                    <li>Accédez à <strong>Terralize → Paramètres → Affichage Premier → Types de panneaux</strong></li>
                    <li>Pour chaque type de panneau, sélectionnez une icône prédéfinie ou téléversez votre propre image</li>
                    <li>Ajustez les couleurs et la taille des marqueurs</li>
                </ol>
            </section>
            
            <!-- FAQ -->
            <section id="faq" class="doc-section">
                <h2>Questions fréquentes (FAQ)</h2>
                
                <div class="faq-item">
                    <h4>Comment modifier la position d'un panneau existant ?</h4>
                    <div class="faq-answer">
                        <p>
                            Éditez le panneau concerné, puis utilisez l'outil de carte pour repositionner le marqueur. 
                            Vous pouvez faire glisser le marqueur à la nouvelle position ou saisir directement les 
                            coordonnées latitude/longitude.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h4>Comment gérer les images des panneaux ?</h4>
                    <div class="faq-answer">
                        <p>
                            Chaque panneau peut avoir plusieurs images associées. Dans l'éditeur de panneau, 
                            utilisez la section "Images du panneau" pour téléverser de nouvelles photos. 
                            L'image mise en avant sera celle affichée dans les infobulles de la carte.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h4>Est-il possible d'exporter uniquement certains panneaux ?</h4>
                    <div class="faq-answer">
                        <p>
                            Oui, dans la liste des panneaux, vous pouvez d'abord filtrer les résultats selon 
                            vos critères (type, ville, statut, etc.), puis utiliser le bouton "Exporter la sélection" 
                            pour n'exporter que les panneaux actuellement affichés.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h4>Comment intégrer la carte des panneaux sur une autre page ?</h4>
                    <div class="faq-answer">
                        <p>
                            Vous pouvez utiliser le shortcode <code>[terralize_map_panneaux]</code> pour intégrer la carte 
                            sur n'importe quelle page ou article. Personnalisez l'affichage avec des attributs :
                        </p>
                        <pre>[terralize_map_panneaux height="500px" zoom="12" type="4x3" ville="Paris"]</pre>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h4>Comment configurer le formulaire de réservation ?</h4>
                    <div class="faq-answer">
                        <p>
                            Pour configurer le formulaire de réservation :
                        </p>
                        <ol>
                            <li>Créez une page WordPress avec le slug <code>contact</code> (recommandé)</li>
                            <li>Utilisez le modèle "Contact - Réservation de panneaux" si vous utilisez le thème Affichage Premier</li>
                            <li>Ou insérez le shortcode <code>[terralize_panel_contact_form]</code> dans la page</li>
                            <li>Personnalisez le formulaire via CSS ou en modifiant le fichier <code>panel-contact-shortcode.php</code></li>
                        </ol>
                        <p>
                            Pour un slug différent de <code>contact</code>, modifiez les liens de redirection dans le fichier 
                            <code>assets/js/affichage-premier-map.js</code>.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h4>Comment gérer les demandes de réservation reçues ?</h4>
                    <div class="faq-answer">
                        <p>
                            Les demandes de réservation sont enregistrées comme un type de post personnalisé dans WordPress :
                        </p>
                        <ul>
                            <li>Accédez au menu "Demandes de réservation" dans le tableau de bord WordPress</li>
                            <li>Chaque demande contient les détails du panneau concerné et les informations du client</li>
                            <li>Un bouton "Répondre par email" permet de contacter rapidement le client</li>
                            <li>Un lien vers le panneau concerné permet de vérifier ses détails</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h4>Comment mettre à jour les données d'adresse automatiquement ?</h4>
                    <div class="faq-answer">
                        <p>
                            Lors de l'édition d'un panneau, après avoir positionné le marqueur sur la carte, 
                            cliquez sur le bouton "Récupérer l'adresse depuis la carte" pour remplir automatiquement 
                            les champs d'adresse, ville et code postal en fonction de la position géographique.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <style>
    /* Styles pour la page de documentation */
    .terralize-docs {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .terralize-docs-nav {
        background: #fff;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
        border: 1px solid #ddd;
        position: sticky;
        top: 32px;
        z-index: 100;
    }
    
    .nav-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .nav-tabs a {
        padding: 8px 15px;
        border-radius: 20px;
        text-decoration: none;
        color: #333;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .nav-tabs a.active,
    .nav-tabs a:hover {
        background: var(--terralize_ap-primary, #70c141);
        color: white;
    }
    
    .terralize-docs-content {
        background: #fff;
        padding: 30px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .doc-section {
        display: none;
        margin-bottom: 30px;
    }
    
    .doc-section.active {
        display: block;
    }
    
    .doc-section h2 {
        color: var(--terralize_ap-primary, #70c141);
        border-bottom: 2px solid var(--terralize_ap-primary, #70c141);
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .doc-section h3 {
        color: var(--terralize_ap-secondary, #E04D00);
        margin-top: 30px;
        margin-bottom: 15px;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }
    
    .feature-item {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        border-left: 3px solid var(--terralize_ap-primary, #70c141);
    }
    
    .feature-item h4 {
        color: var(--terralize_ap-primary, #70c141);
        margin-top: 0;
    }
    
    .doc-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    
    .doc-table th,
    .doc-table td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        text-align: left;
    }
    
    .doc-table th {
        background: #f5f5f5;
        font-weight: 600;
    }
    
    .doc-table tr:nth-child(even) {
        background: #f9f9f9;
    }
    
    .faq-item {
        margin-bottom: 20px;
        border: 1px solid #eee;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .faq-item h4 {
        margin: 0;
        padding: 15px;
        background: #f5f5f5;
        cursor: pointer;
        position: relative;
    }
    
    .faq-answer {
        padding: 0 15px;
        border-top: 1px solid #eee;
    }
    
    code, pre {
        background: #f5f5f5;
        padding: 2px 5px;
        border-radius: 3px;
        font-family: monospace;
        font-size: 0.9em;
    }
    
    pre {
        padding: 15px;
        overflow-x: auto;
        margin: 15px 0;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Navigation par onglets
        $('.nav-tabs a').on('click', function(e) {
            e.preventDefault();
            
            // Supprimer la classe active de tous les onglets et sections
            $('.nav-tabs a').removeClass('active');
            $('.doc-section').removeClass('active');
            
            // Ajouter la classe active à l'onglet cliqué
            $(this).addClass('active');
            
            // Afficher la section correspondante
            var target = $(this).attr('href');
            $(target).addClass('active');
        });
        
        // Gestion des questions FAQ (toggle)
        $('.faq-item h4').on('click', function() {
            $(this).next('.faq-answer').slideToggle();
        });
        
        // Si un hash est présent dans l'URL, activer l'onglet correspondant
        if (window.location.hash) {
            var hash = window.location.hash;
            $('.nav-tabs a[href="' + hash + '"]').trigger('click');
        }
    });
    </script>
    <?php
}

// Section sur l'importance d'importer les communes
function terralize_ap_docs_communes_import_info() {
    ?>
    <div class="wrap">
        <h2>Importation des communes - Information importante</h2>
        
        <div class="card" style="max-width: 100%;">
            <h3>Pourquoi importer les communes ?</h3>
            <p>L'importation des communes dans la base de données est une étape <strong>essentielle</strong> pour le bon fonctionnement de la carte des panneaux et de ses filtres géographiques.</p>
            
            <h3>Fonctionnalités dépendant de l'importation des communes :</h3>
            <ul>
                <li><strong>Filtre par ville centrale :</strong> Permet aux utilisateurs de sélectionner une ville comme point central et de filtrer les panneaux dans un certain rayon.</li>
                <li><strong>Recherche géographique :</strong> Améliore la précision des recherches basées sur la localisation.</li>
                <li><strong>Performance :</strong> Évite de charger et de parser le fichier CSV à chaque affichage de la carte.</li>
            </ul>
            
            <h3>Comment vérifier si les communes sont importées ?</h3>
            <p>Si les communes ne sont pas importées, vous verrez une notification en haut de l'interface d'administration. De plus, le menu déroulant "Ville centrale" sur la carte affichera un message indiquant qu'aucune commune n'est disponible.</p>
            
            <h3>Comment importer les communes ?</h3>
            <ol>
                <li>Accédez à <strong>Affichage Premier > Importer des panneaux</strong> dans le menu d'administration.</li>
                <li>Dans la section "Importer les communes", cliquez sur le bouton "Importer les communes".</li>
                <li>Le processus importera automatiquement toutes les communes des départements 59, 62 et 80 depuis le fichier CSV fourni avec le plugin.</li>
            </ol>
            
            <div class="notice notice-info inline">
                <p><strong>Note :</strong> L'importation peut prendre quelques minutes. Une fois terminée, un message de confirmation s'affichera avec le nombre de communes importées.</p>
            </div>
        </div>
    </div>
    <?php
}

// Ajouter cette section à la documentation existante si elle est disponible
if (function_exists('add_action')) {
    add_action('terralize_ap_docs_section_after_import', 'terralize_ap_docs_communes_import_info');
} 