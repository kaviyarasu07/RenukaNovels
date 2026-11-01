<?php
/**
 * Renuga Child theme bootstrap.
 *
 * @package renuga-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RN_CHILD_VERSION', '0.7.0' );

function rn_child_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
}
add_action( 'after_setup_theme', 'rn_child_setup' );

function rn_child_enqueue_assets() {
	wp_enqueue_style( 'rn-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;500;600;700&family=Noto+Serif+Tamil:wght@500;600;700&family=Hind+Madurai:wght@400;500;600&display=swap', array(), null );
	wp_enqueue_style( 'rn-tokens', get_stylesheet_directory_uri() . '/assets/css/tokens.css', array(), RN_CHILD_VERSION );
	wp_enqueue_style( 'rn-main', get_stylesheet_directory_uri() . '/assets/css/main.css', array( 'rn-tokens', 'rn-fonts' ), RN_CHILD_VERSION );
	wp_enqueue_style( 'rn-child-style', get_stylesheet_uri(), array( 'rn-main' ), RN_CHILD_VERSION );
	wp_enqueue_script( 'rn-main', get_stylesheet_directory_uri() . '/assets/js/main.js', array(), RN_CHILD_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'rn_child_enqueue_assets', 20 );

/** Body class so our CSS only affects our own markup. */
function rn_body_class( $classes ) {
	$classes[] = 'rn';
	return $classes;
}
add_filter( 'body_class', 'rn_body_class' );

/** Menu items (same as the approved prototype). Updates and Bookmarks arrive with later modules. */
function rn_menu_items() {
	return array(
		array( 'Home', home_url( '/' ), 'home' ),
		array( 'Ongoing', home_url( '/ongoing/' ), 'ongoing' ),
		array( 'Completed', home_url( '/completed/' ), 'completed' ),
		array( 'Updates', home_url( '/updates/' ), 'updates' ),
		array( 'Bookmarks', home_url( '/#bookmarks' ), '' ),
		array( 'About Us', home_url( '/about/' ), 'about' ),
		array( 'Contact Us', home_url( '/contact/' ), 'contact' ),
	);
}

