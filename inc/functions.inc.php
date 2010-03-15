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

/**
 * Get term datas with only the taxonomy and the term taxonomy ID.
 * 
 * @param (integer) $term_taxonomy_id
 * @param (string) $taxonomy
 * @return boolean/object
 */
function get_term_by_tt_id( $term_taxonomy_id = 0, $taxonomy = '' ) {
	global $wpdb;
	
	$term_taxonomy_id = (int) $term_taxonomy_id;
	if ( $term_taxonomy_id == 0 ) 
		return false;
	
	if ( !isset($taxonomy) || empty($taxonomy) || !is_taxonomy($taxonomy) ) 
		return false;
	
	$key = md5( $term_taxonomy_id . $taxonomy );
	
	$term = wp_cache_get($key, 'terms');
	if ( false === $term ) {
		$term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND tt.term_taxonomy_id = %d LIMIT 1", $taxonomy, $term_taxonomy_id) );
		wp_cache_set( $key, $term, 'terms' );
	}
	
	return $term;
}

/**
 * Clone of is_cloned_tax()
 * 
 * @param (string) $tax
 * @return boolean
 */
function is_cloned_taxonomy( $tax = null ) {
	return is_cloned_tax( $tax );
}

/**
 * Allow to detect if the current or manuel taxo are a cloned taxonomy.
 * 
 * @param (string) $tax
 * @return boolean
 */
function is_cloned_tax( $tax = null ) {
	if ( $tax == null ) { // Manual param or WP_Query ?
		if ( !is_tax() )
			return false;
		
		$tax = get_query_var('taxonomy');
	}
	
	if ( !is_taxonomy($tax) ) // Taxo exist ?
		return false;
	
	$tax = get_taxonomy( $tax );
	if( isset($tax->taxo_type) && $tax->taxo_type == 'cloned' && !empty($tax->cloned_object) ) // Cloned taxo ?
		return true;
		
	return false;
}

/**
 * Allow to detect if the current post or manuel object_id are a cloned object.
 * 
 * @param (integer) $object_id
 * @return boolean/array
 */
function is_cloned_object( $object_id = null ) {
	global $wp_taxonomies;
	
	if ( $object_id == null ) { // Param or global post ?
		global $post;
		$object_id = $post->ID;
	}
	
	$object = get_post( $object_id );
	if ( $object == false || is_wp_error($object) ) // Valid object ?
		return false;
		
	$cloned_taxonomy = false;
	foreach ( $wp_taxonomies as $tax ) { // Object is cloned post_type ?
		if( isset($tax->taxo_type) && $tax->taxo_type == 'cloned' && !empty($tax->cloned_object) && $tax->cloned_object == $object->post_type ) {
			$cloned_taxonomy = $tax;
			
			break;
		}
	}
	if ( $cloned_taxonomy == false ) { // No cloned ?
		return false;
	}
	return $cloned_taxonomy;
}

/**
 * Get cloned custom post_type from a term. (WP_Query or Manual)
 * 
 * @param (integer) $term_id
 * @param (string) $taxonomy
 * @return boolean/object
 */
function get_cloned_object_by_term( $term_id = null, $taxonomy = '' ) {
	if ( is_cloned_tax($taxonomy) == false ) // Is cloned taxo ?
		return false;
		
	$term = false;
	if ( $term_id != null && !empty($taxonomy) && is_taxonomy($taxonomy) ) {
		// Manual term with param ?
		$term = get_term( $term_id, $taxonomy );
	}
	
	if ( $term == false || is_wp_error($term) || $term == null ) {
		// Get current term from WP_Query
		$term = get_current_term();
		if ( $term == false )
			return false;
	}
	
	$object_id = get_term_taxonomy_meta( $term->term_taxonomy_id, 'object_id', true );
	if ( $object_id == false || (int) $object_id == 0 ) // Valid ID? 
		return false;
		
	return get_post( $object_id );
}

/**
 * Get cloned term from a custom object. (WP_Query or Manual)
 * 
 * @param (integer) $object_id
 * @return boolean/object
 */
function get_cloned_term_by_object( $object_id = null ) {
	if ( $object_id == null ) { // Param or global post ?
		global $post;
		$object_id = $post->ID;
	}
	
	$object = get_post( $object_id );
	if ( $object == false || is_wp_error($object) ) // Valid object ?
		return false;
	
	$cloned_taxonomy = is_cloned_object( $object_id );
	if ( $cloned_taxonomy == false ) { // No cloned ?
		return false;
	}
	
	$tt_id = (int) get_term_taxonomy_id_from_meta( 'object_id', $object->ID ) ;
	if ( $tt_id == 0 ) { // No valid term ?
		return false;
	}
		
	return get_term_by_tt_id( $tt_id, $cloned_taxonomy->name );
}
?>