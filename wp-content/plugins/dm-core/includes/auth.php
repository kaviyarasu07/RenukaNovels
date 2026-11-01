<?php
/**
 * Reader registration, login and approval.
 *
 * Flow: a reader registers (role dm_reader, status "pending") and is logged in. A pending reader can read
 * but cannot comment, rate or bookmark. The admin approves or rejects from My Account > Reader approvals.
 * Accounts without a status (for example made by the admin in wp-admin) count as approved.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** pending | approved | rejected */
function dm_user_status( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return '';
	}
	if ( user_can( $user_id, 'manage_options' ) ) {
		return 'approved';
	}
	$s = get_user_meta( $user_id, 'dm_status', true );
	return in_array( $s, array( 'pending', 'rejected' ), true ) ? $s : 'approved';
}

/** True when the current user may comment, rate and bookmark. */
function dm_can_engage() {
	return is_user_logged_in() && 'approved' === dm_user_status();
}

function dm_login_page_url( $args = array() ) {
	return add_query_arg( $args, home_url( '/login/' ) );
}

/** Use our own login page everywhere WordPress asks for the login URL. wp-login.php itself still works. */
function dm_filter_login_url( $login_url, $redirect ) {
	$args = array();
	if ( $redirect ) {
		$args['redirect_to'] = $redirect;
	}
	return dm_login_page_url( $args );
}
add_filter( 'login_url', 'dm_filter_login_url', 10, 2 );

function dm_auth_redirect( $args, $to = '' ) {
	$base = home_url( '/login/' );
	if ( $to ) {
		$args['redirect_to'] = $to;
	}
	wp_safe_redirect( add_query_arg( $args, $base ) );
	exit;
}

function dm_post( $key ) {
	return isset( $_POST[ $key ] ) ? trim( wp_unslash( $_POST[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security
}

function dm_check_nonce( $action ) {
	return isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), $action );
}

/** Register. */
function dm_handle_register() {
	$to  = dm_post( 'redirect_to' );
	$tab = array( 'tab' => 'register' );
	if ( ! dm_check_nonce( 'dm_register' ) ) {
		dm_auth_redirect( $tab + array( 'dm_err' => 'nonce' ), $to );
	}
	if ( '' !== dm_post( 'dm_website' ) ) { // honeypot: bots fill the hidden field.
		dm_auth_redirect( $tab + array( 'dm_err' => 'spam' ), $to );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'dm_reg_' . md5( $ip );
	$cnt = (int) get_transient( $key );
	if ( $cnt >= 5 ) {
		dm_auth_redirect( $tab + array( 'dm_err' => 'rate' ), $to );
	}
	set_transient( $key, $cnt + 1, HOUR_IN_SECONDS );

	$name  = sanitize_text_field( dm_post( 'dm_name' ) );
	$email = sanitize_email( dm_post( 'dm_email' ) );
	$pass  = dm_post( 'dm_pass' );
	if ( '' === $name || ! is_email( $email ) ) {
		dm_auth_redirect( $tab + array( 'dm_err' => 'fields' ), $to );
	}
	if ( strlen( $pass ) < 8 ) {
		dm_auth_redirect( $tab + array( 'dm_err' => 'weak' ), $to );
	}
	if ( $pass !== dm_post( 'dm_pass2' ) ) {
		dm_auth_redirect( $tab + array( 'dm_err' => 'mismatch' ), $to );
	}
	if ( email_exists( $email ) ) {
		dm_auth_redirect( $tab + array( 'dm_err' => 'exists' ), $to );
	}
	$login = sanitize_user( strstr( $email, '@', true ), true );
	$login = $login ? $login : 'reader';
	$base  = $login;
	$i     = 1;
	while ( username_exists( $login ) ) {
		$login = $base . ++$i;
	}
	$uid = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'display_name' => $name,
			'nickname'     => $name,
			'role'         => 'dm_reader',
		)
	);
	if ( is_wp_error( $uid ) ) {
		dm_auth_redirect( $tab + array( 'dm_err' => 'fields' ), $to );
	}
	update_user_meta( $uid, 'dm_status', 'pending' );
	// Tell the admin. Silent if the server cannot send mail (local sites usually cannot).
	wp_mail( get_option( 'admin_email' ), 'New reader waiting for approval', "A new reader registered: $name <$email>.\nApprove or reject at " . home_url( '/my-account/?tab=readers' ) );
	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true );
	wp_safe_redirect( add_query_arg( 'dm_msg', 'welcome', home_url( '/my-account/' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_dm_register', 'dm_handle_register' );
add_action( 'admin_post_dm_register', 'dm_handle_register' );

/** Log in with email or username. */
function dm_handle_login() {
	$to = dm_post( 'redirect_to' );
	if ( ! dm_check_nonce( 'dm_login' ) ) {
		dm_auth_redirect( array( 'dm_err' => 'nonce' ), $to );
	}
	$user = wp_signon(
		array(
			'user_login'    => sanitize_text_field( dm_post( 'dm_login' ) ),
			'user_password' => dm_post( 'dm_pass' ),
			'remember'      => ! empty( $_POST['dm_remember'] ),
		),
		is_ssl()
	);
	if ( is_wp_error( $user ) ) {
		$code = in_array( 'dm_rejected', $user->get_error_codes(), true ) ? 'rejected' : 'login';
		dm_auth_redirect( array( 'dm_err' => $code ), $to );
	}
	$dest = $to ? wp_validate_redirect( $to, home_url( '/my-account/' ) ) : home_url( '/my-account/' );
	wp_safe_redirect( user_can( $user, 'edit_posts' ) ? $dest : home_url( '/my-account/' ) );
	exit;
}
add_action( 'admin_post_nopriv_dm_login', 'dm_handle_login' );
add_action( 'admin_post_dm_login', 'dm_handle_login' );

/** Rejected readers cannot log in. */
function dm_block_rejected( $user ) {
	if ( $user instanceof WP_User && 'rejected' === get_user_meta( $user->ID, 'dm_status', true ) && ! user_can( $user, 'manage_options' ) ) {
		return new WP_Error( 'dm_rejected', 'This account was not approved.' );
	}
	return $user;
}
add_filter( 'wp_authenticate_user', 'dm_block_rejected' );

/** Admin: approve or reject a reader. */
function dm_handle_set_status() {
	$uid    = isset( $_POST['dm_user'] ) ? absint( $_POST['dm_user'] ) : 0;
	$status = isset( $_POST['dm_set'] ) ? sanitize_key( wp_unslash( $_POST['dm_set'] ) ) : '';
	if ( ! $uid || ! current_user_can( 'manage_options' ) || ! dm_check_nonce( 'dm_set_status_' . $uid ) || ! in_array( $status, array( 'approved', 'rejected' ), true ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$u = get_userdata( $uid );
	if ( $u && in_array( 'dm_reader', (array) $u->roles, true ) ) {
		update_user_meta( $uid, 'dm_status', $status );
		if ( 'approved' === $status ) {
			wp_mail( $u->user_email, 'Your account is approved', 'You can now comment, rate and bookmark stories at ' . home_url( '/' ) );
		}
	}
	wp_safe_redirect( add_query_arg( array( 'tab' => 'readers', 'dm_msg' => $status ), home_url( '/my-account/' ) ) );
	exit;
}
add_action( 'admin_post_dm_set_status', 'dm_handle_set_status' );
