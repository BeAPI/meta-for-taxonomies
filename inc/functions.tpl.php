<?php
/**
 * Get the current term of tax view from DB. Use WP_Query datas.
 * 
 * @return object term
 */
function get_current_term() {
	if ( !is_tax() )
		return false;
	
	// Build unique key
	$key = 'current-term-'.get_query_var('term').'-'.get_query_var('taxonomy');
	
	// Get current term
	$term = wp_cache_get( $key, 'terms' );
	if ( $term == false || $term == null ) {
		$term = get_term_by( 'slug', get_query_var('term'), get_query_var('taxonomy'), OBJECT, 'display' );
		if ( $term == false ) {
			return false;
		}
		wp_cache_set( $key, $term, 'terms');
	}
	
	return $term;
}

/**
 * Return the term title on tax view.
 * 
 */
function st_get_term_title( $prefix = false ) {
	if ( !is_tax() )
		return '';
	
	// Get current term
	$term = get_current_term();
	if ( $term == false ) {
		return false;
	}
	
	if ( $prefix == true ) {
		$taxonomy = get_taxonomy ( get_query_var('taxonomy') );
		return apply_filters( 'get_term_title', $taxonomy->label .' : '. $term->name, $prefix );
	}
	
	return apply_filters( 'get_term_title', $term->name, $prefix );
}

/**
 * Display the term title on tax view.
 * 
 * use st_get_term_title()
 * 
 */
function st_term_title() {
	echo st_get_term_title();
}

/**
 * Return the term description on tax view.
 * 
 */
function st_get_term_description() {
	if ( !is_tax() )
		return '';
	
	// Get current term
	$term = get_current_term();
	if ( $term == false ) {
		return false;
	}
	
	return apply_filters( 'get_term_description', $term->description, $term );
}

/**
 * Display the term description on tax view.
 * 
 * use st_term_description()
 * 
 */
function st_term_description() {
	echo apply_filters( 'the_content', st_get_term_description() );
}

/**
 * Return the value of a term meta. Support before and after wrapper. Use WP_Query term by default, with parameters for specificy a custom term.
 * 
 * @param (string) $meta_key
 * @param (string) $before
 * @param (string) $after
 * @param (integer) $term_id
 * @param (string) $taxonomy
 * @param (array) $filters
 */
function st_get_term_meta( $meta_key = '', $before = '', $after = '', $term_id = null, $taxonomy = '', $filters = array() ) {
	if ( empty($meta_key) || $meta_key == false )
		return '';
	
	$term = false;
	if ( $term_id != null && !empty($taxonomy) && is_taxonomy($taxonomy) ) {
		// Manual term with param ?
		$term = get_term( $term_id, $taxonomy );
	}
		
	if ( $term == false || is_wp_error($term) || $term == null ) {
		// Get current term from WP_Query
		$term = get_current_term();
		if ( $term == false )
			return '';
	}
	
	if ( (int) $term->term_taxonomy_id == 0 ) { // Last check if term is valid.
		return '';
	}
	
	$meta_value = get_term_taxonomy_meta( $term->term_taxonomy_id, $meta_key, true );
	$meta_value = maybe_unserialize($meta_value);
	if ( $meta_value == false || is_wp_error($meta_value) || empty($meta_value) ) {
		return '';
	}	
	
	if ( is_string($meta_value) ) {
		$meta_value = trim( stripslashes($meta_value) );
		if ( is_array($filters) && !empty($filters) ) {
			foreach( $filters as $filter ) {
				$meta_value = apply_filters( $filter, $meta_value, $term, $meta_key );
			}
		}
	}
	
	return $before . apply_filters( 'st_get_term_meta', $meta_value, $meta_key, $term ) . $after;
}

/**
 * Display an term term. Just make an echo of st_get_term_meta().
 * 
 * @param (string) $meta_key
 * @param (string) $before
 * @param (string) $after
 * @param (integer) $term_id
 * @param (string) $taxonomy
 * @param (array) $filters
 */
function st_term_meta( $meta_key = '', $before = '', $after = '', $term_id = null, $taxonomy = '', $filters = array() ) {
	echo st_get_term_meta( $meta_key, $before, $after, $term_id, $taxonomy, $filters );
}
?>