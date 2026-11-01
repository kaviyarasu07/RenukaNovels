<?php
/**
 * Comments on chapters: approved readers only, one level of replies, delete by author or admin.
 * Posted through admin-post.php so the theme controls the whole layout.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_comment_back( $chapter_id, $anchor = 'comments', $msg = '' ) {
	$url = get_permalink( $chapter_id );
	if ( $msg ) {
		$url = add_query_arg( 'dm_msg', $msg, $url );
	}
	wp_safe_redirect( $url . '#' . $anchor );
	exit;
}

function dm_handle_post_comment() {
	$chapter = isset( $_POST['dm_chapter'] ) ? absint( $_POST['dm_chapter'] ) : 0;
	if ( ! $chapter || 'dm_chapter' !== get_post_type( $chapter ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'dm_comment_' . $chapter ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	if ( ! dm_can_engage() ) {
		dm_comment_back( $chapter, 'comments', 'cnotallowed' );
	}
	if ( '' !== dm_post( 'dm_website' ) ) { // honeypot.
		dm_comment_back( $chapter );
	}
	$text = trim( sanitize_textarea_field( dm_post( 'dm_comment' ) ) );
	$text = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 1000 ) : substr( $text, 0, 1000 );
	if ( '' === $text ) {
		dm_comment_back( $chapter, 'comments', 'cempty' );
	}
	if ( preg_match_all( '#https?://|www\.#i', $text ) > 2 ) { // too many links looks like spam.
		dm_comment_back( $chapter, 'comments', 'cspam' );
	}
	$uid = get_current_user_id();
	$key = 'dm_cmt_' . $uid;
	$cnt = (int) get_transient( $key );
	if ( $cnt >= 5 ) { // 5 comments per 10 minutes.
		dm_comment_back( $chapter, 'comments', 'crate' );
	}
	set_transient( $key, $cnt + 1, 10 * MINUTE_IN_SECONDS );

	$parent = isset( $_POST['dm_parent'] ) ? absint( $_POST['dm_parent'] ) : 0;
	if ( $parent ) {
		$p = get_comment( $parent );
		if ( ! $p || (int) $p->comment_post_ID !== $chapter ) {
			$parent = 0;
		} elseif ( (int) $p->comment_parent ) {
			$parent = (int) $p->comment_parent; // replies stay one level deep.
		}
	}
	$user = wp_get_current_user();
	$id   = wp_insert_comment(
		array(
			'comment_post_ID'      => $chapter,
			'comment_parent'       => $parent,
			'user_id'              => $uid,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_content'      => $text,
			'comment_approved'     => 1,
			'comment_type'         => 'comment',
		)
	);
	dm_comment_back( $chapter, $id ? 'comment-' . $id : 'comments' );
}
add_action( 'admin_post_dm_post_comment', 'dm_handle_post_comment' );

function dm_handle_delete_comment() {
	$cid = isset( $_POST['dm_cid'] ) ? absint( $_POST['dm_cid'] ) : 0;
	$c   = $cid ? get_comment( $cid ) : null;
	if ( ! $c || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'dm_delcomment_' . $cid ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	if ( current_user_can( 'manage_options' ) || ( is_user_logged_in() && (int) $c->user_id === get_current_user_id() ) ) {
		wp_trash_comment( $cid );
	}
	$back = isset( $_POST['dm_back'] ) ? esc_url_raw( wp_unslash( $_POST['dm_back'] ) ) : '';
	wp_safe_redirect( $back ? wp_validate_redirect( $back, home_url( '/' ) ) : get_permalink( (int) $c->comment_post_ID ) . '#comments' );
	exit;
}
add_action( 'admin_post_dm_delete_comment', 'dm_handle_delete_comment' );

/** Admin: block the reader who wrote a comment (they can no longer log in) and remove that comment. */
function dm_handle_block_reader() {
	$cid = isset( $_POST['dm_cid'] ) ? absint( $_POST['dm_cid'] ) : 0;
	$c   = $cid ? get_comment( $cid ) : null;
	if ( ! $c || ! current_user_can( 'manage_options' ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'dm_block_' . $cid ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$author = (int) $c->user_id;
	if ( $author && ! user_can( $author, 'manage_options' ) ) {
		update_user_meta( $author, 'dm_status', 'rejected' );
		foreach ( get_comments( array( 'user_id' => $author, 'status' => 'approve' ) ) as $x ) {
			wp_trash_comment( $x->comment_ID );
		}
	}
	wp_safe_redirect( get_permalink( (int) $c->comment_post_ID ) . '#comments' );
	exit;
}
add_action( 'admin_post_dm_block_reader', 'dm_handle_block_reader' );
