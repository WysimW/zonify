<?php
/**
 * Fonctionnalités d'importation CSV pour Terralize Affichage Premier
 * Permet d'importer des données de panneaux d'affichage depuis un fichier CSV
 */

// Si ce fichier est appelé directement, abandonner
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Récupère le département et la région à partir d'un code postal
 * 
 * @param string $code_postal Le code postal à analyser
 * @return array Tableau associatif contenant 'departement' et 'region'
 */
function terralize_ap_get_dept_region_from_postal($code_postal) {
    // Si code postal vide, retourner valeurs par défaut
    if (empty($code_postal)) {
        return array(
            'departement' => '',
            'code_departement' => '',
            'region' => ''
        );
    }

    // Nettoyer le code postal (supprimer espaces et caractères non numériques)
    $code_postal = preg_replace('/[^0-9]/', '', $code_postal);
    
    // Récupérer les deux premiers chiffres pour le département
    $dept_code = substr($code_postal, 0, 2);
    
    // Gestion des cas particuliers (DOM-TOM)
    if ($dept_code == '97') {
        $dept_code = substr($code_postal, 0, 3); // 971, 972, etc.
    }
    
    // Liste des départements et régions
    $departments = array(
        '01' => array('name' => 'Ain', 'region' => 'Auvergne-Rhône-Alpes'),
        '02' => array('name' => 'Aisne', 'region' => 'Hauts-de-France'),
        '03' => array('name' => 'Allier', 'region' => 'Auvergne-Rhône-Alpes'),
        '04' => array('name' => 'Alpes-de-Haute-Provence', 'region' => 'Provence-Alpes-Côte d\'Azur'),
        '05' => array('name' => 'Hautes-Alpes', 'region' => 'Provence-Alpes-Côte d\'Azur'),
        '06' => array('name' => 'Alpes-Maritimes', 'region' => 'Provence-Alpes-Côte d\'Azur'),
        '07' => array('name' => 'Ardèche', 'region' => 'Auvergne-Rhône-Alpes'),
        '08' => array('name' => 'Ardennes', 'region' => 'Grand Est'),
        '09' => array('name' => 'Ariège', 'region' => 'Occitanie'),
        '10' => array('name' => 'Aube', 'region' => 'Grand Est'),
        '11' => array('name' => 'Aude', 'region' => 'Occitanie'),
        '12' => array('name' => 'Aveyron', 'region' => 'Occitanie'),
        '13' => array('name' => 'Bouches-du-Rhône', 'region' => 'Provence-Alpes-Côte d\'Azur'),
        '14' => array('name' => 'Calvados', 'region' => 'Normandie'),
        '15' => array('name' => 'Cantal', 'region' => 'Auvergne-Rhône-Alpes'),
        '16' => array('name' => 'Charente', 'region' => 'Nouvelle-Aquitaine'),
        '17' => array('name' => 'Charente-Maritime', 'region' => 'Nouvelle-Aquitaine'),
        '18' => array('name' => 'Cher', 'region' => 'Centre-Val de Loire'),
        '19' => array('name' => 'Corrèze', 'region' => 'Nouvelle-Aquitaine'),
        '21' => array('name' => 'Côte-d\'Or', 'region' => 'Bourgogne-Franche-Comté'),
        '22' => array('name' => 'Côtes-d\'Armor', 'region' => 'Bretagne'),
        '23' => array('name' => 'Creuse', 'region' => 'Nouvelle-Aquitaine'),
        '24' => array('name' => 'Dordogne', 'region' => 'Nouvelle-Aquitaine'),
        '25' => array('name' => 'Doubs', 'region' => 'Bourgogne-Franche-Comté'),
        '26' => array('name' => 'Drôme', 'region' => 'Auvergne-Rhône-Alpes'),
        '27' => array('name' => 'Eure', 'region' => 'Normandie'),
        '28' => array('name' => 'Eure-et-Loir', 'region' => 'Centre-Val de Loire'),
        '29' => array('name' => 'Finistère', 'region' => 'Bretagne'),
        '30' => array('name' => 'Gard', 'region' => 'Occitanie'),
        '31' => array('name' => 'Haute-Garonne', 'region' => 'Occitanie'),
        '32' => array('name' => 'Gers', 'region' => 'Occitanie'),
        '33' => array('name' => 'Gironde', 'region' => 'Nouvelle-Aquitaine'),
        '34' => array('name' => 'Hérault', 'region' => 'Occitanie'),
        '35' => array('name' => 'Ille-et-Vilaine', 'region' => 'Bretagne'),
        '36' => array('name' => 'Indre', 'region' => 'Centre-Val de Loire'),
        '37' => array('name' => 'Indre-et-Loire', 'region' => 'Centre-Val de Loire'),
        '38' => array('name' => 'Isère', 'region' => 'Auvergne-Rhône-Alpes'),
        '39' => array('name' => 'Jura', 'region' => 'Bourgogne-Franche-Comté'),
        '40' => array('name' => 'Landes', 'region' => 'Nouvelle-Aquitaine'),
        '41' => array('name' => 'Loir-et-Cher', 'region' => 'Centre-Val de Loire'),
        '42' => array('name' => 'Loire', 'region' => 'Auvergne-Rhône-Alpes'),
        '43' => array('name' => 'Haute-Loire', 'region' => 'Auvergne-Rhône-Alpes'),
        '44' => array('name' => 'Loire-Atlantique', 'region' => 'Pays de la Loire'),
        '45' => array('name' => 'Loiret', 'region' => 'Centre-Val de Loire'),
        '46' => array('name' => 'Lot', 'region' => 'Occitanie'),
        '47' => array('name' => 'Lot-et-Garonne', 'region' => 'Nouvelle-Aquitaine'),
        '48' => array('name' => 'Lozère', 'region' => 'Occitanie'),
        '49' => array('name' => 'Maine-et-Loire', 'region' => 'Pays de la Loire'),
        '50' => array('name' => 'Manche', 'region' => 'Normandie'),
        '51' => array('name' => 'Marne', 'region' => 'Grand Est'),
        '52' => array('name' => 'Haute-Marne', 'region' => 'Grand Est'),
        '53' => array('name' => 'Mayenne', 'region' => 'Pays de la Loire'),
        '54' => array('name' => 'Meurthe-et-Moselle', 'region' => 'Grand Est'),
        '55' => array('name' => 'Meuse', 'region' => 'Grand Est'),
        '56' => array('name' => 'Morbihan', 'region' => 'Bretagne'),
        '57' => array('name' => 'Moselle', 'region' => 'Grand Est'),
        '58' => array('name' => 'Nièvre', 'region' => 'Bourgogne-Franche-Comté'),
        '59' => array('name' => 'Nord', 'region' => 'Hauts-de-France'),
        '60' => array('name' => 'Oise', 'region' => 'Hauts-de-France'),
        '61' => array('name' => 'Orne', 'region' => 'Normandie'),
        '62' => array('name' => 'Pas-de-Calais', 'region' => 'Hauts-de-France'),
        '63' => array('name' => 'Puy-de-Dôme', 'region' => 'Auvergne-Rhône-Alpes'),
        '64' => array('name' => 'Pyrénées-Atlantiques', 'region' => 'Nouvelle-Aquitaine'),
        '65' => array('name' => 'Hautes-Pyrénées', 'region' => 'Occitanie'),
        '66' => array('name' => 'Pyrénées-Orientales', 'region' => 'Occitanie'),
        '67' => array('name' => 'Bas-Rhin', 'region' => 'Grand Est'),
        '68' => array('name' => 'Haut-Rhin', 'region' => 'Grand Est'),
        '69' => array('name' => 'Rhône', 'region' => 'Auvergne-Rhône-Alpes'),
        '70' => array('name' => 'Haute-Saône', 'region' => 'Bourgogne-Franche-Comté'),
        '71' => array('name' => 'Saône-et-Loire', 'region' => 'Bourgogne-Franche-Comté'),
        '72' => array('name' => 'Sarthe', 'region' => 'Pays de la Loire'),
        '73' => array('name' => 'Savoie', 'region' => 'Auvergne-Rhône-Alpes'),
        '74' => array('name' => 'Haute-Savoie', 'region' => 'Auvergne-Rhône-Alpes'),
        '75' => array('name' => 'Paris', 'region' => 'Île-de-France'),
        '76' => array('name' => 'Seine-Maritime', 'region' => 'Normandie'),
        '77' => array('name' => 'Seine-et-Marne', 'region' => 'Île-de-France'),
        '78' => array('name' => 'Yvelines', 'region' => 'Île-de-France'),
        '79' => array('name' => 'Deux-Sèvres', 'region' => 'Nouvelle-Aquitaine'),
        '80' => array('name' => 'Somme', 'region' => 'Hauts-de-France'),
        '81' => array('name' => 'Tarn', 'region' => 'Occitanie'),
        '82' => array('name' => 'Tarn-et-Garonne', 'region' => 'Occitanie'),
        '83' => array('name' => 'Var', 'region' => 'Provence-Alpes-Côte d\'Azur'),
        '84' => array('name' => 'Vaucluse', 'region' => 'Provence-Alpes-Côte d\'Azur'),
        '85' => array('name' => 'Vendée', 'region' => 'Pays de la Loire'),
        '86' => array('name' => 'Vienne', 'region' => 'Nouvelle-Aquitaine'),
        '87' => array('name' => 'Haute-Vienne', 'region' => 'Nouvelle-Aquitaine'),
        '88' => array('name' => 'Vosges', 'region' => 'Grand Est'),
        '89' => array('name' => 'Yonne', 'region' => 'Bourgogne-Franche-Comté'),
        '90' => array('name' => 'Territoire de Belfort', 'region' => 'Bourgogne-Franche-Comté'),
        '91' => array('name' => 'Essonne', 'region' => 'Île-de-France'),
        '92' => array('name' => 'Hauts-de-Seine', 'region' => 'Île-de-France'),
        '93' => array('name' => 'Seine-Saint-Denis', 'region' => 'Île-de-France'),
        '94' => array('name' => 'Val-de-Marne', 'region' => 'Île-de-France'),
        '95' => array('name' => 'Val-d\'Oise', 'region' => 'Île-de-France'),
        '971' => array('name' => 'Guadeloupe', 'region' => 'Guadeloupe'),
        '972' => array('name' => 'Martinique', 'region' => 'Martinique'),
        '973' => array('name' => 'Guyane', 'region' => 'Guyane'),
        '974' => array('name' => 'La Réunion', 'region' => 'La Réunion'),
        '976' => array('name' => 'Mayotte', 'region' => 'Mayotte')
    );
    
    // Si le département est trouvé, on retourne le nom et la région
    if (isset($departments[$dept_code])) {
        return array(
            'departement' => $departments[$dept_code]['name'],
            'code_departement' => $dept_code,
            'region' => $departments[$dept_code]['region']
        );
    }
    
    // Si non trouvé, retourner des valeurs par défaut
    return array(
        'departement' => '',
        'code_departement' => '',
        'region' => ''
    );
}

