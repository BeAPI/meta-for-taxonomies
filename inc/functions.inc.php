<?php
/**
 * This function is called when the plugin is activated, it allow to create the SQL table.
 *
 * @return void
 * @author Amaury Balmer
 */
function install_table_termmeta() {
	global $wpdb;
	
	// Add one library admin function for next function
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	// Try to create the meta table
	return maybe_create_table( $wpdb->termmeta, "CREATE TABLE " . $wpdb->termmeta . " (
			`meta_id` int(20) NOT NULL auto_increment,
			`term_taxonomy_id` INT( 20 ) NOT NULL ,
			`meta_key` VARCHAR( 255 ) NOT NULL ,
			`meta_value` LONGTEXT NOT NULL,
			PRIMARY KEY  (`meta_id`),
			KEY `term_taxonomy_id` (`term_taxonomy_id`),
			KEY `meta_key` (`meta_key`)
		);" );
}
?>