<?php
/** A story page: cover, teaser and the list of chapters. */
get_header();
the_post();
$chapters = dm_story_chapters( get_the_ID() );
?>
<div class="rn-wrap">
	<article class="rn-read" style="padding-bottom:0">
		<div class="rn-card" style="display:flex;flex-direction:column;gap:12px">
			<span class="rn-pill <?php echo 'completed' === dm_story_status( get_the_ID() ) ? 'teal' : 'solid'; ?>" style="align-self:flex-start"><?php echo 'completed' === dm_story_status( get_the_ID() ) ? 'Completed' : 'Ongoing'; ?></span>
			<h1 lang="ta" style="font-size:30px"><?php the_title(); ?></h1>
			<div lang="ta"><?php the_content(); ?></div>
		</div>
		<?php
		$sid                = get_the_ID();
		list( $avg, $cnt )  = dm_rating_summary( $sid );
		$mine               = dm_user_rating( $sid );
		$st                 = dm_user_status();
		$rmsg               = isset( $_GET['dm_msg'] ) && 'rated' === $_GET['dm_msg']; // phpcs:ignore WordPress.Security.NonceVerification
		?>
		<section class="rn-card rn-rate" id="rate" aria-labelledby="rn-rate-h">
			<h2 id="rn-rate-h">Rate this story</h2>
			<div class="rn-ratesum"><?php echo $cnt ? '<span class="rn-star" aria-hidden="true">&#9733;</span> <strong>' . esc_html( number_format_i18n( $avg, 1 ) ) . '</strong> <span class="rn-muted">(' . (int) $cnt . ' rating' . ( 1 === $cnt ? '' : 's' ) . ')</span>' : '<span class="rn-muted">No ratings yet.</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php if ( 'approved' === $st ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="rn-stars">
					<input type="hidden" name="action" value="dm_rate">
					<input type="hidden" name="dm_story" value="<?php echo (int) $sid; ?>">
					<?php wp_nonce_field( 'dm_rate_' . $sid ); ?>
					<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
						<button type="submit" name="dm_stars" value="<?php echo (int) $i; ?>" class="rn-starbtn<?php echo $i <= $mine ? ' on' : ''; ?>" aria-label="Rate <?php echo (int) $i; ?> star<?php echo 1 === $i ? '' : 's'; ?>">&#9733;</button>
					<?php endfor; ?>
				</form>
				<p class="rn-muted" style="margin:0;font-size:14px"><?php echo $rmsg ? 'Thanks! Your rating is saved.' : ( $mine ? 'Your rating: ' . (int) $mine . ' of 5. Tap a star to change it.' : 'Tap a star to rate.' ); ?></p>
			<?php elseif ( 'pending' === $st ) : ?>
				<p class="rn-muted" style="margin:0">Your account is waiting for approval. You can rate once it is approved.</p>
			<?php elseif ( ! is_user_logged_in() ) : ?>
				<p style="margin:0"><a class="rn-btn" href="<?php echo esc_url( dm_login_page_url( array( 'redirect_to' => get_permalink() ) ) ); ?>">Log in to rate</a></p>
			<?php endif; ?>
		</section>
		<h2>Chapters</h2>
		<div class="rn-chlist">
			<?php foreach ( $chapters as $c ) : ?>
				<a href="<?php echo esc_url( get_permalink( $c ) ); ?>"><span lang="ta"><?php echo esc_html( get_the_title( $c ) ); ?></span><span class="rn-muted">Chapter <?php echo (int) get_post_meta( $c->ID, 'dm_chapter_no', true ); ?></span></a>
			<?php endforeach; ?>
			<?php if ( ! $chapters ) : ?><p class="rn-muted">No chapter has been published yet.</p><?php endif; ?>
		</div>
	</article>
</div>
<?php
get_footer();
