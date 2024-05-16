<?php
// Generate the random codes to be used for the discounts
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

// Checks to make sure the code generated is unique (not already in use)
function gw_check_unique_code($unique) {
    global $wpdb;

    $table = $wpdb->prefix . 'coupon_codes';
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT code FROM $table WHERE code = %s",
        $unique
    ));

    return empty($result);
}
