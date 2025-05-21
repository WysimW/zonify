<?php
function terralize_poi_pages() {
    ?>
    <div class="wrap terralize-poi-page">
        <header class="terralize-banner">
            <div class="terralize-banner-left">
                <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . '../assets/icons/icon.png' ); ?>" alt="Terralize Icon" class="terralize-icon" />
                <h1 class="terralize-title">Gestion des Points d'Intérêt</h1>
            </div>
        </header>

        <main class="terralize-content">
            <section class="terralize-section">
                <p>Utilisez la carte ci-dessous pour tracer et gérer vos points d’intérêt.</p>
                <div id="poi-map" style="height: 500px; margin-top:20px;"></div>
                <!-- Vous pouvez ajouter ici d'autres éléments comme des formulaires de filtrage ou d'édition -->
            </section>
        </main>
    </div>
    <?php
}
