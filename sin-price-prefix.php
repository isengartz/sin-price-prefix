<?php
/**
 * Plugin Name: Sin Price Prefix
 * Description: Adds prefix based on product attributes
 * Version: 1.0
 * Author: Thanasis Kontokostas
 **/


if (!defined('WPINC')) {
    die;
}

require_once(trailingslashit(dirname(__FILE__)) . 'PricePrefixHelper.php');


class SinPricePrefix
{

    private $options;
    private $helper;

    const CHECKBOX_ENABLED_VALUE = 'enabled';

    /**
     * SinPricePrefix constructor.
     * @param PricePrefixHelper $helper
     */

    public function __construct(PricePrefixHelper $helper)
    {
        $this->helper = $helper;

        add_action('admin_menu', array($this, 'sin_price_prefix_plugin_setup_menu'));

    }


    /**
     * Creates the menu for the plugin
     */
    public function sin_price_prefix_plugin_setup_menu()
    {
        add_action('admin_init', array($this, 'register_my_setting'));
        add_menu_page('Price Prefix', 'Price Prefix Plugin', 'manage_options', 'sin-price-prefix', array($this, 'menu_init'));
    }

    /**
     * Creates the menu content
     */
    public function menu_init()
    {
        echo '<h1>Options</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('sin_price_prefix_options_group');
        do_settings_sections('my-cool-plugin-settings-group');

        echo '<table class="form-table">';

//        $this->helper->add_string_option_to_menu('prefix_name','Default Prefix Name');
        $this->helper->add_select_option_to_menu($this->options, 'Attribute', 'sin_attribute_field');
        $this->helper->add_checkbox_option_to_menu(self::CHECKBOX_ENABLED_VALUE, 'Show In Products', 'show_prefix_in_products', 'Enabled');
        $this->helper->add_checkbox_option_to_menu(self::CHECKBOX_ENABLED_VALUE, 'Show In Cart Products', 'show_prefix_in_cart', 'Enabled');
        $this->helper->add_checkbox_option_to_menu(self::CHECKBOX_ENABLED_VALUE, 'Show Quantity', 'show_stock_with_prefix', 'Enabled');

        echo '</table>';

        submit_button();

        echo '</form>';

    }

    /**
     * Register the settings we gonna use
     */
    public function register_my_setting()
    {
        $this->helper->plugin_options_config();
        $this->options = $this->helper->getConfig();

        $args = array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => NULL,
        );
        // The attribute field that will be used to show the prefix after products
        register_setting('sin_price_prefix_options_group', 'sin_attribute_field', $args);
        // Enable prefix for products
        register_setting('sin_price_prefix_options_group', 'show_prefix_in_products', $args);
        // Enable prefix for cart products
        register_setting('sin_price_prefix_options_group', 'show_prefix_in_cart', $args);
        // Enable show always stock with prefix
//        register_setting('sin_price_prefix_options_group', 'show_stock_with_prefix', $args);

    }

    /**
     * Register the override Filters
     */
    public function registerHooks()
    {

        if (get_option('show_prefix_in_products') === self::CHECKBOX_ENABLED_VALUE) {
            add_filter('woocommerce_get_price_html', array($this, 'sin_change_product_price_display'), 10, 2);

        }
        if (get_option('show_prefix_in_cart') === self::CHECKBOX_ENABLED_VALUE) {
            add_filter('woocommerce_cart_item_price', array($this, 'sin_change_product_price_display_in_cart'),10, 2);
        }
        if (get_option('show_stock_with_prefix') === self::CHECKBOX_ENABLED_VALUE) {
//            add_action('woocommerce_single_product_summary', array($this,'sin_add_stock_quantity'), 11);
        }
    }

    /**
     * Adds stock quantity to product page
     * @param $test
     */
    public function sin_add_stock_quantity() {
//        global $product;
//        var_dump($product);
//        echo 'Test Test';
    }

    /**
     * Change the price for product views
     */
    public function sin_change_product_price_display($price, $productObject)
    {

        $selectedAttr = get_option('sin_attribute_field');

        $productId = $productObject->get_id();

        if ($selectedAttr) {

            $selectedTerm = wc_get_product_terms($productId, $selectedAttr, array('fields' => 'names'));

            if (!empty($selectedTerm)) {
                $pricePrefix = '<span class="sin-price-prefix"> / ' . $selectedTerm[0];
                return $price . $pricePrefix;
            }
        }


        return $price;
    }
    public function sin_change_product_price_display_in_cart($price, $productObject)
    {

        $selectedAttr = get_option('sin_attribute_field');



        $productId = $productObject["product_id"];

        if ($selectedAttr) {

            $selectedTerm = wc_get_product_terms($productId, $selectedAttr, array('fields' => 'names'));

            if (!empty($selectedTerm)) {
                $pricePrefix = '<span class="sin-price-prefix"> / ' . $selectedTerm[0];
                return $price . $pricePrefix;
            }
        }


        return $price;
    }

}

$priceHelper = new PricePrefixHelper();
$sinPricePrefix = new SinPricePrefix($priceHelper);
$sinPricePrefix->registerHooks();