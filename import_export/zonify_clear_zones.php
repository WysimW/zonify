<?php
/**
 * Fonction pour supprimer toutes les zones
 * 
 * Cette fonction supprime toutes les entrées du CPT 'zone'
 */
function terralize_clear_all_zones() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée');
    }

    // Vérifier le nonce pour la sécurité
    check_admin_referer('terralize_clear_zones_nonce');

    // Récupérer toutes les zones
    $args = array(
        'post_type' => 'zone',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'fields' => 'ids' // Récupérer seulement les IDs pour plus d'efficacité
    );
    
    $zones = get_posts($args);
    $count = 0;
    
    // Supprimer chaque zone
    foreach ($zones as $zone_id) {
        wp_delete_post($zone_id, true); // true = suppression définitive (bypass corbeille)
        $count++;
    }
    
    // Rediriger avec un message de succès
    wp_redirect(admin_url('admin.php?page=terralize_import_export&zones_cleared=1&count=' . $count));
    exit;
}

// Ajouter l'action pour le hook admin-post.php
add_action('admin_post_terralize_clear_zones', 'terralize_clear_all_zones'); 