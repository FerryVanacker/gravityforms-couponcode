<?php
if (!class_exists('GFCouponCodesDB')) {
    class GFCouponCodesDB {
        public static function create_coupon_codes_table() {
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
    }
}
