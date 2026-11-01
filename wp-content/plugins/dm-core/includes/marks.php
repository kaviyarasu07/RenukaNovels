<?php
/**
 * Bookmarks and highlights, saved per approved reader.
 * Bookmark  = one chapter + paragraph per reader per chapter.
 * Highlight = one sentence (paragraph number + sentence number) in one of three colours.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_bm_table() {
	global $wpdb;
	return $wpdb->prefix . 'dm_bookmarks';
}

function dm_hl_table() {
	global $wpdb;
	return $wpdb->prefix . 'dm_highlights';
}

function dm_install_marks_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$bm      = dm_bm_table();
	$hl      = dm_hl_table();
	dbDelta( "CREATE TABLE $bm (
		user_id bigint(20) unsigned NOT NULL,
		chapter_id bigint(20) unsigned NOT NULL,
		para smallint(5) unsigned NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (user_id,chapter_id),
		KEY chapter_id (chapter_id)
	) $charset;" );
	dbDelta( "CREATE TABLE $hl (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		user_id bigint(20) unsigned NOT NULL,
		chapter_id bigint(20) unsigned NOT NULL,
		para smallint(5) unsigned NOT NULL,
		sent smallint(5) unsigned NOT NULL,
		color varchar(10) NOT NULL,
		snippet varchar(120) NOT NULL DEFAULT '',
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY pos (user_id,chapter_id,para,sent),
		KEY user_chapter (user_id,chapter_id)
	) $charset;" );
}

/** Marks of one reader on one chapter, for the reading page. */
function dm_marks_for( $chapter_id, $user_id ) {
	global $wpdb;
	$bm = dm_bm_table();
	$hl = dm_hl_table();
	$b  = $wpdb->get_var( $wpdb->prepare( "SELECT para FROM $bm WHERE user_id = %d AND chapter_id = %d", $user_id, $chapter_id ) ); // phpcs:ignore WordPress.DB
	$h  = $wpdb->get_results( $wpdb->prepare( "SELECT para p, sent s, color c FROM $hl WHERE user_id = %d AND chapter_id = %d", $user_id, $chapter_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
	return array(
		'bm' => null === $b ? null : (int) $b,
		'hl' => $h ? array_map( function ( $r ) { return array( 'p' => (int) $r['p'], 's' => (int) $r['s'], 'c' => $r['c'] ); }, $h ) : array(),
	);
}

function dm_user_bookmarks( $user_id, $limit = 20, $offset = 0 ) {
	global $wpdb;
	$t = dm_bm_table();
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d", $user_id, $limit, $offset ) ); // phpcs:ignore WordPress.DB
}

/** Admin view: bookmarks of all readers (newest first). */
function dm_all_bookmarks( $limit = 20, $offset = 0 ) {
	global $wpdb;
	$t = dm_bm_table();
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset ) ); // phpcs:ignore WordPress.DB
}

function dm_user_highlights( $user_id, $limit = 20, $offset = 0 ) {
	global $wpdb;
	$t = dm_hl_table();
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t WHERE user_id = %d ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", $user_id, $limit, $offset ) ); // phpcs:ignore WordPress.DB
}

function dm_all_highlights( $limit = 20, $offset = 0 ) {
	global $wpdb;
	$t = dm_hl_table();
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", $limit, $offset ) ); // phpcs:ignore WordPress.DB
}

/** Totals for paging. $kind: bm or hl. $user_id 0 = all readers. */
function dm_count_marks( $kind, $user_id = 0 ) {
	global $wpdb;
	$t = 'bm' === $kind ? dm_bm_table() : dm_hl_table();
	if ( $user_id ) {
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t WHERE user_id = %d", $user_id ) ); // phpcs:ignore WordPress.DB
	}
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t" ); // phpcs:ignore WordPress.DB
}