/**
 * Normalise le type de panneau
 * 
 * @param string $type Le type brut du panneau
 * @return string Type normalisé
 */
function terralize_ap_normalize_panel_type($type) {
    $type = strtolower(trim($type));
    
    // Correspondance des types spécifiés
    $type_mapping = array(
        'deroulant' => 'DEROULANT',
        'déroulant' => 'DEROULANT',
        'fixe eclaire' => 'FIXE ECLAIRE',
        'fixe éclairé' => 'FIXE ECLAIRE',
        'fixe éclaire' => 'FIXE ECLAIRE',
        'fixe eclairé' => 'FIXE ECLAIRE',
        'fixe' => 'FIXE',
        'trivision' => 'TRIVISION',
        'digital' => 'DIGITAL'
    );
    
    foreach ($type_mapping as $key => $value) {
        if (strpos($type, $key) !== false) {
            return $value;
        }
    }
    
    // Si aucune correspondance n'est trouvée, retourner le type original en majuscules
    return strtoupper($type);
}

/**
 * Normalise le type de support
 * 
 * @param string $support Le support brut du panneau
 * @return string Support normalisé
 */
function terralize_ap_normalize_panel_support($support) {
    $support = strtolower(trim($support));
    
    // Correspondance des supports spécifiés
    $support_mapping = array(
        'vitrine murale' => 'VITRINE MURALE',
        'vitrine' => 'VITRINE',
        'tole' => 'TOLE',
        'tôle' => 'TOLE',
        'trivision' => 'TRIVISION',
        'lame' => 'LAME'
    );
    
    foreach ($support_mapping as $key => $value) {
        if (strpos($support, $key) !== false) {
            return $value;
        }
    }
    
    // Si aucune correspondance n'est trouvée, retourner le support original en majuscules
    return strtoupper($support);
}

