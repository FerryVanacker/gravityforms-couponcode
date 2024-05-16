<?php
/*
    Plugin Name: GP Couponcodes Generator
    Plugin URI: https://support.conversiepartners.nl/
    Description: Generate coupon codes into Gravity Forms, easy install with Settings-tab
    Author: Conversie Partners
    Author URI: https://www.conversiepartners.nl/
    Version: 2.1
    GitHub Plugin URI: https://github.com/FerryVanacker/gravityforms-couponcode
 */

add_action('gform_loaded', function() {
    GFForms::include_addon_framework();
    class GFCouponCodesAddOn extends GFAddOn {
        protected $_version = '2.1';
        protected $_min_gravityforms_version = '1.9';
        protected $_slug = 'gf_couponcodes';
        protected $_path = __FILE__;
        protected $_full_path = __FILE__;
        protected $_title = 'Gravity Forms Coupon Codes Add-On';
        protected $_short_title = 'Coupon Codes';

        private static $_instance = null;

        public static function get_instance() {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function init() {
            parent::init();
            add_action('gform_after_submission', array($this, 'create_coupon'), 10, 2);
        }

        public function create_coupon($entry, $form) {
            $settings = $this->get_plugin_settings();

            if ($form['id'] != rgar($settings, 'form_id')) {
                return;
            }

            $source_field_id = rgar($settings, 'source_field_id');
            $email_field_id = rgar($settings, 'email_field_id');
            $coupon_code = rgar($entry, $source_field_id);
            $email = rgar($entry, $email_field_id);
            $amount = rgar($settings, 'discount_amount');
            $type = rgar($settings, 'discount_type');

            // Check if the code is already used
            if ($this->is_coupon_code_used($coupon_code)) {
                return;
            }

            $this->store_coupon_code($coupon_code, $email);
        }

        public function is_coupon_code_used($code) {
            global $wpdb;

            $table = $wpdb->prefix . 'coupon_codes';
            $result = $wpdb->get_var($wpdb->prepare(
                "SELECT used FROM $table WHERE code = %s",
                $code
            ));

            return !empty($result);
        }

        public function store_coupon_code($code, $email) {
            global $wpdb;

            $table = $wpdb->prefix . 'coupon_codes';
            $wpdb->insert($table, array('code' => $code, 'email' => $email, 'used' => 0));
        }

        public function plugin_settings_fields() {
            return array(
                array(
                    'title'  => esc_html__('Coupon Codes Settings', 'gf_couponcodes'),
                    'description' => esc_html__('Configure the settings for generating coupon codes. You can select the form, specify the source field, and set the discount parameters.', 'gf_couponcodes'),
                    'fields' => array(
                        array(
                            'label'   => esc_html__('Form', 'gf_couponcodes'),
                            'type'    => 'select',
                            'name'    => 'form_id',
                            'tooltip' => esc_html__('Select the form to associate with coupon codes.', 'gf_couponcodes'),
                            'choices' => $this->get_forms_as_choices(),
                        ),
                        array(
                            'label'   => esc_html__('Source Field ID', 'gf_couponcodes'),
                            'type'    => 'text',
                            'name'    => 'source_field_id',
                            'tooltip' => esc_html__('Enter the ID of the field where the coupon code will be stored.', 'gf_couponcodes'),
                        ),
                        array(
                            'label'   => esc_html__('Email Field ID', 'gf_couponcodes'),
                            'type'    => 'text',
                            'name'    => 'email_field_id',
                            'tooltip' => esc_html__('Enter the ID of the field where the email address will be stored.', 'gf_couponcodes'),
                        ),
                        array(
                            'label'   => esc_html__('Coupon Code Length', 'gf_couponcodes'),
                            'type'    => 'text',
                            'name'    => 'code_length',
                            'tooltip' => esc_html__('Enter the length of the generated coupon code.', 'gf_couponcodes'),
                            'default_value' => 19,
                        ),
                        array(
                            'label'   => esc_html__('Discount Amount', 'gf_couponcodes'),
                            'type'    => 'text',
                            'name'    => 'discount_amount',
                            'tooltip' => esc_html__('Enter the discount amount for the coupon code.', 'gf_couponcodes'),
                            'default_value' => 10,
                        ),
                        array(
                            'label'   => esc_html__('Discount Type', 'gf_couponcodes'),
                            'type'    => 'select',
                            'name'    => 'discount_type',
                            'tooltip' => esc_html__('Select the discount type for the coupon code.', 'gf_couponcodes'),
                            'choices' => array(
                                array(
                                    'label' => 'Flat',
                                    'value' => 'flat',
                                ),
                                array(
                                    'label' => 'Percentage',
                                    'value' => 'percentage',
                                ),
                            ),
                        ),
                        array(
                            'label'   => esc_html__('Prefix (Optional)', 'gf_couponcodes'),
                            'type'    => 'text',
                            'name'    => 'prefix',
                            'tooltip' => esc_html__('Enter the prefix for the coupon code.', 'gf_couponcodes'),
                        ),
                        array(
                            'label'   => esc_html__('Suffix (Optional)', 'gf_couponcodes'),
                            'type'    => 'text',
                            'name'    => 'suffix',
                            'tooltip' => esc_html__('Enter the suffix for the coupon code.', 'gf_couponcodes'),
                        ),
                    ),
                ),
            );
        }

        private function get_forms_as_choices() {
            $forms = GFAPI::get_forms();
            $choices = array();

            foreach ($forms as $form) {
                $choices[] = array(
                    'label' => $form['title'],
                    'value' => $form['id'],
                );
            }

            return $choices;
        }
    }

    GFCouponCodesAddOn::get_instance();
});

/**
 * Generate the random codes to be used for the discounts
 */
add_filter('gform_field_value_uuid', 'gw_generate_unique_code');
function gw_generate_unique_code() {
    $settings = GFCouponCodesAddOn::get_instance()->get_plugin_settings();
    $length = rgar($settings, 'code_length', 19);
    $prefix = rgar($settings, 'prefix', '');
    $suffix = rgar($settings, 'suffix', '');
    $available_length = $length - strlen($prefix) - strlen($suffix);
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    do {
        $unique = $prefix . substr(str_shuffle(str_repeat($chars, $available_length)), 0, $available_length) . $suffix;
    } while (!gw_check_unique_code($unique));

    return $unique;
}

/**
 * Checks to make sure the code generated is unique (not already in use)
 */
function gw_check_unique_code($unique) {
    global $wpdb;

    $table = $wpdb->prefix . 'coupon_codes';
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT code FROM $table WHERE code = %s",
        $unique
    ));

    return empty($result);
}

/**
 * Create the coupon codes table on plugin activation
 */
register_activation_hook(__FILE__, 'create_coupon_codes_table');
function create_coupon_codes_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'coupon_codes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        code varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        used tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY code (code)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
?>