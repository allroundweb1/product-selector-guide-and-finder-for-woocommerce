<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://velocityplugins.com
 * @since      1.0.0
 *
 * @package    Velo_Product_Selector_Free
 * @subpackage Velo_Product_Selector_Free/admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Velo_Product_Selector_Free
 * @subpackage Velo_Product_Selector_Free/admin
 * @author     VelocityPlugins <info@velocityplugins.com>
 */
class Velo_Product_Selector_Free_Admin
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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        // CSS for the whole admin area
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/velo-product-selector-free-admin.css', array(), $this->version, 'all');

        // CSS for our admin pages
        $screen = get_current_screen();
        if (str_contains($screen->id, 'velo-product-selector')) {
            // Select2 library for autocomplete multi-select
            wp_enqueue_style($this->plugin_name . '-select2', plugin_dir_url(__FILE__) . 'library/select2-4.0.13/select2.css', array(), '4.0.13', 'all');

            // Enqueue uikit CSS file
            wp_enqueue_style($this->plugin_name . '-uikit', plugin_dir_url(__FILE__) . 'library/uikit-3.19.1/css/uikit.min.css', array(), '3.19.1', 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        // JS for our admin pages
        $screen = get_current_screen();
        if (str_contains($screen->id, 'velo-product-selector')) {
            // Enqueue WordPress media scripts
            wp_enqueue_media();

            // Use the jQuery UI autocompletes, that comes with WordPress default
            wp_enqueue_script('jquery-ui-autocomplete');

            // Select2 library for autocomplete multi-select
            wp_enqueue_script($this->plugin_name . '-select2', plugin_dir_url(__FILE__) . 'library/select2-4.0.13/select2.js', array('jquery'), '4.0.13', false);

            // Enqueue uikit JS files
            wp_enqueue_script($this->plugin_name . '-uikit', plugin_dir_url(__FILE__) . 'library/uikit-3.19.1/js/uikit.min.js', array('jquery'), '3.19.1', false);
            wp_enqueue_script($this->plugin_name . '-uikit-icons', plugin_dir_url(__FILE__) . 'library/uikit-3.19.1/js/uikit-icons.min.js', array('jquery', $this->plugin_name . '-uikit'), '3.19.1', false);

            // Enqueue sortable JS file
            wp_enqueue_script($this->plugin_name . '-sortable', plugin_dir_url(__FILE__) . 'library/sortable-1.15.2/js/sortable.min.js', array('jquery'), '1.15.2', false);
            wp_enqueue_script($this->plugin_name . '-jquery-sortable', plugin_dir_url(__FILE__) . 'library/sortable-1.15.2/js/jquery-sortable.js', array('jquery', $this->plugin_name . '-sortable'), '1.15.2', false);

            // VELO admin pages JS
            wp_enqueue_script($this->plugin_name . '-admin-pages', plugin_dir_url(__FILE__) . 'js/velo-product-selector-free-admin.js', array('jquery', 'jquery-ui-autocomplete', $this->plugin_name . '-select2', $this->plugin_name . '-sortable', $this->plugin_name . '-jquery-sortable', $this->plugin_name . '-uikit', $this->plugin_name . '-uikit-icons'), $this->version, false);

            // Create a JS object 'velo_product_selector' for PHP variable that we want to pass to the JS
            $variable_array = array();
            $variable_array['ajax_url'] = admin_url('admin-ajax.php');
            $variable_array['ajax_settings_nonce'] = wp_create_nonce('velo_settings_nonce');
            wp_localize_script($this->plugin_name . '-admin-pages', 'velo_product_selector', $variable_array);
        }
    }

    // CREATE CUSTOM POST TYPE
    function create_velo_selectors_post_type()
    {
        $args = array(
            'public'              => true,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => false,
            'exclude_from_search' => true,
            'capability_type'     => 'post',
            'has_archive'         => false,
            'labels'              => array(
                'name'               => __('Velo Selectors', 'velo-product-selector-free'),
                'singular_name'      => __('Velo Selector', 'velo-product-selector-free'),
            ),
            'supports' => array('title', 'editor', 'custom-fields'),
        );
        register_post_type('velo_selectors', $args);
    }

    // Function to get the product selector select (dropdown) and buttons to create a new product selector
    function velo_ajax_product_selector_select_and_create()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Create OB to get the HTML
        ob_start();

        // Setup some return data
        $return_obj = array();
        $return_obj['html'] = '';

        // Get selectors data from our CPT 'velo_selectors'
        $args = array(
            'post_type' => 'velo_selectors',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);

        $selector_options = '';
        if (!empty($query->posts)) {
            foreach ($query->posts as $post) {
                $selector_options .= '<option value="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</option>';
            }
        }

        if (!empty($selector_options)) {
            echo '<select class="uk-select">';
            echo wp_kses($selector_options, array(
                'option' => array(
                    'value' => array(),
                ),
            ));
            echo '</select>';
            echo '<button type="button" class="uk-button uk-button-primary uk-margin-left edit-single-product-selector">Select</button>';
            echo '<span class="uk-margin-left uk-margin-right"> or </span>';
            echo '<button type="button" class="uk-button uk-button-default create-product-selector-pup-up">Create new selector</button>';
        } else {
            echo '<button type="button" class="uk-button uk-button-primary create-product-selector-pup-up">Create your first selector 🚀</button>';
        }

        // Close the OB and get the data
        $return_obj['html'] .= ob_get_contents();
        ob_end_clean();

        // Return the data
        wp_send_json_success($return_obj, 200);

        die();
    }

    // Function to create a URL slug by string
    private function velo_create_url_slug_by_string($string)
    {
        // Remove HTML tags if found
        $string = strip_tags($string);

        // Replace special characters with white space
        $string = preg_replace('/[^A-Za-z0-9-]+/', ' ', $string);

        // Trim White Spaces and both sides
        $string = trim($string);

        // Replace whitespaces with Hyphen (-)
        $string = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);

        // Convert final string to lowercase
        $slug = strtolower($string);

        return $slug;
    }

    // Function to create a new product selector
    function velo_ajax_create_selector()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required fields are set
        if (!isset($_REQUEST['name'])) {
            wp_send_json_error('Not all required fields are set.', 400);
        }

        // Create OB to get the HTML
        ob_start();

        // Setup some return data
        $return_obj = array();
        $return_obj['html'] = '';

        $name = sanitize_text_field(strip_tags($_REQUEST['name'])); // Fetch name from request
        $slug = sanitize_title($this->velo_create_url_slug_by_string($_REQUEST['name']));

        // Check if slug already exists
        if (get_page_by_path($slug, OBJECT, 'velo_selectors')) {
            wp_send_json_error('Slug already exists. <br><button type="button" class="uk-button uk-button-default uk-margin-top create-product-selector-pup-up">Try again</button>', 400);
        }

        $new_post = array(
            'post_title' => $name,
            'post_name' => $slug,
            'post_type' => 'velo_selectors',
            'post_status' => 'publish',
        );
        $post_id = wp_insert_post($new_post);

        if (!$post_id) {
            wp_send_json_error('Error creating selector.', 400);
        } else {
            echo 'Selector created! 🚀 Reloading... <div uk-spinner></div>';
        }

        // Close the OB and get the data
        $return_obj['html'] .= ob_get_contents();
        ob_end_clean();

        // Return the data
        wp_send_json_success($return_obj, 200);

        die();
    }

    // Function to get the pop-up form to create a new selector
    function velo_ajax_get_form_to_create_selector()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Create OB to get the HTML
        ob_start();

        // Setup some return data
        $return_obj = array();
        $return_obj['html'] = '';

