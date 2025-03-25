<?php
function zonify_main_page() {
    $plugin_version = '1.0';
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';
    ?>
    <div class="wrap zonify-home">
        <!-- En-tête -->
        <div class="zonify-header">
            <div class="zonify-header-left">
                <img src="<?php echo esc_url( $icon_url ); ?>" alt="Zonify Icon" class="zonify-icon" />
                <h1 class="zonify-title">Zonify by MBS</h1>
            </div>
            <div class="zonify-header-right">
                <span class="zonify-version">Version <?php echo esc_html($plugin_version); ?></span>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="zonify-content">
            <!-- Message de bienvenue -->
            <section class="zonify-section">
                <h2>Bienvenue sur Zonify</h2>
                <p>
                    Zonify est un plugin puissant conçu pour faciliter la gestion de vos zones commerciales.
                    Associez rapidement un commercial à chaque zone et gérez vos opérations grâce à une interface intuitive.
                </p>
            </section>
            
            <!-- Tutoriel d'utilisation -->
            <section class="zonify-section">
                <h3>Tutoriel d'utilisation</h3>
                <ol>
                    <li>
                        <strong>Création d'un commercial :</strong> Avant de tracer une zone, vous devez créer un commercial.
                        Pour cela, rendez-vous dans la section <a href="<?php echo admin_url('edit.php?post_type=commercial'); ?>">Gérer les Commerciaux</a>.
                    </li>
                    <li>
                        <strong>Tracer une zone :</strong> Accédez à la section <a href="<?php echo admin_url('admin.php?page=zonify_map'); ?>">Tracer des zones</a>.
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
            </section>
            
            <!-- Premiers pas -->
            <section class="zonify-section">
                <h3>Liens pratiques</h3>
                <p>
                    Pour démarrer, nous vous recommandons de consulter les sections ci-dessous :
                </p>
                <ul class="zonify-links">
                    <li><a href="<?php echo admin_url('edit.php?post_type=commercial'); ?>">Gérer les Commerciaux</a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=zonify_map'); ?>">Visualiser la Carte des Zones</a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=zonify_list'); ?>">Gérer la Liste des Zones</a></li>
                </ul>
            </section>
            
            <!-- Ressources et support -->
            <section class="zonify-section">
                <h3>Ressources &amp; Support</h3>
                <p>
                    Pour en savoir plus sur Zonify, consultez la documentation ou contactez notre support technique.
                </p>
                <ul class="zonify-links">
                    <li><a href="https://votresite.com/support" target="_blank">Support Technique</a></li>
                </ul>
                <?php echo '<a href="' . admin_url('admin-post.php?action=zonify_export_geojson') . '" class="button button-primary">Exporter toutes les zones (GeoJSON)</a>';
?>
            </section>
        </div>
    </div>

    <?php
}
?>