/**
 * Détermine le format standard à partir des dimensions ou du format brut
 * 
 * @param string $format Format brut indiqué dans le CSV
 * @param float $largeur Largeur en cm
 * @param float $hauteur Hauteur en cm
 * @return string Format standard
 */
function terralize_ap_get_standard_format($format, $largeur, $hauteur) {
    // Si le format est déjà spécifié (par ex. "2M2"), on le conserve tel quel
    if (!empty($format) && preg_match('/(\d+[,.]?\d*)M2/i', $format)) {
        return strtoupper(trim($format)); // Retourne le format brut en majuscules
    }
    
    // Sinon, on calcule à partir des dimensions (largeur/hauteur)
    if (!empty($largeur) && !empty($hauteur)) {
        // Convertir en nombres avec point décimal
        $largeur_num = floatval(str_replace(',', '.', $largeur));
        $hauteur_num = floatval(str_replace(',', '.', $hauteur));
        
        // Calculer la surface en m²
        $surface_m2 = ($largeur_num * $hauteur_num) / 10000; // cm² -> m²
        
        // Arrondir à des formats standards
        $formats_standards = array(
            '1,5' => '1,5M2',
            '2' => '2M2', 
            '4' => '4M2', 
            '6' => '6M2', 
            '8' => '8M2', 
            '12' => '12M2',
            '20' => '20M2',
            '40' => '40M2'
        );
        
        $closest = '2'; // Format par défaut
        $closest_diff = PHP_FLOAT_MAX;
        
        foreach (array_keys($formats_standards) as $std) {
            $std_num = floatval(str_replace(',', '.', $std));
            $diff = abs($surface_m2 - $std_num);
            if ($diff < $closest_diff) {
                $closest_diff = $diff;
                $closest = $std;
            }
        }
        
        return $formats_standards[$closest];
    }
    
    // Si on ne peut pas calculer ou déterminer, on retourne une valeur par défaut
    return !empty($format) ? strtoupper(trim($format)) : '2M2';
}

/**
 * Conserve l'angle de visibilité sous forme de texte
 * 
 * @param string $angle_text L'angle sous forme de texte
 * @return string L'angle non modifié
 */
function terralize_ap_normalize_angle($angle_text) {
    // Retourner simplement la valeur telle quelle
    return $angle_text;
}

/**
 * Ajouter une page de menu pour l'import CSV dans l'administration
 */
function terralize_ap_import_csv_menu() {
    add_submenu_page(
        'terralize',
        'Importer des Panneaux (CSV)',
        'Importer Panneaux',
        'manage_options',
        'terralize_ap_import_csv',
        'terralize_ap_import_csv_page'
    );
}
add_action('admin_menu', 'terralize_ap_import_csv_menu');

/**
 * Affiche la page d'importation CSV
 */
