<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Bootstrap terms metas migration.
 */

$finished_split_terms = get_option( 'finished_splitting_shared_terms', false );
$finished_migrating_terms_metas = get_option( 'finished_migrating_terms_metas', false );

// We have to wait for wp split terms completion
if ( ! $finished_split_terms || $finished_migrating_terms_metas ) {
	return;
}

// Avoid rescheduling our cron
if ( ! $finished_migrating_terms_metas && wp_next_scheduled( 'mft_migrate_term_metas_batch' ) ) {
	return;
}

wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'mft_migrate_term_metas_batch' );