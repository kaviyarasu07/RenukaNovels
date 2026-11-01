<?php
/** My Account (WordPress page with slug "my-account"). Readers: reading, bookmarks, comments, highlights. Admin: also announcements. */
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( dm_login_page_url() );
	exit;
}
get_header();
$user     = wp_get_current_user();
$is_admin = current_user_can( 'manage_options' );
$tabs     = array();
$pending_n = $is_admin ? count( rn_pending_readers() ) : 0;
if ( $is_admin ) {
	$tabs['announcements'] = 'Announcements';
	$tabs['readers']       = 'Reader approvals' . ( $pending_n ? ' (' . $pending_n . ')' : '' );
}
$tabs['reading']    = 'Reading';
$tabs['bookmarks']  = 'Bookmarks';
$tabs['comments']   = 'Comments';
$tabs['highlights'] = 'Highlights';
$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( ! isset( $tabs[ $tab ] ) ) {
	$tab = $is_admin ? 'announcements' : 'reading';
}
$msg = isset( $_GET['dm_msg'] ) ? sanitize_key( wp_unslash( $_GET['dm_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$notes = array(
	'posted'  => 'Announcement published. Readers can now see it in Latest Posts & Updates.',
	'deleted' => 'Announcement removed.',
	'empty'   => 'Write the announcement first.',
	'approved' => 'Reader approved. They can now comment, rate and bookmark.',
	'rejected' => 'Reader rejected. They can no longer log in.',
	'welcome'  => 'Welcome! Your account is waiting for approval. You can read every story now. Commenting, rating and bookmarking open once the author approves you.',
);
$my_status = dm_user_status();
?>
<div class="rn-wrap">
	<section class="rn-sec">
		<div class="rn-acchead">
			<div>
				<h1 class="rn-ptitle">My Account</h1>
				<div class="rn-muted">Hello, <?php echo esc_html( $user->display_name ); ?> · <?php echo $is_admin ? 'Admin' : 'Reader'; ?></div>
			</div>
			<a class="rn-btn ghost" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
		</div>
		<nav class="rn-filters" aria-label="Account sections">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<a href="<?php echo esc_url( rn_account_url( $key ) ); ?>"<?php echo $tab === $key ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php if ( 'pending' === $my_status && 'welcome' !== $msg ) : ?><div class="rn-note" role="status">Your account is waiting for approval by the author. You can read every story meanwhile.</div><?php endif; ?>
		<?php if ( 'rejected' === $my_status ) : ?><div class="rn-note" role="status">Your account was not approved.</div><?php endif; ?>
		<?php if ( $msg && isset( $notes[ $msg ] ) ) : ?><div class="rn-note" role="status"><?php echo esc_html( $notes[ $msg ] ); ?></div><?php endif; ?>
	</section>

	<?php if ( 'announcements' === $tab && $is_admin ) : ?>
		<section class="rn-sec">
			<div class="rn-card rn-compose">
				<h2>Post an announcement</h2>
				<p class="rn-muted" style="margin:0">A short note for readers, for example when a chapter is late or a new chapter is coming. It appears in Latest Posts &amp; Updates on the home page.</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="dm_post_announcement">
					<?php wp_nonce_field( 'dm_post_announcement' ); ?>
					<label class="rn-field" for="dm_text">Announcement for readers
						<textarea id="dm_text" name="dm_text" rows="4" maxlength="500" lang="ta" placeholder="Write in Tamil or English"></textarea>
					</label>
					<div style="display:flex;gap:8px;flex-wrap:wrap;margin:12px 0">
						<button type="button" class="rn-chip" data-fill="அத்தியாயம் வெளியாவதில் சிறிய தாமதம் ஏற்பட்டுள்ளது. விரைவில் வெளியிடுகிறேன். பொறுமைக்கு நன்றி!">Episode delayed</button>
						<button type="button" class="rn-chip" data-fill="புதிய அத்தியாயம் இன்னும் சிறிது நேரத்தில் பதிவேற்றப்படும். காத்திருந்தமைக்கு நன்றி!">New episode soon</button>
					</div>
					<label class="rn-check"><input type="checkbox" name="dm_pinned" value="1"> Pin to the top of the list</label>
					<div style="margin-top:14px"><button type="submit" class="rn-btn">Publish announcement</button></div>
				</form>
			</div>
			<h2>Published announcements</h2>
			<div class="rn-card" style="padding:0;overflow:hidden">
				<?php
				$anns = get_posts( array( 'post_type' => 'dm_announcement', 'post_status' => 'publish', 'posts_per_page' => 20, 'orderby' => 'date', 'order' => 'DESC' ) );
				foreach ( $anns as $a ) :
					?>
					<div class="rn-upd rn-upd-note">
						<span class="rn-muted" style="font-size:13px"><?php echo esc_html( get_the_date( 'd-M-y H:i', $a ) ); ?></span>
						<span lang="ta" style="flex:1;min-width:0"><?php echo get_post_meta( $a->ID, 'dm_pinned', true ) ? '<span class="rn-pill teal">Pinned</span> ' : ''; ?><?php echo nl2br( esc_html( $a->post_content ) ); ?></span>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="dm_delete_announcement">
							<input type="hidden" name="dm_id" value="<?php echo (int) $a->ID; ?>">
							<?php wp_nonce_field( 'dm_delete_announcement_' . $a->ID ); ?>
							<button type="submit" class="rn-linkbtn">Remove</button>
						</form>
					</div>
				<?php endforeach; ?>
				<?php if ( ! $anns ) : ?><p class="rn-muted" style="padding:18px;margin:0">No announcements yet.</p><?php endif; ?>
			</div>
		</section>

	<?php elseif ( 'readers' === $tab && $is_admin ) : ?>
		<section class="rn-sec">
			<div><h2>Reader approvals</h2><div class="rn-muted">Approve each new reader once. After that they can comment, rate and bookmark without further approval.</div></div>
			<?php
			$readers = get_users( array( 'role' => 'dm_reader', 'orderby' => 'registered', 'order' => 'DESC', 'number' => 100 ) );
			?>
			<div class="rn-card" style="padding:0;overflow:hidden">
				<?php foreach ( $readers as $r ) : $st = dm_user_status( $r->ID ); ?>
					<div class="rn-upd rn-reader">
						<span style="flex:1;min-width:200px;display:flex;flex-direction:column">
							<strong><?php echo esc_html( $r->display_name ); ?></strong>
							<span class="rn-muted" style="font-size:14px"><?php echo esc_html( $r->user_email ); ?> · joined <?php echo esc_html( wp_date( 'd-M-y', strtotime( $r->user_registered ) ) ); ?></span>
						</span>
						<span class="rn-pill <?php echo 'approved' === $st ? 'teal' : ( 'pending' === $st ? 'solid' : '' ); ?>"><?php echo esc_html( ucfirst( $st ) ); ?></span>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:6px">
							<input type="hidden" name="action" value="dm_set_status">
							<input type="hidden" name="dm_user" value="<?php echo (int) $r->ID; ?>">
							<?php wp_nonce_field( 'dm_set_status_' . $r->ID ); ?>
							<?php if ( 'approved' !== $st ) : ?><button class="rn-btn" style="min-height:44px;padding:0 18px" name="dm_set" value="approved">Approve</button><?php endif; ?>
							<?php if ( 'rejected' !== $st ) : ?><button class="rn-btn ghost" style="min-height:44px;padding:0 18px" name="dm_set" value="rejected">Reject</button><?php endif; ?>
						</form>
					</div>
				<?php endforeach; ?>
				<?php if ( ! $readers ) : ?><p class="rn-muted" style="padding:18px;margin:0">No readers have registered yet.</p><?php endif; ?>
			</div>
		</section>

	<?php elseif ( 'reading' === $tab ) : ?>
		<section class="rn-sec" id="rn-continue" aria-labelledby="rn-cont-h" hidden>
			<div><h2 id="rn-cont-h">Continue Where You Left Off</h2><div class="rn-muted">Open your story at the exact paragraph where you stopped</div></div>
			<div class="rn-contlist" id="rn-contlist"></div>
		</section>
		<p class="rn-muted" id="rn-cont-empty" hidden>You have not started a story on this device yet. Open any chapter and it will appear here.</p>

	<?php else : ?>
		<section class="rn-sec">
			<div class="rn-card"><p class="rn-muted" style="margin:0">Your <?php echo esc_html( strtolower( $tabs[ $tab ] ) ); ?> will be listed here. This part is being built next.</p></div>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();
