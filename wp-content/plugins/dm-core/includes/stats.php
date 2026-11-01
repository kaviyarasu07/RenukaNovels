<?php
/**
 * View counting (first version): total views per chapter and per story.
 * A visitor counts once per chapter per day (cookie). Bots are ignored.
 * Daily and monthly rollups for the admin dashboard come later.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_count_view() {
	if ( ! is_singular( 'dm_chapter' ) || is_preview() ) {
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
	update_post_meta( $id, 'dm_views', (int) get_post_meta( $id, 'dm_views', true ) + 1 );
	$story = wp_get_post_parent_id( $id );
	if ( $story ) {
		update_post_meta( $story, 'dm_views', (int) get_post_meta( $story, 'dm_views', true ) + 1 );
	}
}
add_action( 'template_redirect', 'dm_count_view' );