/** Stories by status, newest first. */
function rn_stories( $status, $limit = -1 ) {
	return get_posts(
		array(
			'post_type'      => 'dm_story',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => 'dm_status',
			'meta_value'     => $status,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

/** One story card. $mode: 'card' (grid) or 'row' (completed list). */
function rn_story_card( $story, $mode = 'card' ) {
	$status  = dm_story_status( $story->ID );
	$chapter = dm_latest_chapter( $story->ID );
	$cover   = rn_cover( $story );
	$teaser = get_the_excerpt( $story );
	$label  = 'ongoing' === $status && $chapter ? 'Latest · Chapter ' . (int) get_post_meta( $chapter->ID, 'dm_chapter_no', true ) : ( 'completed' === $status ? 'Completed' : '' );
	ob_start();
	?>
	<a class="<?php echo 'row' === $mode ? 'rn-card rn-row' : ( 'feat' === $mode ? 'rn-card rn-story rn-feat' : 'rn-card rn-story' ); ?>" href="<?php echo esc_url( get_permalink( $story ) ); ?>">
		<?php echo $cover; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span style="display:flex;flex-direction:column;gap:6px;min-width:0">
			<?php if ( $label ) : ?><span class="rn-pill <?php echo 'completed' === $status ? 'teal' : 'solid'; ?>" style="align-self:flex-start"><?php echo esc_html( $label ); ?></span><?php endif; ?>
			<strong lang="ta" style="font-size:19px;line-height:1.5"><?php echo esc_html( get_the_title( $story ) ); ?></strong>
			<?php if ( $teaser ) : ?><span class="rn-teaser" lang="ta"><?php echo esc_html( wp_trim_words( $teaser, 24 ) ); ?></span><?php endif; ?>
		</span>
	</a>
	<?php
	return ob_get_clean();
}


/** Cover: the featured image if set, otherwise generated art (same four colourways as the prototype). */
function rn_cover( $story ) {
	$img = get_the_post_thumbnail( $story, 'medium', array( 'class' => 'rn-cover', 'alt' => '' ) );
	if ( $img ) {
		return $img;
	}
	$pal = array(
		array( '#BFE6E2', '#FFF8E6', '#6FBDB6', '#0A6F6D' ),
		array( '#D6EEF5', '#FFF8E6', '#8AC4DA', '#3A89A8' ),
		array( '#FADBD3', '#FFF3E0', '#F0A898', '#C9604C' ),
		array( '#FFF0C9', '#FFFFFF', '#F3CC6B', '#D9A23B' ),
	);
	$c = $pal[ $story->ID % count( $pal ) ];
	return '<svg class="rn-cover" viewBox="0 0 120 170" aria-hidden="true"><rect width="120" height="170" fill="' . $c[0] . '"/><circle cx="60" cy="56" r="24" fill="' . $c[1] . '"/><path d="M0 104Q30 88 60 104T120 100V170H0Z" fill="' . $c[2] . '" opacity=".75"/><path d="M0 126Q30 110 60 126T120 122V170H0Z" fill="' . $c[2] . '"/><path d="M0 148Q30 134 60 148T120 144V170H0Z" fill="' . $c[3] . '"/><rect width="8" height="170" fill="#000" opacity=".1"/></svg>';
}

/** Trending: most viewed first (view counts arrive with the stats module), newest as the tie-break. */
function rn_trending( $limit = 5 ) {
	$all = get_posts( array( 'post_type' => 'dm_story', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
	usort(
		$all,
		function ( $a, $b ) {
			$va = (int) get_post_meta( $a->ID, 'dm_views', true );
			$vb = (int) get_post_meta( $b->ID, 'dm_views', true );
			return $vb <=> $va ?: strcmp( $b->post_date, $a->post_date );
		}
	);
	return $limit < 0 ? $all : array_slice( $all, 0, $limit );
}

function rn_count( $type ) {
	$c = wp_count_posts( $type );
	return isset( $c->publish ) ? (int) $c->publish : 0;
}

function rn_logo( $size = 44 ) {
	return '<img src="' . esc_url( get_stylesheet_directory_uri() . '/assets/img/logo.webp' ) . '" width="' . (int) $size . '" height="' . (int) $size . '" alt="Renuga Muthukumar Novels logo" style="border-radius:50%;display:block">';
}


/** Footer links (pages are created from Tools > DM Required Pages). */
function rn_footer_links() {
	return array(
		array( 'About Us', home_url( '/about/' ) ),
		array( 'Contact Us', home_url( '/contact/' ) ),
		array( 'FAQ', home_url( '/faq/' ) ),
		array( 'Privacy Policy', home_url( '/privacy-policy/' ) ),
		array( 'Terms & Conditions', home_url( '/terms/' ) ),
	);
}


/** One library row: cover, title, teaser, views, episodes, status, link. */
function rn_library_card( $story ) {
	$status   = dm_story_status( $story->ID );
	$episodes = count( dm_story_chapters( $story->ID ) );
	$views    = (int) get_post_meta( $story->ID, 'dm_views', true );
	$teaser   = wp_trim_words( get_the_excerpt( $story ), 26 );
	ob_start();
	?>
	<article class="rn-card rn-libcard">
		<a class="rn-libcover" href="<?php echo esc_url( get_permalink( $story ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo rn_cover( $story ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<div class="rn-libbody">
			<h2 lang="ta"><a href="<?php echo esc_url( get_permalink( $story ) ); ?>"><?php echo esc_html( get_the_title( $story ) ); ?></a></h2>
			<?php if ( $teaser ) : ?><p class="rn-teaser" lang="ta"><?php echo esc_html( $teaser ); ?></p><?php endif; ?>
			<div class="rn-libmeta">
				<?php if ( $views > 0 ) : ?><span><?php echo esc_html( number_format_i18n( $views ) ); ?> Views</span><?php endif; ?>
				<span><?php echo (int) $episodes; ?> Episode<?php echo 1 === $episodes ? '' : 's'; ?></span>
				<span class="rn-pill <?php echo 'completed' === $status ? 'teal' : 'solid'; ?>"><?php echo 'completed' === $status ? 'Completed' : 'Ongoing'; ?></span>
			</div>
			<a class="rn-libgo" href="<?php echo esc_url( get_permalink( $story ) ); ?>">View Episodes &rarr;</a>
		</div>
	</article>
	<?php
	return ob_get_clean();
}


/** "Trending" row: rank, cover, title, status, views. Used on the home page and the Trending page. */
function rn_trend_row( $story, $rank ) {
	$ch    = dm_latest_chapter( $story->ID );
	$views = (int) get_post_meta( $story->ID, 'dm_views', true );
	ob_start();
	?>
	<a class="rn-trend" href="<?php echo esc_url( get_permalink( $story ) ); ?>">
		<span class="rn-rank"><?php echo (int) $rank; ?></span>
		<span class="rn-tcover"><?php echo rn_cover( $story ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="rn-ttext"><strong lang="ta"><?php echo esc_html( get_the_title( $story ) ); ?></strong><span class="rn-muted" style="font-size:13px"><?php echo 'completed' === dm_story_status( $story->ID ) ? 'Completed' : 'Ongoing'; ?><?php echo $ch ? ' · Chapter ' . (int) get_post_meta( $ch->ID, 'dm_chapter_no', true ) : ''; ?><?php echo $views > 0 ? ' · ' . esc_html( number_format_i18n( $views ) ) . ' views' : ''; ?></span></span>
		<span class="rn-rd">Read</span>
	</a>
	<?php
	return ob_get_clean();
}


/** Updates feed: new chapters and announcements, pinned announcements first, then newest first. */
function rn_updates( $limit = -1 ) {
	$items = array();
	foreach ( get_posts( array( 'post_type' => 'dm_chapter', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $c ) {
		$items[] = array( 'type' => 'chapter', 'post' => $c, 'ts' => strtotime( $c->post_date_gmt ), 'pin' => 0 );
	}
	foreach ( get_posts( array( 'post_type' => 'dm_announcement', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $a ) {
		$items[] = array( 'type' => 'note', 'post' => $a, 'ts' => strtotime( $a->post_date_gmt ), 'pin' => (int) get_post_meta( $a->ID, 'dm_pinned', true ) );
	}
	usort(
		$items,
		function ( $x, $y ) {
			return ( $y['pin'] <=> $x['pin'] ) ?: ( $y['ts'] <=> $x['ts'] );
		}
	);
	return $limit < 0 ? $items : array_slice( $items, 0, $limit );
}

/** One row of the updates list. */
function rn_update_row( $item ) {
	$p = $item['post'];
	ob_start();
	if ( 'note' === $item['type'] ) {
		?>
		<div class="rn-upd rn-upd-note">
			<span class="rn-muted" style="font-size:13px"><?php echo esc_html( get_the_date( 'd-M-y H:i', $p ) ); ?></span>
			<span style="flex:1;min-width:0;display:flex;flex-direction:column;gap:6px;align-items:flex-start">
				<span class="rn-pill teal">Announcement<?php echo $item['pin'] ? ' · Pinned' : ''; ?></span>
				<span lang="ta" style="font-weight:500"><?php echo nl2br( esc_html( $p->post_content ) ); ?></span>
			</span>
		</div>
		<?php
	} else {
		$story = get_post( $p->post_parent );
		?>
		<a class="rn-upd" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
			<span class="rn-muted" style="font-size:13px"><?php echo esc_html( get_the_date( 'd-M-y H:i', $p ) ); ?></span>
			<span lang="ta" style="flex:1;min-width:0;font-weight:600"><?php echo esc_html( ( $story ? get_the_title( $story ) . ': ' : '' ) . get_the_title( $p ) ); ?></span>
		</a>
		<?php
	}
	return ob_get_clean();
}

/** Account links. */
function rn_account_url( $tab = '' ) {
	return $tab ? add_query_arg( 'tab', $tab, home_url( '/my-account/' ) ) : home_url( '/my-account/' );
}

/** Readers waiting for approval. */
function rn_pending_readers() {
	return get_users( array( 'role' => 'dm_reader', 'meta_key' => 'dm_status', 'meta_value' => 'pending', 'orderby' => 'registered', 'order' => 'ASC' ) );
}
