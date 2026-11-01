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
