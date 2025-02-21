<?php

function zones_commerciales_page() {
    // Récupérer tous les commerciaux
    $args = array(
        'post_type'      => 'commercial',
        'posts_per_page' => -1,
    );
    $query = new WP_Query( $args );

    // Récupérer toutes les zones actives (commercials avec une meta 'zone_geojson')
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
    <div class="wrap">
        <h1>Gestion des Zones Commerciales</h1>
        <p>Choisissez le commercial :</p>
        <select id="commercial-select">
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
        <div id="map" style="height: 500px; margin-top:20px;"></div>
        <button id="save-zone">Sauvegarder la zone</button>
    </div>
    <?php

    // Passer toutes les zones actives au script JavaScript
    wp_localize_script('zones-commerciaux-script', 'zonesAdminData', $zones);
}

