<?php
/**
 * In-site notifications (the bell in the menu bar).
 *
 * Two kinds of items are merged for each logged-in user:
 *  1. Content items, worked out from published posts (no rows stored):
 *     new story, new episode, announcement. Shown to every logged-in user,
 *     only for things published after they registered and in the last 30 days.
 *  2. Personal items, stored in {prefix}dm_notifications:
 *     reply to your comment, account approved or rejected (readers);
 *     new comment, rating, reaction and reader waiting for approval (admins).
 *
 * "Unread" = newer than the user's last-seen time (user meta dm_notif_seen), set when the bell is opened.
 * Repeat actions by the same person on the same item (for example changing a rating) update one row
 * instead of adding another. Each user keeps at most 100 personal rows.
 * This is not web push: it only shows while the site is open. Push is a separate module.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_notif_table() {
	global $wpdb;
	return $wpdb->prefix . 'dm_notifications';
}

function dm_install_notifications_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = dm_notif_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		user_id bigint(20) unsigned NOT NULL,
		kind varchar(20) NOT NULL,
		msg varchar(255) NOT NULL,
		url varchar(255) NOT NULL DEFAULT '',
		dedupe varchar(64) NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY user_dedupe (user_id,dedupe),
		KEY user_time (user_id,created_at)
	) $charset;" );
}

/** Short plain text, safe to store (escaped again on output). */
function dm_notif_cut( $text, $len = 90 ) {
	$text = trim( wp_strip_all_tags( (string) $text ) );
	if ( function_exists( 'mb_strlen' ) ) {
		return mb_strlen( $text ) > $len ? mb_substr( $text, 0, $len ) . '...' : $text;
	}
	return strlen( $text ) > $len ? substr( $text, 0, $len ) . '...' : $text;
}

/** Add (or refresh) one personal notification. $dedupe identifies "the same thing" so it is not repeated. */
function dm_notify( $user_id, $kind, $msg, $url, $dedupe ) {
	global $wpdb;
	$user_id = (int) $user_id;
	if ( ! $user_id ) {
		return;
	}
	$t   = dm_notif_table();
	$now = current_time( 'mysql', true );
	$msg = dm_notif_cut( $msg, 200 );
	$wpdb->query( $wpdb->prepare( "INSERT INTO $t (user_id, kind, msg, url, dedupe, created_at) VALUES (%d, %s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE kind = VALUES(kind), msg = VALUES(msg), url = VALUES(url), created_at = VALUES(created_at)", $user_id, $kind, $msg, $url, substr( $dedupe, 0, 64 ), $now ) ); // phpcs:ignore WordPress.DB
	// keep the newest 100 per user.
	$cut = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE user_id = %d ORDER BY id DESC LIMIT 1 OFFSET 100", $user_id ) ); // phpcs:ignore WordPress.DB
	if ( $cut ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM $t WHERE user_id = %d AND id <= %d", $user_id, $cut ) ); // phpcs:ignore WordPress.DB
	}
}

function dm_admin_ids() {
	static $ids = null;
	if ( null === $ids ) {
		$ids = array_map( 'intval', get_users( array( 'role' => 'administrator', 'fields' => 'ID' ) ) );
	}
	return $ids;
}

/** Notify every admin except $skip (the person who did the action, and anyone already told). */
function dm_notify_admins( $kind, $msg, $url, $dedupe, $skip = array() ) {
	foreach ( dm_admin_ids() as $aid ) {
		if ( ! in_array( $aid, (array) $skip, true ) ) {
			dm_notify( $aid, $kind, $msg, $url, $dedupe );
		}
	}
}

function dm_notif_seen_time( $user_id ) {
	$seen = get_user_meta( $user_id, 'dm_notif_seen', true );
	if ( '' === $seen ) { // first time for this account: start with nothing unread.
		$seen = time();
		update_user_meta( $user_id, 'dm_notif_seen', $seen );
	}
	return (int) $seen;
}

/**
 * The bell list for one user, newest first.
 * Returns array( items, unread_count ). Each item: kind, label, msg, url, ts, unread.
 */
function dm_notifications_for( $user_id, $limit = 20 ) {
	global $wpdb;
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return array( array(), 0 );
	}
	$seen  = dm_notif_seen_time( $user_id );
	$since = max( strtotime( $user->user_registered . ' UTC' ), time() - 30 * DAY_IN_SECONDS );
	$items = array();

	// 1. content items from published posts.
	$posts = get_posts(
		array(
			'post_type'      => array( 'dm_story', 'dm_chapter', 'dm_announcement' ),
			'post_status'    => 'publish',
			'posts_per_page' => 30,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'date_query'     => array( array( 'after' => gmdate( 'Y-m-d H:i:s', $since ), 'column' => 'post_date_gmt' ) ),
		)
	);
	foreach ( $posts as $p ) {
		$ts = strtotime( $p->post_date_gmt . ' UTC' );
		if ( 'dm_story' === $p->post_type ) {
			$items[] = array( 'story', 'New story', 'New story: ' . get_the_title( $p ), get_permalink( $p ), $ts );
		} elseif ( 'dm_chapter' === $p->post_type ) {
			$story = $p->post_parent ? get_post( $p->post_parent ) : null;
			if ( ! $story || 'publish' !== $story->post_status ) {
				continue;
			}
			$no      = (int) get_post_meta( $p->ID, 'dm_chapter_no', true );
			$items[] = array( 'chapter', 'New episode', 'New episode in ' . get_the_title( $story ) . ( $no ? ' (Episode ' . $no . ')' : '' ) . ': ' . get_the_title( $p ), get_permalink( $p ), $ts );
		} else {
			$items[] = array( 'announce', 'Announcement', dm_notif_cut( $p->post_content, 110 ), home_url( '/updates/' ), $ts );
		}
	}

	// 2. personal items.
	$t    = dm_notif_table();
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT kind, msg, url, created_at FROM $t WHERE user_id = %d ORDER BY created_at DESC LIMIT 50", $user_id ) ); // phpcs:ignore WordPress.DB
	$labels = array(
		'reply'    => 'Reply',
		'comment'  => 'Comment',
		'rating'   => 'Rating',
		'react'    => 'Reaction',
		'register' => 'Approval',
		'account'  => 'Account',
	);
	foreach ( (array) $rows as $r ) {
		$items[] = array( $r->kind, isset( $labels[ $r->kind ] ) ? $labels[ $r->kind ] : 'Notice', $r->msg, $r->url, strtotime( $r->created_at . ' UTC' ) );
	}

	usort( $items, function ( $a, $b ) { return $b[4] <=> $a[4]; } );
	$items  = array_slice( $items, 0, $limit );
	$out    = array();
	$unread = 0;
	foreach ( $items as $i ) {
		$is_new = $i[4] > $seen;
		$unread += $is_new ? 1 : 0;
		$out[]   = array( 'kind' => $i[0], 'label' => $i[1], 'msg' => $i[2], 'url' => $i[3], 'ts' => $i[4], 'unread' => $is_new );
	}
	return array( $out, $unread );
}

/** AJAX: the bell was opened, so everything up to now counts as seen. */
function dm_ajax_notif_seen() {
	if ( ! is_user_logged_in() || ! check_ajax_referer( 'dm_notif', 'nonce', false ) ) {
		wp_send_json_error( null, 403 );
	}
	update_user_meta( get_current_user_id(), 'dm_notif_seen', time() );
	wp_send_json_success();
}
add_action( 'wp_ajax_dm_notif_seen', 'dm_ajax_notif_seen' );
