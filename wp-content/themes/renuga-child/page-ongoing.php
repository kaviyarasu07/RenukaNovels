<?php
/** Ongoing Stories page (WordPress page with slug "ongoing"). "Continue Where You Left Off" is added with the bookmarks module. */
get_header();
$stories = rn_stories( 'ongoing' );
?>
<div class="rn-wrap">
	<section class="rn-sec"><div><h1 class="rn-ptitle">Ongoing Stories</h1><div class="rn-muted">New chapters, every week</div></div></section>
	<section class="rn-sec" aria-labelledby="all-on">
		<h2 id="all-on">All Ongoing Stories</h2>
		<div class="rn-grid cols">
			<?php foreach ( $stories as $s ) { echo rn_story_card( $s ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! $stories ) : ?><p class="rn-muted">No ongoing story yet.</p><?php endif; ?>
		</div>
	</section>
</div>
<?php
get_footer();
