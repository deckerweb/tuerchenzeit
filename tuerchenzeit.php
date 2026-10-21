<?php
/**
 * Plugin Name: Türchenzeit
 * Plugin URI: https://github.com/deckerweb/tuerchenzeit
 * Description: A thoughtfully designed Advent calendar for WordPress, with 24 daily doors, your own content, and an optional Advent landing page.
 * Version: 1.0.0
 * Requires at least: 6.9
 * Requires PHP: 8.3
 * Author: David Decker – DECKERWEB
 * Author URI: https://github.com/deckerweb
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tuerchenzeit
 * Domain Path: /languages/
 * Update URI: https://github.com/deckerweb/tuerchenzeit
 * GitHub Plugin URI: https://github.com/deckerweb/tuerchenzeit
 *
 * Copyright © 2026 David Decker – DECKERWEB.
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Deckerweb\Tuerchenzeit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VERSION = '1.0.0';
const FILE = __FILE__;

require_once __DIR__ . '/includes/deckerweb-plugin-library/bootstrap.php';
\deckerweb_library_register_v2( __FILE__, array(), __DIR__ . '/includes/deckerweb-plugin-library' );
require_once __DIR__ . '/includes/class-branding.php';
require_once __DIR__ . '/includes/class-updates.php';
Updates::hooks();

require_once __DIR__ . '/includes/class-presentation.php';
require_once __DIR__ . '/includes/class-calendar.php';
require_once __DIR__ . '/includes/class-season.php';
require_once __DIR__ . '/includes/class-protection.php';
require_once __DIR__ . '/includes/class-editor-extras.php';
require_once __DIR__ . '/includes/class-fonts.php';
require_once __DIR__ . '/includes/class-transfer.php';
require_once __DIR__ . '/includes/class-media-import.php';
require_once __DIR__ . '/includes/class-guide.php';
require_once __DIR__ . '/includes/class-preparation.php';
Preparation::hooks();
Protection::hooks();
Editor_Extras::hooks();
Fonts::hooks();
require_once __DIR__ . '/includes/class-polish.php';
Polish::hooks();
require_once __DIR__ . '/includes/class-diagnostics.php';
Diagnostics::hooks();
Transfer::hooks();
Guide::hooks();
require_once __DIR__ . '/includes/class-content.php';
require_once __DIR__ . '/includes/class-editor.php';
require_once __DIR__ . '/includes/class-landing.php';
Editor::hooks();
Landing::hooks();
require_once __DIR__ . '/includes/class-frontend.php';

/** Initialize the host text domain and private content types. @return void */
add_action( 'init', static function (): void {
	load_plugin_textdomain( 'tuerchenzeit', false, dirname( plugin_basename( FILE ) ) . '/languages' );
	Calendar::register();
} );
Frontend::hooks();

if ( is_admin() ) {
	require_once __DIR__ . '/includes/class-admin.php';
	Admin::hooks();
}
