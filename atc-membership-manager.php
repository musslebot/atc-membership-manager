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

define('ATC_ROLE_ID', 'atc_member');
define('ATC_ROLE_NAME', 'ATC Member');
define('ATC_DISCOUNT_PERCENT', 10);
define('ATC_MEMBERSHIP_PRODUCT_ID', 43); // Replace with real product ID


function assign_atc_member_role_on_purchase($order_id) {
// Assign 'atc_member' role when membership product is purchased.
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    if (!$user_id) return;

    foreach ($order->get_items() as $item) {
        if ($item->get_product_id() == ATC_MEMBERSHIP_PRODUCT_ID) {
            $user = new WP_User($user_id);
            $user->add_role(ATC_ROLE_ID);
            break;
        }
    }
}

function apply_atc_member_discount_to_items($cart) {
    // Apply ATC Member discount to 'atc_member' users' eligible items
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (!is_user_logged_in()) return;

    $user = wp_get_current_user();
    //if (!in_array('atc_member', (array) $user->roles) && !is_admin()) return;
    error_log("[AMD]:✅  is ATC Member or admin");

    // Avoid running multiple times (important)
    if (did_action('woocommerce_before_calculate_totals') >= 2) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $original_price = $product->get_regular_price();
        $discounted_price = $original_price * (1 - (ATC_DISCOUNT_PERCENT / 100));
        $product->set_price($discounted_price);
    }
}


function create_atc_member_role(){
    // Create the 'atc_member' role
    /* NOTE: ideally this would run once on activation, but I can't seem to get that
    * to work in bluehost's staging environment :shrug: 
    * Will try to make this an "on activation" hook in production later.
    */
    if (!get_role(ATC_ROLE_ID)) {
        add_role(ATC_ROLE_ID, ATC_ROLE_NAME, ['read' => true]);
        error_log("[AMD]: ✅ ATC_ROLE_ID, ATC_ROLE_NAME created");
    }
}


add_action('init', 'create_atc_member_role');
add_action('woocommerce_order_status_completed', 'assign_atc_member_role_on_purchase');
add_action('woocommerce_before_calculate_totals', 'apply_atc_member_discount_to_items', 10, 1);
