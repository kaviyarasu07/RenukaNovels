<?php
/**
 * Comments under a chapter. Expects the chapter as the current post.
 * Top-level comments newest first, replies oldest first, one level deep.
 */
$chapter_id = get_the_ID();
$all        = get_comments( array( 'post_id' => $chapter_id, 'status' => 'approve', 'orderby' => 'comment_date_gmt', 'order' => 'ASC' ) );
$top        = array();
$replies    = array();
foreach ( $all as $c ) {
	if ( (int) $c->comment_parent ) {
		$replies[ (int) $c->comment_parent ][] = $c;
	} else {
		$top[] = $c;
	}
}
$top      = array_reverse( $top );
$status   = dm_user_status();
$is_admin = current_user_can( 'manage_options' );
$msgs     = array(
	'cempty'      => 'Write something before you post.',
	'cspam'       => 'Please remove some links and try again.',
	'crate'       => 'You are commenting very quickly. Please wait a few minutes.',
	'cnotallowed' => 'Your account must be approved before you can comment.',
);
$msg  = isset( $_GET['dm_msg'] ) ? sanitize_key( wp_unslash( $_GET['dm_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$back = get_permalink( $chapter_id ) . '#comments';

/** One comment (used for both levels). */
$render = function ( $c, $is_reply ) use ( $chapter_id, $status, $is_admin, $back ) {
	$own = is_user_logged_in() && (int) $c->user_id === get_current_user_id();
	$ini = function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $c->comment_author, 0, 1 ) ) : strtoupper( substr( $c->comment_author, 0, 1 ) );
	?>
	<div class="rn-cmt<?php echo $is_reply ? ' is-reply' : ''; ?>" id="comment-<?php echo (int) $c->comment_ID; ?>">
		<span class="rn-avatar" aria-hidden="true"><?php echo esc_html( $ini ); ?></span>
		<div class="rn-cbody">
			<div class="rn-chead"><strong><?php echo esc_html( $c->comment_author ); ?></strong><span class="rn-muted"><?php echo esc_html( get_comment_date( 'd-M-y H:i', $c ) ); ?></span></div>
			<p lang="ta"><?php echo nl2br( esc_html( $c->comment_content ) ); ?></p>
			<div class="rn-cact">
				<?php if ( 'approved' === $status ) : ?>
					<details class="rn-replyform">
						<summary>Reply</summary>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="dm_post_comment">
							<input type="hidden" name="dm_chapter" value="<?php echo (int) $chapter_id; ?>">
							<input type="hidden" name="dm_parent" value="<?php echo (int) $c->comment_ID; ?>">
							<?php wp_nonce_field( 'dm_comment_' . $chapter_id ); ?>
							<div class="rn-hp" aria-hidden="true"><input name="dm_website" type="text" tabindex="-1" autocomplete="off"></div>
							<label class="rn-field" for="rep-<?php echo (int) $c->comment_ID; ?>">Your reply<textarea id="rep-<?php echo (int) $c->comment_ID; ?>" name="dm_comment" rows="3" maxlength="1000" lang="ta" required></textarea></label>
							<button class="rn-btn" type="submit" style="min-height:44px">Post reply</button>
						</form>
					</details>
				<?php endif; ?>
				<?php if ( $own || $is_admin ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="rn-inline">
						<input type="hidden" name="action" value="dm_delete_comment">
						<input type="hidden" name="dm_cid" value="<?php echo (int) $c->comment_ID; ?>">
						<input type="hidden" name="dm_back" value="<?php echo esc_url( $back ); ?>">
						<?php wp_nonce_field( 'dm_delcomment_' . $c->comment_ID ); ?>
						<button class="rn-linkbtn" type="submit">Delete</button>
					</form>
				<?php endif; ?>
				<?php if ( $is_admin && (int) $c->user_id && ! user_can( (int) $c->user_id, 'manage_options' ) ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="rn-inline">
						<input type="hidden" name="action" value="dm_block_reader">
						<input type="hidden" name="dm_cid" value="<?php echo (int) $c->comment_ID; ?>">
						<?php wp_nonce_field( 'dm_block_' . $c->comment_ID ); ?>
						<button class="rn-linkbtn" type="submit">Block reader</button>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
};
?>
<section class="rn-comments" id="comments" aria-labelledby="rn-cmt-h">
	<h2 id="rn-cmt-h">Comments (<?php echo (int) count( $all ); ?>)</h2>
	<?php if ( $msg && isset( $msgs[ $msg ] ) ) : ?><div class="rn-note rn-err" role="alert"><?php echo esc_html( $msgs[ $msg ] ); ?></div><?php endif; ?>

	<?php if ( ! $top ) : ?><p class="rn-muted" style="margin:0">No comments yet. Be the first to comment.</p><?php endif; ?>
	<ul class="rn-cmtlist" id="rn-cmtlist">
		<?php
		foreach ( $top as $c ) {
			echo '<li class="rn-group" data-group>';
			$render( $c, false );
			if ( ! empty( $replies[ (int) $c->comment_ID ] ) ) {
				echo '<ul class="rn-replies" data-replies>';
				foreach ( $replies[ (int) $c->comment_ID ] as $r ) {
					echo '<li class="rn-rep">';
					$render( $r, true );
					echo '</li>';
				}
				echo '</ul>';
			}
			echo '</li>';
		}
		?>
	</ul>
	<button type="button" class="rn-more-btn" id="rn-cmt-more" hidden></button>

	<?php if ( 'approved' === $status ) : ?>
		<?php $me = wp_get_current_user(); $me_ini = function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $me->display_name, 0, 1 ) ) : strtoupper( substr( $me->display_name, 0, 1 ) ); ?>
		<form class="rn-composer" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="dm_post_comment">
			<input type="hidden" name="dm_chapter" value="<?php echo (int) $chapter_id; ?>">
			<?php wp_nonce_field( 'dm_comment_' . $chapter_id ); ?>
			<div class="rn-hp" aria-hidden="true"><input name="dm_website" type="text" tabindex="-1" autocomplete="off"></div>
			<span class="rn-avatar" aria-hidden="true"><?php echo esc_html( $me_ini ); ?></span>
			<div class="rn-compbox">
				<label class="screen-reader-text" for="dm_comment">Add a comment</label>
				<textarea id="dm_comment" name="dm_comment" rows="2" maxlength="1000" lang="ta" placeholder="Write a comment..." required data-grow></textarea>
				<div class="rn-compbar">
					<span class="rn-muted" style="font-size:13px"><span data-count>0</span>/1000</span>
					<button class="rn-btn" type="submit">Post</button>
				</div>
			</div>
		</form>
	<?php elseif ( 'pending' === $status ) : ?>
		<div class="rn-note">Your account is waiting for approval by the author. You can comment once it is approved.</div>
	<?php elseif ( ! is_user_logged_in() ) : ?>
		<div class="rn-card rn-loginask"><span>Log in to join the conversation.</span><a class="rn-btn" href="<?php echo esc_url( dm_login_page_url( array( 'redirect_to' => get_permalink( $chapter_id ) ) ) ); ?>">Log in</a></div>
	<?php endif; ?>
</section>
