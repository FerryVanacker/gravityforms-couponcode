<?php
/*
    Plugin Name: Gravity Forms Couponcodes Generator
    Plugin URI: https://support.conversiepartners.nl/
    Description: Generate coupon codes into Gravity Forms, easy install with Settings-tab
    Author: Conversie Partners
    Author URI: https://www.conversiepartners.nl/
    Version: 2.1
    GitHub Plugin URI: https://github.com/FerryVanacker/gravityforms-couponcode
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('GF_COUPONCODES_PATH', plugin_dir_path(__FILE__));
define('GF_COUPONCODES_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once GF_COUPONCODES_PATH . 'includes/class-gf-couponcodes.php';
require_once GF_COUPONCODES_PATH . 'includes/class-gf-couponcodes-admin.php';
require_once GF_COUPONCODES_PATH . 'includes/class-gf-couponcodes-db.php';
require_once GF_COUPONCODES_PATH . 'includes/functions.php';

// Initialize the plugin
add_action('gform_loaded', array('GFCouponCodesAddOn', 'get_instance'));
register_activation_hook(__FILE__, array('GFCouponCodesDB', 'create_coupon_codes_table'));
?>
