<?php
/**
 * This function is called when the plugin is activated, it allow to create the SQL table.
 * @return boolean
 */
function install_table_termmeta() {
	global $wpdb;
	
	// Table exist already ?
	if( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->termmeta}'") != $wpdb->termmeta ) {	
		// Build query with dynamic table name
		$sql =
			"
			CREATE TABLE " . $wpdb->termmeta . " (
				`meta_id` int(20) NOT NULL auto_increment,
				`term_taxonomy_id` INT( 20 ) NOT NULL ,
				`meta_key` VARCHAR( 255 ) NOT NULL ,
				`meta_value` LONGTEXT NOT NULL,
				PRIMARY KEY  (`meta_id`),
				KEY `term_taxonomy_id` (`term_taxonomy_id`),
				KEY `meta_key` (`meta_key`)
			);
			";
		
		// Query execution
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		return true;
	}
	return false;
}
?>