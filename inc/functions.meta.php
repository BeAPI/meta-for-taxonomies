<?php
/**
 * Add metadata for term
 *
 * @param string $taxonomy 
 * @param integer $term_id 
 * @param string $meta_key 
 * @param string|array $meta_value 
 * @param boolean $unique 
 * @return boolean
 * @author Amaury Balmer
 */
function add_term_meta( $taxonomy = '', $term_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {
	// Taxonomy is valid ?
	if ( !is_taxonomy($taxonomy) ) {
		return false;
	}
	
	// Term ID exist for this taxonomy ?
	$term = get_term( (int) $term_id, $taxonomy );
	if ( $term == false || is_wp_error($term) ) {
		return false;
	}
	
	return add_term_taxonomy_meta( $term->term_taxonomy_id, $meta_key, $meta_value, $unique );
}

/**
 * Delete term meta for term
 *
 * @param string $taxonomy 
 * @param integer $term_id 
 * @param string $meta_key 
 * @param string|array $meta_value 
 * @return boolean
 * @author Amaury Balmer
 */
function delete_term_meta( $taxonomy = '', $term_id = 0, $meta_key = '', $meta_value = '') {
	// Taxonomy is valid ?
	if ( !is_taxonomy($taxonomy) ) {
		return false;
	}
	
	// Term ID exist for this taxonomy ?
	$term = get_term( (int) $term_id, $taxonomy );
	if ( $term == false || is_wp_error($term) ) {
		return false;
	}
	
	return delete_term_taxonomy_meta( $term->term_taxonomy_id, $meta_key, $meta_value );
}

/**
 * Get a term meta field
 *
 * @param string $taxonomy 
 * @param integer $term_id 
 * @param string|array $meta_key 
 * @param boolean $single 
 * @return boolean
 * @author Amaury Balmer
 */
function get_term_meta( $taxonomy = '', $term_id = 0, $meta_key = '', $single = false ) {
	// Taxonomy is valid ?
	if ( !is_taxonomy($taxonomy) ) {
		return false;
	}
	
	// Term ID exist for this taxonomy ?
	$term = get_term( (int) $term_id, $taxonomy );
	if ( $term == false || is_wp_error($term) ) {
		return false;
	}
	
	return get_term_taxonomy_meta( $term->term_taxonomy_id, $meta_key, $single );
}

/**
 * Update a term meta field
 *
 * @param string $taxonomy 
 * @param integer $term_id 
 * @param string $meta_key 
 * @param string|array $meta_value 
 * @param string|array $prev_value 
 * @return boolean
 * @author Amaury Balmer
 */
function update_term_meta( $taxonomy = '', $term_id = 0, $meta_key, $meta_value, $prev_value = '' ) {
	// Taxonomy is valid ?
	if ( !is_taxonomy($taxonomy) ) {
		return false;
	}
	
	// Term ID exist for this taxonomy ?
	$term = get_term( (int) $term_id, $taxonomy );
	if ( $term == false || is_wp_error($term) ) {
		return false;
	}
	
	return update_term_taxonomy_meta( $term->term_taxonomy_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Get a term meta field
 *
 * @param string $taxonomy 
 * @param integer $term_id 
 * @return boolean
 * @author Amaury Balmer
 */
function get_term_custom( $taxonomy = '', $term_id = 0 ) {
	// Taxonomy is valid ?
	if ( !is_taxonomy($taxonomy) ) {
		return false;
	}
	
	// Term ID exist for this taxonomy ?
	$term = get_term( (int) $term_id, $taxonomy );
	if ( $term == false || is_wp_error($term) ) {
		return false;
	}

	return get_term_taxonomy_custom( $term->term_taxonomy_id );
}

/**
 * undocumented function
 *
 * @param string $taxonomy 
 * @param string $term_id 
 * @return void
 * @author Amaury Balmer
 */
function get_term_custom_keys( $taxonomy = '', $term_id = 0 ) {
	// Taxonomy is valid ?
	if ( !is_taxonomy($taxonomy) ) {
		return false;
	}
	
	// Term ID exist for this taxonomy ?
	$term = get_term( (int) $term_id, $taxonomy );
	if ( $term == false || is_wp_error($term) ) {
		return false;
	}

	return get_term_taxonomy_custom_keys( $term->term_taxonomy_id );
}

/**
 * undocumented function
 *
 * @param string $taxonomy 
 * @param string $term_id 
 * @param string $key 
 * @return void
 * @author Amaury Balmer
 */
function get_term_custom_values( $taxonomy = '', $term_id = 0, $key = '' ) {
	// Taxonomy is valid ?
	if ( !is_taxonomy($taxonomy) ) {
		return false;
	}
	
	// Term ID exist for this taxonomy ?
	$term = get_term( (int) $term_id, $taxonomy );
	if ( $term == false || is_wp_error($term) ) {
		return false;
	}

	return get_term_taxonomy_custom_values( $key, $term->term_taxonomy_id );
}

/**
 * Add metadata for term taxonomy context
 *
 * @package Simple Taxonomy Meta
 * @uses $wpdb
 *
 * @param int $term_taxonomy_id term ID
 * @param string $key {@internal Missing Description}}
 * @param mixed $value {@internal Missing Description}}
 * @param bool $unique whether to check for a value with the same key
 * @return bool {@internal Missing Description}}
 */
function add_term_taxonomy_meta( $term_taxonomy_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {
	global $wpdb;

	// expected_slashed ($meta_key)
	$meta_key = stripslashes($meta_key);

	if ( $unique && $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->termmeta WHERE meta_key = %s AND term_taxonomy_id = %d", $meta_key, $term_taxonomy_id ) ) )
		return false;

	$meta_value = maybe_serialize($meta_value);
	$wpdb->insert( $wpdb->termmeta, compact( 'term_taxonomy_id', 'meta_key', 'meta_value' ) );
	
	wp_cache_delete($term_taxonomy_id, 'term_meta');

	return true;
}

/**
 * Delete term metadata
 *
 * @package Simple Taxonomy Meta
 * @uses $wpdb
 *
 * @param int $term_taxonomy_id term ID
 * @param string $key {@internal Missing Description}}
 * @param mixed $value {@internal Missing Description}}
 * @return bool {@internal Missing Description}}
 */
function delete_term_taxonomy_meta($term_taxonomy_id = 0, $key = '', $value = '') {
	global $wpdb;

	// expected_slashed ($key, $value)
	$key = stripslashes( $key );
	$value = stripslashes( $value );

	if ( empty( $value ) )
		$meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->termmeta WHERE term_taxonomy_id = %d AND meta_key = %s", $term_taxonomy_id, $key ) );
	else
		$meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->termmeta WHERE term_taxonomy_id = %d AND meta_key = %s AND meta_value = %s", $term_taxonomy_id, $key, $value ) );

	if ( !$meta_id )
		return false;

	if ( empty( $value ) )
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE term_taxonomy_id = %d AND meta_key = %s", $term_taxonomy_id, $key ) );
	else
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE term_taxonomy_id = %d AND meta_key = %s AND meta_value = %s", $term_taxonomy_id, $key, $value ) );

	wp_cache_delete($term_taxonomy_id, 'term_meta');

	return true;
}

/**
 * Get a term meta field
 *
 * @package Simple Taxonomy Meta
 * @uses $wpdb
 *
 * @param int $term_taxonomy_id term ID
 * @param string $meta_key The meta key to retrieve
 * @param bool $single Whether to return a single value
 * @return mixed {@internal Missing Description}}
 */
function get_term_taxonomy_meta($term_taxonomy_id, $meta_key, $single = false) {
	$term_taxonomy_id = (int) $term_taxonomy_id;
	$meta_key = stripslashes($meta_key); // expected_slashed ($meta_key)
	
	$meta_cache = wp_cache_get($term_taxonomy_id, 'term_meta');
	
	if ( !$meta_cache ) {
		update_termmeta_cache($term_taxonomy_id);
		$meta_cache = wp_cache_get($term_taxonomy_id, 'term_meta');
	}
		
	if ( isset($meta_cache[$meta_key]) ) {
		if ( $single ) {
			return maybe_unserialize( $meta_cache[$meta_key][0] );
		} else {
			return array_map('maybe_unserialize', $meta_cache[$meta_key]);
		}
	}
	return '';
}

/**
 * Update a term meta field
 *
 *
 * @package Simple Taxonomy Meta
 * @uses $wpdb
 *
 * @param int $term_taxonomy_id term ID
 * @param string $key {@internal Missing Description}}
 * @param mixed $value {@internal Missing Description}}
 * @param mixed $prev_value previous value (for differentiating between meta fields with the same key and term ID)
 * @return bool {@internal Missing Description}}
 */
function update_term_taxonomy_meta($term_taxonomy_id, $meta_key, $meta_value, $prev_value = '') {
	global $wpdb;

	// expected_slashed ($meta_key)
	$meta_key = stripslashes($meta_key);

	if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->termmeta WHERE meta_key = %s AND term_taxonomy_id = %d", $meta_key, $term_taxonomy_id ) ) ) {
		return add_term_taxonomy_meta($term_taxonomy_id, $meta_key, $meta_value);
	}

	$meta_value = maybe_serialize($meta_value);

	$data  = compact( 'meta_value' );
	$where = compact( 'meta_key', 'term_taxonomy_id' );

	if ( !empty( $prev_value ) ) {
		$prev_value = maybe_serialize($prev_value);
		$where['meta_value'] = $prev_value;
	}

	$wpdb->update( $wpdb->termmeta, $data, $where );
	wp_cache_delete($term_taxonomy_id, 'term_meta');
	return true;
}

/**
 * Delete everything from term meta matching $term_meta_key
 *
 * @package Simple Taxonomy Meta
 * @uses $wpdb
 *
 * @param string $term_meta_key What to search for when deleting
 * @return bool Whether the term meta key was deleted from the database
 */
function delete_term_meta_by_key( $term_meta_key = '' ) {
	global $wpdb;
	if ( $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->termmeta WHERE meta_key = %s", $term_meta_key)) ) {
		/** @todo Get term_taxonomy_ids and delete cache */
		// wp_cache_delete($term_taxonomy_id, 'term_meta');
		return true;
	}
	return false;
}

/**
 * Delete a term by key/value
 *
 * @param string $key 
 * @param string $value 
 * @return boolean
 * @author Amaury Balmer
 */
function delete_term_meta_by_key_and_value($key = '', $value = '') {
	global $wpdb;

	// expected_slashed ($key, $value)
	$key 	= stripslashes( $key );
	$value 	= stripslashes( $value );

	if ( empty( $value ) )
		$meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->termmeta WHERE meta_key = %s", $key ) );
	else
		$meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->termmeta WHERE meta_key = %s AND meta_value = %s", $key, $value ) );

	if ( !$meta_id )
		return false;

	if ( empty( $value ) )
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE meta_key = %s", $key ) );
	else
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE meta_key = %s AND meta_value = %s", $key, $value ) );

	/** @todo Get term_taxonomy_ids and delete cache */
	// wp_cache_delete($term_taxonomy_id, 'term_meta');
	return true;
}

/**
 * Delete everything from term taxonomy ID matching $term_taxonomy_id
 *
 * @package Simple Taxonomy Meta
 * @uses $wpdb
 *
 * @param integer $term_taxonomy_id What to search for when deleting
 * @return bool Whether the term meta key was deleted from the database
 */
function delete_term_meta_by_term_taxonomy_id( $term_taxonomy_id = 0 ) {
	global $wpdb;
	if ( $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->termmeta WHERE term_taxonomy_id = %s", (int) $term_taxonomy_id)) ) {
		wp_cache_delete($term_taxonomy_id, 'term_meta');
		return true;
	}
	return false;
}

/**
 * {@internal Missing Short Description}}
 *
 *
 * @package Simple Taxonomy Meta
 *
 * @uses $wpdb
 *
 * @param array $term_taxonomy_ids {@internal Missing Description}}
 * @return bool|array Returns false if there is nothing to update or an array of metadata
 */
function update_termmeta_cache( $term_taxonomy_ids ) {
	global $wpdb;

	if ( empty( $term_taxonomy_ids ) )
		return false;

	if ( !is_array($term_taxonomy_ids) ) {
		$term_taxonomy_ids = preg_replace('|[^0-9,]|', '', $term_taxonomy_ids);
		$term_taxonomy_ids = explode(',', $term_taxonomy_ids);
	}

	$term_taxonomy_ids = array_map('intval', $term_taxonomy_ids);

	$ids = array();
	foreach ( (array) $term_taxonomy_ids as $id ) {
		if ( false === wp_cache_get($id, 'term_meta') )
			$ids[] = $id;
	}

	if ( empty( $ids ) )
		return false;

	// Get term-meta info
	$id_list = join(',', $ids);
	$cache = array();
	$meta_list = $wpdb->get_results("SELECT term_taxonomy_id, meta_key, meta_value FROM $wpdb->termmeta WHERE term_taxonomy_id IN ($id_list) ORDER BY term_taxonomy_id, meta_key", ARRAY_A);
	if ( $meta_list != false ) {
		foreach ( (array) $meta_list as $metarow) {
			$mtid = (int) $metarow['term_taxonomy_id'];
			$mkey = $metarow['meta_key'];
			$mval = $metarow['meta_value'];

			// Force subkeys to be array type:
			if ( !isset($cache[$mtid]) || !is_array($cache[$mtid]) )
				$cache[$mtid] = array();
			if ( !isset($cache[$mtid][$mkey]) || !is_array($cache[$mtid][$mkey]) )
				$cache[$mtid][$mkey] = array();

			// Add a value to the current tid/key:
			$cache[$mtid][$mkey][] = $mval;
		}
	}

	foreach ( (array) $ids as $id ) {
		if ( ! isset($cache[$id]) )
			$cache[$id] = array();
	}

	foreach ( array_keys($cache) as $term )
		wp_cache_set($term, $cache[$term], 'term_meta');

	return $cache;
}

/**
 * Retrieve term custom fields
 *
 *
 * @package Simple Taxonomy Meta
 *
 * @uses $id
 * @uses $wpdb
 *
 * @param int $term_taxonomy_id term ID
 * @return array {@internal Missing Description}}
 */
function get_term_taxonomy_custom($term_taxonomy_id = 0) {
	global $id;

	if ( !$term_taxonomy_id )
		$term_taxonomy_id = (int) $id;

	$term_taxonomy_id = (int) $term_taxonomy_id;

	if ( ! wp_cache_get($term_taxonomy_id, 'term_meta') )
		update_termmeta_cache($term_taxonomy_id);

	return wp_cache_get($term_taxonomy_id, 'term_meta');
}

/**
 * Retrieve term custom field names
 *
 * @package Simple Taxonomy Meta
 *
 * @param int $term_taxonomy_id term ID
 * @return array|null Either array of the keys, or null if keys would not be retrieved
 */
function get_term_taxonomy_custom_keys( $term_taxonomy_id = 0 ) {
	$custom = get_term_custom( $term_taxonomy_id );

	if ( !is_array($custom) )
		return false;

	$keys = array_keys($custom);
	return $keys;
}

/**
 * Retrieve values for a custom term field
 *
 * @package Simple Taxonomy Meta
 *
 * @param string $key field name
 * @param int $term_taxonomy_id term ID
 * @return mixed {@internal Missing Description}}
 */
function get_term_taxonomy_custom_values( $key = '', $term_taxonomy_id = 0 ) {
	$custom = get_term_custom($term_taxonomy_id);
	return $custom[$key];
}

/**
 * Retrieve term taxonomy ID by meta_key/meta_value
 *
 * @package Simple Taxonomy Meta
 *
 * @param string $meta_key meta key
 * @param string $meta_value meta value
 * @return mixed {@internal Missing Description}}
 */
function get_term_taxonomy_id_from_meta( $meta_key = '', $meta_value = '' ) {
	global $wpdb;
	
	$key = md5( $meta_key . $meta_value );
	
	$result = wp_cache_get( $key, 'term_meta' );
	if ( false === $result ) {
		$result = (int) $wpdb->get_var( $wpdb->prepare("SELECT term_taxonomy_id FROM $wpdb->termmeta WHERE meta_key = %s AND meta_value = %s", $meta_key, $meta_value ) );
		wp_cache_set( $key, $result, 'term_meta' );
	}
	
	return $result;
}

/**
 * Allow to get meta datas for a specificied key.
 * 
 * @package Simple Taxonomy Meta
 *
 * @param string $key
 * @return array
 */
function get_term_meta_by_key( $meta_key = '' ) {
	global $wpdb;
	
	$key = md5( 'key-'.$meta_key );
	
	$result = wp_cache_get( $key, 'term_meta' );
	if ( false === $result ) {
	 	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->termmeta WHERE meta_key = %s", $meta_key ) );
		wp_cache_set( $key, $result, 'term_meta' );
	}

	return $result;
}
?>