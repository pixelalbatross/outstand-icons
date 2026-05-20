<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Outstand Icons
 * Description:       A toolkit for working with icons in the block editor.
 * Plugin URI:        https://outstand.site/?utm_source=wp-plugins&utm_medium=outstand-icons&utm_campaign=plugin-uri
 * Requires at least: 7.0
 * Requires PHP:      8.2
 * Version:           1.0.0
 * Author:            Outstand
 * Author URI:        https://outstand.site/?utm_source=wp-plugins&utm_medium=outstand-icons&utm_campaign=author-uri
 * License:           GPL-3.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-3.0-or-later.html
 * Update URI:        https://outstand.site/
 * GitHub Plugin URI: https://github.com/pixelalbatross/outstand-icons
 * Text Domain:       outstand-icons
 * Domain Path:       /languages
 */

namespace Outstand\WP\Icons;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'OS_ICONS_VERSION', '1.0.0' );
define( 'OS_ICONS_BASENAME', plugin_basename( __FILE__ ) );
define( 'OS_ICONS_URL', plugin_dir_url( __FILE__ ) );
define( 'OS_ICONS_PATH', plugin_dir_path( __FILE__ ) );
define( 'OS_ICONS_DIST_URL', OS_ICONS_URL . 'build/' );
define( 'OS_ICONS_DIST_PATH', OS_ICONS_PATH . 'build/' );

if ( ! file_exists( OS_ICONS_PATH . 'vendor/autoload.php' ) ) {
	return;
}

require_once OS_ICONS_PATH . 'vendor/autoload.php';

PucFactory::buildUpdateChecker(
	'https://github.com/pixelalbatross/outstand-icons/',
	__FILE__,
	'outstand-icons'
)->setBranch( 'main' );

/**
 * Load the plugin.
 */
add_action(
	'plugins_loaded',
	function () {
		$plugin = Plugin::get_instance();
		$plugin->enable();
	}
);
