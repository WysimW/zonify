jQuery(document).ready(function($){
    // Initialiser les onglets jQuery UI
    $("#terralize-tabs").tabs();
    
    // Afficher/cacher le champ URL personnalisée pour le Back Office
    $("#terralize_tile_provider").on("change", function(){
        if($(this).val() === "custom"){
            $("#custom_tile_url_row_back").show();
        } else {
            $("#custom_tile_url_row_back").hide();
        }
    });
    
    // Afficher/cacher le champ URL personnalisée pour le Front Office
    $("#terralize_tile_provider_front").on("change", function(){
        if($(this).val() === "custom"){
            $("#custom_tile_url_row_front").show();
        } else {
            $("#custom_tile_url_row_front").hide();
        }
    });
});
