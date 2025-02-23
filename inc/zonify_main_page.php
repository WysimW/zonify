<?php
function zonify_main_page() {
    // Définissez ici la version du plugin (vous pouvez aussi la récupérer dynamiquement)
    $plugin_version = '1.0';
    // Chemin de l'icône du plugin
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';
    ?>
    <div class="wrap zonify-home">
        <div class="zonify-banner">
            <img src="<?php echo esc_url( $icon_url ); ?>" alt="Zonify Icon" class="zonify-icon" />
            <h2 class="zonify-title">Zonify by MBS</h2>
        </div>
        <p class="zonify-version">Version : <?php echo esc_html($plugin_version); ?></p>
        
        <div class="zonify-tutorial">
            <h2>Bienvenue sur Zonify !</h2>
            <p>
                Zonify est un plugin qui vous permet de gérer facilement les zones commerciales et d'associer à chacune d'elles un commercial.
                Vous pouvez créer, modifier et visualiser vos zones via une interface intuitive.
            </p>
            <h3>Pour commencer :</h3>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=zonify_map'); ?>">Gestion des Zones</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=zonify_list'); ?>">Liste des Zones</a></li>
                <li><a href="<?php echo admin_url('edit.php?post_type=commercial'); ?>">Liste des Commerciaux</a></li>

                <!-- Ajoutez d'autres liens si nécessaire -->
            </ul>
        </div>
    </div>
    <?php
}
