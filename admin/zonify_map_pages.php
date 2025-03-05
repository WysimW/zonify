<?php
function zonify_map_pages() {
    // Récupérer l'URL de l'icône
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';

    // Récupérer l’option toggle
    $always_show_all_zones = get_option('zonify_always_show_all_zones', 0);

    // Récupérer tous les commerciaux (pour le <select>)
    $args_com = array(
        'post_type'      => 'commercial',
        'posts_per_page' => -1,
    );
    $commercials_query = new WP_Query($args_com);

    // Tableau qui contiendra les polygones si on veut tous les charger
    $zones = array();
    if ( $always_show_all_zones ) {
        // Charger toutes les zones
        $args_zone = array(
            'post_type'      => 'zone',
            'posts_per_page' => -1,
        );
        $zone_query = new WP_Query($args_zone);
        if ( $zone_query->have_posts() ) {
            while ( $zone_query->have_posts() ) {
                $zone_query->the_post();
                $zone_geojson = get_post_meta( get_the_ID(), 'zone_geojson', true );
                $zone_com_id  = get_post_meta( get_the_ID(), 'zone_commercial_id', true );

                if ( $zone_geojson ) {
                    $zones[] = array(
                        'type' => 'Feature',
                        'properties' => array(
                            'zone_id'          => get_the_ID(),
                            'commercial_id'    => $zone_com_id,
                            'commercial_title' => $zone_com_id ? get_the_title($zone_com_id) : '',
                        ),
                        'geometry' => json_decode($zone_geojson, true )
                    );
                }
            }
            wp_reset_postdata();
        }
    }
    ?>
    <div class="wrap zonify-map-page">
        <!-- Bandeau -->
        <header class="zonify-banner">
            <div class="zonify-banner-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Zonify Icon" class="zonify-icon" />
                <h1 class="zonify-title">Zonify by MBS</h1>
            </div>
        </header>

        <main class="zonify-content">
            <section class="zonify-section">
                <h2>Gestion des Zones Commerciales</h2>
                <p>Sélectionnez un commercial pour afficher et gérer sa zone géographique.</p>
                <div class="zonify-form-group">
                    <label for="commercial-select">Commercial :</label>
                    <select id="commercial-select" class="zonify-select">
                        <option value="">-- Sélectionnez un commercial --</option>
                        <?php
                        if ( $commercials_query->have_posts() ) {
                            while ( $commercials_query->have_posts() ) {
                                $commercials_query->the_post();
                                echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                            }
                            wp_reset_postdata();
                        }
                        ?>
                    </select>
                </div>
            </section>

            <section class="zonify-section">
                <h3>Carte Interactive</h3>
                <div id="map" style="height: 500px; margin-top:20px;"></div>
                <button id="save-zone" class="button button-primary" style="margin-top:20px;">Sauvegarder la zone</button>
                <button id="toggle-view-mode" class="button" style="margin-top:20px;">Basculer le mode d’affichage</button>
            </section>
        </main>
    </div>

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

    // Passer la liste (potentielle) des zones au script
    // On passe aussi la valeur always_show_all_zones (0 ou 1)
    wp_localize_script('zonify-script', 'zonesAdminData', $zones);
    wp_localize_script('zonify-script', 'alwaysShowAllZones', $always_show_all_zones);
}
