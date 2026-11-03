<?php
/** My Account (WordPress page with slug "my-account"). Readers: reading, bookmarks, comments, highlights. Admin: also announcements. */
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( dm_login_page_url() );
	exit;
}
get_header();
$user     = wp_get_current_user();
$is_admin = current_user_can( 'manage_options' );
$tabs     = array();
$pending_n = $is_admin ? count( rn_pending_readers() ) : 0;
if ( $is_admin ) {
	$tabs['announcements'] = 'Announcements';
	$tabs['readers']       = 'Reader approvals' . ( $pending_n ? ' (' . $pending_n . ')' : '' );
	$tabs['stats']         = 'Stats';
}
$tabs['reading']    = 'Reading';
$tabs['bookmarks']  = 'Bookmarks';
$tabs['comments']   = 'Comments';
$tabs['highlights'] = 'Highlights';
$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( ! isset( $tabs[ $tab ] ) ) {
	$tab = $is_admin ? 'announcements' : 'reading';
}
$msg = isset( $_GET['dm_msg'] ) ? sanitize_key( wp_unslash( $_GET['dm_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$notes = array(
	'posted'  => 'Announcement published. Readers can now see it in Latest Posts & Updates.',
	'deleted' => 'Announcement removed.',
	'empty'   => 'Write the announcement first.',
	'approved' => 'Reader approved. They can now comment, rate and bookmark.',
	'rejected' => 'Reader rejected. They can no longer log in.',
	'welcome'  => 'Welcome! Your account is waiting for approval. You can read every story now. Commenting, rating and bookmarking open once the author approves you.',
);
$my_status = dm_user_status();
$pg        = max( 1, isset( $_GET['pg'] ) ? (int) $_GET['pg'] : 1 ); // phpcs:ignore WordPress.Security.NonceVerification
$per       = 20;
$off       = ( $pg - 1 ) * $per;
$scope     = ( $is_admin && ! ( isset( $_GET['scope'] ) && 'mine' === $_GET['scope'] ) ) ? 'all' : 'mine'; // phpcs:ignore WordPress.Security.NonceVerification
$scope_tab = $is_admin && in_array( $tab, array( 'comments', 'bookmarks', 'highlights' ), true );
$who       = function ( $uid ) {
	$u = get_userdata( (int) $uid );
	return $u ? $u->display_name : 'Deleted user';
};
?>
<div class="rn-wrap">
	<section class="rn-sec">
		<div class="rn-acchead">
			<div>
				<h1 class="rn-ptitle">My Account</h1>
				<div class="rn-muted">Hello, <?php echo esc_html( $user->display_name ); ?> · <?php echo $is_admin ? 'Admin' : 'Reader'; ?></div>
			</div>
			<a class="rn-btn ghost" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
		</div>
		<nav class="rn-filters" aria-label="Account sections">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<a href="<?php echo esc_url( rn_account_url( $key ) ); ?>"<?php echo $tab === $key ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php if ( $scope_tab ) : ?>
			<nav class="rn-filters" aria-label="Whose items">
				<a href="<?php echo esc_url( rn_account_url( $tab ) ); ?>"<?php echo 'all' === $scope ? ' aria-current="true"' : ''; ?>>All readers</a>
				<a href="<?php echo esc_url( add_query_arg( 'scope', 'mine', rn_account_url( $tab ) ) ); ?>"<?php echo 'mine' === $scope ? ' aria-current="true"' : ''; ?>>Only mine</a>
			</nav>
		<?php endif; ?>
		<?php if ( 'pending' === $my_status && 'welcome' !== $msg ) : ?><div class="rn-note" role="status">Your account is waiting for approval by the author. You can read every story meanwhile.</div><?php endif; ?>
		<?php if ( 'rejected' === $my_status ) : ?><div class="rn-note" role="status">Your account was not approved.</div><?php endif; ?>
		<?php if ( $msg && isset( $notes[ $msg ] ) ) : ?><div class="rn-note" role="status"><?php echo esc_html( $notes[ $msg ] ); ?></div><?php endif; ?>
	</section>

	<?php if ( 'announcements' === $tab && $is_admin ) : ?>
		<section class="rn-sec">
			<div class="rn-card rn-compose">
				<h2>Post an announcement</h2>
				<p class="rn-muted" style="margin:0">A short note for readers, for example when a chapter is late or a new chapter is coming. It appears in Latest Posts &amp; Updates on the home page.</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="dm_post_announcement">
					<?php wp_nonce_field( 'dm_post_announcement' ); ?>
					<label class="rn-field" for="dm_text">Announcement for readers
						<textarea id="dm_text" name="dm_text" rows="4" maxlength="500" lang="ta" placeholder="Write in Tamil or English"></textarea>
					</label>
					<div style="display:flex;gap:8px;flex-wrap:wrap;margin:12px 0">
						<button type="button" class="rn-chip" data-fill="அத்தியாயம் வெளியாவதில் சிறிய தாமதம் ஏற்பட்டுள்ளது. விரைவில் வெளியிடுகிறேன். பொறுமைக்கு நன்றி!">Episode delayed</button>
						<button type="button" class="rn-chip" data-fill="புதிய அத்தியாயம் இன்னும் சிறிது நேரத்தில் பதிவேற்றப்படும். காத்திருந்தமைக்கு நன்றி!">New episode soon</button>
					</div>
					<label class="rn-check"><input type="checkbox" name="dm_pinned" value="1"> Pin to the top of the list</label>
					<div style="margin-top:14px"><button type="submit" class="rn-btn">Publish announcement</button></div>
				</form>
			</div>
			<h2>Published announcements</h2>
			<div class="rn-card" style="padding:0;overflow:hidden">
				<?php
				$anns = get_posts( array( 'post_type' => 'dm_announcement', 'post_status' => 'publish', 'posts_per_page' => $per, 'offset' => $off, 'orderby' => 'date', 'order' => 'DESC' ) );
				$anns_total = (int) wp_count_posts( 'dm_announcement' )->publish;
				foreach ( $anns as $a ) :
					?>
					<div class="rn-upd rn-upd-note">
						<span class="rn-muted" style="font-size:13px"><?php echo esc_html( get_the_date( 'd-M-y H:i', $a ) ); ?></span>
						<span lang="ta" style="flex:1;min-width:0"><?php echo get_post_meta( $a->ID, 'dm_pinned', true ) ? '<span class="rn-pill teal">Pinned</span> ' : ''; ?><?php echo nl2br( esc_html( $a->post_content ) ); ?></span>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="dm_delete_announcement">
							<input type="hidden" name="dm_id" value="<?php echo (int) $a->ID; ?>">
							<?php wp_nonce_field( 'dm_delete_announcement_' . $a->ID ); ?>
							<button type="submit" class="rn-linkbtn">Remove</button>
						</form>
					</div>
				<?php endforeach; ?>
				<?php if ( ! $anns ) : ?><p class="rn-muted" style="padding:18px;margin:0">No announcements yet.</p><?php endif; ?>
			</div>
			<?php echo rn_account_pager( $anns_total, $pg, 'announcements' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</section>

	<?php elseif ( 'readers' === $tab && $is_admin ) : ?>
		<section class="rn-sec">
			<div><h2>Reader approvals</h2><div class="rn-muted">Approve each new reader once. After that they can comment, rate and bookmark without further approval.</div></div>
			<?php
			$rq      = new WP_User_Query( array( 'role' => 'dm_reader', 'orderby' => 'registered', 'order' => 'DESC', 'number' => $per, 'paged' => $pg, 'count_total' => true ) );
			$readers = $rq->get_results();
			?>
			<div class="rn-card" style="padding:0;overflow:hidden">
				<?php foreach ( $readers as $r ) : $st = dm_user_status( $r->ID ); ?>
					<div class="rn-upd rn-reader">
						<span style="flex:1;min-width:200px;display:flex;flex-direction:column">
							<strong><?php echo esc_html( $r->display_name ); ?></strong>
							<span class="rn-muted" style="font-size:14px"><?php echo esc_html( $r->user_email ); ?> · joined <?php echo esc_html( wp_date( 'd-M-y', strtotime( $r->user_registered ) ) ); ?></span>
						</span>
						<span class="rn-pill <?php echo 'approved' === $st ? 'teal' : ( 'pending' === $st ? 'solid' : '' ); ?>"><?php echo esc_html( ucfirst( $st ) ); ?></span>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:6px">
							<input type="hidden" name="action" value="dm_set_status">
							<input type="hidden" name="dm_user" value="<?php echo (int) $r->ID; ?>">
							<?php wp_nonce_field( 'dm_set_status_' . $r->ID ); ?>
							<?php if ( 'approved' !== $st ) : ?><button class="rn-btn" style="min-height:44px;padding:0 18px" name="dm_set" value="approved">Approve</button><?php endif; ?>
							<?php if ( 'rejected' !== $st ) : ?><button class="rn-btn ghost" style="min-height:44px;padding:0 18px" name="dm_set" value="rejected">Reject</button><?php endif; ?>
						</form>
					</div>
				<?php endforeach; ?>
				<?php if ( ! $readers ) : ?><p class="rn-muted" style="padding:18px;margin:0">No readers have registered yet.</p><?php endif; ?>
			</div>
			<?php echo rn_account_pager( $rq->get_total(), $pg, 'readers' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</section>

	<?php elseif ( 'stats' === $tab && $is_admin ) : ?>
		<?php
		$period  = ( isset( $_GET['period'] ) && 'monthly' === $_GET['period'] ) ? 'monthly' : 'daily'; // phpcs:ignore WordPress.Security.NonceVerification
		$sid     = isset( $_GET['story'] ) ? absint( $_GET['story'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $sid && 'dm_story' !== get_post_type( $sid ) ) {
			$sid = 0;
		}
		$stories   = get_posts( array( 'post_type' => 'dm_story', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		$chapters_all = get_posts( array( 'post_type' => 'dm_chapter', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		$by_story  = array();
		foreach ( $chapters_all as $ch ) {
			$by_story[ (int) $ch->post_parent ][] = $ch;
		}
		$ro        = dm_reaction_options();
		$ccount    = dm_comment_counts_all();
		$rcount    = dm_reaction_counts_all();
		$today     = current_time( 'Y-m-d' );
		$since     = 'monthly' === $period ? gmdate( 'Y-m-01', strtotime( $today . ' -11 months' ) ) : gmdate( 'Y-m-d', strtotime( $today . ' -29 days' ) );
		$range_lbl = 'monthly' === $period ? 'Last 12 months' : 'Last 30 days';
		$pviews    = dm_views_by_chapter_since( $since );
		$series    = dm_views_series( $period, $sid );
		$daily     = 'daily' === $period ? $series : dm_views_series( 'daily', $sid );
		$vals      = array_map( function ( $r ) { return $r[2]; }, $series );
		$maxv      = max( 1, $vals ? max( $vals ) : 1 );
		$dvals     = array_map( function ( $r ) { return $r[2]; }, $daily );
		$v_today   = $dvals ? end( $dvals ) : 0;
		$v_week    = array_sum( array_slice( $dvals, -7 ) );
		$v_month   = array_sum( $dvals );
		$scope_ids = $sid ? ( isset( $by_story[ $sid ] ) ? wp_list_pluck( $by_story[ $sid ], 'ID' ) : array() ) : wp_list_pluck( $chapters_all, 'ID' );
		$v_total   = 0;
		$cm_total  = 0;
		$rx_total  = 0;
		foreach ( $scope_ids as $cid ) {
			$v_total  += (int) get_post_meta( $cid, 'dm_views', true );
			$cm_total += isset( $ccount[ $cid ] ) ? $ccount[ $cid ] : 0;
			$rx_total += isset( $rcount[ $cid ] ) ? array_sum( $rcount[ $cid ] ) : 0;
		}
		if ( $sid ) {
			$cm_total += isset( $ccount[ $sid ] ) ? $ccount[ $sid ] : 0;
		} else {
			foreach ( $stories as $st_obj ) {
				$cm_total += isset( $ccount[ $st_obj->ID ] ) ? $ccount[ $st_obj->ID ] : 0;
			}
		}
		$url_for = function ( $args ) use ( $period, $sid ) {
			return add_query_arg( array_merge( array( 'tab' => 'stats', 'period' => $period, 'story' => $sid ), $args ), home_url( '/my-account/' ) );
		};
		$react_line = function ( $cid ) use ( $ro, $rcount ) {
			$out = '';
			foreach ( $ro as $key => $opt ) {
				if ( ! empty( $rcount[ $cid ][ $key ] ) ) {
					$out .= '<span class="rn-rx" title="' . esc_attr( $opt[1] ) . '"><span aria-hidden="true">' . esc_html( $opt[0] ) . '</span> ' . (int) $rcount[ $cid ][ $key ] . '<span class="screen-reader-text"> ' . esc_html( $opt[1] ) . '</span></span> ';
				}
			}
			return $out ? $out : '<span class="rn-muted">&ndash;</span>';
		};
		?>
		<section class="rn-sec">
			<div><h2>Stats</h2><div class="rn-muted">Views count once per visitor per episode per day. Your own (admin) visits and search-engine bots are not counted.</div></div>
			<nav class="rn-filters" aria-label="Period">
				<a href="<?php echo esc_url( $url_for( array( 'period' => 'daily' ) ) ); ?>"<?php echo 'daily' === $period ? ' aria-current="true"' : ''; ?>>Daily (30 days)</a>
				<a href="<?php echo esc_url( $url_for( array( 'period' => 'monthly' ) ) ); ?>"<?php echo 'monthly' === $period ? ' aria-current="true"' : ''; ?>>Monthly (12 months)</a>
			</nav>
			<nav class="rn-filters" aria-label="Story">
				<a href="<?php echo esc_url( $url_for( array( 'story' => 0 ) ) ); ?>"<?php echo ! $sid ? ' aria-current="true"' : ''; ?>>All stories</a>
				<?php foreach ( $stories as $st_obj ) : ?>
					<a lang="ta" href="<?php echo esc_url( $url_for( array( 'story' => $st_obj->ID ) ) ); ?>"<?php echo $sid === (int) $st_obj->ID ? ' aria-current="true"' : ''; ?>><?php echo esc_html( get_the_title( $st_obj ) ); ?></a>
				<?php endforeach; ?>
			</nav>
			<div class="rn-statgrid">
				<div class="rn-card rn-stat"><span class="rn-muted">Total views</span><strong><?php echo esc_html( number_format_i18n( $v_total ) ); ?></strong></div>
				<div class="rn-card rn-stat"><span class="rn-muted">Today</span><strong><?php echo esc_html( number_format_i18n( $v_today ) ); ?></strong></div>
				<div class="rn-card rn-stat"><span class="rn-muted">Last 7 days</span><strong><?php echo esc_html( number_format_i18n( $v_week ) ); ?></strong></div>
				<div class="rn-card rn-stat"><span class="rn-muted">Last 30 days</span><strong><?php echo esc_html( number_format_i18n( $v_month ) ); ?></strong></div>
				<div class="rn-card rn-stat"><span class="rn-muted">Comments</span><strong><?php echo esc_html( number_format_i18n( $cm_total ) ); ?></strong></div>
				<div class="rn-card rn-stat"><span class="rn-muted">Reactions</span><strong><?php echo esc_html( number_format_i18n( $rx_total ) ); ?></strong></div>
			</div>
			<div class="rn-card rn-chartcard">
				<div class="rn-chartHead"><h3><?php echo esc_html( $range_lbl ); ?> &middot; views per <?php echo 'monthly' === $period ? 'month' : 'day'; ?></h3><span class="rn-muted">Highest: <?php echo esc_html( number_format_i18n( max( $vals ) ) ); ?></span></div>
				<div class="rn-chartscroll">
					<div class="rn-bars rn-bars-<?php echo esc_attr( $period ); ?>" role="img" aria-label="<?php echo esc_attr( $range_lbl . ': ' . array_sum( $vals ) . ' views in total' ); ?>">
						<?php foreach ( $series as $i => $pt ) : $h = $pt[2] ? max( 3, round( $pt[2] / $maxv * 100 ) ) : 0; $show = 'monthly' === $period || 0 === ( count( $series ) - 1 - $i ) % 5; ?>
							<div class="rn-bar" title="<?php echo esc_attr( $pt[0] . ': ' . rn_views_label( $pt[2] ) ); ?>">
								<span class="rn-barval"><?php echo $pt[2] && 'monthly' === $period ? (int) $pt[2] : ''; ?></span>
								<span class="rn-barfill" style="height:<?php echo (int) $h; ?>%"></span>
								<em><?php echo $show ? esc_html( $pt[0] ) : ''; ?></em>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php if ( ! $sid ) : ?>
				<h3>Stories</h3>
				<div class="rn-card rn-tablewrap">
					<table class="rn-table">
						<thead><tr><th scope="col">Story</th><th scope="col"><?php echo esc_html( $range_lbl ); ?></th><th scope="col">Total views</th><th scope="col">Episodes</th><th scope="col">Comments</th><th scope="col">Reactions</th><th scope="col">Rating</th></tr></thead>
						<tbody>
						<?php
						foreach ( $stories as $st_obj ) :
							$chs = isset( $by_story[ $st_obj->ID ] ) ? $by_story[ $st_obj->ID ] : array();
							$pv  = 0;
							$cm  = isset( $ccount[ $st_obj->ID ] ) ? $ccount[ $st_obj->ID ] : 0;
							$rx  = 0;
							foreach ( $chs as $ch ) {
								$pv += isset( $pviews[ $ch->ID ] ) ? $pviews[ $ch->ID ] : 0;
								$cm += isset( $ccount[ $ch->ID ] ) ? $ccount[ $ch->ID ] : 0;
								$rx += isset( $rcount[ $ch->ID ] ) ? array_sum( $rcount[ $ch->ID ] ) : 0;
							}
							?>
							<tr>
								<th scope="row" lang="ta"><a href="<?php echo esc_url( $url_for( array( 'story' => $st_obj->ID ) ) ); ?>"><?php echo esc_html( get_the_title( $st_obj ) ); ?></a></th>
								<td><?php echo esc_html( number_format_i18n( $pv ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( (int) get_post_meta( $st_obj->ID, 'dm_views', true ) ) ); ?></td>
								<td><?php echo (int) count( $chs ); ?></td>
								<td><?php echo (int) $cm; ?></td>
								<td><?php echo (int) $rx; ?></td>
								<td><?php echo rn_rating_html( $st_obj->ID ) ? rn_rating_html( $st_obj->ID ) : '<span class="rn-muted">&ndash;</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
							</tr>
						<?php endforeach; ?>
						<?php if ( ! $stories ) : ?><tr><td colspan="7" class="rn-muted">No stories yet.</td></tr><?php endif; ?>
						</tbody>
					</table>
				</div>
				<p class="rn-muted" style="margin:0">Tap a story to see every episode with its views, comments and reactions.</p>
			<?php else : ?>
				<h3 lang="ta"><?php echo esc_html( get_the_title( $sid ) ); ?> &middot; episode by episode</h3>
				<div class="rn-card rn-tablewrap">
					<table class="rn-table">
						<thead><tr><th scope="col">Episode</th><th scope="col"><?php echo esc_html( $range_lbl ); ?></th><th scope="col">Total views</th><th scope="col">Comments</th><th scope="col">Reactions</th></tr></thead>
						<tbody>
						<?php foreach ( dm_story_chapters( $sid ) as $ch ) : ?>
							<tr>
								<th scope="row"><a href="<?php echo esc_url( get_permalink( $ch ) ); ?>"><span class="rn-muted">#<?php echo (int) get_post_meta( $ch->ID, 'dm_chapter_no', true ); ?></span> <span lang="ta"><?php echo esc_html( get_the_title( $ch ) ); ?></span></a></th>
								<td><?php echo esc_html( number_format_i18n( isset( $pviews[ $ch->ID ] ) ? $pviews[ $ch->ID ] : 0 ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( (int) get_post_meta( $ch->ID, 'dm_views', true ) ) ); ?></td>
								<td><?php echo (int) ( isset( $ccount[ $ch->ID ] ) ? $ccount[ $ch->ID ] : 0 ); ?></td>
								<td><?php echo $react_line( $ch->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
							</tr>
						<?php endforeach; ?>
						<?php if ( ! isset( $by_story[ $sid ] ) ) : ?><tr><td colspan="5" class="rn-muted">No episode has been published yet.</td></tr><?php endif; ?>
						</tbody>
					</table>
				</div>
				<p class="rn-muted" style="margin:0">Comments written on the story page itself (not under an episode): <?php echo (int) ( isset( $ccount[ $sid ] ) ? $ccount[ $sid ] : 0 ); ?>.</p>
			<?php endif; ?>
		</section>

	<?php elseif ( 'reading' === $tab ) : ?>
		<section class="rn-sec" id="rn-continue" aria-labelledby="rn-cont-h" hidden>
			<div><h2 id="rn-cont-h">Continue Where You Left Off</h2><div class="rn-muted">Open your story at the exact paragraph where you stopped</div></div>
			<div class="rn-contlist" id="rn-contlist"></div>
		</section>
		<p class="rn-muted" id="rn-cont-empty" hidden>You have not started a story on this device yet. Open any chapter and it will appear here.</p>

	<?php elseif ( 'comments' === $tab ) : ?>
		<section class="rn-sec">
			<div><h2><?php echo 'all' === $scope ? 'All comments' : 'My comments'; ?></h2><div class="rn-muted"><?php echo 'all' === $scope ? 'Every comment readers have written, newest first' : 'Everything you have written on chapters'; ?></div></div>
			<div class="rn-card" style="padding:0;overflow:hidden">
				<?php
				$cargs = array( 'status' => 'approve', 'orderby' => 'comment_date_gmt', 'order' => 'DESC' );
				if ( 'mine' === $scope ) {
					$cargs['user_id'] = get_current_user_id();
				}
				$c_total = (int) get_comments( array_merge( $cargs, array( 'count' => true ) ) );
				$mine    = get_comments( array_merge( $cargs, array( 'number' => $per, 'offset' => $off ) ) );
				foreach ( $mine as $c ) :
					$ch = get_post( $c->comment_post_ID );
					if ( ! $ch ) {
						continue;
					}
					$st_ = get_post( $ch->post_parent );
					?>
					<div class="rn-upd rn-mycmt">
						<span class="rn-muted" style="font-size:13px"><?php echo esc_html( get_comment_date( 'd-M-y H:i', $c ) ); ?></span>
						<span style="flex:1;min-width:200px;display:flex;flex-direction:column;gap:4px">
							<?php if ( 'all' === $scope ) : ?><span class="rn-muted" style="font-size:14px"><strong style="color:var(--rn-text)"><?php echo esc_html( $c->comment_author ); ?></strong><?php echo (int) $c->comment_parent ? ' (reply)' : ''; ?></span><?php endif; ?>
							<a lang="ta" href="<?php echo esc_url( get_permalink( $ch ) . '#comment-' . (int) $c->comment_ID ); ?>" style="font-weight:600"><?php echo esc_html( ( $st_ ? get_the_title( $st_ ) . ': ' : '' ) . get_the_title( $ch ) ); ?></a>
							<span lang="ta"><?php echo nl2br( esc_html( wp_trim_words( $c->comment_content, 40 ) ) ); ?></span>
						</span>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="dm_delete_comment">
							<input type="hidden" name="dm_cid" value="<?php echo (int) $c->comment_ID; ?>">
							<input type="hidden" name="dm_back" value="<?php echo esc_url( rn_account_url( 'comments' ) ); ?>">
							<?php wp_nonce_field( 'dm_delcomment_' . $c->comment_ID ); ?>
							<button class="rn-linkbtn" type="submit">Delete</button>
						</form>
						<?php if ( 'all' === $scope && (int) $c->user_id && (int) $c->user_id !== get_current_user_id() && ! user_can( (int) $c->user_id, 'manage_options' ) ) : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="dm_block_reader">
							<input type="hidden" name="dm_cid" value="<?php echo (int) $c->comment_ID; ?>">
							<?php wp_nonce_field( 'dm_block_' . $c->comment_ID ); ?>
							<button class="rn-linkbtn" type="submit">Block reader</button>
						</form>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				<?php if ( ! $mine ) : ?><p class="rn-muted" style="padding:18px;margin:0">You have not commented yet. Open a chapter and share what you felt.</p><?php endif; ?>
			</div>
			<?php echo rn_account_pager( $c_total, $pg, 'comments', $scope ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</section>

	<?php elseif ( 'bookmarks' === $tab ) : ?>
		<section class="rn-sec">
			<div><h2><?php echo 'all' === $scope ? 'All bookmarks' : 'My bookmarks'; ?></h2><div class="rn-muted"><?php echo 'all' === $scope ? 'Places readers saved while reading' : 'Places you saved while reading'; ?></div></div>
			<div class="rn-card" style="padding:0;overflow:hidden">
				<?php
				$bms = 'all' === $scope ? dm_all_bookmarks( $per, $off ) : dm_user_bookmarks( get_current_user_id(), $per, $off );
				$b_total = dm_count_marks( 'bm', 'all' === $scope ? 0 : get_current_user_id() );
				foreach ( $bms as $b ) :
					$chp = get_post( (int) $b->chapter_id );
					if ( ! $chp || 'publish' !== $chp->post_status ) {
						continue;
					}
					$sty = get_post( $chp->post_parent );
					?>
					<div class="rn-upd rn-mycmt">
						<span class="rn-muted" style="font-size:13px"><?php echo esc_html( wp_date( 'd-M-y H:i', strtotime( $b->created_at . ' UTC' ) ) ); ?></span>
						<span style="flex:1;min-width:200px;display:flex;flex-direction:column">
							<a lang="ta" href="<?php echo esc_url( get_permalink( $chp ) . '#p' . (int) $b->para ); ?>" style="font-weight:600"><?php echo esc_html( ( $sty ? get_the_title( $sty ) . ': ' : '' ) . get_the_title( $chp ) ); ?></a>
							<span class="rn-muted" style="font-size:14px"><?php echo 'all' === $scope ? '<strong style="color:var(--rn-text)">' . esc_html( $who( $b->user_id ) ) . '</strong> · ' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>Paragraph <?php echo (int) $b->para + 1; ?></span>
						</span>
						<a class="rn-btn ghost" style="min-height:44px;padding:0 18px" href="<?php echo esc_url( get_permalink( $chp ) . '#p' . (int) $b->para ); ?>">Open</a>
						<?php if ( (int) $b->user_id === get_current_user_id() ) : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="dm_remove_mark">
							<input type="hidden" name="dm_type" value="bookmark">
							<input type="hidden" name="dm_chapter" value="<?php echo (int) $b->chapter_id; ?>">
							<?php wp_nonce_field( 'dm_rmmark' ); ?>
							<button class="rn-linkbtn" type="submit">Remove</button>
						</form>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				<?php if ( ! $bms ) : ?><p class="rn-muted" style="padding:18px;margin:0">No bookmarks yet. While reading, tap the bookmark button in the toolbar at the bottom.</p><?php endif; ?>
			</div>
			<?php echo rn_account_pager( $b_total, $pg, 'bookmarks', $scope ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</section>

	<?php elseif ( 'highlights' === $tab ) : ?>
		<section class="rn-sec">
			<div><h2><?php echo 'all' === $scope ? 'All highlights' : 'My highlights'; ?></h2><div class="rn-muted"><?php echo 'all' === $scope ? 'Sentences readers highlighted, newest first' : 'Sentences you highlighted while reading'; ?></div></div>
			<div class="rn-card" style="padding:0;overflow:hidden">
				<?php
				$hls = 'all' === $scope ? dm_all_highlights( $per, $off ) : dm_user_highlights( get_current_user_id(), $per, $off );
				$h_total = dm_count_marks( 'hl', 'all' === $scope ? 0 : get_current_user_id() );
				foreach ( $hls as $h ) :
					$chp = get_post( (int) $h->chapter_id );
					if ( ! $chp || 'publish' !== $chp->post_status ) {
						continue;
					}
					$sty = get_post( $chp->post_parent );
					?>
					<div class="rn-upd rn-mycmt">
						<span style="flex:1;min-width:200px;display:flex;flex-direction:column;gap:6px">
							<a lang="ta" href="<?php echo esc_url( get_permalink( $chp ) . '#p' . (int) $h->para ); ?>" style="font-weight:600"><?php echo esc_html( ( $sty ? get_the_title( $sty ) . ': ' : '' ) . get_the_title( $chp ) ); ?></a>
							<span lang="ta" class="rn-hlquote hl-<?php echo esc_attr( $h->color ); ?>"><?php echo esc_html( $h->snippet ); ?></span>
							<?php if ( 'all' === $scope ) : ?><span class="rn-muted" style="font-size:14px">Highlighted by <strong style="color:var(--rn-text)"><?php echo esc_html( $who( $h->user_id ) ); ?></strong></span><?php endif; ?>
						</span>
						<?php if ( (int) $h->user_id === get_current_user_id() ) : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="dm_remove_mark">
							<input type="hidden" name="dm_type" value="highlight">
							<input type="hidden" name="dm_chapter" value="<?php echo (int) $h->chapter_id; ?>">
							<input type="hidden" name="dm_para" value="<?php echo (int) $h->para; ?>">
							<input type="hidden" name="dm_sent" value="<?php echo (int) $h->sent; ?>">
							<?php wp_nonce_field( 'dm_rmmark' ); ?>
							<button class="rn-linkbtn" type="submit">Remove</button>
						</form>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				<?php if ( ! $hls ) : ?><p class="rn-muted" style="padding:18px;margin:0">No highlights yet. While reading, tap the highlighter in the toolbar, then tap a sentence.</p><?php endif; ?>
			</div>
			<?php echo rn_account_pager( $h_total, $pg, 'highlights', $scope ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</section>

	<?php else : ?>
		<section class="rn-sec">
			<div class="rn-card"><p class="rn-muted" style="margin:0">Your <?php echo esc_html( strtolower( $tabs[ $tab ] ) ); ?> will be listed here. This part is being built next.</p></div>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();
