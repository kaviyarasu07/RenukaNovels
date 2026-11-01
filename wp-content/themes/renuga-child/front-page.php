<?php
/** Home page, built from the approved prototype. Rating, author text, notifications and announcements arrive with later modules. */
get_header();
$ongoing   = rn_stories( 'ongoing', 1 );
$completed = rn_stories( 'completed', 1 );
$trending  = rn_trending( 3 );
$latest    = rn_updates( 6 );
?>
<div class="rn-wrap">
	<section class="rn-card rn-hero2">
		<div class="rn-herotext">
			<span class="rn-pill">Author Renuga Muthukumar</span>
			<h1>Stories that bloom, like a secret garden</h1>
			<p style="margin:0;font-size:20px;font-weight:600">Stories</p>
			<div style="display:flex;gap:12px;flex-wrap:wrap">
				<?php if ( $ongoing ) : ?><a class="rn-btn" href="<?php echo esc_url( get_permalink( $ongoing[0] ) ); ?>">Begin Reading</a><?php endif; ?>
				<a class="rn-btn ghost" href="<?php echo esc_url( home_url( '/library/' ) ); ?>">Browse Library</a>
			</div>
			<div class="rn-stats">
				<div><b><?php echo (int) rn_count( 'dm_story' ); ?></b>Stories</div>
				<div><b><?php echo (int) rn_count( 'dm_chapter' ); ?></b>Chapters</div>
			</div>
		</div>
		<div class="rn-logohero"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/logo.webp' ); ?>" width="300" height="300" alt="Renuga Muthukumar Novels logo: a green parrot writing with a quill on an open book"></div>
	</section>

	<div class="rn-twocol">
		<section class="rn-sec" id="trending">
			<div><h2>Trending This Week</h2><div class="rn-muted">What readers are opening most</div></div>
			<div class="rn-card" style="padding:4px 18px">
				<?php foreach ( $trending as $i => $s ) { echo rn_trend_row( $s, $i + 1 ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( ! $trending ) : ?><p class="rn-muted">No stories yet.</p><?php endif; ?>
			</div>
			<div class="rn-more"><a class="rn-btn ghost" href="<?php echo esc_url( home_url( '/trending/' ) ); ?>">View More</a></div>
		</section>
		<aside class="rn-aside">
			<div class="rn-card" style="display:flex;flex-direction:column;gap:10px">
				<strong style="font-size:19px">About the Author</strong>
				<span class="rn-muted">Renuga</span>
				<p class="rn-muted" style="margin:0">[Short author introduction goes here]</p>
			</div>
		</aside>
	</div>

	<section class="rn-sec" id="ongoing">
		<div><h2>Ongoing Stories</h2><div class="rn-muted">New chapters, every week</div></div>
		<div class="rn-grid">
			<?php foreach ( $ongoing as $s ) { echo rn_story_card( $s, 'feat' ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! $ongoing ) : ?><p class="rn-muted">No ongoing story yet.</p><?php endif; ?>
		</div>
		<div class="rn-more"><a class="rn-btn ghost" href="<?php echo esc_url( home_url( '/ongoing/' ) ); ?>">View More</a></div>
	</section>

	<section class="rn-sec" id="completed">
		<div><h2>Completed Novels</h2><div class="rn-muted">Finished, from first page to last</div></div>
		<div class="rn-grid">
			<?php foreach ( $completed as $s ) { echo rn_story_card( $s, 'row' ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! $completed ) : ?><p class="rn-muted">No completed novel yet.</p><?php endif; ?>
		</div>
		<div class="rn-more"><a class="rn-btn ghost" href="<?php echo esc_url( home_url( '/completed/' ) ); ?>">View More</a></div>
	</section>

	<section class="rn-sec" id="updates">
		<div><h2>Latest Posts &amp; Updates</h2><div class="rn-muted">New chapters and short notes, fresh from the writing desk</div></div>
		<div class="rn-card" style="padding:0;overflow:hidden">
			<?php foreach ( $latest as $it ) { echo rn_update_row( $it ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! $latest ) : ?><p class="rn-muted" style="padding:18px;margin:0">No updates yet.</p><?php endif; ?>
		</div>
		<div class="rn-more"><a class="rn-btn ghost" href="<?php echo esc_url( home_url( '/updates/' ) ); ?>">View More</a></div>
	</section>
</div>
<?php
get_footer();
