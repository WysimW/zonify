<?php
function zonify_map_pages() {
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';

    // 1. Lister les commerciaux
    $args_com = array(
        'post_type' => 'commercial',
        'posts_per_page' => -1
    );
    $commercials_query = new WP_Query($args_com);

    // 2. Lister toutes les zones
    $args_zone = array(
        'post_type' => 'zone',
        'posts_per_page' => -1
    );
    $zone_query = new WP_Query($args_zone);
    $zones = array();
    if ($zone_query->have_posts()) {
        while ($zone_query->have_posts()) {
            $zone_query->the_post();
            $zone_geojson = get_post_meta(get_the_ID(), 'zone_geojson', true);
            $zone_commercial_id = get_post_meta(get_the_ID(), 'zone_commercial_id', true);

            if ($zone_geojson) {
                $zones[] = array(
                    'type' => 'Feature',
                    'properties' => array(
                        'zone_id' => get_the_ID(),
                        'commercial_id' => $zone_commercial_id,
                        'commercial_title' => $zone_commercial_id ? get_the_title($zone_commercial_id) : '',
                    ),
                    'geometry' => json_decode($zone_geojson, true)
                );
            }
        }
        wp_reset_postdata();
    }
    ?>
    <div class="wrap zonify-map-page">
        <header class="zonify-banner">
            <div class="zonify-banner-left">
                <img src="<?php echo esc_url($icon_url); ?>" alt="Zonify Icon" class="zonify-icon" />
                <h1 class="zonify-title">Zonify by MBS</h1>
            </div>
        </header>

        <main class="zonify-content">
            <section class="zonify-section">
                <h2>Gestion des Zones Commerciales</h2>
                <div class="zonify-form-group">
                    <label for="commercial-select">Commercial :</label>
                    <select id="commercial-select" class="zonify-select">
                        <option value="">-- Sélectionnez un commercial --</option>
                        <?php
                        if ($commercials_query->have_posts()) {
                            while ($commercials_query->have_posts()) {
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
                <button id="save-zones" class="button button-primary" style="margin-top:20px;">Sauvegarder les zones</button>
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

    // Passer les zones au script JS
    wp_localize_script('zonify-script', 'zonesAdminData', $zones);
}
