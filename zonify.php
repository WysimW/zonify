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

// Chemin vers le dossier inc du plugin
$inc_dir = plugin_dir_path(__FILE__) . 'inc/';

// Parcours de tous les fichiers PHP dans le dossier inc et inclusion de chacun d'eux
foreach ( glob( $inc_dir . '*.php' ) as $file ) {
    require_once $file;
}


