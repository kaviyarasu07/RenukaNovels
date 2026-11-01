<?php
/** Updates page: new chapters and announcements, 20 per page (WordPress page with slug "updates"). */
get_header();
$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$all   = rn_updates( -1 );
$pages = max( 1, (int) ceil( count( $all ) / 20 ) );
$paged = min( $paged, $pages );
$rows  = array_slice( $all, ( $paged - 1 ) * 20, 20 );
$links = paginate_links(
	array(
		'base'      => trailingslashit( get_permalink() ) . 'page/%#%/',
		'format'    => '',
		'current'   => $paged,
		'total'     => $pages,
		'mid_size'  => 1,
		'prev_text' => '&larr; Previous',
		'next_text' => 'Next &rarr;',
	)
);
?>
<div class="rn-wrap">
	<section class="rn-sec">
		<div><h1 class="rn-ptitle">Latest Posts &amp; Updates</h1><div class="rn-muted">New chapters and announcements, newest first</div></div>
		<div class="rn-card" style="padding:0;overflow:hidden;max-width:920px">
			<?php foreach ( $rows as $it ) { echo rn_update_row( $it ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! $rows ) : ?><p class="rn-muted" style="padding:18px;margin:0">No updates yet.</p><?php endif; ?>
		</div>
		<?php if ( $links && $pages > 1 ) : ?><nav class="rn-pager" aria-label="Pages"><?php echo $links; // phpcs:ignore WordPress.Security.EscapeOutput ?></nav><?php endif; ?>
	</section>
</div>
<?php
get_footer();
