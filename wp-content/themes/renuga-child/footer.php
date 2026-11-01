<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>
<footer class="rn-foot">
	<div class="rn-foot-in">
		<div class="rn-foot-brand">
			<?php echo rn_logo( 48 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<strong>Renuga Muthukumar<br><small>NOVELS</small></strong>
		</div>
		<p>Tamil stories about family, memories and promises. New chapters every week.</p>
		<nav class="rn-foot-links" aria-label="Footer">
			<?php foreach ( rn_footer_links() as $l ) : ?>
				<a href="<?php echo esc_url( $l[1] ); ?>"><?php echo esc_html( $l[0] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<p class="rn-foot-copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Renuga Muthukumar Novels. All rights reserved. Stories may not be copied or shared.</p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
