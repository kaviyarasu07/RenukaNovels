<?php
/** Home page, built from the approved prototype. Rating, author text, notifications and announcements arrive with later modules. */
get_header();
$ongoing   = rn_stories( 'ongoing', 1 );
$completed = rn_stories( 'completed', 1 );
$trending  = rn_trending( 5 );
$latest    = get_posts( array( 'post_type' => 'dm_chapter', 'post_status' => 'publish', 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC' ) );
?>
<div class="rn-wrap">
	<section class="rn-card rn-hero2">
		<div class="rn-herotext">
			<span class="rn-pill">Author Renuga Muthukumar</span>
			<h1>Stories that bloom, like a secret garden</h1>
			<p style="margin:0;font-size:20px;font-weight:600">Stories</p>
			<div style="display:flex;gap:12px;flex-wrap:wrap">
				<?php if ( $ongoing ) : ?><a class="rn-btn" href="<?php echo esc_url( get_permalink( $ongoing[0] ) ); ?>">Begin Reading</a><?php endif; ?>
				<a class="rn-btn ghost" href="#trending">Browse Library</a>
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
				<?php foreach ( $trending as $i => $s ) : $ch = dm_latest_chapter( $s->ID ); ?>
					<a class="rn-trend" href="<?php echo esc_url( get_permalink( $s ) ); ?>"<?php echo $i >= 3 ? ' data-more hidden' : ''; ?>>
						<span class="rn-rank"><?php echo (int) ( $i + 1 ); ?></span>
						<span class="rn-tcover"><?php echo rn_cover( $s ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span class="rn-ttext"><strong lang="ta"><?php echo esc_html( get_the_title( $s ) ); ?></strong><span class="rn-muted" style="font-size:13px"><?php echo 'completed' === dm_story_status( $s->ID ) ? 'Completed' : 'Ongoing'; ?><?php echo $ch ? ' · Chapter ' . (int) get_post_meta( $ch->ID, 'dm_chapter_no', true ) : ''; ?></span></span>
						<span class="rn-rd">Read</span>
					</a>
				<?php endforeach; ?>
				<?php if ( ! $trending ) : ?><p class="rn-muted">No stories yet.</p><?php endif; ?>
				<?php if ( count( $trending ) > 3 ) : ?><button type="button" class="rn-trend-more" id="rn-trend-more" aria-expanded="false">See more trending novels</button><?php endif; ?>
			</div>
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
			<?php foreach ( $latest as $c ) : $st = get_post( $c->post_parent ); ?>
				<a class="rn-upd" href="<?php echo esc_url( get_permalink( $c ) ); ?>">
					<span class="rn-muted" style="font-size:13px"><?php echo esc_html( get_the_date( 'd-M-y H:i', $c ) ); ?></span>
					<span lang="ta" style="flex:1;min-width:0;font-weight:600"><?php echo esc_html( ( $st ? get_the_title( $st ) . ': ' : '' ) . get_the_title( $c ) ); ?></span>
				</a>
			<?php endforeach; ?>
			<?php if ( ! $latest ) : ?><p class="rn-muted" style="padding:18px;margin:0">No updates yet.</p><?php endif; ?>
		</div>
	</section>
</div>
<?php
get_footer();
