<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://velocityplugins.com
 * @since      1.0.0
 *
 * @package    Velo_Product_Selector_Free
 * @subpackage Velo_Product_Selector_Free/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Velo_Product_Selector_Free
 * @subpackage Velo_Product_Selector_Free/public
 * @author     VelocityPlugins <info@velocityplugins.com>
 */
class Velo_Product_Selector_Free_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/velo-product-selector-free-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/velo-product-selector-free-public.js', array('jquery'), $this->version, false);

        // ADD VARIABLE FOR AJAX TO FRONTEND
        $variable_array = array();
        $variable_array['ajax_url'] = admin_url('admin-ajax.php');
        $variable_array['velo_frontend_ajax_nonce'] = wp_create_nonce('velo_frontend_ajax_nonce');
        $variable_array['velo_back_text'] = __('Back', 'velo-product-selector');
        wp_localize_script($this->plugin_name, 'velo_product_selector', $variable_array);
    }

    /**
     * Product selector shortcode
     */
    public function velo_shortcode_show_product_selector($atts = array())
    {
        // Extract the 'id' from the shortcode attributes
        $atts = shortcode_atts(array(
            'id' => '0',
        ), $atts, 'velo_show_product_selector');

        // Setup the return variable
        $return_html = '';

        // Start the output buffer
        ob_start();

        // Check if the selector exists
        $selector = get_post((int)$atts['id']);

        // Check if the selector is of the post type 'velo_selectors'
        if ($selector && $selector->post_type == 'velo_selectors') {
            // Get saved selector data
            $sortable_json_data_meta = get_post_meta($atts['id'], 'velo_product_selector_data', true);

            // Check if we got any data
            if (empty($sortable_json_data_meta)) {
                return 'The product selector is empty. Please fill in the product selector first.';
            }
        } else {
            // Error if the product selector is not found
            return 'The product selector does not exist. Please check the ID of the product selector.';
        }
?>
        <div class="velo-wrapper" data-id="<?php echo (int)$atts['id']; ?>">
            <div class="velo-loading">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
        <div class="velo-free-version-credits" style="display:block!important;">🚀 Created with the free Velocity Product Selector by <a href="https://velocityplugins.com/" target="_blank">Velocity Plugins</a></div>
<?php
        // Get the contents of the output buffer
        $return_html .= ob_get_contents();
        ob_end_clean();

        // Return the html
        return $return_html;
    }

    // Get frontend data for the product selector
    function velo_ajax_get_product_selector_data()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_frontend_ajax_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required values are set
        if (!isset($_REQUEST['selector_id'])) {
            wp_send_json_error('Not all required values are set.', 400);
        }

        // Get the selector ID and check if the post type is 'velo_selectors'
        $velo_selector_id = (int)$_REQUEST['selector_id'];
        $velo_selector = get_post($velo_selector_id);
        if (!$velo_selector || $velo_selector->post_type != 'velo_selectors') {
            wp_send_json_error('No product selector ID found.', 400);
        }

        // Get the selector data
        $velo_selector_data = get_post_meta($velo_selector_id, 'velo_product_selector_data', true);

        // Check if selector value is not empty
        if (empty($velo_selector_data)) {
            wp_send_json_error('No product selector data found.', 400);
        }

        // Fetch data and create image URL's of the attachment ID's
        array_walk_recursive($velo_selector_data, function (&$item, $key) {
            if ($key == 'image' && !empty($item)) {
                $item = wp_get_attachment_url($item);
            }
        });

        // Setup some return data
        $return_obj = array();
        $return_obj['data'] = $velo_selector_data;

        // Return the data
        wp_send_json_success($return_obj, 200);

        die();
    }

    // Get frontend data for the final item
    function velo_ajax_get_html_data_for_final_item()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_frontend_ajax_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required values are set
        if (empty($_REQUEST['item_value'])) {
            wp_send_json_error('Not all required values are set.', 400);
        }

        // All items
        $item_array = explode(',', $_REQUEST['item_value']);

        // Create OB to get the HTML
        ob_start();

        // Loop trough all items
        $all_products = array();
        $all_product_categories = array();
        $all_pages_and_posts = array();
        $all_other_items = array();
        foreach ($item_array as $item_inarray) {
            $item_exploded = explode('_', $item_inarray);
            if (isset($item_exploded[0]) && isset($item_exploded[1])) {
                $item_id = (int)$item_exploded[0];
                $item_type = $item_exploded[1];

                if ($item_type == 'product') {
                    $all_products[] = $item_id;
                } elseif ($item_type == 'product-cat') {
                    $all_product_categories[] = $item_id;
                } elseif ($item_type == 'page' || $item_type == 'post') {
                    $all_pages_and_posts[] = $item_id;
                } else {
                    // Custom post types
                    $all_other_items[] = $item_id;
                }
            }
        }

        // H2
        echo '<h2>All results</h2>';

        // Open wrapper
        echo '<div class="velo-choices-wrapper">';

        // Products --> Do WooCommerce shortcode to show all products
        if (!empty($all_products)) {
            echo do_shortcode('[products ids="' . implode(',', $all_products) . '" columns="4" limit="30" orderby="post__in"]');
        }

        // Product Categories --> Do WooCommerce shortcode to show all products
        if (!empty($all_product_categories)) {
            echo do_shortcode('[product_categories ids="' . implode(',', $all_product_categories) . '" columns="4" limit="30" orderby="post__in"]');
        }

        // Pages and Posts --> Do loop to create HTML items
        if (!empty($all_pages_and_posts)) {
            $args = array(
                'post_type' => array('page', 'post'),
                'post__in' => $all_pages_and_posts,
                'posts_per_page' => -1,
                'orderby' => 'post__in',
            );
            $pages_and_posts = new WP_Query($args);
            if ($pages_and_posts->have_posts()) {
                while ($pages_and_posts->have_posts()) {
                    $pages_and_posts->the_post();
                    echo '<a href="' . get_permalink(get_the_ID()) . '" target="_self" class="velo-inner-choice final-redirect" data-level="">';
                    echo '<img class="velo-choice-image" src="' . get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') . '" />';
                    echo '<br>';
                    echo get_the_title();
                    echo '</a>';
                }
            }
            wp_reset_postdata();
        }

        // Other items --> Do loop to create HTML items
        if (!empty($all_other_items)) {
            $args = array(
                'post_type' => 'any',
                'post__in' => $all_other_items,
                'posts_per_page' => -1,
                'orderby' => 'post__in',
            );
            $other_items = new WP_Query($args);
            if ($other_items->have_posts()) {
                while ($other_items->have_posts()) {
                    $other_items->the_post();
                    echo '<a href="' . get_permalink(get_the_ID()) . '" target="_self" class="velo-inner-choice final-redirect" data-level="">';
                    echo '<img class="velo-choice-image" src="' . get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') . '" />';
                    echo '<br>';
                    echo get_the_title();
                    echo '</a>';
                }
            }
            wp_reset_postdata();
        }

        // Close wrapper
        echo '</div>';

        // Get the contents of the output buffer
        $return_html .= ob_get_contents();
        ob_end_clean();

        // Setup some return data
        $return_obj = array();
        $return_obj['data'] = $return_html;

        // Return the data
        wp_send_json_success($return_obj, 200);

        die();
    }

    // Get the template to show the product selector
    function velo_templates($template)
    {
        $post_type = 'velo_selectors';
        if (is_singular($post_type) && file_exists(plugin_dir_path(__FILE__) . "templates/single-$post_type.php")) {
            $template = plugin_dir_path(__FILE__) . "templates/single-$post_type.php";
        }
        return $template;
    }
}
