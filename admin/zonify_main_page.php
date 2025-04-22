<?php
// Prévenir l'accès direct au fichier
if (!defined('ABSPATH')) {
    die('Accès direct interdit.');
}

/**
 * Affiche la page principale du plugin Terralize
 */
function terralize_main_page() {
    // Informations sur le plugin
    $plugin_version = '1.0';
    $plugin_dir = plugin_dir_url(dirname(__FILE__));
    $icon_url = $plugin_dir . 'assets/icons/icon.png';
    
    // Vérifier si l'extension Terralize Affichage Premier est activée
    $affichage_premier_active = class_exists('TerralizeAffichagePremier') || function_exists('terralize_affichage_premier_init');
    ?>
    <div class="wrap terralize-home">
        <!-- En-tête -->
        <div class="terralize-header">
            <div class="terralize-header-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Terralize Icon" class="terralize-icon" />
                <h1 class="terralize-title">Terralize by MBS</h1>
            </div>
            <div class="terralize-header-right">
                <span class="terralize-version">Version <?php echo esc_html($plugin_version); ?></span>
                <?php if ($affichage_premier_active): ?>
                <span class="extension-badge" style="background-color: #70c141; color: white; padding: 3px 8px; border-radius: 4px; margin-left: 10px; font-size: 12px;">Extension Affichage Premier activée</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="terralize-content">
            <!-- Message de bienvenue -->
            <section class="terralize-section">
                <h2>Bienvenue sur Terralize</h2>
                <p>
                    Terralize est un plugin puissant conçu pour faciliter la gestion de vos zones commerciales.
                    <?php if ($affichage_premier_active): ?>
                    Avec l'extension Affichage Premier, vous pouvez également gérer et présenter vos panneaux d'affichage urbain sur une carte interactive.
                    <?php else: ?>
                    Associez rapidement un commercial à chaque zone et gérez vos opérations grâce à une interface intuitive.
                    <?php endif; ?>
                </p>
            </section>
            
            <!-- Tutoriel d'utilisation -->
            <section class="terralize-section">
                <h3>Tutoriel d'utilisation</h3>
                
                <?php if ($affichage_premier_active): ?>
                <div class="terralize-tabs">
                    <div class="tab-buttons">
                        <button class="tab-btn active" data-target="zones">Gestion des zones</button>
                        <button class="tab-btn" data-target="panneaux">Gestion des panneaux d'affichage</button>
                    </div>
                    
                    <div class="tab-content">
                        <div class="tab-panel active" id="zones-panel">
                            <ol>
                                <li>
                                    <strong>Création d'un commercial :</strong> Avant de tracer une zone, vous devez créer un commercial.
                                    Pour cela, rendez-vous dans la section <a href="<?php echo esc_url(admin_url('edit.php?post_type=commercial')); ?>">Gérer les Commerciaux</a>.
                                </li>
                                <li>
                                    <strong>Tracer une zone :</strong> Accédez à la section <a href="<?php echo esc_url(admin_url('admin.php?page=terralize_map')); ?>">Tracer des zones</a>.
                                    <br />
                                    <em>Étapes :</em>
                                    <ul>
                                        <li>Sélectionnez le commercial concerné dans le menu déroulant.</li>
                                        <li>
                                            Utilisez l'outil de traçage symbolisé par l'icône du polygone (Leaflet Draw) pour dessiner la zone.
                                            Cliquez successivement pour définir les sommets. Pour fermer la zone, assurez-vous que le dernier point rejoint le premier.
                                        </li>
                                        <li>Cliquez sur le bouton <em>Sauvegarder la zone</em> et attendez l'apparition du message de validation.</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong>Modification d'une zone :</strong> Pour modifier une zone existante, cliquez sur l'outil d'édition (icône d'édition).
                                    Vous pourrez alors ajuster les points du polygone.
                                    Une fois vos modifications effectuées, cliquez de nouveau sur <em>Sauvegarder la zone</em> pour enregistrer les changements.
                                </li>
                            </ol>
                        </div>
                        
                        <div class="tab-panel" id="panneaux-panel">
                            <ol>
                                <li>
                                    <strong>Création d'un panneau d'affichage :</strong> Accédez à la section <a href="<?php echo esc_url(admin_url('edit.php?post_type=affichage_panneau')); ?>">Panneaux d'affichage</a> et cliquez sur "Ajouter".
                                </li>
                                <li>
                                    <strong>Remplir les informations :</strong> Complétez les champs suivants:
                                    <ul>
                                        <li>Titre et référence du panneau</li>
                                        <li>Adresse et localisation</li>
                                        <li>Type de panneau et support</li>
                                        <li>Dimensions et format</li>
                                        <li>Statut de disponibilité</li>
                                        <li>Informations de visibilité</li>
                                        <li>Catégories (facultatif)</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong>Définir l'emplacement :</strong> Utilisez la carte pour marquer l'emplacement précis du panneau.
                                </li>
                                <li>
                                    <strong>Ajouter une image :</strong> Téléchargez une photo du panneau pour faciliter son identification.
                                </li>
                                <li>
                                    <strong>Visualisation sur la carte :</strong> Après avoir enregistré vos panneaux, vous pouvez les visualiser sur la <a href="<?php echo esc_url(home_url('/carte-des-panneaux/')); ?>">carte interactive</a>.
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <ol>
                    <li>
                        <strong>Création d'un commercial :</strong> Avant de tracer une zone, vous devez créer un commercial.
                        Pour cela, rendez-vous dans la section <a href="<?php echo esc_url(admin_url('edit.php?post_type=commercial')); ?>">Gérer les Commerciaux</a>.
                    </li>
                    <li>
                        <strong>Tracer une zone :</strong> Accédez à la section <a href="<?php echo esc_url(admin_url('admin.php?page=terralize_map')); ?>">Tracer des zones</a>.
                        <br />
                        <em>Étapes :</em>
                        <ul>
                            <li>Sélectionnez le commercial concerné dans le menu déroulant.</li>
                            <li>
                                Utilisez l'outil de traçage symbolisé par l'icône du polygone (Leaflet Draw) pour dessiner la zone.
                                Cliquez successivement pour définir les sommets. Pour fermer la zone, assurez-vous que le dernier point rejoint le premier.
                            </li>
                            <li>Cliquez sur le bouton <em>Sauvegarder la zone</em> et attendez l'apparition du message de validation.</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Modification d'une zone :</strong> Pour modifier une zone existante, cliquez sur l'outil d'édition (icône d'édition).
                        Vous pourrez alors ajuster les points du polygone.
                        Une fois vos modifications effectuées, cliquez de nouveau sur <em>Sauvegarder la zone</em> pour enregistrer les changements.
                    </li>
                </ol>
                <?php endif; ?>
            </section>
            
            <!-- Premiers pas -->
            <section class="terralize-section">
                <h3>Liens pratiques</h3>
                <p>
                    Pour démarrer, nous vous recommandons de consulter les sections ci-dessous :
                </p>
                <ul class="terralize-links">
                    <?php if ($affichage_premier_active): ?>
                    <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=commercial')); ?>">Gérer les Commerciaux</a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=terralize_map')); ?>">Visualiser la Carte des Zones</a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=terralize_list')); ?>">Gérer la Liste des Zones</a></li>
                    <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=affichage_panneau')); ?>">Gérer les Panneaux d'Affichage</a></li>
                    <li><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=panneau_category&post_type=affichage_panneau')); ?>">Gérer les Catégories de Panneaux</a></li>
                    <li><a href="<?php echo esc_url(home_url('/carte-des-panneaux/')); ?>">Voir la Carte des Panneaux (frontend)</a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=terralize_affichage_docs')); ?>">Documentation de l'Extension</a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=terralize_affichage_settings')); ?>">Paramètres de la Carte des Panneaux</a></li>
                    <?php else: ?>
                    <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=commercial')); ?>">Gérer les Commerciaux</a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=terralize_map')); ?>">Visualiser la Carte des Zones</a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=terralize_list')); ?>">Gérer la Liste des Zones</a></li>
                    <?php endif; ?>
                </ul>
            </section>
            
            <!-- Ressources et support -->
            <section class="terralize-section">
                <h3>Ressources &amp; Support</h3>
                <p>
                    Pour en savoir plus sur Terralize, consultez la documentation ou contactez notre support technique.
                </p>
                <ul class="terralize-links">
                    <li><a href="https://votresite.com/support" target="_blank">Support Technique</a></li>
                    <?php if ($affichage_premier_active): ?>
                    <li><a href="https://votresite.com/documentation-affichage-premier" target="_blank">Documentation de l'extension Affichage Premier</a></li>
                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=terralize_affichage_docs')); ?>">Aide Rapide</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="export-buttons" style="margin-top: 20px;">
                    <?php echo '<a href="' . esc_url(admin_url('admin-post.php?action=terralize_export_geojson')) . '" class="button button-primary">Exporter toutes les zones (GeoJSON)</a>'; ?>
                    
                    <?php if ($affichage_premier_active): ?>
                    <?php echo '<a href="' . esc_url(admin_url('admin-post.php?action=terralize_export_panneaux_geojson')) . '" class="button button-primary" style="margin-left: 10px;">Exporter tous les panneaux (GeoJSON)</a>'; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <?php if ($affichage_premier_active): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des onglets
        var tabButtons = document.querySelectorAll('.tab-btn');
        var tabPanels = document.querySelectorAll('.tab-panel');
        
        tabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                // Désactiver tous les onglets
                tabButtons.forEach(function(btn) {
                    btn.classList.remove('active');
                });
                tabPanels.forEach(function(panel) {
                    panel.classList.remove('active');
                });
                
                // Activer l'onglet cliqué
                this.classList.add('active');
                var target = this.getAttribute('data-target');
                document.getElementById(target + '-panel').classList.add('active');
            });
        });
    });
    </script>
    <style>
    .terralize-tabs {
        margin-top: 20px;
    }
    
    .tab-buttons {
        border-bottom: 1px solid #ccc;
        margin-bottom: 20px;
    }
    
    .tab-btn {
        background: none;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        outline: none;
    }
    
    .tab-btn.active {
        border-bottom: 2px solid #70c141;
        color: #70c141;
    }
    
    .tab-panel {
        display: none;
    }
    
    .tab-panel.active {
        display: block;
    }
    
    .extension-badge {
        display: inline-block;
    }
    </style>
    <?php endif; ?>

    <?php
}
?>
