<?php
if (!class_exists('GFCouponCodesAddOn')) {
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

            if ($this->is_coupon_code_used($coupon_code)) {
                error_log('Coupon Code Error: The coupon code is already used.');
                return;
            }

            $result = $this->store_coupon_code($coupon_code, $email);

            if (is_wp_error($result)) {
                error_log('Coupon Code Error: ' . $result->get_error_message());
            } else {
                $this->increment_usage_count($coupon_code);
            }
        }

        public function is_coupon_code_used($code) {
            global $wpdb;

            $table = $wpdb->prefix . 'coupon_codes';
            $result = $wpdb->get_row($wpdb->prepare(
                "SELECT used FROM $table WHERE code = %s",
                $code
            ));

            return !empty($result) && $result->used;
        }

        public function store_coupon_code($code, $email) {
            global $wpdb;

            if (!$this->validate_coupon_code($code)) {
                return new WP_Error('invalid_code', 'Invalid coupon code.');
            }

            $table = $wpdb->prefix . 'coupon_codes';
            $wpdb->insert($table, array('code' => $code, 'email' => $email, 'used' => 0));
        }

        public function validate_coupon_code($code) {
            return preg_match('/^[A-Za-z0-9]{10}$/', $code);
        }

        public function increment_usage_count($code) {
            global $wpdb;

            $table = $wpdb->prefix . 'coupon_codes';
            $wpdb->query($wpdb->prepare(
                "UPDATE $table SET usage_count = usage_count + 1 WHERE code = %s",
                $code
            ));
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
}
