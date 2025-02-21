<?php
/*
Template Name: Zones Commerciales Frontend
*/
get_header(); ?>
<style>
.zones-container {
    padding: 20px;
    background: #f7f7f7;
}
.zones-container h1 {
    text-align: center;
    margin-bottom: 20px;
}
</style>
<div class="zones-container">
    <h1>Nos zones commerciales</h1>
    <?php
    // Affichez le shortcode qui charge la carte
    echo do_shortcode('[zones_commerciales_map]');
    ?>
</div>

<?php get_footer(); ?>
