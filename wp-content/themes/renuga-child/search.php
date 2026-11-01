<?php
/** Search results (stories). */
get_header();
?>
<div class="rn-wrap">
	<section class="rn-sec"><div><h1 class="rn-ptitle">Search</h1><div class="rn-muted">Results for &ldquo;<?php echo esc_html( get_search_query() ); ?>&rdquo;</div></div></section>
	<div class="rn-grid cols">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				if ( 'dm_story' === get_post_type() ) {
					echo rn_story_card( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
			}
		} else {
			echo '<p class="rn-muted">No story matches your search.</p>';
		}
		?>
	</div>
</div>
<?php
get_footer();
