<?php
/**
 * Renuga Child theme bootstrap.
 *
 * @package renuga-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RN_CHILD_VERSION', '0.3.1' );

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
		array( 'Updates', home_url( '/#updates' ), '' ),
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
	return array_slice( $all, 0, $limit );
}

function rn_count( $type ) {
	$c = wp_count_posts( $type );
	return isset( $c->publish ) ? (int) $c->publish : 0;
}

function rn_logo( $size = 44 ) {
	return '<img src="' . esc_url( get_stylesheet_directory_uri() . '/assets/img/logo.webp' ) . '" width="' . (int) $size . '" height="' . (int) $size . '" alt="Renuga Muthukumar Novels logo" style="border-radius:50%;display:block">';
}
