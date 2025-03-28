<?php
function zonify_poi_pages() {
    ?>
    <div class="wrap zonify-poi-page">
        <header class="zonify-banner">
            <div class="zonify-banner-left">
                <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . '../assets/icons/icon.png' ); ?>" alt="Zonify Icon" class="zonify-icon" />
                <h1 class="zonify-title">Gestion des Points d'Intérêt</h1>
            </div>
        </header>

        <main class="zonify-content">
            <section class="zonify-section">
                <p>Utilisez la carte ci-dessous pour tracer et gérer vos points d’intérêt.</p>
                <div id="poi-map" style="height: 500px; margin-top:20px;"></div>
                <!-- Vous pouvez ajouter ici d'autres éléments comme des formulaires de filtrage ou d'édition -->
            </section>
        </main>
    </div>
    <?php
}
