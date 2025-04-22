<?php
function terralize_map_pages()
{
    // Récupérer l'URL de l'icône
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';

    // Récupérer l’option toggle
    $always_show_all_zones = get_option('terralize_always_show_all_zones', 0);

    // Récupérer tous les commerciaux (pour le <select>)
    $args_com = array(
        'post_type'      => 'commercial',
        'posts_per_page' => -1,
    );
    $commercials_query = new WP_Query($args_com);

    // Tableau qui contiendra les polygones si on veut tous les charger
    $zones = array();
    if ($always_show_all_zones) {
        // Charger toutes les zones
        $args_zone = array(
            'post_type'      => 'zone',
            'posts_per_page' => -1,
        );
        $zone_query = new WP_Query($args_zone);
        if ($zone_query->have_posts()) {
            while ($zone_query->have_posts()) {
                $zone_query->the_post();
                $zone_geojson = get_post_meta(get_the_ID(), 'zone_geojson', true);
                $zone_com_id  = get_post_meta(get_the_ID(), 'zone_commercial_id', true);

                if ($zone_geojson) {
                    $zones[] = array(
                        'type' => 'Feature',
                        'properties' => array(
                            'zone_id'          => get_the_ID(),
                            'commercial_id'    => $zone_com_id,
                            'commercial_title' => $zone_com_id ? get_the_title($zone_com_id) : '',
                        ),
                        'geometry' => json_decode($zone_geojson, true)
                    );
                }
            }
            wp_reset_postdata();
        }
    }
?>
    <div class="wrap terralize-map-page">
        <!-- Bandeau -->
        <header class="terralize-banner">
            <div class="terralize-banner-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Terralize Icon" class="terralize-icon" />
                <h1 class="terralize-title">Terralize by MBS</h1>
            </div>
        </header>

        <main class="terralize-content">
            <section class="terralize-section">
                <h2>Gestion des Zones Commerciales</h2>
                <p>Sélectionnez un commercial et/ou une région pour afficher et gérer les zones géographiques.</p>
                <div class="terralize-form-group">
                    <label for="commercial-select">Commercial :</label>
                    <select id="commercial-select" class="terralize-select" multiple>
                        <option value="0">-- Aucun commercial --</option>
                        <?php
                        if ($commercials_query->have_posts()) :
                            while ($commercials_query->have_posts()) : $commercials_query->the_post();
                                echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                    </select>
                </div>

                <div class="terralize-form-group">
                    <label for="region-select">Région :</label>
                    <select id="region-select" class="terralize-select" multiple>
                        <option value="0">-- Aucune région --</option>
                        <?php
                        // Récupération des régions
                        $regions = get_terms([
                            'taxonomy' => 'region',
                            'hide_empty' => false,
                        ]);
                        
                        if (!is_wp_error($regions) && !empty($regions)) :
                            foreach ($regions as $region) :
                                echo '<option value="' . $region->term_id . '">' . $region->name . '</option>';
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>
            </section>

            <section class="terralize-section">
                <h3>Carte Interactive</h3>
                <div id="map" style="height: 500px; margin-top:20px;"></div>
            </section>
        </main>
    </div>

    <style>
        .terralize-map-page {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #444;
        }

        .terralize-banner {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #e5e5e5;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .terralize-banner-left {
            display: flex;
            align-items: center;
        }

        .terralize-icon {
            width: 60px;
            margin-right: 15px;
        }

        .terralize-title {
            font-size: 2em;
            margin: 0;
        }
    </style>


<?php

    // Passer la liste (potentielle) des zones au script
    // On passe aussi la valeur always_show_all_zones (0 ou 1)
    wp_localize_script('terralize-script', 'zonesAdminData', $zones);
    wp_localize_script('terralize-script', 'alwaysShowAllZones', $always_show_all_zones);
}
