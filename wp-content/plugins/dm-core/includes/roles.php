<?php
/**
 * Two roles: Admin (WordPress "administrator") and Reader ("dm_reader").
 * Readers never see the WordPress admin screens; they use the My Account page.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_ensure_roles() {
	if ( ! get_role( 'dm_reader' ) ) {
		add_role( 'dm_reader', 'Reader', array( 'read' => true ) );
	}
}
add_action( 'init', 'dm_ensure_roles' );

/** Readers: no admin bar and no wp-admin; send them to My Account. */
function dm_hide_admin_bar( $show ) {
	return current_user_can( 'edit_posts' ) ? $show : false;
}
add_filter( 'show_admin_bar', 'dm_hide_admin_bar' );

function dm_block_reader_admin() {
	if ( wp_doing_ajax() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( is_user_logged_in() && ! current_user_can( 'edit_posts' ) ) {
		$script = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';
		if ( 'admin-post.php' === $script ) {
			return; // front-end forms post here.
		}
		wp_safe_redirect( home_url( '/my-account/' ) );
		exit;
	}
}
add_action( 'admin_init', 'dm_block_reader_admin' );

/** After login go to My Account (unless a specific page was asked for). */
function dm_login_redirect( $redirect_to, $requested, $user ) {
	if ( $user instanceof WP_User && ! user_can( $user, 'edit_posts' ) ) {
		return home_url( '/my-account/' );
	}
	return $redirect_to;
}
add_filter( 'login_redirect', 'dm_login_redirect', 10, 3 );
