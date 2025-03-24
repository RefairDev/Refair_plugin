<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              pixelscodex.com
 * @since             1.0.0
 * @package           Refairplugin
 *
 * @wordpress-plugin
 * Plugin Name:       Extension REFAIR
 * Plugin URI:        pixelscodex.com
 * Description:       Plugin de gestion des sites de récupération (création/consultation/etc)
 * Version:           1.0.0
 * Author:            Thomas Vias
 * Author URI:        pixelscodex.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       refair-plugin
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
define( 'REFAIRPLUGIN_VERSION', '1.0.0' );
define( 'REFAIRPLUGIN_ROOT_FILE', __FILE__ );


// TVI 2024-10-23 SILENT CLASS NAMING WARNING!
if ( ! defined( 'REQUESTS_SILENCE_PSR0_DEPRECATIONS' ) ) {
	define( 'REQUESTS_SILENCE_PSR0_DEPRECATIONS', true );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-refairplugin-activator.php
 */
function activate_refairplugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-refairplugin-activator.php';
	Refairplugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-refairplugin-deactivator.php
 */
function deactivate_refairplugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-refairplugin-deactivator.php';
	Refairplugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_refairplugin' );
register_deactivation_hook( __FILE__, 'deactivate_refairplugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-refairplugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_refairplugin() {

	$plugin = new Refairplugin();
	$plugin->run();
}

add_action( 'woocommerce_loaded', 'run_refairplugin' );
