<?php
/** Trending page: every novel, most viewed first (WordPress page with slug "trending"). */
get_header();
$stories = rn_trending( -1 );
?>
<div class="rn-wrap">
	<section class="rn-sec">
		<div><h1 class="rn-ptitle">Trending This Week</h1><div class="rn-muted">Novels ranked by number of views, most read first</div></div>
		<div class="rn-card" style="padding:4px 18px;max-width:920px">
			<?php foreach ( $stories as $i => $s ) { echo rn_trend_row( $s, $i + 1 ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! $stories ) : ?><p class="rn-muted">No novels yet.</p><?php endif; ?>
		</div>
	</section>
</div>
<?php
get_footer();
