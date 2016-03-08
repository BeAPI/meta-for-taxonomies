<?php
// Bail if WP-CLI is not present
if ( !defined( 'WP_CLI' ) ) {
	return false;
}

class MFT_CLI extends WP_CLI_Command {

	/**
	 * Launch migration with no limitations
	 *
	 *## OPTIONS
	 *
	 * <number>
	 * : the number of terms to do. Apply -1 to have unlimited
	 *
	 * ## EXAMPLES
	 *
	 *  wp mft migrate
	 *
	 * @synopsis
	 */
	function migrate( $args ) {
		global $wpdb;

		$number = isset( $args[0] ) ? (int)$args[0] : 100;
		$limit = $number <= 0 ? '' : ' LIMIT 0, '.$number ;

		_mft_maybe_register_taxometa_table();

		// Get a list of shared terms (those with more than one associated row in term_taxonomy).
		$terms_metas = $wpdb->get_results(
			"SELECT ttm.meta_id, tt.taxonomy, tt.term_id, ttm.meta_key, ttm.meta_value
		 FROM {$wpdb->term_taxometa} ttm
		 INNER JOIN {$wpdb->term_taxonomy} tt
		 ON tt.term_taxonomy_id = ttm.term_taxo_id
		 ORDER BY ttm.meta_id
		 $limit
		 ;"
		);

		WP_CLI::line( 'Start migration' );
		WP_CLI::line( sprintf( '%d meta selected', count( $terms_metas ) ) );

		// No more terms, we're done here.
		if ( ! $terms_metas ) {
			WP_CLI::line( 'Stop migration, no term metas' );
			update_option( 'finished_migrating_terms_metas', true );
			delete_option( $lock_name );
			return;
		}

		$failed_transactions = get_option( 'mft_migrate_fails', array() );
		$previous_failed_transactions_count = count( $failed_transactions );

		$deleted = 0;
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
			} else {
				$deleted += $wpdb->delete( $wpdb->term_taxometa, [ 'meta_id' => $meta->meta_id ], [ '%d' ] );
			}
		}

		// Save failed transactions if new ones
		if ( count( $failed_transactions ) !== $previous_failed_transactions_count ) {
			update_option( 'mft_migrate_fails', $failed_transactions );
			WP_CLI::line( '%s failed transactions', $previous_failed_transactions_count-$failed_transactions );
		}


		WP_CLI::line( sprintf( 'Deleted %s metas from origin metas', $deleted ) );


		WP_CLI::line( 'Migration end' );
	}
}
WP_CLI::add_command( 'mft', 'MFT_CLI' );