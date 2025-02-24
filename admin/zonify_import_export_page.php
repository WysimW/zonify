<?php
function zonify_import_export_page() {
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';

    ?>
    <div class="wrap">
    <div class="zonify-header">
            <div class="zonify-header-left">
                <img src="<?php echo esc_url( $icon_url ); ?>" alt="Zonify Icon" class="zonify-icon" />
                <h1 class="zonify-title">Zonify by MBS</h1>
            </div>
        </div>
        
        <h2>Importer / Exporter les Zones</h2>

        <?php
        // Vérifier si un import GeoJSON a été effectué
        if ( isset($_GET['geojson_import_done']) ) {
            $created = isset($_GET['created']) ? intval($_GET['created']) : 0;
            $updated = isset($_GET['updated']) ? intval($_GET['updated']) : 0;
            echo '<div class="updated notice"><p>Import GeoJSON réussi : ' . $created . ' zones créées, ' . $updated . ' mises à jour.</p></div>';
        }

        // Vérifier si un import CSV a été effectué
        if ( isset($_GET['csv_import_done']) ) {
            $created = isset($_GET['created']) ? intval($_GET['created']) : 0;
            $updated = isset($_GET['updated']) ? intval($_GET['updated']) : 0;
            echo '<div class="updated notice"><p>Import CSV réussi : ' . $created . ' zones créées, ' . $updated . ' mises à jour.</p></div>';
        }
        ?>

        <p>Depuis cette page, vous pouvez à la fois exporter ou importer l'ensemble de vos zones.</p>

        <h2>Export global</h2>
        <p>
            <!-- Bouton pour exporter en GeoJSON -->
            <a href="<?php echo esc_url( admin_url('admin-post.php?action=zonify_export_geojson') ); ?>" class="button button-primary">
                Exporter toutes les zones (GeoJSON)
            </a>
            <!-- Bouton pour exporter en CSV -->
            <a href="<?php echo esc_url( admin_url('admin-post.php?action=zonify_export_csv') ); ?>" class="button button-secondary">
                Exporter toutes les zones (CSV)
            </a>
        </p>

        <hr/>

        <h2>Import</h2>
        <p>Choisissez un fichier GeoJSON ou CSV pour importer (créer / mettre à jour) vos zones.</p>

        <!-- Formulaire d'import GeoJSON -->
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url('admin-post.php?action=zonify_import_geojson') ); ?>">
            <?php wp_nonce_field('zonify_import_geojson_nonce'); ?>
            <p>
                <label for="zones_geojson">Fichier GeoJSON :</label>
                <input type="file" name="zones_geojson" id="zones_geojson" accept=".geojson,.json,application/json" />
            </p>
            <input type="submit" value="Importer GeoJSON" class="button button-primary" />
        </form>

        <br/>
    </div>
    <?php
}
