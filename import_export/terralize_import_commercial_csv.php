<?php
/**
 * Import CSV des commerciaux avec association aux zones
 */

// Traitement de l'import CSV des commerciaux
add_action('admin_post_terralize_import_commercial_csv', 'terralize_import_commercial_csv_handler');

function terralize_import_commercial_csv_handler() {
    // Vérification de sécurité
    if (!current_user_can('manage_options')) {
        wp_die('Vous n\'avez pas les permissions nécessaires.');
    }

    // Vérification du nonce
    if (!wp_verify_nonce($_POST['_wpnonce'], 'terralize_import_commercial_csv_nonce')) {
        wp_die('Erreur de sécurité.');
    }

    // Vérification du fichier
    if (!isset($_FILES['commercial_csv']) || $_FILES['commercial_csv']['error'] !== UPLOAD_ERR_OK) {
        wp_redirect(admin_url('admin.php?page=terralize-import-export&import_error=' . urlencode('Erreur lors du téléchargement du fichier.')));
        exit;
    }

    $file = $_FILES['commercial_csv'];
    $filename = $file['tmp_name'];

    // Ouvrir le fichier CSV
    if (($handle = fopen($filename, "r")) === FALSE) {
        wp_redirect(admin_url('admin.php?page=terralize-import-export&import_error=' . urlencode('Impossible d\'ouvrir le fichier CSV.')));
        exit;
    }

    $created_commercials = 0;
    $updated_commercials = 0;
    $associations_created = 0;
    $line_number = 0;
    $errors = array();

    // Lire le fichier ligne par ligne
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $line_number++;
        
        // Ignorer la première ligne si elle contient les en-têtes
        if ($line_number == 1 && isset($_POST['skip_header'])) {
            continue;
        }

        // Vérifier la structure du CSV (au moins 4 colonnes)
        if (count($data) < 4) {
            $errors[] = "Ligne $line_number : Structure CSV invalide";
            continue;
        }

        $nom_commune = trim($data[0]);
        $code_postal = trim($data[1]); 
        $code_commune = trim($data[2]);
        $commercial_brut = trim($data[3]);

        // Ignorer les lignes vides ou sans commercial
        if (empty($commercial_brut) || empty($code_commune)) {
            continue;
        }

        // Nettoyer le nom du commercial (retirer NH et NON NH)
        $commercial_nom = terralize_clean_commercial_name($commercial_brut);
        
        if (empty($commercial_nom)) {
            continue;
        }

        // Créer ou récupérer le commercial
        $commercial_id = terralize_get_or_create_commercial($commercial_nom);
        
        if ($commercial_id === false) {
            $errors[] = "Ligne $line_number : Impossible de créer le commercial '$commercial_nom'";
            continue;
        }

        // Incrémenter les compteurs
        if (terralize_is_new_commercial($commercial_id)) {
            $created_commercials++;
        } else {
            $updated_commercials++;
        }

        // Trouver la zone correspondante
        $zone_id = terralize_find_zone_by_code_commune($code_commune, $nom_commune);
        
        if ($zone_id) {
            // Associer le commercial à la zone
            update_post_meta($zone_id, 'zone_commercial_id', $commercial_id);
            $associations_created++;
        } else {
            $errors[] = "Ligne $line_number : Zone non trouvée pour le code commune '$code_commune' ($nom_commune)";
        }
    }

    fclose($handle);

    // Construire l'URL de redirection avec les résultats
    $redirect_url = admin_url('admin.php?page=terralize-import-export&commercial_import_done=1');
    $redirect_url .= '&created=' . $created_commercials;
    $redirect_url .= '&updated=' . $updated_commercials; 
    $redirect_url .= '&associations=' . $associations_created;
    $redirect_url .= '&errors=' . count($errors);

    // Si il y a des erreurs, les stocker temporairement
    if (!empty($errors)) {
        set_transient('terralize_import_errors', $errors, 30);
    }

    wp_redirect($redirect_url);
    exit;
}

/**
 * Nettoie le nom du commercial en retirant NH et NON NH
 */
function terralize_clean_commercial_name($name) {
    $name = trim($name);
    
    // Retirer "NH" et "NON NH" en fin de chaîne (insensible à la casse)
    $patterns = array(
        '/\s+NH\s*$/i',
        '/\s+NON\s+NH\s*$/i'
    );
    
    foreach ($patterns as $pattern) {
        $name = preg_replace($pattern, '', $name);
    }
    
    return trim($name);
}

/**
 * Trouve ou crée un commercial par son nom
 */
function terralize_get_or_create_commercial($nom) {
    // Rechercher si le commercial existe déjà
    $existing = get_posts(array(
        'post_type' => 'commercial',
        'title' => $nom,
        'post_status' => 'any',
        'numberposts' => 1
    ));

    if (!empty($existing)) {
        // Marquer comme existant pour le comptage
        set_transient('terralize_commercial_' . $existing[0]->ID . '_is_new', false, 10);
        return $existing[0]->ID;
    }

    // Créer un nouveau commercial
    $commercial_data = array(
        'post_title' => $nom,
        'post_type' => 'commercial',
        'post_status' => 'publish'
    );

    $commercial_id = wp_insert_post($commercial_data);
    
    if (is_wp_error($commercial_id)) {
        return false;
    }

    // Marquer comme nouveau pour le comptage
    set_transient('terralize_commercial_' . $commercial_id . '_is_new', true, 10);
    
    return $commercial_id;
}

/**
 * Vérifie si un commercial est nouveau (pour le comptage)
 */
function terralize_is_new_commercial($commercial_id) {
    return get_transient('terralize_commercial_' . $commercial_id . '_is_new') === true;
}

/**
 * Trouve une zone par son code commune
 */
function terralize_find_zone_by_code_commune($code_commune, $nom_commune = '') {
    // Rechercher une zone dont le titre contient le code commune
    $zones = get_posts(array(
        'post_type' => 'zone',
        'post_status' => 'any',
        'numberposts' => -1,
        's' => $code_commune,
        'meta_query' => array()
    ));

    if (empty($zones)) {
        return false;
    }

    // Vérifier que le code commune est bien au début du titre
    foreach ($zones as $zone) {
        $title = $zone->post_title;
        
        // Vérifier si le titre commence par "code_commune - "
        if (preg_match('/^' . preg_quote($code_commune, '/') . '\s*-\s*/i', $title)) {
            return $zone->ID;
        }
    }

    return false;
} 