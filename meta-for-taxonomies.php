<?php
/*
Plugin Name: Meta for Taxonomies
Plugin URI: http://www.beapi.fr
Description: Add table for term taxonomy meta and some methods for use it. Inspiration from core post meta.
Author: BeAPI
Author URI: http://beapi.fr
Version: 1.0.0
*/

// 1. Setup table name for term taxonomy meta
global $wpdb;
$wpdb->termmeta = $wpdb->prefix . 'term_taxonomy_meta';

// 2. Library
require( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.meta.php' );

// 3. Functions
require( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.hook.php' );
require( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.inc.php' );
require( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.tpl.php' );

// 4. Meta API hook
install_table_term_meta
register_activation_hook( __FILE__, 'install_table_termmeta' );
add_action ( 'delete_term', 'remove_meta_during_delete', 10, 3 );


?>