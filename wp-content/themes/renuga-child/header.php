<?php
/** Site header: brand, search link area, menu button and the hamburger drawer. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="rn-top">
	<a class="rn-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php echo rn_logo( 40 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span>Renuga Muthukumar<small>NOVELS</small></span>
	</a>
	<form class="rn-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="hidden" name="post_type" value="dm_story">
		<input type="search" name="s" aria-label="Search stories" placeholder="Search stories" value="<?php echo esc_attr( get_search_query() ); ?>">
	</form>
	<button class="rn-iconbtn" type="button" id="rn-menu-open" aria-label="Open menu" aria-haspopup="dialog" aria-expanded="false">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
	</button>
</header>
<div class="rn-scrim" id="rn-scrim" hidden>
	<nav class="rn-drawer" role="dialog" aria-modal="true" aria-label="Main menu">
		<div style="display:flex;justify-content:flex-end">
			<button class="rn-iconbtn" type="button" id="rn-menu-close" aria-label="Close menu">&times;</button>
		</div>
		<?php if ( is_user_logged_in() ) : ?>
			<a class="rn-acct" href="<?php echo esc_url( rn_account_url() ); ?>">My Account</a>
		<?php endif; ?>
		<?php
		foreach ( rn_menu_items() as $item ) :
			$is_current = ( is_front_page() && 'home' === $item[2] ) || ( $item[2] && is_page( $item[2] ) ) || ( is_page( 'my-account' ) && 'account' === $item[2] );
			?>
			<a href="<?php echo esc_url( $item[1] ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item[0] ); ?></a>
		<?php endforeach; ?>
		<div class="rn-auth">
			<?php if ( is_user_logged_in() ) : ?>
				<a class="rn-btn ghost" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
			<?php else : ?>
				<a class="rn-btn" href="<?php echo esc_url( dm_login_page_url() ); ?>">Log in</a>
			<?php endif; ?>
		</div>
	</nav>
</div>
<main id="main">
