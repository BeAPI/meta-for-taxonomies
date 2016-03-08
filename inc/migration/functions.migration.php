<?php

add_action( 'mft_migrate_term_metas_batch', '_mft_batch_migrate_terms_metas' );
/**
 * Migrate existing meta terms to the new WordPress table
 * Heavily inspired by _wp_batch_split_terms
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

	// Get a list of shared terms (those with more than one associated row in term_taxonomy).
	$terms_metas = $wpdb->get_results(
		"SELECT ttm.meta_id, tt.taxonomy, tt.term_id, ttm.meta_key, ttm.meta_value
		 FROM {$wpdb->term_taxometa} ttm
		 INNER JOIN {$wpdb->term_taxonomy} tt
		 ON tt.term_taxonomy_id = ttm.term_taxo_id
		 ORDER BY ttm.meta_id
		 LIMIT 50;"
	);

	// No more terms, we're done here.
	if ( ! $terms_metas ) {
		update_option( 'finished_migrating_terms_metas', true );
		delete_option( $lock_name );
		return;
	}

	// Terms metas found? We'll need to run this script again.
	wp_schedule_single_event( time() + ( 2 * MINUTE_IN_SECONDS ), 'mft_migrate_term_metas_batch' );

	$failed_transactions = get_option( 'mft_migrate_fails', array() );
	$previous_failed_transactions_count = count( $failed_transactions );

	// Insert metas to the wordpress metas table
	foreach( $terms_metas as $meta ) {

		$update = update_term_meta( $meta->term_id, $meta->meta_key, $meta->meta_value );

		// If something went wrong save the term metas data and continue
		if ( is_wp_error( $update ) || false === $update ) {
			$oops = array(
				'taxonomy' => $meta->taxonomy,
				'term_id' => $meta->term_id,
				'meta_key' => $meta->meta_key,
				'meta_value' => $meta->meta_value,
				'is_wp_error' => is_wp_error( $update ),
			);

			$failed_transactions[] = $oops;
		}
	}

	// Save failed transactions if new ones
	if ( count( $failed_transactions ) !== $previous_failed_transactions_count ) {
		update_option( 'mft_migrate_fails', $failed_transactions );
	}

	// Build an array of old meta ids and delete them
	$ids = implode( ',', array_filter( array_map( 'absint', wp_list_pluck( $terms_metas, 'meta_id' ) ) ) );

	$wpdb->query(
		"
		DELETE FROM {$wpdb->term_taxometa}
		WHERE meta_id IN ({$ids})
		"
	);

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