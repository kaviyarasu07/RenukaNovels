<?php
/** Library: every novel, with All / Ongoing / Completed filters (WordPress page with slug "library"). */
get_header();
$filter = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification
$filter = in_array( $filter, array( 'ongoing', 'completed' ), true ) ? $filter : 'all';
$args   = array(
	'post_type'      => 'dm_story',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'date',
	'order'          => 'DESC',
);
if ( 'all' !== $filter ) {
	$args['meta_key']   = 'dm_status';
	$args['meta_value'] = $filter;
}
$stories = get_posts( $args );
$base    = get_permalink();
$tabs    = array( 'all' => 'All', 'ongoing' => 'Ongoing', 'completed' => 'Completed' );
?>
<div class="rn-wrap">
	<section class="rn-sec">
		<div><h1 class="rn-ptitle">Library</h1><div class="rn-muted">All novels by Renuga Muthukumar</div></div>
		<nav class="rn-filters" aria-label="Filter novels">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<a href="<?php echo esc_url( 'all' === $key ? $base : add_query_arg( 'status', $key, $base ) ); ?>"<?php echo $filter === $key ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
	</section>
	<section class="rn-liblist">
		<?php foreach ( $stories as $s ) { echo rn_library_card( $s ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( ! $stories ) : ?><p class="rn-muted">No novels here yet.</p><?php endif; ?>
	</section>
</div>
<?php
get_footer();
