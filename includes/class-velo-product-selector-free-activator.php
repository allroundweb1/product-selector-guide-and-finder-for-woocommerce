<?php

/**
 * Fired during plugin activation
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Velo_Product_Selector_Free_Activator
{
    public static function activate()
    {
        // Check if the other plugin is active
        if (is_plugin_active('product-selector-guide-and-finder-for-woocommerce-pro/product-selector-guide-and-finder-for-woocommerce-pro.php')) {
            // Display an error message
            wp_die('It seems that you have already activated "Product selector guide and finder for WooCommerce PRO". You cannot activate the free version as long as the PRO version is active.');
        }
    }
}
