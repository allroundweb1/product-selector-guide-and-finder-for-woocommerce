<?php

/**
 * VelocityPlugins
 *
 * @wordpress-plugin
 * Plugin Name:       Product selector guide and finder for WooCommerce
 * Plugin URI:        https://velocityplugins.com
 * Description:       Expand the capabilities of your WordPress site with Velocity Plugins. With this unique product selector tool you can help your users even better to get to the right product! This can lead to more sales and a better user experience.
 * Version:           1.0.6
 * Author:            VelocityPlugins
 * Author URI:        https://profiles.wordpress.org/velocityplugins/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       product-selector-guide-and-finder-for-woocommerce
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Currently plugin version
 */
define( 'VELO_PRODUCT_SELECTOR_FREE_VERSION', '1.0.6' );

/**
 * Plugin root file
 */
define('VELO_PLUGIN_FILE', __FILE__);

/**
 * Plugin base
 */
define('VELO_PLUGIN_BASE', plugin_basename(VELO_PLUGIN_FILE));

/**
 * Plugin Folder Path
 */
define('VELO_PLUGIN_DIR', plugin_dir_path(VELO_PLUGIN_FILE));

/**
 * Plugin Folder URL
 */
define('VELO_PLUGIN_URL', plugin_dir_url(VELO_PLUGIN_FILE));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-velo-product-selector-free-activator.php
 */
function velo_activate_velo_product_selector_free() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-velo-product-selector-free-activator.php';
	Velo_Product_Selector_Free_Activator::activate();
}

register_activation_hook( __FILE__, 'velo_activate_velo_product_selector_free' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-velo-product-selector-free.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function velo_run_velo_product_selector_free() {

	$plugin = new Velo_Product_Selector_Free();
	$plugin->run();

}
velo_run_velo_product_selector_free();
