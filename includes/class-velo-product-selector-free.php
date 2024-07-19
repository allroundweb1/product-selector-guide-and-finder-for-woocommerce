<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class Velo_Product_Selector_Free
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     */
    public function __construct()
    {
        if (defined('VELO_PRODUCT_SELECTOR_FREE_VERSION')) {
            $this->version = VELO_PRODUCT_SELECTOR_FREE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'product-selector-guide-and-finder-for-woocommerce';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-velo-product-selector-free-loader.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-velo-product-selector-free-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-velo-product-selector-free-admin-backend-pages.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-velo-product-selector-free-public.php';

        $this->loader = new Velo_Product_Selector_Free_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks()
    {
        // ADMIN CLASSES
        $plugin_admin = new Velo_Product_Selector_Free_Admin($this->get_plugin_name(), $this->get_version());
        $plugin_backend_pages = new Velo_Product_Selector_Free_Admin_Backend_Pages($this->get_plugin_name(), $this->get_version());

        // LOAD STYLE AND SCRIPTS
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // CREATE POST TYPE
        $this->loader->add_action('init', $plugin_admin, 'create_velo_selectors_post_type');

        // PLUGIN BACKEND
        $this->loader->add_action('admin_menu', $plugin_backend_pages, 'velo_plugin_create_menu');

        // WP AJAX
        $this->loader->add_action('wp_ajax_velo_product_selector_select_and_create', $plugin_admin, 'velo_ajax_product_selector_select_and_create');
        $this->loader->add_action('wp_ajax_velo_create_selector', $plugin_admin, 'velo_ajax_create_selector');
        $this->loader->add_action('wp_ajax_velo_get_form_to_create_selector', $plugin_admin, 'velo_ajax_get_form_to_create_selector');
        $this->loader->add_action('wp_ajax_velo_get_single_product_selector_editor', $plugin_admin, 'velo_ajax_get_single_product_selector_editor');
        $this->loader->add_action('wp_ajax_velo_search_posts_callback', $plugin_admin, 'velo_ajax_search_posts_callback');
        $this->loader->add_action('wp_ajax_velo_save_edited_product_selector', $plugin_admin, 'velo_ajax_save_edited_product_selector');
        $this->loader->add_action('wp_ajax_velo_delete_product_selector', $plugin_admin, 'velo_ajax_delete_product_selector');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    private function define_public_hooks()
    {
        // PUBLIC CLASS
        $plugin_public = new Velo_Product_Selector_Free_Public($this->get_plugin_name(), $this->get_version());

        // LOAD STYLES AND SCRIPTS
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // SHORTCODES
        $this->loader->add_shortcode('velo_show_product_selector', $plugin_public, 'velo_shortcode_show_product_selector');

        // PAGE TEMPLATE
        $this->loader->add_filter('template_include', $plugin_public, 'velo_templates', 9999, 1);

        // AJAX
        $this->loader->add_action('wp_ajax_velo_get_product_selector_data', $plugin_public, 'velo_ajax_get_product_selector_data');
        $this->loader->add_action('wp_ajax_nopriv_velo_get_product_selector_data', $plugin_public, 'velo_ajax_get_product_selector_data');
        $this->loader->add_action('wp_ajax_velo_get_html_data_for_final_item', $plugin_public, 'velo_ajax_get_html_data_for_final_item');
        $this->loader->add_action('wp_ajax_nopriv_velo_get_html_data_for_final_item', $plugin_public, 'velo_ajax_get_html_data_for_final_item');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
