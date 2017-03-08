<?php
/*
 Plugin Name: Meta for Taxonomies
 Version: 1.3.5
 Plugin URI: http://www.beapi.fr
 Description: Add table for term taxonomy meta and some methods for use it. Inspiration from core post meta.
 Author: BE API Technical team
 Author URI: http://www.beapi.fr
 Domain Path: languages
 Text Domain: meta-for-taxonomies
 
 TODO:
	Implement purge cache of term metadata on follow hook : clean_term_cache 
 
 ----
 
 Copyright 2017 BE API Technical team (human@beapi.fr)
 
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Before 4.4
 */
if ( ! function_exists( 'get_term_meta' ) ) {

	// 1. Setup table name for term taxonomy meta
	global $wpdb;
	$wpdb->tables[]      = 'term_taxometa';
	$wpdb->term_taxometa = $wpdb->prefix . 'term_taxometa';

	// 2. Library
	require_once( dirname( __FILE__ ) . '/inc/default/functions.meta.php' );
	require_once( dirname( __FILE__ ) . '/inc/default/functions.meta.ext.php' );
	require_once( dirname( __FILE__ ) . '/inc/default/functions.meta.terms.php' );

	// 3. Functions
	require_once( dirname( __FILE__ ) . '/inc/default/functions.hook.php' );
	require_once( dirname( __FILE__ ) . '/inc/default/functions.inc.php' );
	require_once( dirname( __FILE__ ) . '/inc/default/functions.tpl.php' );

	// 4. Meta API hook
	register_activation_hook( __FILE__, 'install_table_termmeta' );
	
	add_action( 'delete_term', 'remove_meta_during_delete', 10, 3 );

} else {
	/**
	 * After 4.4
	 */
	// 1. Migration tools
	require_once( dirname( __FILE__ ) . '/inc/migration/migration.php' );
	require_once( dirname( __FILE__ ) . '/inc/migration/functions.migration.php' );
	require_once( dirname( __FILE__ ) . '/inc/migration/wp-cli.php' );

	// 2. Library
	require_once( dirname( __FILE__ ) . '/inc/compat/functions.meta.php' );
	require_once( dirname( __FILE__ ) . '/inc/compat/functions.meta.ext.php' );
	require_once( dirname( __FILE__ ) . '/inc/compat/functions.meta.terms.php' );

	// 3. Functions
	require_once( dirname( __FILE__ ) . '/inc/compat/functions.tpl.php' );

	if( is_admin() ) {
		require_once( dirname( __FILE__ ) . '/inc/admin.php' );

		new MFT_Admin();
	}

}
