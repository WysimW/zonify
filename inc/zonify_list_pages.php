<?php
function zonify_list_pages() {
    // Définir l'URL de l'icône (située dans le dossier du plugin)
    $icon_url = plugin_dir_url(__FILE__) . '../assets/icons/icon.png';
    ?>
    <div class="wrap">
        <!-- Bandeau de présentation de Zonify -->
        <div class="zonify-banner">
            <img src="<?php echo esc_url( $icon_url ); ?>" alt="Zonify Icon" class="zonify-icon" />
            <h2 class="zonify-title">Zonify by MBS</h1>
        </div>
        <h1>Liste des zones actives</h1>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Commercial</th>
                    <th>Zone (GeoJSON)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type'      => 'commercial',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        array(
                            'key'     => 'zone_geojson',
                            'compare' => 'EXISTS'
                        )
                    )
                );
                $query = new WP_Query( $args );
                if ( $query->have_posts() ) :
                    while ( $query->have_posts() ) : $query->the_post();
                        $zone = get_post_meta( get_the_ID(), 'zone_geojson', true );
                        ?>
                        <tr>
                            <td><?php the_title(); ?></td>
                            <td><?php echo esc_html( $zone ); ?></td>
                            <td><a href="<?php echo get_edit_post_link(); ?>">Modifier</a></td>
                        </tr>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <tr>
                        <td colspan="3">Aucune zone active.</td>
                    </tr>
                    <?php
                endif;
                ?>
            </tbody>
        </table>
    </div>
    <?php
}



function zones_commerciales_add_meta_box() {
    add_meta_box(
        'infos_commercial',
        'Informations Commercial',
        'zones_commerciales_meta_box_callback',
        'commercial'
    );
}
add_action( 'add_meta_boxes', 'zones_commerciales_add_meta_box' );