?>
        <div class="uk-form-horizontal">
            <div class="uk-margin">
                <label class="uk-form-label" for="velo-selector-name">Product Selector Name</label>
                <div class="uk-form-controls">
                    <input class="uk-input" id="velo-selector-name" name="velo-selector-name" type="text" placeholder="Name">
                </div>
            </div>
            <button type="button" class="uk-button uk-button-primary create-product-selector">Create product selector</button>
        </div>
    <?php

        // Close the OB and get the data
        $return_obj['html'] .= ob_get_contents();
        ob_end_clean();

        // Return the data
        wp_send_json_success($return_obj, 200);

        die();
    }

    // Function to the product selector editor
    function velo_ajax_get_single_product_selector_editor()
    {
        // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required fields are set
        if (!isset($_REQUEST['product_selector_id'])) {
            wp_send_json_error('Not all required fields are set.', 400);
        }

        $product_selector_id = filter_input(INPUT_POST, 'product_selector_id', FILTER_SANITIZE_NUMBER_INT);
        if (empty($product_selector_id)) wp_send_json_error('This item does not exist.', 400);

        $post = get_post($product_selector_id);
        if ($post === null) {
            wp_send_json_error('This item does not exist.', 400);
        }

        $post_type = get_post_type($product_selector_id);
        $post_id = $post->ID ?? $product_selector_id;
        $post_title = $post->post_title ?? '';

        if ($post_type !== 'velo_selectors') {
            wp_send_json_error('This item does not exist.', 400);
        }

	    // Check for previous sortable list content, otherwise show a placeholder
	    $sortable_data_found = false;
	    $sortable_data = '<div class="placeholder-sortable-list">This product selector is empty. Create your first question to start 🎉</div>';
	    $sortable_json_data_meta = get_post_meta($post_id, 'velo_product_selector_data', true);
	    if (!empty($sortable_json_data_meta)) {
		    // Do logic if there is a sortable list
		    $sortable_data_found = true;
		    $sortable_data = $this->velo_get_sortablejs_html($sortable_json_data_meta);
		    // $sortable_data = print_r($sortable_json_data_meta, true);
	    }

        // Create OB to get the HTML
        ob_start();

        // Setup some return data
        $return_obj = array();
        $return_obj['html'] = '';
    ?>
        <div class="uk-section uk-section-muted uk-margin-top">
            <div class="uk-container uk-container-expand">

                <div uk-grid>
                    <div class="uk-width-expand@m uk-margin-right">
                        <h3>Edit product selector "<?php echo esc_html($post_title); ?>"</h3>
                    </div>
                    <div class="uk-width-1-2@m uk-padding-remove-left uk-text-right velo-shortcode-preview-wrapper">
                        <span class="velo-mini-text-shortcode">Shortcode: </span>
                        <div class="velo-shortcode-preview"><span class="velo-copy-success">Shortcode copied to clipboard</span><span class="velo-pure-shortcode">[velo_show_product_selector id="<?php echo esc_html($post_id); ?>"]</span><span uk-icon="copy"></span></div>
                    </div>
                </div>

                <div>
                    <?php if (!$sortable_data_found) { ?>
                        <div class="uk-grid-match uk-child-width-expand@m velo-create-first-question-wrapper" uk-grid>
                            <div>
                                <label for="velo-question-field">Create a question</label>
                                <div class="uk-text-center" uk-grid>
                                    <div class="uk-width-expand@m uk-margin-right">
                                        <input class="uk-input uk-margin-bottom" type="text" placeholder="Question...?" aria-label="" id="velo-question-field">
                                    </div>
                                    <div class="uk-width-1-4@m uk-padding-remove-left">
                                        <button type="button" class="uk-button uk-button-primary create-question-button">Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="velo-product-selector-select">
                        <div id="velo-sortable-list" data-id="<?php echo esc_html($post_id); ?>" class="velo-sortable-list uk-margin-medium-bottom">
                            <?php echo wp_kses($sortable_data, array(
                                'div' => array(
                                    'class' => array(),
                                    'data-title' => array(),
                                    'data-answer' => array(),
                                    'data-image' => array(),
                                    'data-type' => array(),
                                ),
                                'span' => array(
                                    'class' => array(),
                                    'uk-icon' => array(),
                                ),
                            )); ?>
                        </div>
                        <button class="uk-button velo-save-edited-product-selector uk-margin-right <?php echo !$sortable_data_found ? 'velo-display-none' : ''; ?>">Save</button>
                        <button class="uk-button uk-button-danger velo-delete-edited-product-selector uk-margin-right">Delete</button>
                        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank" class="uk-button uk-button-primary">Preview (save selector first)</a>
                    </div>

                    <div id="confirmation-editor-item-remove-modal" uk-modal>
                        <div class="uk-modal-dialog uk-modal-body">
                            <h2 class="uk-modal-title uk-text-center">Confirmation</h2>
                            <p>Are you sure you want to remove this item?</p>
                            <p>(It also removes all sub-items contained in this element, if any)</p>
                            <div class="uk-modal-footer uk-text-right">
                                <button class="uk-button uk-button-default uk-modal-close">Cancel</button>
                                <button class="uk-button uk-button-danger" id="confirm-editor-item-remove-btn">Remove</button>
                            </div>
                        </div>
                    </div>

                    <div id="too-many-items-modal" uk-modal>
                        <div class="uk-modal-dialog uk-modal-body">
                            <h2 class="uk-modal-title uk-text-center">Too many items</h2>
                            <p>The maximum amount of items is 20. If you want to add more items, you can get the premium version.<br><a href="https://velocityplugins.com/" class="velo-unlock-premium-button" target="_blank">Get premium plugin <span class="uk-icon-link" uk-icon="star"></span></a></p>
                            <div class="uk-modal-footer uk-text-right">
                                <button class="uk-button uk-button-default uk-modal-close">Close</button>
                            </div>
                        </div>
                    </div>

                    <div id="editor-item-edit-modal" uk-modal>
                        <div class="uk-modal-dialog uk-modal-body">
                            <h2 class="uk-modal-title uk-text-center">Item</h2>
                            <div class="velo-choose-answer-or-final-item">
                                <p class="uk-text-center uk-padding-small">What type of item do you want to create?</p>
                                <div class="uk-text-center" uk-grid>
                                    <div class="uk-width-expand@m">
                                        <button type="button" class="uk-button uk-button-primary velo-choose-in-pop-up-answer-question">Answer / Question</button>
                                    </div>
                                </div>
                                <div class="uk-text-center uk-padding-small">
                                    OR
                                </div>
                                <div class="uk-text-center" uk-grid>
                                    <div class="uk-width-expand@m">
                                        <button type="button" class="uk-button uk-button-primary velo-choose-in-pop-up-final-item">Final item (product, redirect, page, etc.)</button>
                                    </div>
                                </div>
                            </div>

                            <div class="velo-all-edit-and-add-fields">
                                <!-- Answer question -->
                                <div class="velo-create-answer-question">
                                    <label for="velo-edit-answer-field">Answer:</label>
                                    <input class="uk-input uk-margin-bottom" type="text" placeholder="Answer..." aria-label="" value="" id="velo-edit-answer-field">
                                    <label for="velo-edit-text-field">Question:</label>
                                    <input class="uk-input uk-margin-bottom" type="text" placeholder="Question...?" aria-label="" value="" id="velo-edit-text-field">
                                </div>

                                <!-- Final item (product, product cat, post or page) -->
                                <div class="velo-add-final-step-posts">
                                    <label for="vvelo-autocomplete-answer-field">Answer</label>
                                    <input class="uk-input uk-margin-bottom" type="text" placeholder="Answer..." aria-label="" id="velo-autocomplete-answer-field">

                                    <label for="velo-autocomplete-search-field">Add final step item: product, product category, post, page (or redirect URL, <span class="velo-switch-to-url-input-final">click here</span>)</label>
                                    <select class="uk-input uk-margin-bottom" id="velo-autocomplete-search-field"></select>
                                </div>

                                <!-- Final item (Redirect answer) -->
                                <div class="velo-add-final-step-redirect-url">
                                    <label for="velo-redirect-answer-field">Answer</label>
                                    <input class="uk-input uk-margin-bottom" type="text" placeholder="Answer..." aria-label="" id="velo-redirect-answer-field">

                                    <label for="velo-redirect-url-field">Add final step item: redirect URL (or <span class="velo-switch-back-final">switch back</span>)</label>
                                    <input class="uk-input uk-margin-bottom" type="text" placeholder="https://redirect-url..." aria-label="" id="velo-redirect-url-field">
                                </div>

                                <!-- Image upload/preview -->
                                <div class="velo-media-preview-and-upload">
                                    <div id="velo-preview-image"></div>
                                    <input type="button" class="uk-button" value="Select a image" id="velo_media_manager" />
                                </div>

                                <!-- All three add buttons -->
                                <div class="velo-all-add-new-buttons">
                                    <!-- Button Create Question (ADD NEW ITEM) -->
                                    <button type="button" class="uk-button uk-button-primary create-question-answer-button uk-margin-top uk-margin-bottom">Create Answer/Question</button>

                                    <!-- Button Create Final Item (product, product cat, post or page) (ADD NEW ITEM) -->
                                    <button type="button" class="uk-button uk-button-primary create-velo-autocomplete-value-button">Add</button>

                                    <!-- Button Create Final Item (Redirect answer) (ADD NEW ITEM) -->
                                    <button type="button" class="uk-button uk-button-primary create-redirect-url-button">Add</button>
                                </div>
                            </div>

                            <!-- All hidden data -->
                            <input type="hidden" name="velo_image_id" id="velo_image_id" value="" />
                            <input type="hidden" name="velo_element_data_id" id="velo_element_data_id" value="" />
                            <input type="hidden" name="velo_element_type" id="velo_element_type" value="" />
                            <input type="hidden" name="velo_new_or_edit" id="velo_new_or_edit" value="" />

                            <!-- Default buttons (FOR EDITING EXISTING ITEMS) -->
                            <div class="uk-modal-footer uk-text-right uk-margin-top">
                                <button class="uk-button uk-button-default" id="confirm-editor-item-edit-save-btn">Save</button>
                                <button class="uk-button uk-button-danger uk-modal-close">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <div id="confirmation-full-product-selector-remove-modal" uk-modal>
                        <div class="uk-modal-dialog uk-modal-body">
                            <h2 class="uk-modal-title uk-text-center">Confirmation</h2>
                            <p>Are you sure you want to delete the whole product selector?</p>
                            <p>(You can't undo this!)</p>
                            <div class="uk-modal-footer uk-text-right">
                                <button class="uk-button uk-button-default uk-modal-close">Cancel</button>
                                <button class="uk-button uk-button-danger" id="confirm-full-product-selector-remove-btn">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
<?php
        // Close the OB and get the data
        $return_obj['html'] .= ob_get_contents();
        ob_end_clean();

        // Return the data
        wp_send_json_success($return_obj, 200);

        die();
    }

    // Private function to get the HTML for the SortableJS
    private function velo_get_sortablejs_html($data_array)
    {
        // Return string
        $return_string = '';

        // Create OB to get the HTML
        ob_start();

        // Check if we got a valid array
        if (is_array($data_array) && !empty($data_array)) {
            foreach ($data_array as $key => $data_row) {
                if (!empty($data_row['nestedData'])) {

                    // Type & image fallback
                    $data_row['type'] = $data_row['type'] ?? 'nested';
                    $data_row['image'] = is_int(intval($data_row['image'])) ? $data_row['image'] : '';
                    $data_row['answer'] = $data_row['answer'] ?? '';

                    if ($data_row['type'] === 'nested-question') {
                        // Main question (begin question)
	                    printf(
		                    '<div class="velo-nested-wrapper" data-title="%s" data-answer="%s" data-image="%s" data-type="%s"><span class="item-answer"><strong>Answer:</strong> %s</span> | <span class="item-title"><strong>Question:</strong> %s</span> <span class="uk-icon-link velo-add-sub-item-product-editor" uk-icon="plus-circle"></span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-edit-item-product-editor" uk-icon="file-edit"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span>',
		                    esc_html($data_row['text']),
		                    esc_html($data_row['answer']),
		                    esc_attr($data_row['image']),
		                    esc_attr($data_row['type']),
		                    esc_html($data_row['answer']),
		                    esc_html($data_row['text'])
	                    );
                    } else {
                        // Nested question
                        printf(
                            '<div class="velo-nested-wrapper" data-title="%s" data-answer="%s" data-image="%s" data-type="%s"><span class="item-answer"><strong>Answer:</strong> %s</span> | <span class="item-title"><strong>Value:</strong> %s</span> <span class="uk-icon-link velo-add-sub-item-product-editor" uk-icon="plus-circle"></span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-edit-item-product-editor" uk-icon="file-edit"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span>',
                            esc_html($data_row['text']),
                            esc_html($data_row['answer']),
	                        esc_attr($data_row['image']),
                            esc_attr($data_row['type']),
                            esc_html($data_row['answer']),
                            esc_html($data_row['text'])
                        );
                    }

                    echo '<div class="velo-nested-sortable">';
                    echo $this->velo_get_sortablejs_html($data_row['nestedData']);
                    echo '</div>';
                    echo '</div>';
                } elseif (isset($data_row['text'])) {
                    // Type fallback
                    $data_row['type'] = $data_row['type'] ?? 'final-value';
                    $data_row['image'] = $data_row['image'] ?? '';
                    $data_row['answer'] = $data_row['answer'] ?? '';

                    if ($data_row['type'] === 'nested') {
                        // Nested question
                        printf(
                            '<div class="velo-nested-wrapper" data-title="%s" data-answer="%s" data-image="%s" data-type="%s"><span class="item-answer"></span><span class="item-title"><strong>Question:</strong> %s</span> <span class="uk-icon-link velo-add-sub-item-product-editor" uk-icon="plus-circle"></span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-edit-item-product-editor" uk-icon="file-edit"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span>',
                            esc_html($data_row['text']),
                            esc_html($data_row['answer']),
	                        esc_attr($data_row['image']),
                            esc_attr($data_row['type']),
                            esc_html($data_row['text'])
                        );

                        echo '<div class="velo-nested-sortable">';
                        echo '</div>';
                    } elseif ($data_row['type'] === 'nested-question') {
                        // Main question (begin question)
                        printf(
                            '<div class="velo-nested-wrapper" data-title="%s" data-answer="%s" data-image="%s" data-type="%s"><span class="item-answer"></span><span class="item-title"><strong>Question:</strong> %s</span> <span class="uk-icon-link velo-add-sub-item-product-editor" uk-icon="plus-circle"></span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-edit-item-product-editor" uk-icon="file-edit"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span>',
                            esc_html($data_row['text']),
                            esc_html($data_row['answer']),
	                        esc_attr($data_row['image']),
                            esc_attr($data_row['type']),
                            esc_html($data_row['text'])
                        );

                        echo '<div class="velo-nested-sortable">';
                        echo '</div>';
                    } elseif ($data_row['type'] === 'final-redirect') {
                        // Final redirect
                        printf(
                            '<div class="velo-nested-wrapper" data-title="%s" data-answer="%s" data-image="%s" data-type="%s"><span class="item-answer"><strong>Answer:</strong> %s</span> | <span class="item-title"><strong>Redirect:</strong> %s</span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span>',
                            esc_html($data_row['text']),
                            esc_html($data_row['answer']),
	                        esc_attr($data_row['image']),
                            esc_attr($data_row['type']),
                            esc_html($data_row['answer']),
                            esc_html($data_row['text'])
                        );
                    } else {
                        // Final value
                        printf(
                            '<div class="velo-nested-wrapper" data-title="%s" data-answer="%s" data-image="%s" data-type="%s"><span class="item-answer"><strong>Answer:</strong> %s</span> | <span class="item-title"><strong>Value:</strong> %s</span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span>',
                            esc_html($data_row['text']),
                            esc_html($data_row['answer']),
	                        esc_attr($data_row['image']),
                            esc_attr($data_row['type']),
                            esc_html($data_row['answer']),
                            esc_html($data_row['text'])
                        );
                    }
                    echo '</div>';
                }
            }
        }

        // Close the OB and get the data
        $return_string .= ob_get_contents();
        ob_end_clean();

        // fallback
        return $return_string;
    }

    // Search autocomplete callback
    function velo_ajax_search_posts_callback()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required fields are set
        if (!isset($_REQUEST['query'])) {
            wp_send_json_error('Not all required fields are set.', 400);
        }

	    $search_query = filter_input(INPUT_POST, 'query', FILTER_SANITIZE_STRING);
	    $results = array();

        if(empty($search_query)) wp_send_json_error('No search query.', 400);

	    $query = new WP_Query(array(
		    's' => $search_query,
		    'post_type' => array('post', 'page', 'product'),
		    'posts_per_page' => -1,
	    ));

	    while ($query->have_posts()) {
		    $query->the_post();

		    $result = array(
			    'title' => wp_kses_post(get_the_title()),
			    'type' => esc_html(get_post_type()),
			    'id' => get_the_ID(),
		    );

		    $results[] = $result;
	    }

	    wp_reset_postdata();
        $tax_query = new WP_Term_Query(array(
	        'taxonomy' => 'product_cat',
	        'field' => 'name',
	        'name__like' => $search_query,
        ));

        foreach ($tax_query->get_terms() as $term) {
            $result = array(
                'title' => $term->name,
                'type' => 'product-cat',
                'id' => $term->term_id,
            );

            $results[] = $result;
        }

        wp_send_json_success($results, 200);
    }

    // Count nested items
    private function countNestedChildren($array)
    {
        $count = 0;
        foreach ($array as $key => $value) {
            if ($key === 'nestedData') {
                $count += count($value); // Count the immediate children
                foreach ($value as $child) {
                    $count += countNestedChildren($child); // Recursively count nested children
                }
            }
        }
        return $count;
    }

    // Function to save the edited product selector
    function velo_ajax_save_edited_product_selector()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required fields are set
        if (!isset($_REQUEST['json_data']) || !isset($_REQUEST['product_selector_id'])) {
            wp_send_json_error('Not all required fields are set.', 400);
        }

	    $product_selector_id = filter_input(INPUT_POST, 'product_selector_id', FILTER_SANITIZE_NUMBER_INT);
	    if (empty($product_selector_id)) wp_send_json_error('This item does not exist.', 400);

	    // Get information about the post
	    $post = get_post($product_selector_id);
	    $post_type = get_post_type($product_selector_id);
	    $post_id = $post->ID ?? $product_selector_id;

        if ($post_type !== 'velo_selectors' && !empty($post)) {
            // Wrong post type
            wp_send_json_error('This item does not exist.', 400);
        }

        $json_data = filter_input(INPUT_POST, 'json_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!is_array($json_data)) wp_send_json_error('Invalid data. JSON data is not an array.', 400);

        $encoded_data = json_encode($json_data);
        $decoded_data = json_decode($encoded_data, true);

        // Check if the JSON encoding/decoding process was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON data.', 400);
        }

        // Ensure $post_id is a positive integer
        $post_id = absint($product_selector_id);

        // Update selector data
        update_post_meta($post_id, 'velo_product_selector_data', $decoded_data);

        // Setup some return data
        $return_obj = array();
        $return_obj['json_saved'] = $decoded_data;

        // Return the data
        wp_send_json_success($return_obj, 200);
    }

    // Function to delete the product selector
    function velo_ajax_delete_product_selector()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required fields are set
        if (!isset($_REQUEST['product_selector_id'])) {
            wp_send_json_error('Not all required fields are set.', 400);
        }

	    $product_selector_id = filter_input(INPUT_POST, 'product_selector_id', FILTER_SANITIZE_NUMBER_INT);
	    if (empty($product_selector_id)) wp_send_json_error('This item does not exist.', 400);

	    // Get information about the post
	    $post = get_post($product_selector_id);
	    $post_type = get_post_type($product_selector_id);
	    $post_id = $post->ID ?? $product_selector_id;

	    if ($post_type !== 'velo_selectors' && !empty($post)) {
		    // Wrong post type
		    wp_send_json_error('This item does not exist.', 400);
	    }

        // Force delete post
        wp_delete_post((int)$post_id, true);

        // Setup some return data
        $return_obj = array();
        $return_obj['success'] = 'success!';

        // Return the data
        wp_send_json_success($return_obj, 200);
    }

    // Function to get the image URL by image ID
    function velo_ajax_get_image_for_editor()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required fields are set
        if (!isset($_REQUEST['image_id'])) {
            wp_send_json_error('Not all required fields are set.', 400);
        }

        // Setup some return data
        $return_obj = array();
        $return_obj['image_html'] = wp_get_attachment_image((int)$_REQUEST['image_id'], 'thumbnail');

        // Return the data
        wp_send_json_success($return_obj, 200);
    }

    // Function to get all images for the backend
    function velo_ajax_get_all_images_for_backend()
    {
	    // Check if the nonce is valid, if not, return error
	    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'velo_settings_nonce')) {
		    wp_send_json_error('Invalid nonce.', 400);
	    }

        // Check if all required fields are set
        if (!isset($_REQUEST['images'])) {
            wp_send_json_error('Not all required fields are set.', 400);
        }

        if (empty($_REQUEST['images'])) {
            // No images found
            wp_send_json_error('No images found.', 400);
        }

        $all_images = array();
        if (isset($_REQUEST['images']) && is_array($_REQUEST['images'])) {
            foreach ($_REQUEST['images'] as $key => $image_id) {
                $image_id = filter_var($image_id, FILTER_SANITIZE_NUMBER_INT);
                if (filter_var($image_id, FILTER_VALIDATE_INT) !== false) {
                    $image_url = esc_url(wp_get_attachment_image_url((int)$image_id, 'thumbnail'));

                    $value = array(
                        "id" => (int)$image_id,
                        "url" => $image_url,
                    );

                    if (!in_array($value, $all_images, true)) {
                        $all_images[] = $value;
                    }
                }
            }
        }

        // Setup some return data
        $return_obj = array();
        $return_obj['images'] = $all_images;

        // Return the data
        wp_send_json_success($return_obj, 200);
    }
}
