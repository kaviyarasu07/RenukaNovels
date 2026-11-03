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
	<div class="rn-actions">
		<?php
		if ( is_user_logged_in() ) :
			list( $rn_notes, $rn_unread ) = dm_notifications_for( get_current_user_id() );
			?>
			<div class="rn-bellwrap">
				<button class="rn-iconbtn rn-bell" type="button" id="rn-bell" aria-label="Notifications<?php echo $rn_unread ? ', ' . (int) $rn_unread . ' new' : ''; ?>" aria-haspopup="true" aria-expanded="false" aria-controls="rn-notifs" data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dm_notif' ) ); ?>">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
					<span class="rn-badge" id="rn-badge"<?php echo $rn_unread ? '' : ' hidden'; ?>><?php echo $rn_unread > 9 ? '9+' : (int) $rn_unread; ?></span>
				</button>
				<div class="rn-notifs" id="rn-notifs" hidden>
					<div class="rn-nhead"><strong>Notifications</strong></div>
					<?php if ( $rn_notes ) : ?>
						<ul class="rn-nlist">
							<?php foreach ( $rn_notes as $n ) : ?>
								<li><a class="rn-nitem<?php echo $n['unread'] ? ' is-new' : ''; ?>" href="<?php echo esc_url( $n['url'] ); ?>">
									<span class="rn-nlabel"><?php echo esc_html( $n['label'] ); ?></span>
									<span class="rn-nmsg" lang="ta"><?php echo esc_html( $n['msg'] ); ?></span>
									<span class="rn-ntime"><?php echo esc_html( human_time_diff( $n['ts'], time() ) ); ?> ago</span>
								</a></li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="rn-nempty">You are all caught up. New stories, episodes and replies will show here.</p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<button class="rn-iconbtn" type="button" id="rn-menu-open" aria-label="Open menu" aria-haspopup="dialog" aria-expanded="false">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
		</button>
	</div>
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
