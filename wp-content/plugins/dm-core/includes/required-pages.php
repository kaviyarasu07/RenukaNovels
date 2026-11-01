<?php
/**
 * Tools > DM Required Pages: creates the pages Google AdSense and readers expect
 * (About Us, Contact Us, FAQ, Privacy Policy, Terms) plus Ongoing and Completed, as DRAFTS.
 * Text in [square brackets] must be replaced with real details before publishing.
 * Starter text is not legal advice. Have it reviewed.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_pages_menu() {
	add_management_page( 'DM Required Pages', 'DM Required Pages', 'manage_options', 'dm-required-pages', 'dm_pages_screen' );
}
add_action( 'admin_menu', 'dm_pages_menu' );

function dm_required_pages() {
	return array(
		'login'          => array( 'Log in', '<p>This page is drawn by the theme. It holds the Log in and Register forms.</p>' ),
		'my-account'     => array( 'My Account', '<p>This page is drawn by the theme. Readers see their reading, bookmarks, comments and highlights. The admin also sees the announcement form.</p>' ),
		'updates'        => array( 'Updates', '<p>This page lists the latest chapters and announcements. It is drawn by the theme.</p>' ),
		'trending'       => array( 'Trending', '<p>This page lists novels by number of views. It is drawn by the theme.</p>' ),
		'library'        => array( 'Library', '<p>This page lists every novel. It is drawn by the theme.</p>' ),
		'ongoing'        => array( 'Ongoing', '<p>This page lists all ongoing stories. It is drawn by the theme.</p>' ),
		'completed'      => array( 'Completed', '<p>This page lists all completed novels. It is drawn by the theme.</p>' ),
		'about'          => array(
			'About Us',
			'<h2>About the author</h2><p>[Write 2 to 3 short paragraphs about Renuga Muthukumar: who she is, how long she has been writing, what her stories are about.]</p><h2>About this website</h2><p>This website publishes Tamil serial novels by Renuga Muthukumar, one chapter at a time. Reading is free and needs no login. Readers can register to comment, rate and bookmark stories.</p>',
		),
		'contact'        => array(
			'Contact Us',
			'<h2>Get in touch</h2><p>For questions, feedback or permission requests, write to us at <strong>[contact email address]</strong>. We try to reply within [number] working days.</p><p>To report a problem with a chapter or a comment, include the story name and the chapter number.</p>',
		),
		'faq'            => array(
			'FAQ',
			'<h2>Do I need to log in to read?</h2><p>No. Reading is free and open to everyone. Logging in is only needed to comment, rate or bookmark stories.</p><h2>Why is my account waiting for approval?</h2><p>To keep the comments friendly, the author approves each new reader once. After that you can comment freely.</p><h2>When are new chapters published?</h2><p>[Write the usual schedule, for example: new chapters every week.]</p><h2>Can I copy or share the stories?</h2><p>Please do not copy the stories. You may share a link to this website.</p>',
		),
		'privacy-policy' => array(
			'Privacy Policy',
			'<p>Last updated: [date]</p><h2>Who we are</h2><p>This website is run for author Renuga Muthukumar. Contact: [contact email address].</p><h2>What we collect</h2><ul><li>Account details you give when you register: name or username, email address and password (stored securely, never shown to others).</li><li>Comments, ratings and bookmarks you make while logged in.</li><li>Technical data such as pages visited, device and browser type, used to count views and improve the website.</li></ul><h2>How we use it</h2><p>To run your account, show your comments, count story views, protect the website from spam, and send notifications you ask for. We do not sell your personal data.</p><h2>Cookies</h2><p>We use cookies to keep you logged in, remember reading settings such as text size and day or night mode, and measure traffic. [If advertising is enabled: Third parties, including Google, use cookies to show ads based on your earlier visits to this and other websites. You can manage personalised ads at https://adssettings.google.com.]</p><h2>Your choices</h2><p>You can ask us to delete your account and comments by writing to [contact email address]. You can block cookies in your browser settings, but some features may stop working.</p><h2>Changes</h2><p>We will update this page when our practices change.</p>',
		),
		'terms'          => array(
			'Terms & Conditions',
			'<p>Last updated: [date]</p><h2>Use of the website</h2><p>You may read the stories for personal, non-commercial use.</p><h2>Copyright</h2><p>All stories and text on this website are the work of Renuga Muthukumar. You may not copy, republish or sell them without written permission.</p><h2>Accounts and comments</h2><p>You are responsible for what you post. Comments that are abusive, hateful, spam or unlawful will be removed, and accounts may be blocked.</p><h2>Changes</h2><p>We may change these terms. Continued use means you accept the changes.</p><h2>Contact</h2><p>[contact email address]</p>',
		),
	);
}

function dm_pages_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$msg = '';
	if ( isset( $_POST['dm_pages_create'] ) && check_admin_referer( 'dm_pages' ) ) {
		$made = 0;
		foreach ( dm_required_pages() as $slug => $page ) {
			if ( get_page_by_path( $slug ) ) {
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => in_array( $slug, array( 'login', 'my-account', 'updates', 'trending', 'library', 'ongoing', 'completed' ), true ) ? 'publish' : 'draft',
					'post_name'    => $slug,
					'post_title'   => $page[0],
					'post_content' => $page[1],
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				++$made;
			}
		}
		$msg = 'Created ' . $made . ' page(s). Pages that already existed were left alone.';
	}
	echo '<div class="wrap"><h1>DM Required Pages</h1>';
	if ( $msg ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>';
	}
	echo '<p>Creates Log in, My Account, Updates, Trending, Library, Ongoing, Completed (published) and About Us, Contact Us, FAQ, Privacy Policy, Terms (saved as drafts). Replace every [bracketed] text with real details, then publish. Existing pages are never changed.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'dm_pages' );
	echo '<button class="button button-primary" name="dm_pages_create" value="1">Create required pages</button></form></div>';
}
