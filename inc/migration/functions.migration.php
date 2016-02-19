<?php

add_action( 'mft_migrate_term_metas_batch', '_mft_batch_migrate_terms_metas' );
/**
 * Migrate existing meta terms to the new WordPress table
 *
 * @author Clément Boirie
 */
function _mft_batch_migrate_terms_metas() {

	// Ensure our table is register
	_mft_maybe_register_taxometa_table();

	global $wpdb;

	$lock_name = 'mft_term_metas.lock';

	// Try to lock.
	$lock_result = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO `$wpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */", $lock_name, time() ) );

	if ( ! $lock_result ) {
		$lock_result = get_option( $lock_name );

		// Bail if we were unable to create a lock, or if the existing lock is still valid.
		if ( ! $lock_result || ( $lock_result > ( time() - HOUR_IN_SECONDS ) ) ) {
			wp_schedule_single_event( time() + ( 5 * MINUTE_IN_SECONDS ), 'mft_migrate_term_metas_batch' );

			return;
		}
	}

	// Update the lock, as by this point we've definitely got a lock, just need to fire the actions.
	update_option( $lock_name, time() );


	/**
	 * 1) Recuperer les 10 prochain term metas avec le nouveau t_id
	 * 2) insérer la donnée dans la nouvelle table
	 * 3) supprimer la donnée de l'ancienne table (delete_metadata)
	 */

	// Get a list of shared terms (those with more than one associated row in term_taxonomy).
	$shared_terms = $wpdb->get_results(
		"SELECT tt.term_id, t.*, count(*) as term_tt_count FROM {$wpdb->term_taxonomy} tt
		 LEFT JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
		 GROUP BY t.term_id
		 HAVING term_tt_count > 1
		 LIMIT 10"
	);

	// No more terms, we're done here.
	if ( ! $shared_terms ) {
		update_option( 'finished_migrating_terms_metas', true );
		delete_option( $lock_name );
		return;
	}

	// Shared terms found? We'll need to run this script again.
	wp_schedule_single_event( time() + ( 2 * MINUTE_IN_SECONDS ), 'mft_migrate_term_metas_batch' );

	delete_option( $lock_name );
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