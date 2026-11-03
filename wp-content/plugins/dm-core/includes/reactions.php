<?php
/**
 * Reactions on a chapter: one emoji per approved reader per chapter (tap again to remove, tap another to change).
 * Table {prefix}dm_reactions(user_id, chapter_id, kind, created_at).
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_reactions_table() {
	global $wpdb;
	return $wpdb->prefix . 'dm_reactions';
}

function dm_install_reactions_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = dm_reactions_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE $table (
		user_id bigint(20) unsigned NOT NULL,
		chapter_id bigint(20) unsigned NOT NULL,
		kind varchar(10) NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (user_id,chapter_id),
		KEY chapter_kind (chapter_id,kind)
	) $charset;" );
}

/** The six reactions, in display order: key => emoji and label. */
function dm_reaction_options() {
	return array(
		'heart' => array( "\u{2764}\u{FE0F}", 'Loved it' ),
		'laugh' => array( "\u{1F602}", 'Funny' ),
		'sad'   => array( "\u{1F622}", 'Sad' ),
		'wow'   => array( "\u{1F62E}", 'Surprised' ),
		'fire'  => array( "\u{1F525}", 'Great' ),
		'clap'  => array( "\u{1F44F}", 'Well written' ),
	);
}

/** array( key => count ) for one chapter (every key present, zero when nobody picked it). */
function dm_reaction_counts( $chapter_id ) {
	global $wpdb;
	$t      = dm_reactions_table();
	$counts = array_fill_keys( array_keys( dm_reaction_options() ), 0 );
	$rows   = $wpdb->get_results( $wpdb->prepare( "SELECT kind, COUNT(*) c FROM $t WHERE chapter_id = %d GROUP BY kind", $chapter_id ) ); // phpcs:ignore WordPress.DB
	foreach ( (array) $rows as $r ) {
		if ( isset( $counts[ $r->kind ] ) ) {
			$counts[ $r->kind ] = (int) $r->c;
		}
	}
	return $counts;
}

/** The reaction key this reader chose on a chapter, or ''. */
function dm_user_reaction( $chapter_id, $user_id = 0 ) {
	global $wpdb;
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return '';
	}
	$t = dm_reactions_table();
	return (string) $wpdb->get_var( $wpdb->prepare( "SELECT kind FROM $t WHERE user_id = %d AND chapter_id = %d", $user_id, $chapter_id ) ); // phpcs:ignore WordPress.DB
}

/** Reaction totals for every chapter at once: chapter_id => array( key => count ). Used by the Stats tab. */
function dm_reaction_counts_all() {
	global $wpdb;
	$t   = dm_reactions_table();
	$out = array();
	foreach ( (array) $wpdb->get_results( "SELECT chapter_id, kind, COUNT(*) c FROM $t GROUP BY chapter_id, kind" ) as $r ) { // phpcs:ignore WordPress.DB
		$out[ (int) $r->chapter_id ][ $r->kind ] = (int) $r->c;
	}
	return $out;
}

/** AJAX from the reading page. */
function dm_ajax_react() {
	global $wpdb;
	if ( ! check_ajax_referer( 'dm_react', 'nonce', false ) ) {
		wp_send_json_error( array( 'msg' => 'expired' ), 403 );
	}
	if ( ! dm_can_engage() ) {
		wp_send_json_error( array( 'msg' => 'not_allowed' ), 403 );
	}
	$chapter = isset( $_POST['chapter'] ) ? absint( $_POST['chapter'] ) : 0;
	$kind    = isset( $_POST['kind'] ) ? sanitize_key( wp_unslash( $_POST['kind'] ) ) : '';
	$options = dm_reaction_options();
	if ( ! $chapter || 'dm_chapter' !== get_post_type( $chapter ) || ! isset( $options[ $kind ] ) ) {
		wp_send_json_error( array( 'msg' => 'bad_request' ), 400 );
	}
	$uid  = get_current_user_id();
	$t    = dm_reactions_table();
	$mine = dm_user_reaction( $chapter, $uid );
	if ( $mine === $kind ) {
		$wpdb->delete( $t, array( 'user_id' => $uid, 'chapter_id' => $chapter ), array( '%d', '%d' ) ); // phpcs:ignore WordPress.DB
		$mine = '';
	} else {
		$wpdb->replace( $t, array( 'user_id' => $uid, 'chapter_id' => $chapter, 'kind' => $kind, 'created_at' => current_time( 'mysql', true ) ), array( '%d', '%d', '%s', '%s' ) ); // phpcs:ignore WordPress.DB
		$mine = $kind;
	}
	wp_send_json_success( array( 'counts' => dm_reaction_counts( $chapter ), 'mine' => $mine ) );
}
add_action( 'wp_ajax_dm_react', 'dm_ajax_react' );
