<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.searchstax.com
 * @since             0.1.0
 * @package           Searchstax_Serverless
 *
 * @wordpress-plugin
 * Plugin Name:       Searchstax Serverless
 * Plugin URI:        https://www.searchstax.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           0.1.0
 * Author:            SearchStax
 * Author URI:        https://www.searchstax.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       searchstax-serverless
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEARCHSTAX_SERVERLESS_VERSION', '0.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_searchstax_serverless() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-searchstax-serverless-activator.php';
	Searchstax_Serverless_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_searchstax_serverless() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-searchstax-serverless-deactivator.php';
	Searchstax_Serverless_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_searchstax_serverless' );
register_deactivation_hook( __FILE__, 'deactivate_searchstax_serverless' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-searchstax-serverless.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_searchstax_serverless() {

	$plugin = new Searchstax_Serverless();
	$plugin->run();

}
run_searchstax_serverless();
