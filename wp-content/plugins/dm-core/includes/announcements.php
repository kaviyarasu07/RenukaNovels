<?php
/**
 * Announcements: short notices from the author ("Chapter 13 will be late", "New chapter soon").
 * Posted from the front-end My Account page by an admin, also manageable under Announcements in wp-admin.
 * They appear in Latest Posts & Updates, pinned ones first.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_register_announcements() {
	register_post_type(
		'dm_announcement',
		array(
			'labels'              => array( 'name' => 'Announcements', 'singular_name' => 'Announcement', 'add_new_item' => 'Add announcement' ),
			'public'              => false,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-megaphone',
			'menu_position'       => 7,
			'supports'            => array( 'title', 'editor' ),
		)
	);
}
add_action( 'init', 'dm_register_announcements' );

function dm_announcement_redirect( $args = array() ) {
	wp_safe_redirect( add_query_arg( array_merge( array( 'tab' => 'announcements' ), $args ), home_url( '/my-account/' ) ) );
	exit;
}

function dm_handle_post_announcement() {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'dm_post_announcement' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$text = isset( $_POST['dm_text'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['dm_text'] ) ) ) : '';
	$text = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 500 ) : substr( $text, 0, 500 );
	if ( '' === $text ) {
		dm_announcement_redirect( array( 'dm_msg' => 'empty' ) );
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'dm_announcement',
			'post_status'  => 'publish',
			'post_title'   => function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 60 ) : substr( $text, 0, 60 ),
			'post_content' => $text,
		)
	);
	if ( $id && ! is_wp_error( $id ) && ! empty( $_POST['dm_pinned'] ) ) {
		update_post_meta( $id, 'dm_pinned', 1 );
	}
	dm_announcement_redirect( array( 'dm_msg' => 'posted' ) );
}
add_action( 'admin_post_dm_post_announcement', 'dm_handle_post_announcement' );

function dm_handle_delete_announcement() {
	$id = isset( $_POST['dm_id'] ) ? absint( $_POST['dm_id'] ) : 0;
	if ( ! $id || ! current_user_can( 'manage_options' ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'dm_delete_announcement_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	if ( 'dm_announcement' === get_post_type( $id ) ) {
		wp_trash_post( $id );
	}
	dm_announcement_redirect( array( 'dm_msg' => 'deleted' ) );
}
add_action( 'admin_post_dm_delete_announcement', 'dm_handle_delete_announcement' );