function terralize_ap_import_csv_page() {
    // Vérifier les droits
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée');
    }

    // Affichage des notifications de succès ou d'erreur
    if (isset($_GET['import_done'])) {
        $created = isset($_GET['created']) ? intval($_GET['created']) : 0;
        $updated = isset($_GET['updated']) ? intval($_GET['updated']) : 0;
        $errors = isset($_GET['errors']) ? intval($_GET['errors']) : 0;
        echo '<div class="updated notice"><p>Import CSV réussi : ' . $created . ' panneaux créés, ' . $updated . ' panneaux mis à jour, ' . $errors . ' erreurs.</p></div>';
    }
    
    if (isset($_GET['import_error'])) {
        $error_msg = sanitize_text_field(urldecode($_GET['import_error']));
        echo '<div class="error notice"><p>Erreur lors de l\'importation : ' . $error_msg . '</p></div>';
    }
    
    if (isset($_GET['reset_done'])) {
        $deleted = isset($_GET['deleted']) ? intval($_GET['deleted']) : 0;
        echo '<div class="updated notice"><p>Réinitialisation réussie : ' . $deleted . ' panneaux supprimés de la base de données.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Importer des Panneaux d'Affichage (CSV)</h1>
        <p>Importez vos panneaux d'affichage à partir d'un fichier CSV au format "PATRIMOINE ED".</p>

        <div class="card">
            <h2>Instructions</h2>
            <p>Le fichier CSV doit contenir au moins les colonnes suivantes :</p>
            <ul>
                <li>CODE REFERENCE PHOTO - Référence unique du panneau</li>
                <li>VILLE - Ville du panneau</li>
                <li>CODE POSTAL - Code postal</li>
                <li>ADRESSE - Adresse du panneau</li>
                <li>COORDONNEES GPS DU PANNEAU Y - Latitude (exemple: 50,23929)</li>
                <li>COORDONNEES GPS DU PANNEAU X - Longitude (exemple: 2,65531)</li>
                <li>FORMAT - Format du panneau (ex: MOBILIER URBAIN, GRAND FORMAT)</li>
                <li>TYPE - Type de panneau (ex: DEROULANT, FIXE)</li>
                <li>SUPPORT - Support du panneau (ex: VITRINE, TOLE)</li>
                <li>LARGEUR EN CM - Largeur</li>
                <li>HAUTEUR EN CM - Hauteur</li>
            </ul>
        </div>

        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php?action=terralize_ap_import_csv')); ?>">
            <?php wp_nonce_field('terralize_ap_import_csv_nonce'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="panneaux_csv">Fichier CSV :</label></th>
                    <td>
                        <input type="file" name="panneaux_csv" id="panneaux_csv" accept=".csv,text/csv" required />
                        <p class="description">Format attendu : CSV séparé par des virgules (,).<br>
                        Encodage recommandé : UTF-8</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Options d'importation :</th>
                    <td>
                        <label for="update_existing">
                            <input type="checkbox" name="update_existing" id="update_existing" value="1" checked />
                            Mettre à jour les panneaux existants (basé sur le CODE REFERENCE PHOTO)
                        </label><br>
                        
                        <label for="skip_header">
                            <input type="checkbox" name="skip_header" id="skip_header" value="1" checked />
                            Ignorer la première ligne (en-têtes)
                        </label>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Catégorie par défaut :</th>
                    <td>
                        <select name="default_category" id="default_category">
                            <option value="">-- Aucune catégorie --</option>
                            <?php
                            $categories = get_terms(array(
                                'taxonomy' => 'terralize_category',
                                'hide_empty' => false,
                            ));
                            
                            if (!empty($categories) && !is_wp_error($categories)) {
                                foreach ($categories as $category) {
                                    echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <p class="description">Catégorie à associer à tous les panneaux importés</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Importer le CSV" />
            </p>
        </form>
        
        <div class="card" style="margin-top: 30px; background-color: #f8d7da; border-color: #f5c6cb;">
            <h2>Zone Dangereuse</h2>
            <p>Attention ! Cette action est <strong>irréversible</strong> et supprimera <strong>tous</strong> les panneaux d'affichage enregistrés dans la base de données.</p>
            
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=terralize_ap_reset_poi')); ?>" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer tous les POI (panneaux) de la base de données? Cette action est irréversible!');">
                <?php wp_nonce_field('terralize_ap_reset_poi_nonce'); ?>
                <p class="submit">
                    <input type="submit" name="reset_submit" id="reset_submit" class="button button-secondary" value="Réinitialiser tous les POI" style="background-color: #dc3545; border-color: #dc3545; color: white;" />
                </p>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Fonction utilitaire pour rechercher une colonne dans les en-têtes de manière flexible
 */
function terralize_ap_find_column_index($header, $column_name) {
    // Recherche exacte
    $exact_match = array_search($column_name, $header);
    if ($exact_match !== false) {
        return $exact_match;
    }
    
    // Recherche insensible à la casse
    foreach ($header as $key => $value) {
        if (strcasecmp($value, $column_name) === 0) {
            return $key;
        }
    }
    
    // Recherche avec suppression des espaces supplémentaires
    foreach ($header as $key => $value) {
        if (strcasecmp(trim($value), trim($column_name)) === 0) {
            return $key;
        }
    }
    
    // Recherche avancée pour les variations courantes
    $common_variations = array(
        'CODE REFERENCE PHOTO' => ['ref', 'reference', 'code ref', 'code reference'],
        'VILLE' => ['city', 'commune', 'ville'],
        'CODE POSTAL' => ['cp', 'code postal', 'postal code', 'zip'],
        'ADRESSE' => ['address', 'adresse', 'rue'],
        'COORDONNEES GPS DU PANNEAU Y' => ['latitude', 'lat', 'y', 'gps y', 'coordonnees y'],
        'COORDONNEES GPS DU PANNEAU X' => ['longitude', 'long', 'lng', 'x', 'gps x', 'coordonnees x'],
        'FORMAT' => ['format', 'type format'],
        'TYPE' => ['type', 'type panneau'],
        'SUPPORT' => ['support', 'support type'],
        'LARGEUR EN CM' => ['largeur', 'width', 'l'],
        'HAUTEUR EN CM' => ['hauteur', 'height', 'h'],
        'VISIBLE EN VENANT DE' => ['venant de', 'de', 'visibilité de', 'visibilite de'],
        'VISIBLE EN ALLANT A' => ['allant à', 'allant a', 'à', 'a', 'vers', 'visibilité vers', 'visibilite vers'],
        'ANGLE DE VISIBILITE' => ['angle', 'visibilité angle', 'visibilite angle'],
        'ANNONCEUR' => ['annonceur', 'client', 'advertiser'],
        'DISPONIBILITE' => ['disponibilité', 'disponibilite', 'dispo', 'available'],
        'DATE DE FIN DENGAGEMENT  DU BON DE COMMANDE' => ['date fin', 'date de fin', 'fin engagement', 'fin contrat']
    );
    
    if (isset($common_variations[$column_name])) {
        foreach ($common_variations[$column_name] as $variation) {
            foreach ($header as $key => $value) {
                if (stripos($value, $variation) !== false) {
                    return $key;
                }
            }
        }
    }
    
    return false;
}

/**
 * Crée un GeoJSON Point à partir des coordonnées
 */
function terralize_ap_create_geojson_point($lat, $lng) {
    if (empty($lat) || empty($lng)) {
        return '';
    }
    
    // Assurer que les coordonnées sont au format numérique avec point décimal
    $lat = floatval(str_replace(',', '.', $lat));
    $lng = floatval(str_replace(',', '.', $lng));
    
    // Créer un point GeoJSON simplifié (uniquement la partie geometry)
    $point = array(
        'type' => 'Point',
        'coordinates' => array($lng, $lat) // GeoJSON utilise [longitude, latitude]
    );
    
    return wp_json_encode($point);
}

/**
 * Génère une référence automatique basée sur la ville, l'adresse et le code postal
 * 
 * @param string $ville La ville du panneau
 * @param string $adresse L'adresse du panneau
 * @param string $code_postal Le code postal
 * @return string Une référence unique générée
 */
function terralize_ap_generate_auto_reference($ville, $adresse, $code_postal) {
    // Nettoyer les valeurs d'entrée
    $ville = trim(preg_replace('/[^A-Za-z0-9]/', '', strtoupper($ville)));
    $adresse = trim(preg_replace('/[^A-Za-z0-9]/', '', strtoupper($adresse)));
    $code_postal = trim($code_postal);
    
    // Prendre les 3 premiers caractères non vides de la ville
    $ville_prefix = !empty($ville) ? substr($ville, 0, 3) : 'XXX';
    
    // Prendre les 2 premiers caractères non vides de l'adresse
    $adresse_prefix = !empty($adresse) ? substr($adresse, 0, 2) : 'XX';
    
    // Prendre les 2 derniers chiffres du code postal
    $cp_suffix = !empty($code_postal) ? substr($code_postal, -2) : 'XX';
    
    // Générer un timestamp court (4 derniers chiffres)
    $timestamp = substr(time(), -4);
    
    // Combiner pour former une référence unique
    $reference = $ville_prefix . $adresse_prefix . $cp_suffix . '_' . $timestamp;
    
    return $reference;
}

/**
 * Traitement de l'importation du fichier CSV
 */
function terralize_ap_process_csv_import() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée');
    }
    
    try {
        // Vérifier le nonce
        check_admin_referer('terralize_ap_import_csv_nonce');
        
        // Vérifier le fichier
        if (!isset($_FILES['panneaux_csv']) || empty($_FILES['panneaux_csv']['tmp_name'])) {
            throw new Exception('Aucun fichier CSV fourni.');
        }
        
        if ($_FILES['panneaux_csv']['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = array(
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP.',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire.',
                UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé.',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été téléchargé.',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
                UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque.',
                UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté le téléchargement du fichier.'
            );
            $error_code = $_FILES['panneaux_csv']['error'];
            $error_message = isset($upload_errors[$error_code]) ? $upload_errors[$error_code] : 'Erreur inconnue lors du téléchargement.';
            throw new Exception($error_message);
        }
        
        // Récupérer les options d'importation
        $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] == '1';
        $skip_header = isset($_POST['skip_header']) && $_POST['skip_header'] == '1';
        $default_category = isset($_POST['default_category']) ? intval($_POST['default_category']) : 0;
        
        // Ouvrir le fichier
        $handle = fopen($_FILES['panneaux_csv']['tmp_name'], 'r');
        if (!$handle) {
            throw new Exception('Impossible de lire le fichier CSV.');
        }
        
        // Détecter l'encodage et convertir si nécessaire
        $first_line = fgets($handle);
        rewind($handle);
        
        // Si le fichier semble être en ISO-8859-1 ou Windows-1252, le convertir
        if (!mb_check_encoding($first_line, 'UTF-8')) {
            $temp_file = tmpfile();
            if ($temp_file) {
                while (($line = fgets($handle)) !== false) {
                    $utf8_line = mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1, Windows-1252');
                    fwrite($temp_file, $utf8_line);
                }
                fclose($handle);
                
                $meta_data = stream_get_meta_data($temp_file);
                $handle = fopen($meta_data['uri'], 'r');
                if (!$handle) {
                    throw new Exception('Erreur lors de la conversion de l\'encodage du fichier.');
                }
            }
        }
        
        // Détecter le séparateur CSV (virgule ou point-virgule)
        $first_line = fgets($handle);
        rewind($handle);
        $delimiter = (strpos($first_line, ';') !== false) ? ';' : ',';
        
        // Lire les en-têtes pour déterminer les indices des colonnes
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            throw new Exception('CSV vide ou illisible');
        }
        
        // Journaliser les en-têtes pour le débogage
        error_log('En-têtes du CSV : ' . print_r($header, true));
        
        // Définir les colonnes requises et leurs indices en utilisant notre fonction flexible
        $column_indices = array(
            'reference' => terralize_ap_find_column_index($header, 'CODE REFERENCE PHOTO'),
            'ville' => terralize_ap_find_column_index($header, 'VILLE'),
            'code_postal' => terralize_ap_find_column_index($header, 'CODE POSTAL'),
            'adresse' => terralize_ap_find_column_index($header, 'ADRESSE'),
            'latitude' => terralize_ap_find_column_index($header, 'COORDONNEES GPS DU PANNEAU Y'),
            'longitude' => terralize_ap_find_column_index($header, 'COORDONNEES GPS DU PANNEAU X'),
            'format' => terralize_ap_find_column_index($header, 'FORMAT'),
            'type' => terralize_ap_find_column_index($header, 'TYPE'),
            'support' => terralize_ap_find_column_index($header, 'SUPPORT'),
            'largeur' => terralize_ap_find_column_index($header, 'LARGEUR EN CM'),
            'hauteur' => terralize_ap_find_column_index($header, 'HAUTEUR EN CM'),
            'annonceur' => terralize_ap_find_column_index($header, 'ANNONCEUR'),
            'disponibilite' => terralize_ap_find_column_index($header, 'DISPONIBILITE'),
            'date_fin' => terralize_ap_find_column_index($header, 'DATE DE FIN DENGAGEMENT  DU BON DE COMMANDE'),
            'visible_de' => terralize_ap_find_column_index($header, 'VISIBLE EN VENANT DE'),
            'visible_vers' => terralize_ap_find_column_index($header, 'VISIBLE EN ALLANT A'),
            'angle_visibilite' => terralize_ap_find_column_index($header, 'ANGLE DE VISIBILITE'),
            'surface' => terralize_ap_find_column_index($header, 'SURFACE')
        );
        
        // Journaliser les indices trouvés pour le débogage
        error_log('Indices des colonnes trouvés : ' . print_r($column_indices, true));
        
        // Vérifier que les colonnes essentielles sont présentes
        $required_columns = array('ville', 'adresse');
        $missing_columns = array();
        foreach ($required_columns as $column) {
            if ($column_indices[$column] === false) {
                $missing_columns[] = $column;
            }
        }
        
        if (!empty($missing_columns)) {
            fclose($handle);
            throw new Exception('Le fichier CSV ne contient pas toutes les colonnes requises. Colonnes manquantes: ' . implode(', ', $missing_columns));
        }
        
        // Statistiques d'importation
        $count_created = 0;
        $count_updated = 0;
        $count_errors = 0;
        
        // Si skip_header est coché, on a déjà lu la première ligne pour les en-têtes,
        // donc on ne fait rien de spécial ici.
        // Sinon, on devrait remettre le pointeur au début du fichier et lire à nouveau la première ligne.
        if (!$skip_header) {
            rewind($handle);
            $dummy = fgetcsv($handle, 0, $delimiter); // Lire et ignorer la première ligne
        }
        
        // Parcourir chaque ligne
        $row_count = 0;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row_count++;
            try {
                // Journaliser la ligne traitée pour débogage détaillé (limité pour éviter de surcharger les logs)
                if ($row_count <= 5) {
                    error_log("Traitement ligne {$row_count}: " . mb_substr(implode(',', $row), 0, 100) . "...");
                }
                
                // Vérifier que nous avons suffisamment de colonnes
                if (is_array($row)) {
                    $max_index = max(array_values(array_filter($column_indices)));
                    if (count($row) <= $max_index) {
                        error_log("Ligne {$row_count} avec nombre de colonnes insuffisant: " . count($row) . " (besoin de >={$max_index})");
                        $count_errors++;
                        continue;
                    }
                } else {
                    error_log("Ligne {$row_count} n'est pas un tableau valide");
                    $count_errors++;
                    continue;
                }
                
                // Récupérer les données du panneau en vérifiant l'existence de chaque colonne
                $reference = ($column_indices['reference'] !== false && isset($row[$column_indices['reference']])) ? 
                    sanitize_text_field($row[$column_indices['reference']]) : '';
                $ville = ($column_indices['ville'] !== false && isset($row[$column_indices['ville']])) ? 
                    sanitize_text_field($row[$column_indices['ville']]) : '';
                $code_postal = ($column_indices['code_postal'] !== false && isset($row[$column_indices['code_postal']])) ? 
                    sanitize_text_field($row[$column_indices['code_postal']]) : '';
                $adresse = ($column_indices['adresse'] !== false && isset($row[$column_indices['adresse']])) ? 
                    sanitize_text_field($row[$column_indices['adresse']]) : '';
                $latitude = ($column_indices['latitude'] !== false && isset($row[$column_indices['latitude']])) ? 
                    str_replace(',', '.', sanitize_text_field($row[$column_indices['latitude']])) : '';
                $longitude = ($column_indices['longitude'] !== false && isset($row[$column_indices['longitude']])) ? 
                    str_replace(',', '.', sanitize_text_field($row[$column_indices['longitude']])) : '';
                $format_brut = ($column_indices['format'] !== false && isset($row[$column_indices['format']])) ? 
                    sanitize_text_field($row[$column_indices['format']]) : '';
                $type_brut = ($column_indices['type'] !== false && isset($row[$column_indices['type']])) ? 
                    sanitize_text_field($row[$column_indices['type']]) : '';
                $support_brut = ($column_indices['support'] !== false && isset($row[$column_indices['support']])) ? 
                    sanitize_text_field($row[$column_indices['support']]) : '';
                $largeur = ($column_indices['largeur'] !== false && isset($row[$column_indices['largeur']])) ? 
                    sanitize_text_field($row[$column_indices['largeur']]) : '';
                $hauteur = ($column_indices['hauteur'] !== false && isset($row[$column_indices['hauteur']])) ? 
                    sanitize_text_field($row[$column_indices['hauteur']]) : '';
                $annonceur = ($column_indices['annonceur'] !== false && isset($row[$column_indices['annonceur']])) ? 
                    sanitize_text_field($row[$column_indices['annonceur']]) : '';
                $disponibilite_brut = ($column_indices['disponibilite'] !== false && isset($row[$column_indices['disponibilite']])) ? 
                    sanitize_text_field($row[$column_indices['disponibilite']]) : '';
                $date_fin = ($column_indices['date_fin'] !== false && isset($row[$column_indices['date_fin']])) ? 
                    sanitize_text_field($row[$column_indices['date_fin']]) : '';
                $visible_de = ($column_indices['visible_de'] !== false && isset($row[$column_indices['visible_de']])) ? 
                    sanitize_text_field($row[$column_indices['visible_de']]) : '';
                $visible_vers = ($column_indices['visible_vers'] !== false && isset($row[$column_indices['visible_vers']])) ? 
                    sanitize_text_field($row[$column_indices['visible_vers']]) : '';
                $angle_visibilite_brut = ($column_indices['angle_visibilite'] !== false && isset($row[$column_indices['angle_visibilite']])) ? 
                    sanitize_text_field($row[$column_indices['angle_visibilite']]) : '';
                $surface_brut = ($column_indices['surface'] !== false && isset($row[$column_indices['surface']])) ? 
                    sanitize_text_field($row[$column_indices['surface']]) : '';
                
                // Normalisation de la disponibilité (D, ED, ND)
                $disponibilite = '';
                if (!empty($disponibilite_brut)) {
                    $disponibilite_brut = strtoupper(trim($disponibilite_brut));
                    if ($disponibilite_brut == 'D') {
                        $disponibilite = 'Disponible';
                    } elseif ($disponibilite_brut == 'ED') {
                        $disponibilite = 'En disponibilité';
                    } elseif ($disponibilite_brut == 'ND') {
                        $disponibilite = 'Non disponible';
                    } else {
                        $disponibilite = $disponibilite_brut; // Conserver la valeur d'origine si non reconnue
                    }
                }
                
                // Appliquer les normalisations et extractions
                $angle_visibilite = terralize_ap_normalize_angle($angle_visibilite_brut);
                $type = terralize_ap_normalize_panel_type($type_brut);
                $support = terralize_ap_normalize_panel_support($support_brut);
                $format_standard = terralize_ap_get_standard_format($surface_brut ? $surface_brut : $format_brut, $largeur, $hauteur);
                
                // Récupérer le département et la région à partir du code postal
                $loc_info = terralize_ap_get_dept_region_from_postal($code_postal);
                $departement = $loc_info['departement'];
                $code_departement = $loc_info['code_departement'];
                $region = $loc_info['region'];
                
                // Générer une référence automatique si elle est vide
                if (empty($reference) && !empty($adresse)) {
                    $reference = terralize_ap_generate_auto_reference($ville, $adresse, $code_postal);
                    error_log("Ligne {$row_count}: Référence générée automatiquement: {$reference}");
                }
                
                // Vérifier les données essentielles - au minimum, on a besoin de l'adresse
                if (empty($adresse)) {
                    error_log("Ligne {$row_count}: Données essentielles manquantes (adresse vide)");
                    $count_errors++;
                    continue;
                }
                
                // Créer un point GeoJSON si des coordonnées sont disponibles
                $geojson = '';
                if (!empty($latitude) && !empty($longitude)) {
                    $geojson = terralize_ap_create_geojson_point($latitude, $longitude);
                }
                
                // Vérifier si le panneau existe déjà (recherche par référence)
                $existing_panneau = array();
                if (!empty($reference)) {
                    $existing_panneau = get_posts(array(
                        'post_type' => 'poi',
                        'meta_key' => 'panel_reference',
                        'meta_value' => $reference,
                        'posts_per_page' => 1
                    ));
                }
                
                // Titre à utiliser pour le panneau
                $panneau_title = !empty($reference) ? $reference : 'Panneau ' . $adresse;
                if (!empty($ville)) {
                    $panneau_title .= ' - ' . $ville;
                }
                
                // Préparer les méta-données en utilisant les noms exacts des champs de la meta box
                $meta_data = array(
                    'panel_reference' => $reference,
                    'panel_city_name' => $ville,
                    'panel_postal_code' => $code_postal,
                    'panel_departement' => $departement,
                    'panel_code_departement' => $code_departement,
                    'panel_region' => $region,
                    'panel_address' => $adresse,
                    'panel_latitude' => $latitude,
                    'panel_longitude' => $longitude,
                    'panel_format' => $format_brut,
                    'panel_format_standard' => $format_standard,
                    'panel_type' => $type,
                    'panel_support' => $support,
                    'panel_width' => $largeur,
                    'panel_height' => $hauteur,
                    'panel_annonceur' => $annonceur,
                    'panel_disponibilite' => $disponibilite,
                    'panel_date_fin' => $date_fin,
                    'visibility_from' => $visible_de,
                    'visibility_to' => $visible_vers,
                    'visibility_angle' => $angle_visibilite,
                );
                
                // Si on a un GeoJSON valide, l'ajouter aussi
                if (!empty($geojson)) {
                    $meta_data['poi_geojson'] = $geojson;
                }
                
                // Mise à jour ou création du panneau
                if (!empty($existing_panneau) && $update_existing) {
                    // Mettre à jour le panneau existant
                    $panneau_id = $existing_panneau[0]->ID;
                    
                    // Mise à jour du titre si nécessaire
                    if ($existing_panneau[0]->post_title != $panneau_title) {
                        wp_update_post(array(
                            'ID' => $panneau_id,
                            'post_title' => $panneau_title
                        ));
                    }
                    
                    // Mettre à jour les méta-données
                    foreach ($meta_data as $meta_key => $meta_value) {
                        update_post_meta($panneau_id, $meta_key, $meta_value);
                    }
                    
                    $count_updated++;
                    if ($row_count <= 5) {
                        error_log("Ligne {$row_count}: Panneau mis à jour (ID: {$panneau_id})");
                    }
                } else {
                    // Créer un nouveau panneau (post_type = poi)
                    $panneau_id = wp_insert_post(array(
                        'post_title' => $panneau_title,
                        'post_type' => 'poi',
                        'post_status' => 'publish'
                    ));
                    
                    if (!is_wp_error($panneau_id)) {
                        // Ajouter les méta-données
                        foreach ($meta_data as $meta_key => $meta_value) {
                            update_post_meta($panneau_id, $meta_key, $meta_value);
                        }
                        
                        // Associer à la catégorie par défaut si elle est définie
                        if ($default_category > 0) {
                            wp_set_object_terms($panneau_id, $default_category, 'terralize_category');
                        }
                        
                        $count_created++;
                        if ($row_count <= 5) {
                            error_log("Ligne {$row_count}: Nouveau panneau créé (ID: {$panneau_id})");
                        }
                    } else {
                        error_log("Ligne {$row_count}: Erreur lors de la création du panneau: " . $panneau_id->get_error_message());
                        $count_errors++;
                    }
                }
            } catch (Exception $e) {
                error_log("Ligne {$row_count}: Erreur lors du traitement: " . $e->getMessage());
                $count_errors++;
            }
        }
        
        fclose($handle);
        
        // Rediriger vers la page d'import avec un message de confirmation
        wp_redirect(admin_url('admin.php?page=terralize_ap_import_csv&import_done=1&created=' . $count_created . '&updated=' . $count_updated . '&errors=' . $count_errors));
        exit;
        
    } catch (Exception $e) {
        error_log('Erreur d\'importation CSV: ' . $e->getMessage());
        if (isset($handle) && is_resource($handle)) {
            fclose($handle);
        }
        wp_redirect(admin_url('admin.php?page=terralize_ap_import_csv&import_error=' . urlencode($e->getMessage())));
        exit;
    }
}
add_action('admin_post_terralize_ap_import_csv', 'terralize_ap_process_csv_import');

