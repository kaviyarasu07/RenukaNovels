<?php
/**
 * Ratings: 1 to 5 stars per reader per story (one each, changeable).
 * Table {prefix}dm_ratings(user_id, story_id, stars, created_at). Average and count are cached in story meta.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_ratings_table() {
	global $wpdb;
	return $wpdb->prefix . 'dm_ratings';
}

function dm_maybe_install_tables() {
	if ( get_option( 'dm_db_version' ) === DM_CORE_VERSION ) {
		return;
	}
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = dm_ratings_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE $table (
		user_id bigint(20) unsigned NOT NULL,
		story_id bigint(20) unsigned NOT NULL,
		stars tinyint(1) unsigned NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (user_id,story_id),
		KEY story_id (story_id)
	) $charset;" );
	dm_install_marks_tables();
	dm_install_reactions_table();
	dm_install_views_table();
	update_option( 'dm_db_version', DM_CORE_VERSION );
}
add_action( 'init', 'dm_maybe_install_tables', 98 );

function dm_user_rating( $story_id, $user_id = 0 ) {
	global $wpdb;
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return 0;
	}
	$t = dm_ratings_table();
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT stars FROM $t WHERE user_id = %d AND story_id = %d", $user_id, $story_id ) ); // phpcs:ignore WordPress.DB
}

/** array( average (float), count (int) ). */
function dm_rating_summary( $story_id ) {
	$count = (int) get_post_meta( $story_id, 'dm_rating_count', true );
	$avg   = (float) get_post_meta( $story_id, 'dm_rating_avg', true );
	return array( $avg, $count );
}

function dm_refresh_rating( $story_id ) {
	global $wpdb;
	$t   = dm_ratings_table();
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT AVG(stars) a, COUNT(*) c FROM $t WHERE story_id = %d", $story_id ) ); // phpcs:ignore WordPress.DB
	update_post_meta( $story_id, 'dm_rating_avg', $row ? round( (float) $row->a, 1 ) : 0 );
	update_post_meta( $story_id, 'dm_rating_count', $row ? (int) $row->c : 0 );
}

/** Average of all ratings on the site, for the home page. array( avg, count ). */
function dm_overall_rating() {
	global $wpdb;
	$t   = dm_ratings_table();
	$row = $wpdb->get_row( "SELECT AVG(stars) a, COUNT(*) c FROM $t" ); // phpcs:ignore WordPress.DB
	return $row && $row->c ? array( round( (float) $row->a, 1 ), (int) $row->c ) : array( 0, 0 );
}

function dm_handle_rate() {
	global $wpdb;
	$story = isset( $_POST['dm_story'] ) ? absint( $_POST['dm_story'] ) : 0;
	$stars = isset( $_POST['dm_stars'] ) ? absint( $_POST['dm_stars'] ) : 0;
	if ( ! $story || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'dm_rate_' . $story ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	if ( ! dm_can_engage() || 'dm_story' !== get_post_type( $story ) || $stars < 1 || $stars > 5 ) {
		wp_safe_redirect( get_permalink( $story ) );
		exit;
	}
	$wpdb->replace( // phpcs:ignore WordPress.DB
		dm_ratings_table(),
		array(
			'user_id'    => get_current_user_id(),
			'story_id'   => $story,
			'stars'      => $stars,
			'created_at' => current_time( 'mysql', true ),
		),
		array( '%d', '%d', '%d', '%s' )
	);
	dm_refresh_rating( $story );
	wp_safe_redirect( add_query_arg( 'dm_msg', 'rated', get_permalink( $story ) ) . '#rate' );
	exit;
}
add_action( 'admin_post_dm_rate', 'dm_handle_rate' );
