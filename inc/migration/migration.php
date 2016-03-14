<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class MFT_Migration {

	const LOCK_NAME = 'mft_term_metas.lock';


	/**
	 * MFT_Migration constructor.
	 */
	public function __construct() {
		if( ! self::is_finished() ) {
			return;
		}

		if( ! self::can_launch_next() ) {
			return;
		}

		/**
		 * Schedule the next element
		 */
		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'mft_migrate_term_metas_batch' );
	}

	public static function is_finished() {
		/**
		 * Bootstrap terms metas migration.
		 */
		$finished_split_terms = get_option( 'finished_splitting_shared_terms', false );
		$finished_migrating_terms_metas = get_option( 'finished_migrating_terms_metas', false );

		// We have to wait for wp split terms completion
		if ( ! $finished_split_terms || ! $finished_migrating_terms_metas ) {
			return false;
		}

		return true;
	}

	public static function can_launch_next() {
		$finished_migrating_terms_metas = get_option( 'finished_migrating_terms_metas', false );
		// Avoid rescheduling our cron
		if ( ! $finished_migrating_terms_metas && wp_next_scheduled( 'mft_migrate_term_metas_batch' ) ) {
			return false;
		}

		return true;
	}
}

new MFT_Migration();