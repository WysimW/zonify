<?php
/**
 * Template pour l'affichage d'une implantation
 * 
 * @package TerralizeGFG
 * @version 1.0.0
 */

get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="featured-image">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                    </header>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    // Récupérer les POI liés à cette implantation
                    $linked_pois = get_posts(array(
                        'post_type' => 'poi',
                        'posts_per_page' => -1,
                        'meta_key' => '_poi_implantations_id',
                        'meta_value' => get_the_ID(),
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ));

                    if (!empty($linked_pois)) : ?>
                        <div class="linked-pois">
                            <h2><?php _e('Points de vente associés', 'terralize'); ?></h2>
                            <div class="poi-grid">
                                <?php foreach ($linked_pois as $poi) : ?>
                                    <div class="poi-card">
                                        <h3><?php echo esc_html($poi->post_title); ?></h3>
                                        <?php
                                        // Récupérer les coordonnées du POI
                                        $geojson = get_post_meta($poi->ID, 'poi_geojson', true);
                                        if ($geojson) {
                                            $coordinates = json_decode($geojson, true);
                                            if (isset($coordinates['coordinates'])) {
                                                $lat = $coordinates['coordinates'][1];
                                                $lng = $coordinates['coordinates'][0];
                                                ?>
                                                <div class="poi-map" data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>"></div>
                                                <?php
                                            }
                                        }
                                        ?>
                                        <div class="poi-actions">
                                            <a href="<?php echo get_permalink($poi->ID); ?>" class="btn btn-primary">
                                                <?php _e('Voir le point de vente', 'terralize'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        </div>

        <div class="col-md-4">
            <div class="sidebar">
                <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
</div>

<?php
// Enqueue Leaflet pour les mini-cartes
wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);

// Script pour initialiser les mini-cartes
wp_add_inline_script('leaflet-js', '
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".poi-map").forEach(function(mapDiv) {
            var lat = parseFloat(mapDiv.dataset.lat);
            var lng = parseFloat(mapDiv.dataset.lng);
            
            var map = L.map(mapDiv).setView([lat, lng], 15);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap contributors"
            }).addTo(map);
            
            L.marker([lat, lng]).addTo(map);
        });
    });
');
?>

<?php get_footer(); ?> 