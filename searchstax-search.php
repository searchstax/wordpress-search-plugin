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
 * @since             1.0.0
 * @package           Searchstax_Search
 *
 * @wordpress-plugin
 * Plugin Name:       Searchstax Search
 * Plugin URI:        https://www.searchstax.com
 * Description:       Customized site search for WordPress powered by SearchStax
 * Version:           1.0.0
 * Author:            SearchStax
 * Author URI:        https://www.searchstax.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       searchstax-search
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
define( 'SEARCHSTAX_SEARCH_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_searchstax_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-searchstax-search-activator.php';
	Searchstax_Search_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_searchstax_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-searchstax-search-deactivator.php';
	Searchstax_Search_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_searchstax_search' );
register_deactivation_hook( __FILE__, 'deactivate_searchstax_search' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-searchstax-search.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_searchstax_search() {

	$plugin = new Searchstax_Search();
	$plugin->run();

}
run_searchstax_search();
