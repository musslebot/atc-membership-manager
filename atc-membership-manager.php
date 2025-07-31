<?php
/*
Plugin Name: ATC Membership Manager
Description: Assigns an ATC Member role when a membership product is purchased and gives a 10% discount to 
             ATC Members on all subsequent purchases.
Version: 1.0
Author: Michael "Musslebot" Musslewhite
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly
error_log('[AMD]: ✅ ATC Membership Manager Plugin Loaded');

// 1. Create the 'atc_member' role
function add_atc_member_role() {
    if (!get_role('atc_member')) {
        add_role('atc_member', 'ATC Member', ['read' => true]);
        error_log('[AMD]: ✅ ATC Member role created');
    } else {
        error_log('[AMD]: ✅ ATC Member role already exists');
    }
}
register_activation_hook(__FILE__, 'add_atc_member_role');

// 2. Assign 'atc_member' role when membership product is purchased
function assign_atc_member_role_on_purchase($order_id) {
    error_log('[AMD]: Checking order');
    $order = wc_get_order($order_id);
    error_log('[AMD]: getting user from order user');
    $user_id = $order->get_user_id();
    if (!$user_id) return;
    error_log('[AMD]: ✅ UserID available for user');
    $membership_product_id = 43; // Replace with your actual membership product ID

    foreach ($order->get_items() as $item) {
        error_log('[AMD]: Checking item');
        if ($item->get_product_id() == $membership_product_id) {
            error_log('[AMD]: ✅ item is ATC membership');
            $user = new WP_User($user_id);
            $user->add_role('atc_member');
            error_log('[AMD]: ✅ ATC Member applied to user');
            break;
        }
    }
}
add_action('woocommerce_order_status_completed', 'assign_atc_member_role_on_purchase');

// 3. Apply 10% discount to 'atc_member' users
function apply_atc_member_discount($cart) {
    error_log('[AMD]: Checking user to apply discount');
    if (is_admin() || !is_user_logged_in()) return;
    error_log('[AMD]: user is admin or logged in');


    $user = wp_get_current_user();
    error_log('[AMD]: ✅ current user is retrieved and has roles: !');
    if (in_array('atc_member', $user->roles)) {
        error_log('[AMD]: ✅ user is ATC Member!');
        $discount = $cart->get_subtotal() * 0.10;
        $cart->add_fee(__('ATC Member Discount', 'atc-membership-discount'), -$discount);
        error_log('[AMD]: ✅ ATC Member discount applied to cart');
    }
}
add_action('woocommerce_cart_calculate_fees', 'apply_atc_member_discount');

add_action('init', 'add_atc_member_role'); // TEMPORARY FOR TESTING -- THIS SHOULDN'T BE RUN ON EVERY PAGE LOAD
