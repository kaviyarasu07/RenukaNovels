<?php
/** Reading page: the chapter text and the reading controls fixed at the bottom. */
get_header();
the_post();
$story    = get_post( wp_get_post_parent_id( get_the_ID() ) );
$chapters = $story ? dm_story_chapters( $story->ID ) : array();
$ids      = wp_list_pluck( $chapters, 'ID' );
$pos      = array_search( get_the_ID(), $ids, true );
$prev     = ( false !== $pos && $pos > 0 ) ? $chapters[ $pos - 1 ] : null;
$next     = ( false !== $pos && $pos < count( $chapters ) - 1 ) ? $chapters[ $pos + 1 ] : null;
?>
<div class="rn-wrap">
	<div class="rn-read">
		<div>
			<?php if ( $story ) : ?><a href="<?php echo esc_url( get_permalink( $story ) ); ?>" lang="ta">&larr; <?php echo esc_html( get_the_title( $story ) ); ?></a><?php endif; ?>
			<h1 lang="ta" style="font-size:28px;margin-top:8px"><?php the_title(); ?></h1>
		</div>
		<div class="rn-article" id="rn-article" lang="ta"><?php the_content(); ?></div>
		<nav class="rn-chnav" aria-label="Chapters">
			<?php if ( $prev ) : ?><a class="rn-btn ghost" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">&larr; Previous</a><?php else : ?><span></span><?php endif; ?>
			<?php if ( $next ) : ?><a class="rn-btn" href="<?php echo esc_url( get_permalink( $next ) ); ?>">Next chapter &rarr;</a><?php endif; ?>
		</nav>
	</div>
</div>
<div class="rn-tools" role="toolbar" aria-label="Reading controls">
	<button type="button" id="rn-smaller" aria-label="Smaller text">A&minus;</button>
	<output id="rn-size">18px</output>
	<button type="button" id="rn-larger" aria-label="Larger text">A+</button>
	<button type="button" id="rn-mode" aria-label="Day or night mode">Day/Night</button>
</div>
<?php
get_footer();
