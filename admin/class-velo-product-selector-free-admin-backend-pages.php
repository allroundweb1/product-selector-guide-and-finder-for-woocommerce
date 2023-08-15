<?php
class Velo_Product_Selector_Free_Admin_Backend_Pages
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    function velo_plugin_create_menu()
    {
        // Menu icon URL
        $velo_menu_icon = VELO_PLUGIN_URL . '/admin/images/plugin-icon-mini.png';

        // Main menu item
        add_menu_page(
            'Product selector guide and finder for WooCommerce FREE',
            'Product selector guide/finder FREE',
            'administrator',
            'velo-product-selector',
            [$this, 'velo_product_selector_plugin_backend_page'],
            $velo_menu_icon
        );

        // Register settings
        add_action('admin_init', [$this,  'register_velo_product_selector_plugin_settings']);
    }

    function register_velo_product_selector_plugin_settings()
    {
        register_setting('velo-product-selector-plugin-settings-group', 'velo_license');
    }

    function velo_product_selector_plugin_backend_page()
    {
?>
        <div id="mutliple-selectors-modal" uk-modal>
            <div class="uk-modal-dialog uk-modal-body">
                <h2 class="uk-modal-title uk-text-center">Add more selectors</h2>
                <p>In the free version of the plugin you can add 1 selector. If you want to add more selectors you can upgrade to the premium version.<br><a href="https://velocityplugins.com/" class="velo-unlock-premium-button" target="_blank">Unlock premium <span class="uk-icon-link" uk-icon="unlock"></span></a></p>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-default uk-modal-close">Close</button>
                </div>
            </div>
        </div>
        <div class="main-settings-wrap">
            <div class="uk-section uk-section-muted">
                <div class="uk-container uk-container-expand">
                    <div class="velo-settings-wrap-flex-inner-main">
                        <h3>Create and edit your product selectors</h3>
                        <a href="https://velocityplugins.com/" class="velo-unlock-premium-button" target="_blank">Unlock premium <span class="uk-icon-link" uk-icon="unlock"></span></a>
                    </div>
                    <div>
                        <div class="velo-product-selector-select">

                        </div>
                    </div>

                </div>
            </div>
            <div class="velo-selector-editor">

            </div>
        </div>
<?php
    }
}
