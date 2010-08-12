<?php
/**
 * This function is called when the plugin is activated, it allow to create the SQL table.
 *
 * @return void
 * @author Amaury Balmer
 */
function install_table_termmeta() {
	global $wpdb;
	
	if ( ! empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty($wpdb->collate) )
		$charset_collate .= " COLLATE $wpdb->collate";
	
	// Add one library admin function for next function
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	// Try to create the meta table
	return maybe_create_table( $wpdb->term_taxo_meta , "CREATE TABLE " . $wpdb->term_taxo_meta . " (
			`meta_id` int(20) NOT NULL auto_increment,
			`term_taxonomy_id` INT( 20 ) NOT NULL ,
			`meta_key` VARCHAR( 255 ) NOT NULL ,
			`meta_value` LONGTEXT NOT NULL,
			PRIMARY KEY  (`meta_id`),
			KEY `term_taxonomy_id` (`term_taxonomy_id`),
			KEY `meta_key` (`meta_key`)
		) $charset_collate;" );
}

/**
 * Get term datas with only the taxonomy and the term taxonomy ID.
 *
 * @param integer $term_taxonomy_id 
 * @param string $taxonomy 
 * @return object|false
 * @author Amaury Balmer
 */
function get_term_by_tt_id( $term_taxonomy_id = 0, $taxonomy = '' ) {
	global $wpdb;
	
	$term_taxonomy_id = (int) $term_taxonomy_id;
	if ( $term_taxonomy_id == 0 ) 
		return false;
		
	if ( !isset($taxonomy) || empty($taxonomy) || !taxonomy_exists($taxonomy) ) 
		return false;
	
	$key = md5( $term_taxonomy_id . $taxonomy );
	$term = wp_cache_get($key, 'terms');
	if ( false === $term ) {
		$term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxo_meta AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND tt.term_taxonomy_id = %d LIMIT 1", $taxonomy, $term_taxonomy_id) );
		wp_cache_set( $key, $term, 'terms' );
	}
	
	return $term;
}
?>