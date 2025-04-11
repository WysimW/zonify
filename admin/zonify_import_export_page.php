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
        
        // Vérifier si un import de panneaux a été effectué
        if ( isset($_GET['import_done']) ) {
            $created = isset($_GET['created']) ? intval($_GET['created']) : 0;
            $updated = isset($_GET['updated']) ? intval($_GET['updated']) : 0;
            $errors = isset($_GET['errors']) ? intval($_GET['errors']) : 0;
            echo '<div class="updated notice"><p>Import CSV des panneaux réussi : ' . $created . ' panneaux créés, ' . $updated . ' panneaux mis à jour, ' . $errors . ' erreurs.</p></div>';
        }
        
        // Vérifier s'il y a eu une erreur lors de l'importation
        if ( isset($_GET['import_error']) ) {
            $error_msg = sanitize_text_field(urldecode($_GET['import_error']));
            echo '<div class="error notice"><p>Erreur lors de l\'importation : ' . $error_msg . '</p></div>';
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

        <h2>Import des Zones</h2>
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
        
        <!-- Formulaire d'import CSV pour les zones -->
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url('admin-post.php?action=zonify_import_csv') ); ?>" style="margin-top: 20px;">
            <?php wp_nonce_field('zonify_import_csv_nonce'); ?>
            <p>
                <label for="zones_csv">Fichier CSV des zones :</label>
                <input type="file" name="zones_csv" id="zones_csv" accept=".csv,text/csv" />
            </p>
            <input type="submit" value="Importer CSV" class="button button-primary" />
        </form>
        
        <br/>
        
        <?php
        // Vérifier si l'extension Affichage Premier est active
        if (class_exists('ZonifyAffichagePremier')) {
            ?>
            <hr/>
            
            <h2>Import des Panneaux d'Affichage</h2>
            <p>Importez vos panneaux d'affichage à partir d'un fichier CSV au format "PATRIMOINE ED".</p>

            <div class="card">
                <h3>Instructions</h3>
                <p>Le fichier CSV doit contenir au moins les colonnes suivantes :</p>
                <ul>
                    <li>CODE REFERENCE PHOTO - Référence unique du panneau</li>
                    <li>VILLE - Ville du panneau</li>
                    <li>CODE POSTAL - Code postal</li>
                    <li>ADRESSE - Adresse du panneau</li>
                    <li>COORDONNEES GPS DU PANNEAU Y - Latitude (exemple: 50,23929)</li>
                    <li>COORDONNEES GPS DU PANNEAU X - Longitude (exemple: 2,65531)</li>
                    <li>FORMAT - Format du panneau (ex: MOBILIER URBAIN, GRAND FORMAT)</li>
                    <li>TYPE - Type de panneau (ex: DEROULANT, FIXE)</li>
                    <li>SUPPORT - Support du panneau (ex: VITRINE, TOLE)</li>
                    <li>LARGEUR EN CM - Largeur</li>
                    <li>HAUTEUR EN CM - Hauteur</li>
                </ul>
            </div>

            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php?action=zonify_ap_import_csv')); ?>" style="margin-top: 20px;">
                <?php wp_nonce_field('zonify_ap_import_csv_nonce'); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="panneaux_csv">Fichier CSV :</label></th>
                        <td>
                            <input type="file" name="panneaux_csv" id="panneaux_csv" accept=".csv,text/csv" required />
                            <p class="description">Format attendu : CSV séparé par des virgules (,).<br>
                            Encodage recommandé : UTF-8</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Options d'importation :</th>
                        <td>
                            <label for="update_existing">
                                <input type="checkbox" name="update_existing" id="update_existing" value="1" checked />
                                Mettre à jour les panneaux existants (basé sur le CODE REFERENCE PHOTO)
                            </label><br>
                            
                            <label for="skip_header">
                                <input type="checkbox" name="skip_header" id="skip_header" value="1" checked />
                                Ignorer la première ligne (en-têtes)
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Importer les panneaux CSV" />
                </p>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}
