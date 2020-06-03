<?php
/**
 * Created by PhpStorm.
 * User: sin
 * Date: 3/6/2020
 * Time: 11:18 πμ
 */


class PricePrefixHelper
{


    private $config = [];
    // All Product Attributes gets a prefix in the database
    const PRODUCT_ATTRIBUTE_PREFIX = 'pa_';

    /**
     * PricePrefixHelper constructor.
     */
    public function __construct()
    {

    }

    /**
     * Finds All the Woocommerce attributes
     * Attach the terms and return a payload ready to be injected as Plugin Menu
     */
    public function plugin_options_config()
    {
        $attrTaxonomies = wc_get_attribute_taxonomies();
        foreach ($attrTaxonomies as $tax) {
            $pluginOptions = [
                'type' => 'select',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => NULL,

            ];
            $attributeOptions = [
                'id' => $tax->attribute_id,
                'label' => $tax->attribute_label
            ];

            $terms = [];

            // Find Terms
            $termsObj = get_terms(['taxonomy' => self::PRODUCT_ATTRIBUTE_PREFIX . $tax->attribute_name, 'hide_empty' => false]);
            foreach ($termsObj as $item) {

                $terms[$item->term_id] = $item->name;
            }


            $textField = [$tax->attribute_name . '_text_field' => [
                'options' => [
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => NULL,
                ]]];
            $pluginOptions['term_options'] = $terms;
            $pluginOptions['text_field_config'] = $textField;
            $this->setConfig([self::PRODUCT_ATTRIBUTE_PREFIX . $tax->attribute_name => ["pluginOptions" => $pluginOptions, "attributeOptions" => $attributeOptions]]);

        }
    }

    /**
     * Getter for config
     * @return array
     */

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Setter for config
     * @param array $config
     */

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Helper function to render a text option to menu
     * @param string $name
     * @param string $label
     */
    public function add_string_option_to_menu(string $name, string $label)
    {

        echo '<tr valign="top">
                <th scope="row">' . $label . '</th>
                <td><input type="text" name="prefix_name"
                           value="' . esc_attr(get_option($name)) . '"/></td>
            </tr>';

    }

    /**
     * Helper function to render a select to menu
     * @param array $attributes
     * @param string $label
     * @param string $name
     */
    public function add_select_option_to_menu(array $attributes, string $label, string $name)
    {


        echo '<tr valign="top">
                <th scope="row">' . $label . '</th>
                <td><select  name="' . $name . '">';
        echo '<option value="">Κανένα</option>';
        foreach ($attributes as $key => $attr) {
            $selected = get_option( $name ) === $key ? 'selected' : '';
            echo '<option '.$selected.' value="' . $key . '">' . $attr["attributeOptions"]["label"] . '</option>';

        }
        echo '</select>
                </td>
            </tr>';

    }

    /**
     * Helper function to render a single checkbox to menu
     * @param string $value
     * @param string $label
     * @param string $name
     * @param string $checkboxName
     */
    public function add_checkbox_option_to_menu(string $value,string $label,string $name,string $checkboxName) {
        $checked = get_option( $name ) === $value ? 'checked' : '';
        echo '<tr valign="top">
                <th scope="row">' . $label . '</th>
                <td><input '.$checked.' type="checkbox" name="'.$name.'"
                           value="' . esc_attr($value) . '"/>'.$checkboxName.'</td>
            </tr>';
    }
}