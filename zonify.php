<?php
/*
Plugin Name: MBS Zonify
Description: Plugin pour gérer les zones des commerciaux via une carte interactive.
Version: 1.0
Author: THomas Dupez, Agence MBS
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Sécurité
}

// Parcours de tous les fichiers PHP dans le dossier inc et inclusion de chacun d'eux
$inc_dir = plugin_dir_path(__FILE__) . 'inc/';
foreach ( glob( $inc_dir . '*.php' ) as $file ) {
    require_once $file;
}

$inc_dir_draw = plugin_dir_path(__FILE__) . 'drawing/';
foreach ( glob( $inc_dir_draw . '*.php' ) as $file ) {
    require_once $file;
}

$inc_dir_imp_exp = plugin_dir_path(__FILE__) . 'import_export/';
foreach ( glob( $inc_dir_imp_exp . '*.php' ) as $file ) {
    require_once $file;
}

$inc_dir_shortcode = plugin_dir_path(__FILE__) . 'shortcode/';
foreach ( glob( $inc_dir_shortcode . '*.php' ) as $file ) {
    require_once $file;
}

// Parcours de tous les fichiers PHP dans le dossier admin et inclusion de chacun d'eux
$admin_dir = plugin_dir_path(__FILE__) . 'admin/';
foreach ( glob( $admin_dir . '*.php' ) as $file ) {
    require_once $file;
}

// Parcours de tous les fichiers PHP dans le dossier post_type et inclusion de chacun d'eux
$admin_dir = plugin_dir_path(__FILE__) . 'post_type/';
foreach ( glob( $admin_dir . '*.php' ) as $file ) {
    require_once $file;
}

$contact_dir = plugin_dir_path(__FILE__) . 'contact/';
foreach ( glob( $contact_dir . '*.php' ) as $file ) {
    require_once $file;
}

require_once plugin_dir_path(__FILE__) . 'settings/settings.php';
