<?php
/**
 * Sample data for development: Tools > DM Sample Data.
 * Creates 3 placeholder novels (2 ongoing, 1 completed) with chapters, all tagged
 * with meta dm_sample=1 so "Remove sample data" deletes only them. Not for production.
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dm_sample_menu() {
	add_management_page( 'DM Sample Data', 'DM Sample Data', 'manage_options', 'dm-sample-data', 'dm_sample_page' );
}
add_action( 'admin_menu', 'dm_sample_menu' );

function dm_sample_stories() {
	$p = array(
		'மழை ஓய்ந்திருந்த அந்த மாலையில் வாசல் கோலம் பாதி கலைந்திருந்தது. மீரா அதை அழிக்காமல் நீண்ட நேரம் பார்த்துக்கொண்டு நின்றாள்.',
		'சமையலறையிலிருந்து அம்மாவின் குரல் மெதுவாகக் கேட்டது. “மீரா, காபி ஆறிப்போகுது, உள்ளே வா.” அவள் தலையசைத்தாளே தவிர, கால்கள் நகரவில்லை.',
		'தெருமுனையில் ஒரு சைக்கிள் மணி ஒலித்தது. பத்து வருடங்களுக்கு முன்பு அதே மணியோசை அவளை ஓடிவரச் செய்யும். இன்று அது ஒரு பழைய நினைவாக மட்டுமே காதில் விழுந்தது.',
		'வீட்டுக்குள் நுழைந்தபோது பழைய நாட்காட்டியின் தாள் காற்றில் படபடத்தது. நாட்கள் நகர்ந்தாலும் சில நினைவுகள் ஒரே இடத்தில் நின்றுவிடுகின்றன என்று அவள் நினைத்துக்கொண்டாள்.',
		'அன்றிரவு மழை மீண்டும் தொடங்கியது. ஜன்னல் வழியே வந்த குளிர்ந்த காற்றில் மண்வாசனை கலந்திருந்தது. நாளை எல்லாம் மாறும் என்ற நம்பிக்கையுடன் அவள் விளக்கை அணைத்தாள்.',
	);
	return array(
		array(
			'title'   => 'மழைத்துளி மனதோரம் (மாதிரி)',
			'status'  => 'ongoing',
			'excerpt' => 'மழை ஓய்ந்த மாலையில் தொடங்கும் ஒரு குடும்பக் கதை. பழைய நினைவுகளும் புதிய நம்பிக்கைகளும் ஒரே வீட்டுக்குள்.',
			'content' => 'மீராவின் குடும்பத்தில் நடக்கும் சிறு சிறு நிகழ்வுகள் வழியே அன்பையும் நினைவுகளையும் சொல்லும் தொடர்கதை.',
			'chapters'=> array( 'அத்தியாயம் 1 - வாசல் கோலம்', 'அத்தியாயம் 2 - அம்மாவின் குரல்', 'அத்தியாயம் 3 - சைக்கிள் மணி', 'அத்தியாயம் 4 - பழைய நாட்காட்டி' ),
			'paras'   => $p,
		),
		array(
			'title'   => 'நிலாச்சோறு நினைவுகள் (மாதிரி)',
			'status'  => 'ongoing',
			'excerpt' => 'நிலா வெளிச்சத்தில் பாட்டி ஊட்டிய சோற்றின் சுவை இன்னும் நாவில் இருக்கிறது.',
			'content' => 'பாட்டியின் கதைகளும் நிலாச்சோறும் நிறைந்த சிறுவயது நாட்களை நினைவுகூரும் கதை.',
			'chapters'=> array( 'அத்தியாயம் 1 - மொட்டை மாடி', 'அத்தியாயம் 2 - பாட்டியின் கதை', 'அத்தியாயம் 3 - நிலா வெளிச்சம்' ),
			'paras'   => array_reverse( $p ),
		),
		array(
			'title'   => 'அம்மாவின் கடிதம் (மாதிரி)',
			'status'  => 'completed',
			'excerpt' => 'ஒரு பழைய பெட்டியில் கிடைத்த கடிதம் மூன்று தலைமுறைகளை இணைக்கிறது.',
			'content' => 'அம்மா எழுதி வைத்துச் சென்ற ஒரு கடிதம் குடும்பத்தின் மறந்துபோன உறவுகளை மீண்டும் இணைக்கும் முழுநீள நாவல்.',
			'chapters'=> array( 'அத்தியாயம் 1 - பழைய பெட்டி', 'அத்தியாயம் 2 - கடிதம்', 'அத்தியாயம் 3 - மூன்று தலைமுறை', 'அத்தியாயம் 4 - முடிவுரை' ),
			'paras'   => array_slice( $p, 2 ) + array_slice( $p, 0, 2 ),
		),
	);
}

function dm_sample_create() {
	$made = 0;
	foreach ( dm_sample_stories() as $s ) {
		$story_id = wp_insert_post(
			array(
				'post_type'    => 'dm_story',
				'post_status'  => 'publish',
				'post_title'   => $s['title'],
				'post_content' => $s['content'],
				'post_excerpt' => $s['excerpt'],
			)
		);
		if ( is_wp_error( $story_id ) || ! $story_id ) {
			continue;
		}
		update_post_meta( $story_id, 'dm_status', $s['status'] );
		update_post_meta( $story_id, 'dm_sample', 1 );
		++$made;
		foreach ( $s['chapters'] as $i => $title ) {
			$body = '';
			foreach ( $s['paras'] as $para ) {
				$body .= '<p>' . esc_html( $para ) . "</p>\n";
			}
			$ch_id = wp_insert_post(
				array(
					'post_type'    => 'dm_chapter',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_content' => $body,
					'post_parent'  => $story_id,
					'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( ( count( $s['chapters'] ) - $i ) * DAY_IN_SECONDS ) ),
				)
			);
			if ( $ch_id && ! is_wp_error( $ch_id ) ) {
				update_post_meta( $ch_id, 'dm_chapter_no', $i + 1 );
				update_post_meta( $ch_id, 'dm_sample', 1 );
				dm_sample_seed_views( $ch_id, $story_id, $i );
			}
		}
	}
	return $made;
}

/**
 * Fake daily views for the last 30 days so the Stats tab has something to show.
 * Admin visits are not counted, so a fresh test site would otherwise stay at zero.
 */
