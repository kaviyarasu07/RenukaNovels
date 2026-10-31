<?php
/**
 * Plugin Name: DM Core
 * Description: Core features for the Renuga Novels website: engagement (comments, ratings, bookmarks), view stats, moderation and push notifications.
 * Version:     0.1.0
 * Author:      Renuga Novels dev team
 * Text Domain: dm-core
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DM_CORE_VERSION', '0.1.0' );
define( 'DM_CORE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Planned modules (one folder each under includes/):
 *  - engagement:    comments, ratings, bookmarks (login required)
 *  - stats:         views per story and chapter, daily and monthly
 *  - moderation:    comment and rating moderation, spam blocking
 *  - notifications: web push notifications
 *
 * Each module registers its own hooks from a single init() method.
 */
function dm_core_init() {
	// Modules are loaded here as they are built.
}
add_action( 'plugins_loaded', 'dm_core_init' );
