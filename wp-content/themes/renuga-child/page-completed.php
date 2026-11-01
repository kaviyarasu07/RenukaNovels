<?php
/** Completed Stories page (WordPress page with slug "completed"). */
get_header();
$stories = rn_stories( 'completed' );
?>
<div class="rn-wrap">
	<section class="rn-sec"><div><h1 class="rn-ptitle">Completed Stories</h1><div class="rn-muted">Finished, from first page to last</div></div></section>
	<section class="rn-sec">
		<div class="rn-grid cols">
			<?php foreach ( $stories as $s ) { echo rn_story_card( $s ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! $stories ) : ?><p class="rn-muted">No completed novel yet.</p><?php endif; ?>
		</div>
	</section>
</div>
<?php
get_footer();
