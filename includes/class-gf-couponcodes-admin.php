<?php
if (!class_exists('GFCouponCodesAdmin')) {
    class GFCouponCodesAdmin {
        public function __construct() {
            add_action('admin_menu', array($this, 'add_admin_menu'));
        }

        public function add_admin_menu() {
            add_menu_page(
                'Coupon Codes',
                'Coupon Codes',
                'manage_options',
                'gf_couponcodes',
                array($this, 'admin_page'),
                'dashicons-tickets-alt',
                6
            );
        }

        public function admin_page() {
            global $wpdb;
            $table = $wpdb->prefix . 'coupon_codes';

            if (isset($_POST['delete_coupon'])) {
                $id = intval($_POST['delete_coupon']);
                $wpdb->delete($table, array('id' => $id));
            }

            $coupons = $wpdb->get_results("SELECT * FROM $table");
            $total_coupons = count($coupons);
            $used_coupons = count(array_filter($coupons, function($coupon) { return $coupon->used; }));

            echo '<div class="wrap">';
            echo '<h1>Manage Coupon Codes</h1>';
            echo '<p>Total Coupons: ' . esc_html($total_coupons) . '</p>';
            echo '<p>Used Coupons: ' . esc_html($used_coupons) . '</p>';
            echo '<table class="widefat fixed">';
            echo '<thead><tr><th>ID</th><th>Code</th><th>Email</th><th>Used</th><th>Actions</th></tr></thead>';
            echo '<tbody>';
            foreach ($coupons as $coupon) {
                echo '<tr>';
                echo '<td>' . esc_html($coupon->id) . '</td>';
                echo '<td>' . esc_html($coupon->code) . '</td>';
                echo '<td>' . esc_html($coupon->email) . '</td>';
                echo '<td>' . ($coupon->used ? 'Yes' : 'No') . '</td>';
                echo '<td>';
                echo '<form method="post" style="display:inline;"><button type="submit" name="delete_coupon" value="' . esc_attr($coupon->id) . '">Delete</button></form>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            echo '</div>';
        }
    }
}

new GFCouponCodesAdmin();