/** AJAX from the reading page. */
function dm_ajax_mark() {
	global $wpdb;
	if ( ! check_ajax_referer( 'dm_mark', 'nonce', false ) ) {
		wp_send_json_error( array( 'msg' => 'expired' ), 403 );
	}
	if ( ! dm_can_engage() ) {
		wp_send_json_error( array( 'msg' => 'not_allowed' ), 403 );
	}
	$uid     = get_current_user_id();
	$chapter = isset( $_POST['chapter'] ) ? absint( $_POST['chapter'] ) : 0;
	$op      = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';
	$para    = isset( $_POST['para'] ) ? min( absint( $_POST['para'] ), 5000 ) : 0;
	$sent    = isset( $_POST['sent'] ) ? min( absint( $_POST['sent'] ), 5000 ) : 0;
	if ( ! $chapter || 'dm_chapter' !== get_post_type( $chapter ) ) {
		wp_send_json_error( array( 'msg' => 'bad_chapter' ), 400 );
	}
	$bm = dm_bm_table();
	$hl = dm_hl_table();
	$now = current_time( 'mysql', true );
	if ( 'bm_set' === $op ) {
		$wpdb->replace( $bm, array( 'user_id' => $uid, 'chapter_id' => $chapter, 'para' => $para, 'created_at' => $now ), array( '%d', '%d', '%d', '%s' ) ); // phpcs:ignore WordPress.DB
	} elseif ( 'bm_clear' === $op ) {
		$wpdb->delete( $bm, array( 'user_id' => $uid, 'chapter_id' => $chapter ), array( '%d', '%d' ) ); // phpcs:ignore WordPress.DB
	} elseif ( 'hl_set' === $op ) {
		$color = isset( $_POST['color'] ) ? sanitize_key( wp_unslash( $_POST['color'] ) ) : '';
		if ( ! in_array( $color, array( 'yellow', 'green', 'pink' ), true ) ) {
			wp_send_json_error( array( 'msg' => 'bad_color' ), 400 );
		}
		$snip = isset( $_POST['snippet'] ) ? sanitize_text_field( wp_unslash( $_POST['snippet'] ) ) : '';
		$snip = function_exists( 'mb_substr' ) ? mb_substr( $snip, 0, 100 ) : substr( $snip, 0, 100 );
		$wpdb->replace( $hl, array( 'user_id' => $uid, 'chapter_id' => $chapter, 'para' => $para, 'sent' => $sent, 'color' => $color, 'snippet' => $snip, 'created_at' => $now ), array( '%d', '%d', '%d', '%d', '%s', '%s', '%s' ) ); // phpcs:ignore WordPress.DB
	} elseif ( 'hl_clear' === $op ) {
		$wpdb->delete( $hl, array( 'user_id' => $uid, 'chapter_id' => $chapter, 'para' => $para, 'sent' => $sent ), array( '%d', '%d', '%d', '%d' ) ); // phpcs:ignore WordPress.DB
	} else {
		wp_send_json_error( array( 'msg' => 'bad_op' ), 400 );
	}
	wp_send_json_success();
}
add_action( 'wp_ajax_dm_mark', 'dm_ajax_mark' );

/** My Account: remove a bookmark or highlight. */
function dm_handle_remove_mark() {
	if ( ! is_user_logged_in() || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'dm_rmmark' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	global $wpdb;
	$uid     = get_current_user_id();
	$type    = isset( $_POST['dm_type'] ) ? sanitize_key( wp_unslash( $_POST['dm_type'] ) ) : '';
	$chapter = isset( $_POST['dm_chapter'] ) ? absint( $_POST['dm_chapter'] ) : 0;
	if ( 'bookmark' === $type ) {
		$wpdb->delete( dm_bm_table(), array( 'user_id' => $uid, 'chapter_id' => $chapter ), array( '%d', '%d' ) ); // phpcs:ignore WordPress.DB
		$tab = 'bookmarks';
	} else {
		$wpdb->delete( dm_hl_table(), array( 'user_id' => $uid, 'chapter_id' => $chapter, 'para' => isset( $_POST['dm_para'] ) ? absint( $_POST['dm_para'] ) : 0, 'sent' => isset( $_POST['dm_sent'] ) ? absint( $_POST['dm_sent'] ) : 0 ), array( '%d', '%d', '%d', '%d' ) ); // phpcs:ignore WordPress.DB
		$tab = 'highlights';
	}
	wp_safe_redirect( add_query_arg( 'tab', $tab, home_url( '/my-account/' ) ) );
	exit;
}
add_action( 'admin_post_dm_remove_mark', 'dm_handle_remove_mark' );
