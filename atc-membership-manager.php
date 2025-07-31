<?php
/*
Plugin Name: ATC Membership Manager
Description: 
    This plugin ensures compliance with the Austin Tea Cooperative Bylaws, which require active membership for access to voting rights, store discounts, and participation in events and governance. 
    It automates the management of user permissions based on membership state and enforces restrictions on non-members.
    
    According to the Bylaws:
    * Only owners (members) in good standing may vote or access member privileges (Bylaws §2.3).
    * Member benefits such as discounts and early access to events or space bookings may be determined and granted by the Board (Bylaws §2.4).
    * Membership requires ongoing participation and may be terminated if a member is inactive or in violation of the Code of Conduct (Bylaws §2.6–2.8).

    This plugin ensures these provisions are upheld on the cooperative’s website and WooCommerce-powered store.
Version: 0.1
Author: Michael "Musslebot" Musslewhite
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Role variables
define('ATC_ROLE_ID', 'atc_member');
define('ATC_ROLE_NAME', 'ATC Member');

// Membership variables
define('ATC_TAG_MEMBERSHIP_SLUG', 'atc-membership');
define('ATC_TAG_MEMBERSHIP_NAME', 'ATC Membership');
define('ATC_TAG_MEMBERSHIP_DESCRIPTION', 'This tag applies to products that should users an “ATC Membership” when an order with this product is completed. This tag is used to grant membership by the ATC Membership Manager plugin.' );

// Discount variables
define('ATC_DISCOUNT_PERCENT', 10);
define('ATC_TAG_DISCOUNT_SLUG', 'atc-discount');
define('ATC_TAG_DISCOUNT_NAME', 'ATC Discount');
define('ATC_TAG_DISCOUNT_DESCRIPTION', 'This tag applies to products that should be given a discount as part of being an ATC Member. Any product containing this tag will have a discount applied to it, as defined by the ATC Membership Manager plugin.');


function create_atc_membership_tag() {
    // Create membership tag if not exist
    if (!term_exists(ATC_TAG_MEMBERSHIP_SLUG, 'product_tag')) {
        $result = wp_insert_term(ATC_TAG_MEMBERSHIP_NAME, 'product_tag', [
            'slug' => ATC_TAG_MEMBERSHIP_SLUG,
            'description' => ATC_TAG_MEMBERSHIP_DESCRIPTION
        ]);

        if (!is_wp_error($result)) {
            error_log("[AMD]: ✅ Created product tag 'ATC_TAG_MEMBERSHIP_NAME'");
        } else {
            error_log("[AMD]: ❌ Failed to create product tag 'ATC_TAG_MEMBERSHIP_NAME': " . $result->get_error_message());
        }
    } else {
        error_log("[AMD]: ℹ️ Product tag 'ATC_TAG_MEMBERSHIP_NAME' already exists");
    }
}


function create_atc_discount_tag() {
    // Create discount tag if not exist
    if (!term_exists(ATC_TAG_DISCOUNT_SLUG, 'product_tag')) {
        $result = wp_insert_term(ATC_TAG_DISCOUNT_NAME, 'product_tag', [
            'slug' => ATC_TAG_DISCOUNT_SLUG,
            'description' => ATC_TAG_DISCOUNT_DESCRIPTION
        ]);

        if (!is_wp_error($result)) {
            error_log("[AMD]: ✅ Created product tag 'ATC_TAG_DISCOUNT_NAME'");
        } else {
            error_log("[AMD]: ❌ Failed to create product tag 'ATC_TAG_DISCOUNT_NAME': " . $result->get_error_message());
        }
    } else {
        error_log("[AMD]: ℹ️ Product tag 'ATC_TAG_DISCOUNT_NAME' already exists");
    }

}


function handle_membership_purchase($order_id) {
    // Apply `atc_member` ("ATC Member") role, to a user, when they complete an order containing a product with the tag assigned by ATC_MEMBERSHIP_TAGS
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (has_term(ATC_TAG_MEMBERSHIP_SLUG, 'product_tag', $product->get_id())) {
            // Grant ATC Member role
            $user = get_user_by('id', $user_id);
            if ($user && !in_array(ATC_ROLE_ID, $user->roles)) {
                $user->add_role(ATC_ROLE_ID);
            }
        }
    }
}


function apply_atc_member_discount_to_items($cart) {
    // Apply ATC Member discount to 'atc_member' users' eligible items
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (!is_user_logged_in()) return;

    $user = wp_get_current_user();
    if (!in_array('atc_member', (array) $user->roles) && !is_admin()) return;

    // Avoid running multiple times (important)
    if (did_action('woocommerce_before_calculate_totals') >= 2) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        if (has_term(ATC_TAG_DISCOUNT_SLUG, 'product_tag', $product->get_id())) {
            $original_price = $product->get_regular_price();
            $discounted_price = $original_price * (1 - (ATC_DISCOUNT_PERCENT / 100));
            $product->set_price($discounted_price);
        }
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
    }
}


add_action('init', 'create_atc_member_role');  // TODO: This should be on plugin activation instead of `init`
add_action('init', 'create_atc_membership_tag');  // TODO: This should be on plugin activation instead of `init`
add_action('init', 'create_atc_discount_tag');  // TODO: This should be on plugin activation instead of `init`
add_action('woocommerce_order_status_completed', 'handle_membership_purchase');
add_action('woocommerce_before_calculate_totals', 'apply_atc_member_discount_to_items', 10, 1);