/**
 * Traitement de la réinitialisation de tous les POI
 */
function terralize_ap_process_reset_poi() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die('Permission refusée');
    }
    
    try {
        // Vérifier le nonce
        check_admin_referer('terralize_ap_reset_poi_nonce');
        
        global $wpdb;
        
        // Obtenir tous les POI
        $all_pois = get_posts(array(
            'post_type' => 'poi',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids'
        ));
        
        $count_deleted = 0;
        
        // Supprimer chaque POI
        foreach ($all_pois as $poi_id) {
            // Supprimer les meta données associées
            $wpdb->delete($wpdb->postmeta, array('post_id' => $poi_id));
            
            // Supprimer le POI lui-même
            $result = wp_delete_post($poi_id, true);
            
            if ($result !== false) {
                $count_deleted++;
            }
        }
        
        // Journaliser pour débogage
        error_log("Réinitialisation des POI: {$count_deleted} POI supprimés");
        
        // Rediriger vers la page d'import avec message de confirmation
        wp_redirect(admin_url('admin.php?page=terralize_ap_import_csv&reset_done=1&deleted=' . $count_deleted));
        exit;
        
    } catch (Exception $e) {
        error_log('Erreur lors de la réinitialisation des POI: ' . $e->getMessage());
        wp_redirect(admin_url('admin.php?page=terralize_ap_import_csv&import_error=' . urlencode('Erreur lors de la réinitialisation: ' . $e->getMessage())));
        exit;
    }
}
add_action('admin_post_terralize_ap_reset_poi', 'terralize_ap_process_reset_poi');