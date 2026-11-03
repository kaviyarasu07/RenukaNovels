<?php
/**
 * View counting and reports.
 *  - A visitor counts once per chapter per day (cookie). Bots and the admin are ignored.
 *  - Each count adds 1 to the chapter's running total (meta dm_views, also kept on the story)
 *    and 1 to the daily table {prefix}dm_views_daily(chapter_id, story_id, day).
 *  - Story views are always the sum of its chapters, so nothing is counted twice.
 *  - "day" uses the site time zone (Settings > General), so set it to Kolkata.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_views_table() {
	global $wpdb;
	return $wpdb->prefix . 'dm_views_daily';
}

function dm_install_views_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = dm_views_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE $table (
		chapter_id bigint(20) unsigned NOT NULL,
		story_id bigint(20) unsigned NOT NULL,
		day date NOT NULL,
		views int(10) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (chapter_id,day),
		KEY story_day (story_id,day),
		KEY day (day)
	) $charset;" );
}

/** Add views to one chapter for one day (default today). */
function dm_add_views( $chapter_id, $story_id, $n = 1, $day = '' ) {
	global $wpdb;
	$day = $day ? $day : current_time( 'Y-m-d' );
	$t   = dm_views_table();
	$wpdb->query( $wpdb->prepare( "INSERT INTO $t (chapter_id, story_id, day, views) VALUES (%d, %d, %s, %d) ON DUPLICATE KEY UPDATE views = views + %d", $chapter_id, $story_id, $day, $n, $n ) ); // phpcs:ignore WordPress.DB
}

function dm_count_view() {
	if ( ! is_singular( 'dm_chapter' ) || is_preview() || current_user_can( 'manage_options' ) ) {
		return;
	}
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	if ( '' === $ua || preg_match( '/bot|crawl|spider|slurp|bing|facebook|preview|curl|wget/i', $ua ) ) {
		return;
	}
	$id     = get_queried_object_id();
	$cookie = 'dm_v_' . $id;
	if ( isset( $_COOKIE[ $cookie ] ) ) {
		return;
	}
	if ( ! headers_sent() ) {
		setcookie( $cookie, '1', time() + DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	}
	$story = (int) wp_get_post_parent_id( $id );
	update_post_meta( $id, 'dm_views', (int) get_post_meta( $id, 'dm_views', true ) + 1 );
	if ( $story ) {
		update_post_meta( $story, 'dm_views', (int) get_post_meta( $story, 'dm_views', true ) + 1 );
	}
	dm_add_views( $id, $story, 1 );
}
add_action( 'template_redirect', 'dm_count_view' );

/** Views per story in the last 7 days (today included): story_id => views. Used by Trending. */
function dm_week_views_map() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	global $wpdb;
	$t     = dm_views_table();
	$since = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' -6 days' ) );
	$map   = array();
	foreach ( (array) $wpdb->get_results( $wpdb->prepare( "SELECT story_id, SUM(views) v FROM $t WHERE day >= %s GROUP BY story_id", $since ) ) as $r ) { // phpcs:ignore WordPress.DB
		$map[ (int) $r->story_id ] = (int) $r->v;
	}
	return $map;
}

/** Views per chapter since a date (Y-m-d): chapter_id => views. */
function dm_views_by_chapter_since( $since ) {
	global $wpdb;
	$t   = dm_views_table();
	$out = array();
	foreach ( (array) $wpdb->get_results( $wpdb->prepare( "SELECT chapter_id, SUM(views) v FROM $t WHERE day >= %s GROUP BY chapter_id", $since ) ) as $r ) { // phpcs:ignore WordPress.DB
		$out[ (int) $r->chapter_id ] = (int) $r->v;
	}
	return $out;
}

/**
 * Chart data. $period 'daily' = last 30 days, 'monthly' = last 12 months.
 * $story_id 0 = whole site. Returns a list of array( label, key, views ), oldest first.
 */
function dm_views_series( $period, $story_id = 0 ) {
	global $wpdb;
	$t     = dm_views_table();
	$today = current_time( 'Y-m-d' );
	$where = $story_id ? $wpdb->prepare( ' AND story_id = %d', $story_id ) : '';
	$out   = array();
	if ( 'monthly' === $period ) {
		$first = gmdate( 'Y-m-01', strtotime( $today . ' -11 months' ) );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT DATE_FORMAT(day, '%%Y-%%m') m, SUM(views) v FROM $t WHERE day >= %s $where GROUP BY m", $first ) ); // phpcs:ignore WordPress.DB
		$map   = array();
		foreach ( (array) $rows as $r ) {
			$map[ $r->m ] = (int) $r->v;
		}
		for ( $i = 11; $i >= 0; $i-- ) {
			$ts    = strtotime( gmdate( 'Y-m-01', strtotime( $today ) ) . " -$i months" );
			$key   = gmdate( 'Y-m', $ts );
			$out[] = array( gmdate( 'M y', $ts ), $key, isset( $map[ $key ] ) ? $map[ $key ] : 0 );
		}
		return $out;
	}
	$first = gmdate( 'Y-m-d', strtotime( $today . ' -29 days' ) );
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT day, SUM(views) v FROM $t WHERE day >= %s $where GROUP BY day", $first ) ); // phpcs:ignore WordPress.DB
	$map   = array();
	foreach ( (array) $rows as $r ) {
		$map[ $r->day ] = (int) $r->v;
	}
	for ( $i = 29; $i >= 0; $i-- ) {
		$ts    = strtotime( $today . " -$i days" );
		$key   = gmdate( 'Y-m-d', $ts );
		$out[] = array( gmdate( 'j M', $ts ), $key, isset( $map[ $key ] ) ? $map[ $key ] : 0 );
	}
	return $out;
}

/** Approved comment totals for every post: post_id => count. */
function dm_comment_counts_all() {
	global $wpdb;
	$out = array();
	foreach ( (array) $wpdb->get_results( "SELECT comment_post_ID id, COUNT(*) c FROM {$wpdb->comments} WHERE comment_approved = '1' GROUP BY comment_post_ID" ) as $r ) { // phpcs:ignore WordPress.DB
		$out[ (int) $r->id ] = (int) $r->c;
	}
	return $out;
}

/** When a chapter is deleted for good, drop its daily views and reactions. */
function dm_cleanup_chapter_data( $post_id ) {
	if ( 'dm_chapter' !== get_post_type( $post_id ) ) {
		return;
	}
	global $wpdb;
	$wpdb->delete( dm_views_table(), array( 'chapter_id' => $post_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB
	$wpdb->delete( dm_reactions_table(), array( 'chapter_id' => $post_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB
}
add_action( 'before_delete_post', 'dm_cleanup_chapter_data' );
