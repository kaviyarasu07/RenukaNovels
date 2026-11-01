<?php
/**
 * Stories and chapters.
 *
 * dm_story   : one novel. Cover = featured image, short teaser = excerpt.
 * dm_chapter : one chapter. Hierarchical so the "Parent" box picks its story.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_register_post_types() {
	register_post_type(
		'dm_story',
		array(
			'labels'       => array(
				'name'          => 'Stories',
				'singular_name' => 'Story',
				'add_new_item'  => 'Add new story',
				'edit_item'     => 'Edit story',
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-book-alt',
			'menu_position'=> 5,
			'rewrite'      => array( 'slug' => 'story' ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		)
	);

	register_post_type(
		'dm_chapter',
		array(
			'labels'       => array(
				'name'          => 'Chapters',
				'singular_name' => 'Chapter',
				'add_new_item'  => 'Add new chapter',
				'edit_item'     => 'Edit chapter',
			),
			'public'       => true,
			'hierarchical' => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-media-text',
			'menu_position'=> 6,
			'rewrite'      => array( 'slug' => 'chapter' ),
			'supports'     => array( 'title', 'editor', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'dm_register_post_types' );

/** Story status box: Ongoing or Completed. */
function dm_add_story_meta_box() {
	add_meta_box( 'dm_story_status', 'Story status', 'dm_story_status_box', 'dm_story', 'side', 'high' );
	add_meta_box( 'dm_chapter_info', 'Chapter details', 'dm_chapter_info_box', 'dm_chapter', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'dm_add_story_meta_box' );

function dm_story_status_box( $post ) {
	$status = get_post_meta( $post->ID, 'dm_status', true );
	$status = 'completed' === $status ? 'completed' : 'ongoing';
	wp_nonce_field( 'dm_save_story', 'dm_story_nonce' );
	echo '<label><input type="radio" name="dm_status" value="ongoing"' . checked( $status, 'ongoing', false ) . '> Ongoing</label><br>';
	echo '<label><input type="radio" name="dm_status" value="completed"' . checked( $status, 'completed', false ) . '> Completed</label>';
	echo '<p class="description">The short teaser is the "Excerpt" box below the editor.</p>';
}

function dm_chapter_info_box( $post ) {
	$num = (int) get_post_meta( $post->ID, 'dm_chapter_no', true );
	wp_nonce_field( 'dm_save_chapter', 'dm_chapter_nonce' );
	echo '<p><label for="dm_chapter_no">Chapter number</label><br>';
	echo '<input type="number" min="1" id="dm_chapter_no" name="dm_chapter_no" value="' . esc_attr( $num ? $num : '' ) . '" style="width:100%"></p>';
	echo '<p class="description">Choose the story in the "Parent" box (Page Attributes).</p>';
}

function dm_save_story_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['dm_story_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dm_story_nonce'] ) ), 'dm_save_story' ) ) {
		$status = isset( $_POST['dm_status'] ) && 'completed' === $_POST['dm_status'] ? 'completed' : 'ongoing';
		update_post_meta( $post_id, 'dm_status', $status );
	}
	if ( isset( $_POST['dm_chapter_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dm_chapter_nonce'] ) ), 'dm_save_chapter' ) ) {
		update_post_meta( $post_id, 'dm_chapter_no', isset( $_POST['dm_chapter_no'] ) ? absint( $_POST['dm_chapter_no'] ) : 0 );
	}
}
add_action( 'save_post', 'dm_save_story_meta' );

/** Helpers used by the theme. */
function dm_story_status( $story_id ) {
	return 'completed' === get_post_meta( $story_id, 'dm_status', true ) ? 'completed' : 'ongoing';
}

function dm_story_chapters( $story_id ) {
	return get_posts(
		array(
			'post_type'      => 'dm_chapter',
			'post_parent'    => $story_id,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => 'dm_chapter_no',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		)
	);
}

function dm_latest_chapter( $story_id ) {
	$list = dm_story_chapters( $story_id );
	return $list ? end( $list ) : null;
}

/**
 * Chapter addresses: /chapter/{id}/.
 * The default hierarchical address would include the parent story's name, which
 * WordPress cannot resolve because the parent is a different post type.
 */
function dm_chapter_permalink( $link, $post ) {
	if ( $post instanceof WP_Post && 'dm_chapter' === $post->post_type && 'publish' === $post->post_status ) {
		return home_url( '/chapter/' . $post->ID . '/' );
	}
	return $link;
}
add_filter( 'post_type_link', 'dm_chapter_permalink', 10, 2 );

function dm_chapter_rewrite() {
	add_rewrite_rule( '^chapter/([0-9]+)/?$', 'index.php?post_type=dm_chapter&p=$matches[1]', 'top' );
}
add_action( 'init', 'dm_chapter_rewrite' );

/** Refresh the address rules automatically whenever the plugin version changes. */
function dm_maybe_flush_rewrites() {
	if ( get_option( 'dm_rewrite_version' ) !== DM_CORE_VERSION ) {
		flush_rewrite_rules();
		update_option( 'dm_rewrite_version', DM_CORE_VERSION );
	}
}
add_action( 'init', 'dm_maybe_flush_rewrites', 99 );
