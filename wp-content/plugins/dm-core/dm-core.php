<?php
/**
 * Plugin Name: DM Core
 * Description: Core features for the Renuga Novels website: stories and chapters, engagement (comments, ratings, bookmarks), view stats, moderation and push notifications.
 * Version:     0.5.0
 * Author:      Renuga Novels dev team
 * Text Domain: dm-core
 *
 * @package dm-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DM_CORE_VERSION', '0.5.0' );
define( 'DM_CORE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Modules (one file or folder each under includes/). See docs/DM_CORE_SPEC.md.
 *  - post-types:    stories and chapters                (built)
 *  - engagement:    comments, ratings, bookmarks        (planned)
 *  - stats:         views per story and chapter         (planned)
 *  - moderation:    reader approval, spam blocking      (planned)
 *  - notifications: web push                            (planned)
 */
require_once DM_CORE_PATH . 'includes/post-types.php';
require_once DM_CORE_PATH . 'includes/roles.php';
require_once DM_CORE_PATH . 'includes/auth.php';
require_once DM_CORE_PATH . 'includes/announcements.php';
require_once DM_CORE_PATH . 'includes/stats.php';
require_once DM_CORE_PATH . 'includes/required-pages.php';
require_once DM_CORE_PATH . 'includes/sample-data.php'; // development only, remove before launch

/** Refresh permalinks once on activation so /story/ and /chapter/ work. */
function dm_core_activate() {
	dm_register_post_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'dm_core_activate' );