function dm_sample_seed_views( $ch_id, $story_id, $index ) {
	global $wpdb;
	$t     = dm_views_table();
	$total = 0;
	for ( $d = 29; $d >= 0; $d-- ) {
		$n = wp_rand( 0, 12 ) + max( 0, 8 - $index * 2 );
		if ( $n < 1 ) {
			continue;
		}
		$day = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . " -$d days" ) );
		$wpdb->query( $wpdb->prepare( "INSERT INTO $t (chapter_id, story_id, day, views) VALUES (%d, %d, %s, %d) ON DUPLICATE KEY UPDATE views = %d", $ch_id, $story_id, $day, $n, $n ) ); // phpcs:ignore WordPress.DB
		$total += $n;
	}
	update_post_meta( $ch_id, 'dm_views', $total );
	update_post_meta( $story_id, 'dm_views', (int) get_post_meta( $story_id, 'dm_views', true ) + $total );
}

function dm_sample_remove() {
	$ids = get_posts(
		array(
			'post_type'      => array( 'dm_story', 'dm_chapter' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => 'dm_sample',
			'meta_value'     => 1,
		)
	);
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
	return count( $ids );
}

function dm_sample_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$msg = '';
	if ( isset( $_POST['dm_sample_action'] ) && check_admin_referer( 'dm_sample' ) ) {
		if ( 'create' === $_POST['dm_sample_action'] ) {
			$msg = 'Created ' . dm_sample_create() . ' sample stories with chapters.';
		} elseif ( 'remove' === $_POST['dm_sample_action'] ) {
			$msg = 'Removed ' . dm_sample_remove() . ' sample items.';
		}
	}
	echo '<div class="wrap"><h1>DM Sample Data</h1>';
	if ( $msg ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>';
	}
	echo '<p>For development only. Creates 3 placeholder novels (2 ongoing, 1 completed) with chapters. Only items made here are removed by the Remove button.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'dm_sample' );
	echo '<button class="button button-primary" name="dm_sample_action" value="create">Create sample data</button> ';
	echo '<button class="button" name="dm_sample_action" value="remove">Remove sample data</button>';
	echo '</form></div>';
}
