<?php
/**
 * Page d'administration pour la gestion des modules Terralize
 */

// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Afficher la page de gestion des modules
 */
function terralize_modules_page() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    // Traiter les soumissions de formulaire
    if (isset($_POST['terralize_modules_submit']) && wp_verify_nonce($_POST['terralize_modules_nonce'], 'terralize_modules_settings')) {
        terralize_handle_modules_settings();
    }

    // Récupérer l'état des modules
    $modules = terralize_get_available_modules();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>Gérez les modules complémentaires de Terralize. Activez ou désactivez les fonctionnalités selon vos besoins.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('terralize_modules_settings', 'terralize_modules_nonce'); ?>
            
            <div class="terralize-modules-grid">
                <?php foreach ($modules as $module_key => $module) : ?>
                    <div class="terralize-module-card <?php echo $module['enabled'] ? 'enabled' : 'disabled'; ?>">
                        <div class="module-header">
                            <div class="module-icon">
                                <span class="dashicons <?php echo esc_attr($module['icon']); ?>"></span>
                            </div>
                            <h3><?php echo esc_html($module['name']); ?></h3>
                            <label class="module-toggle">
                                <input type="checkbox" 
                                       name="module_<?php echo esc_attr($module_key); ?>" 
                                       value="1" 
                                       <?php checked($module['enabled'], true); ?>
                                       <?php echo $module['required'] ? 'disabled' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="module-content">
                            <p class="module-description"><?php echo esc_html($module['description']); ?></p>
                            
                            <?php if (!empty($module['features'])) : ?>
                                <ul class="module-features">
                                    <?php foreach ($module['features'] as $feature) : ?>
                                        <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <div class="module-meta">
                                <span class="module-version">Version : <?php echo esc_html($module['version']); ?></span>
                                <?php if ($module['required']) : ?>
                                    <span class="module-required">Requis</span>
                                <?php endif; ?>
                                <?php if (!empty($module['dependencies']) && !$module['dependencies_met']) : ?>
                                    <span class="module-dependencies-error">Dépendances manquantes</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($module['enabled'] && !empty($module['admin_url'])) : ?>
                                <div class="module-actions">
                                    <a href="<?php echo esc_url($module['admin_url']); ?>" class="button button-secondary">
                                        Configuration
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php submit_button('Enregistrer les paramètres', 'primary', 'terralize_modules_submit'); ?>
        </form>
    </div>

    <style>
    .terralize-modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .terralize-module-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .terralize-module-card.enabled {
        border-color: #46b450;
        background: #f0fff4;
    }

    .terralize-module-card.disabled {
        border-color: #ccc;
        background: #f8f8f8;
    }

    .module-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
    }

    .module-icon {
        background: #3388ff;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .terralize-module-card.enabled .module-icon {
        background: #46b450;
    }

    .module-header h3 {
        margin: 0;
        flex-grow: 1;
        font-size: 16px;
    }

    .module-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .module-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        border-radius: 24px;
        transition: .4s;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: .4s;
    }

    input:checked + .toggle-slider {
        background-color: #46b450;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }

    input:disabled + .toggle-slider {
        background-color: #888;
        cursor: not-allowed;
    }

    .module-description {
        color: #666;
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .module-features {
        list-style: none;
        margin: 0 0 15px 0;
        padding: 0;
    }

    .module-features li {
        margin: 5px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .module-features .dashicons {
        color: #46b450;
        font-size: 16px;
    }

    .module-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }

    .module-version {
        background: #f0f0f1;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
        color: #555;
    }

    .module-required {
        background: #f56e28;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }

    .module-dependencies-error {
        background: #dc3232;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
    }

    .module-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }

    @media (max-width: 768px) {
        .terralize-modules-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php
}

/**
 * Traiter les paramètres des modules
 */
function terralize_handle_modules_settings() {
    $modules = terralize_get_available_modules();
    $updated_modules = array();

    foreach ($modules as $module_key => $module) {
        if (!$module['required']) {
            $is_enabled = isset($_POST['module_' . $module_key]) && $_POST['module_' . $module_key] == '1';
            
            // Mettre à jour l'option pour ce module
            update_option('terralize_' . $module_key . '_enabled', $is_enabled);
            
            $updated_modules[$module_key] = $is_enabled;
        }
    }

    // Afficher un message de succès
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>Paramètres des modules sauvegardés avec succès !</p>';
        echo '</div>';
    });

    // Hook pour permettre aux modules de réagir aux changements
    do_action('terralize_modules_settings_updated', $updated_modules);
}

/**
 * Obtenir la liste des modules disponibles
 */
function terralize_get_available_modules() {
    $modules = array(
        'gfg' => array(
            'name' => 'Module GFG - Gestion des Implantationss',
            'description' => 'Permet de lier les points de vente (POI) à des implantationss et de rediriger le bouton contact vers la page de l\'implantations.',
            'version' => '1.0.0',
            'icon' => 'dashicons-store',
            'enabled' => get_option('terralize_gfg_enabled', false),
            'required' => false,
            'features' => array(
                'Création automatique du CPT Implantations',
                'Liaison POI ↔ Implantations via metabox',
                'Redirection automatique du bouton contact',
                'Template de page implantations inclus',
                'Interface d\'administration dédiée'
            ),
            'dependencies' => array(),
            'dependencies_met' => true,
            'admin_url' => admin_url('edit.php?post_type=zone&page=terralize-gfg')
        ),
        'affichage_premier' => array(
            'name' => 'Module Affichage Premier',
            'description' => 'Module d\'affichage avancé pour personnaliser l\'apparence des cartes et des éléments.',
            'version' => '1.0.0',
            'icon' => 'dashicons-admin-appearance',
            'enabled' => get_option('terralize_affichage_premier_enabled', false),
            'required' => false,
            'features' => array(
                'Styles personnalisés avancés',
                'Templates modifiables',
                'Options d\'affichage étendues'
            ),
            'dependencies' => array(),
            'dependencies_met' => true,
            'admin_url' => get_option('terralize_affichage_premier_enabled', false) ? admin_url('admin.php?page=terralize-affichage-premier') : ''
        ),
        'core' => array(
            'name' => 'Module Core - Fonctionnalités de base',
            'description' => 'Fonctionnalités principales de Terralize : gestion des zones, POI, commerciaux et cartes interactives.',
            'version' => '2.0.0',
            'icon' => 'dashicons-location-alt',
            'enabled' => true,
            'required' => true,
            'features' => array(
                'Gestion des zones commerciales',
                'Points d\'intérêt (POI)',
                'Cartes interactives Leaflet',
                'Système de filtrage avancé',
                'Import/Export de données'
            ),
            'dependencies' => array(),
            'dependencies_met' => true,
            'admin_url' => admin_url('admin.php?page=terralize')
        )
    );

    // Permettre aux modules externes d'ajouter leurs propres définitions
    return apply_filters('terralize_available_modules', $modules);
} 