<?php
/**
 * Renuga Child theme bootstrap.
 *
 * @package renuga-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RN_CHILD_VERSION', '0.1.0' );

/**
 * Load the parent (Kadence) styles, then tokens, then child styles.
 */
function rn_child_enqueue_assets() {
	wp_enqueue_style(
		'rn-tokens',
		get_stylesheet_directory_uri() . '/assets/css/tokens.css',
		array(),
		RN_CHILD_VERSION
	);

	wp_enqueue_style(
		'rn-child-style',
		get_stylesheet_uri(),
		array( 'rn-tokens' ),
		RN_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'rn_child_enqueue_assets', 20 );
