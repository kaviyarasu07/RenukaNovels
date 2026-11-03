<?php
/**
 * Renuga Child theme bootstrap.
 *
 * @package renuga-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RN_CHILD_VERSION', '0.10.0' );

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
/** Trending = most views in the last 7 days; ties and quiet weeks fall back to total views, then newest. */
function rn_trending( $limit = 5 ) {
	$all  = get_posts( array( 'post_type' => 'dm_story', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
	$week = function_exists( 'dm_week_views_map' ) ? dm_week_views_map() : array();
	usort(
		$all,
		function ( $a, $b ) use ( $week ) {
			$wa = isset( $week[ $a->ID ] ) ? $week[ $a->ID ] : 0;
			$wb = isset( $week[ $b->ID ] ) ? $week[ $b->ID ] : 0;
			if ( $wa !== $wb ) {
				return $wb <=> $wa;
			}
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
				<?php if ( $views > 0 ) : ?><span><?php echo esc_html( number_format_i18n( $views ) ); ?> <?php echo 1 === $views ? 'View' : 'Views'; ?></span><?php endif; ?>
				<span><?php echo (int) $episodes; ?> Episode<?php echo 1 === $episodes ? '' : 's'; ?></span>
				<?php echo rn_rating_html( $story->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
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
		<span class="rn-ttext"><strong lang="ta"><?php echo esc_html( get_the_title( $story ) ); ?></strong><span class="rn-muted" style="font-size:13px"><?php echo 'completed' === dm_story_status( $story->ID ) ? 'Completed' : 'Ongoing'; ?><?php echo $ch ? ' · Chapter ' . (int) get_post_meta( $ch->ID, 'dm_chapter_no', true ) : ''; ?><?php echo $views > 0 ? ' · ' . esc_html( number_format_i18n( $views ) ) . ( 1 === $views ? ' view' : ' views' ) : ''; ?><?php echo rn_rating_html( $story->ID ) ? ' · ' . rn_rating_html( $story->ID ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></span></span>
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


/** "★ 4.8" or empty when the story has no ratings yet. */
function rn_rating_html( $story_id ) {
	list( $avg, $n ) = dm_rating_summary( $story_id );
	if ( $n < 1 ) {
		return '';
	}
	return '<span class="rn-rate-inline"><span class="rn-star" aria-hidden="true">&#9733;</span> ' . esc_html( number_format_i18n( $avg, 1 ) ) . '<span class="screen-reader-text"> out of 5, ' . (int) $n . ' ratings</span></span>';
}


/** Page numbers for My Account lists (20 per page, query arg "pg"). */
function rn_account_pager( $total, $page, $tab, $scope = 'all', $per = 20 ) {
	$pages = (int) ceil( $total / $per );
	if ( $pages < 2 ) {
		return '';
	}
	$base  = home_url( '/my-account/' ) . '?tab=' . rawurlencode( $tab ) . ( 'mine' === $scope ? '&scope=mine' : '' ) . '&pg=%#%';
	$links = paginate_links(
		array(
			'base'      => $base,
			'format'    => '',
			'current'   => $page,
			'total'     => $pages,
			'mid_size'  => 1,
			'prev_text' => '&larr; Previous',
			'next_text' => 'Next &rarr;',
		)
	);
	return $links ? '<nav class="rn-pager" aria-label="Pages">' . $links . '</nav>' : '';
}


/** Small inline icons, sized 1em so they follow the text. */
function rn_icon( $name ) {
	$paths = array(
		'eye'  => '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>',
		'chat' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
		'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/><path d="M4 19.5V21h16"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="rn-ico" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[ $name ] . '</svg>';
}

/** Rough reading time in minutes (about 180 words a minute), at least 1. */
function rn_min_read( $post ) {
	$text  = trim( wp_strip_all_tags( $post->post_content ) );
	$words = '' === $text ? 0 : count( preg_split( '/\s+/u', $text ) );
	return max( 1, (int) ceil( $words / 180 ) );
}

function rn_views_label( $n ) {
	return number_format_i18n( (int) $n ) . ( 1 === (int) $n ? ' view' : ' views' );
}

/**
 * One episode in a list (story page and the reading-page side list).
 * The dot and the small tag show: current episode (set here), read (set by JS from this browser), or not read yet.
 */
function rn_ep_item( $chapter, $current_id = 0, $counts = array() ) {
	$no      = (int) get_post_meta( $chapter->ID, 'dm_chapter_no', true );
	$views   = (int) get_post_meta( $chapter->ID, 'dm_views', true );
	$cmts    = isset( $counts[ $chapter->ID ] ) ? (int) $counts[ $chapter->ID ] : 0;
	$current = (int) $current_id === (int) $chapter->ID;
	ob_start();
	?>
	<a class="rn-ep<?php echo $current ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $chapter ) ); ?>" data-ch="<?php echo (int) $chapter->ID; ?>"<?php echo $current ? ' aria-current="page"' : ''; ?>>
		<span class="rn-dot" aria-hidden="true"></span>
		<span class="rn-eptext">
			<span class="rn-epno">Episode <?php echo (int) $no; ?> <span class="rn-state"><?php echo $current ? 'Reading now' : ''; ?></span></span>
			<span class="rn-eptitle" lang="ta"><?php echo esc_html( get_the_title( $chapter ) ); ?></span>
			<span class="rn-epmeta"><?php echo esc_html( get_the_date( 'M j, Y', $chapter ) ); ?> <span aria-hidden="true">&middot;</span> <?php echo (int) rn_min_read( $chapter ); ?> min read <span aria-hidden="true">&middot;</span> <?php echo rn_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( number_format_i18n( $views ) ); ?><?php echo $cmts ? ' <span aria-hidden="true">&middot;</span> ' . rn_icon( 'chat' ) . ' ' . (int) $cmts : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
}
