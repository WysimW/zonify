<?php
/**
 * Template pour l'affichage d'un panneau d'affichage
 */
get_header();

$panel_reference = get_post_meta(get_the_ID(), 'panel_reference', true);
$panel_type = get_post_meta(get_the_ID(), 'panel_type', true);
$panel_support = get_post_meta(get_the_ID(), 'panel_support', true);
$panel_width = get_post_meta(get_the_ID(), 'panel_width', true);
$panel_height = get_post_meta(get_the_ID(), 'panel_height', true);
$panel_format = get_post_meta(get_the_ID(), 'panel_format', true);
$panel_format_standard = get_post_meta(get_the_ID(), 'panel_format_standard', true);
$panel_disponibilite = get_post_meta(get_the_ID(), 'panel_disponibilite', true);
$panel_annonceur = get_post_meta(get_the_ID(), 'panel_annonceur', true);
$panel_date_fin = get_post_meta(get_the_ID(), 'panel_date_fin', true);

// Localisation
$panel_address = get_post_meta(get_the_ID(), 'panel_address', true);
$panel_cp = get_post_meta(get_the_ID(), 'panel_cp', true);
$panel_city = get_post_meta(get_the_ID(), 'panel_city', true);
$panel_dept = get_post_meta(get_the_ID(), 'panel_dept', true);
$panel_region = get_post_meta(get_the_ID(), 'panel_region', true);

// Coordonnées géographiques
$panel_lat = get_post_meta(get_the_ID(), 'panel_lat', true);
$panel_lng = get_post_meta(get_the_ID(), 'panel_lng', true);

// Visibilité
$visibility_angle = get_post_meta(get_the_ID(), 'visibility_angle', true);
$visibility_direction = get_post_meta(get_the_ID(), 'visibility_direction', true);
$visibility_distance = get_post_meta(get_the_ID(), 'visibility_distance', true);
$visibility_note = get_post_meta(get_the_ID(), 'visibility_note', true);
$panel_traffic = get_post_meta(get_the_ID(), 'panel_traffic', true);

// Photo du panneau
$photo_id = get_post_meta(get_the_ID(), 'panel_photo_id', true);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('terralize-ap-panel'); ?>>
    <div class="container">
        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>

        <div class="entry-content">
            <div class="row">
                <div class="col-md-8">
                    <div class="panel-details card">
                        <div class="card-header">
                            <h2><?php _e('Détails du panneau', 'terralize-ap'); ?></h2>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong><?php _e('Référence:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_reference); ?></p>
                                    <p><strong><?php _e('Type:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_type); ?></p>
                                    <p><strong><?php _e('Support:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_support); ?></p>
                                    <p><strong><?php _e('Format:', 'terralize-ap'); ?></strong> 
                                        <?php 
                                        if ($panel_format_standard) {
                                            echo esc_html($panel_format_standard);
                                        } else {
                                            echo esc_html($panel_width) . ' x ' . esc_html($panel_height) . ' m';
                                        }
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><?php _e('Disponibilité:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_disponibilite); ?></p>
                                    <?php if ($panel_annonceur) : ?>
                                        <p><strong><?php _e('Annonceur actuel:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_annonceur); ?></p>
                                    <?php endif; ?>
                                    <?php if ($panel_date_fin) : ?>
                                        <p><strong><?php _e('Fin de contrat:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_date_fin); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-location card mt-4">
                        <div class="card-header">
                            <h2><?php _e('Localisation', 'terralize-ap'); ?></h2>
                        </div>
                        <div class="card-body">
                            <p><strong><?php _e('Adresse:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_address); ?></p>
                            <p><strong><?php _e('Ville:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_cp) . ' ' . esc_html($panel_city); ?></p>
                            <p><strong><?php _e('Département:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_dept); ?></p>
                            <p><strong><?php _e('Région:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_region); ?></p>
                            
                            <?php if ($panel_lat && $panel_lng) : ?>
                                <div id="panel-map" style="height: 300px; margin-top: 20px;"></div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var map = L.map('panel-map').setView([<?php echo esc_js($panel_lat); ?>, <?php echo esc_js($panel_lng); ?>], 15);
                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                        }).addTo(map);
                                        
                                        L.marker([<?php echo esc_js($panel_lat); ?>, <?php echo esc_js($panel_lng); ?>]).addTo(map)
                                            .bindPopup('<?php echo esc_js(get_the_title()); ?>');
                                    });
                                </script>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="panel-visibility card mt-4">
                        <div class="card-header">
                            <h2><?php _e('Visibilité', 'terralize-ap'); ?></h2>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong><?php _e('Angle de vision:', 'terralize-ap'); ?></strong> <?php echo esc_html($visibility_angle); ?></p>
                                    <p><strong><?php _e('Direction:', 'terralize-ap'); ?></strong> <?php echo esc_html($visibility_direction); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><?php _e('Distance:', 'terralize-ap'); ?></strong> <?php echo esc_html($visibility_distance); ?> m</p>
                                    <p><strong><?php _e('Trafic journalier:', 'terralize-ap'); ?></strong> <?php echo esc_html($panel_traffic); ?></p>
                                </div>
                            </div>
                            <?php if ($visibility_note) : ?>
                                <p><strong><?php _e('Note:', 'terralize-ap'); ?></strong> <?php echo nl2br(esc_html($visibility_note)); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Section pour la photo du panneau -->
                    <?php if ($photo_id) : 
                        $img_url = wp_get_attachment_image_url($photo_id, 'large');
                        $img_full = wp_get_attachment_image_url($photo_id, 'full');
                    ?>
                    <div class="panel-photo card">
                        <div class="card-header">
                            <h2><?php _e('Photo du panneau', 'terralize-ap'); ?></h2>
                        </div>
                        <div class="card-body">
                            <a href="<?php echo esc_url($img_full); ?>" target="_blank">
                                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="img-fluid rounded" />
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="panel-actions card mt-4">
                        <div class="card-header">
                            <h2><?php _e('Actions', 'terralize-ap'); ?></h2>
                        </div>
                        <div class="card-body">
                            <a href="<?php echo esc_url(home_url('/contact/?panel=' . $panel_reference)); ?>" class="btn btn-primary btn-block mb-2">
                                <?php _e('Demander un devis', 'terralize-ap'); ?>
                            </a>
                            <a href="<?php echo esc_url(wp_get_referer() ? wp_get_referer() : home_url('/nos-panneaux/')); ?>" class="btn btn-outline-secondary btn-block">
                                <?php _e('Retour à la liste', 'terralize-ap'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

<?php get_footer(); ?> 