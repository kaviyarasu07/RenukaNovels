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
<?php
$rn_counts = dm_comment_counts_all();
$rn_can    = dm_can_engage();
$rn_st     = dm_user_status();
$rn_ro     = dm_reaction_options();
$rn_rc     = dm_reaction_counts( get_the_ID() );
$rn_mine   = $rn_can ? dm_user_reaction( get_the_ID() ) : '';
$rn_no     = (int) get_post_meta( get_the_ID(), 'dm_chapter_no', true );
?>
<div class="rn-wrap">
	<div class="rn-readgrid">
		<div class="rn-rhead">
			<?php if ( $story ) : ?><a href="<?php echo esc_url( get_permalink( $story ) ); ?>" lang="ta">&larr; <?php echo esc_html( get_the_title( $story ) ); ?></a><?php endif; ?>
			<h1 lang="ta" style="font-size:28px;margin-top:8px"><?php the_title(); ?></h1>
		</div>
		<?php
		$rn_cover = $story ? get_the_post_thumbnail_url( $story, 'medium' ) : '';
		?>
		<div class="rn-article" id="rn-article" lang="ta"
			data-story-id="<?php echo (int) ( $story ? $story->ID : 0 ); ?>"
			data-story-title="<?php echo esc_attr( $story ? get_the_title( $story ) : '' ); ?>"
			data-story-url="<?php echo esc_url( $story ? get_permalink( $story ) : '' ); ?>"
			data-chapter-id="<?php echo (int) get_the_ID(); ?>"
			data-chapter-no="<?php echo (int) get_post_meta( get_the_ID(), 'dm_chapter_no', true ); ?>"
			data-chapter-title="<?php echo esc_attr( get_the_title() ); ?>"
			data-url="<?php echo esc_url( get_permalink() ); ?>"
			data-cover="<?php echo esc_url( $rn_cover ); ?>"
			data-pal="<?php echo (int) ( $story ? $story->ID % 4 : 0 ); ?>"
			data-status="<?php echo esc_attr( is_user_logged_in() ? dm_user_status() : 'guest' ); ?>"
			data-login="<?php echo esc_url( dm_login_page_url( array( 'redirect_to' => get_permalink() ) ) ); ?>"
			data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-nonce="<?php echo esc_attr( dm_can_engage() ? wp_create_nonce( 'dm_mark' ) : '' ); ?>"
			data-marks="<?php echo esc_attr( wp_json_encode( dm_can_engage() ? dm_marks_for( get_the_ID(), get_current_user_id() ) : array( 'bm' => null, 'hl' => array() ) ) ); ?>"><?php the_content(); ?></div>
		<section class="rn-card rn-endcard" id="rn-end" aria-labelledby="rn-end-h">
			<div class="rn-endline"><span class="rn-endtick" aria-hidden="true"></span><h2 id="rn-end-h">End of Episode <?php echo (int) $rn_no; ?></h2></div>
			<p class="rn-endask">How did this episode feel?</p>
			<div class="rn-reacts" id="rn-reacts" role="group" aria-label="Reactions"
				data-chapter="<?php echo (int) get_the_ID(); ?>"
				data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
				data-nonce="<?php echo esc_attr( $rn_can ? wp_create_nonce( 'dm_react' ) : '' ); ?>"
				data-state="<?php echo esc_attr( $rn_can ? 'ok' : ( is_user_logged_in() ? 'pending' : 'guest' ) ); ?>"
				data-login="<?php echo esc_url( dm_login_page_url( array( 'redirect_to' => get_permalink() . '#rn-end' ) ) ); ?>">
				<?php foreach ( $rn_ro as $key => $opt ) : $n = isset( $rn_rc[ $key ] ) ? (int) $rn_rc[ $key ] : 0; ?>
					<button type="button" class="rn-react<?php echo $rn_mine === $key ? ' on' : ''; ?>" data-kind="<?php echo esc_attr( $key ); ?>" aria-pressed="<?php echo $rn_mine === $key ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $opt[1] ); ?>">
						<span class="rn-emoji" aria-hidden="true"><?php echo esc_html( $opt[0] ); ?></span><span class="rn-rcount"><?php echo $n ? (int) $n : ''; ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<p class="rn-muted rn-reactmsg" id="rn-reactmsg" role="status" aria-live="polite"><?php echo $rn_can ? '' : ( is_user_logged_in() ? 'Reactions open once the author approves your account.' : '' ); ?><?php if ( ! is_user_logged_in() ) : ?><a href="<?php echo esc_url( dm_login_page_url( array( 'redirect_to' => get_permalink() . '#rn-end' ) ) ); ?>">Log in</a> to react.<?php endif; ?></p>
		</section>
		<nav class="rn-chnav" aria-label="Episodes">
			<?php if ( $prev ) : ?><a class="rn-btn ghost" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">&larr; Previous</a><?php else : ?><span></span><?php endif; ?>
			<?php if ( $next ) : ?><a class="rn-btn" href="<?php echo esc_url( get_permalink( $next ) ); ?>">Next episode &rarr;</a><?php endif; ?>
		</nav>
		<?php if ( $story ) : ?>
		<aside class="rn-eplist" aria-label="All episodes" data-readlist="<?php echo (int) $story->ID; ?>">
			<div class="rn-ephead">
				<h2>All Episodes</h2>
				<span class="rn-muted rn-readcount" data-readcount>0 / <?php echo (int) count( $chapters ); ?> read</span>
			</div>
			<div class="rn-eps">
				<?php foreach ( $chapters as $c ) { echo rn_ep_item( $c, get_the_ID(), $rn_counts ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</aside>
		<?php endif; ?>
		<div class="rn-rcomments"><?php get_template_part( 'template-parts/comments' ); ?></div>
	</div>
</div>
<div class="rn-tools" role="toolbar" aria-label="Reading controls">
	<div class="rn-hlbar" id="rn-hlbar" hidden role="group" aria-label="Highlighter">
		<button type="button" class="rn-sw" data-color="yellow" aria-label="Yellow" style="--sw:var(--rn-hl-yellow)"></button>
		<button type="button" class="rn-sw" data-color="green" aria-label="Green" style="--sw:var(--rn-hl-green)"></button>
		<button type="button" class="rn-sw" data-color="pink" aria-label="Pink" style="--sw:var(--rn-hl-pink)"></button>
		<button type="button" class="rn-sw rn-erase" data-color="erase" aria-label="Eraser">Erase</button>
		<button type="button" class="rn-sw rn-done" id="rn-hl-done" aria-label="Finish highlighting">Done</button>
	</div>
	<button type="button" id="rn-smaller" aria-label="Smaller text">A&minus;</button>
	<output id="rn-size">18px</output>
	<button type="button" id="rn-larger" aria-label="Larger text">A+</button>
	<button type="button" id="rn-mode" aria-label="Day or night mode"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg></button>
	<button type="button" id="rn-bm" aria-label="Bookmark this place" aria-pressed="false"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z"/></svg></button>
	<button type="button" id="rn-hl" aria-label="Highlighter" aria-pressed="false"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button>
</div>
<?php
get_footer();
