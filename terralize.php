<?php
/*
Plugin Name: Terralize
Description: Plugin pour gérer les zones des commerciaux via une carte interactive.
Version: 1.0.5
Author: THomas Dupez, Agence MBS
License: GPL2
Requires at least: 5.0
Tested up to: 6.4.3
Requires PHP: 7.4
*/

if (! defined('ABSPATH')) {
    exit; // Sécurité
}

// Vérification des fonctions WordPress essentielles
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return rtrim(dirname($file), '/') . '/';
    }
}

// URL de l'API pour les mises à jour
define('TERRALIZE_API_URL', 'https://mbs-tma.mbscom.net/api/terralize/updates');

// Système de mise à jour automatique
add_filter('pre_set_site_transient_update_plugins', 'terralize_check_update');
add_filter('plugins_api', 'terralize_plugin_info', 20, 3);

function terralize_check_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $response = wp_remote_get(TERRALIZE_API_URL);
    if (is_wp_error($response)) {
        return $transient;
    }

    $body = json_decode(wp_remote_retrieve_body($response));
    if (empty($body) || !isset($body->version)) {
        return $transient;
    }

    if (version_compare('1.0.0', $body->version, '<')) {
        $transient->response['zone-commercial-pluginwp/terralize.php'] = (object) array(
            'slug' => 'terralize',
            'new_version' => $body->version,
            'url' => 'https://mbs-tma.mbscom.net/terralize',
            'package' => $body->download_url,
            'tested' => $body->tested,
            'requires' => $body->requires,
            'compatibility' => $body->compatibility
        );
    }

    return $transient;
}

function terralize_plugin_info($res, $action, $args) {
    if ($action !== 'plugin_information') {
        return $res;
    }

    if ($args->slug !== 'terralize') {
        return $res;
    }

    $response = wp_remote_get(TERRALIZE_API_URL);
    if (is_wp_error($response)) {
        return $res;
    }

    $body = json_decode(wp_remote_retrieve_body($response));
    if (empty($body)) {
        return $res;
    }

    $res = new stdClass();
    $res->name = $body->name;
    $res->slug = 'terralize';
    $res->version = $body->version;
    $res->tested = $body->tested;
    $res->requires = $body->requires;
    $res->author = 'THomas Dupez, Agence MBS';
    $res->author_profile = 'https://mbs-tma.mbscom.net';
    $res->last_updated = $body->last_updated;
    $res->sections = $body->sections;
    $res->compatibility = $body->compatibility;

    return $res;
}

// Parcours de tous les fichiers PHP dans le dossier inc et inclusion de chacun d'eux
$inc_dir = plugin_dir_path(__FILE__) . 'inc/';
foreach (glob($inc_dir . '*.php') as $file) {
    require_once $file;
}

$inc_dir_draw = plugin_dir_path(__FILE__) . 'drawing/';
foreach (glob($inc_dir_draw . '*.php') as $file) {
    require_once $file;
}

$inc_dir_imp_exp = plugin_dir_path(__FILE__) . 'import_export/';
foreach (glob($inc_dir_imp_exp . '*.php') as $file) {
    require_once $file;
}

$inc_dir_shortcode = plugin_dir_path(__FILE__) . 'shortcode/';
foreach (glob($inc_dir_shortcode . '*.php') as $file) {
    require_once $file;
}

// Parcours de tous les fichiers PHP dans le dossier admin et inclusion de chacun d'eux
$admin_dir = plugin_dir_path(__FILE__) . 'admin/';
foreach (glob($admin_dir . '*.php') as $file) {
    require_once $file;
}

// Parcours de tous les fichiers PHP dans le dossier post_type et inclusion de chacun d'eux
$admin_dir = plugin_dir_path(__FILE__) . 'post_type/';
foreach (glob($admin_dir . '*.php') as $file) {
    require_once $file;
}

$contact_dir = plugin_dir_path(__FILE__) . 'contact/';
foreach (glob($contact_dir . '*.php') as $file) {
    require_once $file;
}

// Chargement du fichier de styles pour les tableaux CPT
require_once plugin_dir_path(__FILE__) . 'inc/cpt-tables-styles.php';

// Enregistrement et chargement des styles dynamiques
function terralize_enqueue_dynamic_styles($hook) {
    // Vérifier si nous sommes sur une page d'administration du plugin
    if (strpos($hook, 'terralize') === false && strpos($hook, 'zone-commercial') === false) {
        return;
    }

    wp_enqueue_style(
        'terralize-dynamic-styles',
        admin_url('admin-ajax.php') . '?action=terralize_dynamic_styles',
        array(),
        '1.0.0'
    );
}
// Ne charger les styles que dans l'admin
add_action('admin_enqueue_scripts', 'terralize_enqueue_dynamic_styles');

// Gestion de la requête AJAX pour les styles dynamiques
function terralize_ajax_dynamic_styles() {
    require_once plugin_dir_path(__FILE__) . 'css/terralize-dynamic-styles.php';
    exit;
}
add_action('wp_ajax_terralize_dynamic_styles', 'terralize_ajax_dynamic_styles');
add_action('wp_ajax_nopriv_terralize_dynamic_styles', 'terralize_ajax_dynamic_styles');

require_once plugin_dir_path(__FILE__) . 'settings/settings.php';

// Charger les extensions
/*
    if (file_exists(plugin_dir_path(__FILE__) . 'extensions/terralize-affichage-premier/terralize-affichage-premier.php')) {
        require_once plugin_dir_path(__FILE__) . 'extensions/terralize-affichage-premier/terralize-affichage-premier.php';
    }
*/

