<?php

/**
 * Migrate existing meta terms to the new WordPress table
 *
 * @author Clément Boirie
 */
function _mft_batch_migrate_terms_metas() {

	// Ensure our table is register
	_mft_maybe_register_taxometa_table();
}

/**
 * Register legacy metas table
 *
 * @author Clément Boirie
 */
function _mft_maybe_register_taxometa_table() {
	global $wpdb;

	if ( ! isset( $wpdb->term_taxometa ) ) {
		$wpdb->tables[]      = 'term_taxometa';
		$wpdb->term_taxometa = $wpdb->prefix . 'term_taxometa';
	}
}