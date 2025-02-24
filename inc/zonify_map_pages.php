<?php
function zonify_map_pages() {
    // Récupérer l'URL de l'icône (située dans le dossier du plugin)
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';
    
    // Récupérer tous les commerciaux
    $args = array(
        'post_type'      => 'commercial',
        'posts_per_page' => -1,
    );
    $query = new WP_Query( $args );

    // Récupérer toutes les zones actives (commerciaux avec une meta 'zone_geojson')
    $zones = array();
    $args_zone = array(
        'post_type'      => 'commercial',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'zone_geojson',
                'compare' => 'EXISTS',
            ),
        ),
    );
    $zone_query = new WP_Query( $args_zone );
    if ( $zone_query->have_posts() ) {
        while ( $zone_query->have_posts() ) {
            $zone_query->the_post();
            $zone_geojson = get_post_meta( get_the_ID(), 'zone_geojson', true );
            if ( $zone_geojson ) {
                $zones[] = array(
                    'type'       => 'Feature',
                    'properties' => array(
                        'commercial_id'    => get_the_ID(),
                        'commercial_title' => get_the_title(),
                    ),
                    // On suppose que le champ stocke uniquement la géométrie
                    'geometry'   => json_decode( $zone_geojson, true )
                );
            }
        }
        wp_reset_postdata();
    }
    ?>
    <div class="wrap zonify-map-page">
        <!-- En-tête de la page -->
        <header class="zonify-banner">
            <div class="zonify-banner-left">
                <img src="<?php echo esc_url( $icon_url ); ?>" alt="Zonify Icon" class="zonify-icon" />
                <h1 class="zonify-title">Zonify by MBS</h1>
            </div>
        </header>
        
        <!-- Contenu principal -->
        <main class="zonify-content">
            <section class="zonify-section">
                <h2>Gestion des Zones Commerciales</h2>
                <p>Sélectionnez un commercial pour afficher et gérer sa zone géographique.</p>
                <div class="zonify-form-group">
                    <label for="commercial-select">Commercial :</label>
                    <select id="commercial-select" class="zonify-select">
                        <option value="">-- Sélectionnez un commercial --</option>
                        <?php
                        if ( $query->have_posts() ) :
                            while ( $query->have_posts() ) : $query->the_post();
                                echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                    </select>
                </div>
            </section>
            
            <section class="zonify-section">
                <h3>Carte Interactive</h3>
                <div id="map" style="height: 500px; margin-top:20px;"></div>
                <button id="save-zone" class="button button-primary" style="margin-top:20px;">Sauvegarder la zone</button>
            </section>
        </main>
    </div>
    
    <!-- Style intégré pour une apparence professionnelle -->
    <style>
 .zonify-map-page {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #444;
        }
        .zonify-banner {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #e5e5e5;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        .zonify-banner-left {
            display: flex;
            align-items: center;
        }
        .zonify-icon {
            width: 60px;
            margin-right: 15px;
        }
        .zonify-title {
            font-size: 2em;
            margin: 0;
        }
    </style>
    <?php
    // Passer toutes les zones actives au script JavaScript
    wp_localize_script('zonify-script', 'zonesAdminData', $zones);
}
?>
