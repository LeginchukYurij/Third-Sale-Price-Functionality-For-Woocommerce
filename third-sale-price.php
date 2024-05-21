<?php

/**
 * Plugin Name: Third Sale Price
 * Plugin URI: 
 * Description: 
 * Version: 1.0
 * Author: YL
 */



function add_third_sale_price_field() {
  woocommerce_wp_text_input( array(
      'id' => '_third_sale_price',
      'label' => __('Third Sale Price ($)', 'woocommerce'),
      'description' => __('', 'woocommerce'),
      'data_type' => 'price'
  ));
}


function save_third_sale_price_field( $post_id ) {
  $third_sale_price = isset($_POST['_third_sale_price']) ? $_POST['_third_sale_price'] : '';
  update_post_meta($post_id, '_third_sale_price', sanitize_text_field($third_sale_price));
}


add_action('woocommerce_product_options_pricing', 'add_third_sale_price_field');

add_action('woocommerce_process_product_meta', 'save_third_sale_price_field');


// Function to get third_sale_price
function get_third_sale_price($product_id) {
  return get_post_meta($product_id, '_third_sale_price', true);
}

// Changing the price output on the frontend
add_filter('woocommerce_get_price_html', 'custom_price_display', 99, 2);
function custom_price_display($price_html, $product) {
  
  if ($product->is_type('variable')) {
    return $price_html;
  }


    // Get regular price and discounted price
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_sale_price();
    $third_sale_price = get_third_sale_price($product->get_id());

    // Form a string with prices
    $price_display = '';

    // Regular price
    if ($regular_price) {
      if ($sale_price || !empty($third_sale_price)) {
        $price_display .= '<del>'  . wc_price($regular_price) . '</del>';
      } else {
        $price_display .= wc_price($regular_price);
      }
    }

   // Discounted price
    if ($sale_price ) {
      if (!empty($third_sale_price)) {
        $price_display .= ' <del class="discount_2" style="color: var(--e-global-color-primary);">' . wc_price($sale_price) . '</del>';
      } else {
        $price_display .= ' <ins>' . wc_price($sale_price) . '</ins>';
      }
    }

    // Third price discounted
    if (!empty($third_sale_price)) {
        $price_display .= ' <ins class="discount_3" style="color: red">' . wc_price($third_sale_price) . '</ins>';
    }

    return $price_display;
}




// Changing the price of a product in the shopping cart and on checkout
add_action('woocommerce_before_calculate_totals', 'set_custom_price', 10, 1);
function set_custom_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $third_sale_price = get_third_sale_price($cart_item['product_id']);

        if (!empty($third_sale_price)) {
            $final_price = $third_sale_price;

            if (!empty($cart_item['yith_wapo_item_price'])) {

                if (!empty($cart_item['yith_wapo_item_price'])) {
                  $addons_total = $cart_item['yith_wapo_total_options_price'] ?? 0;
                }
            
                $final_price += $addons_total;
            }

             $cart_item['data']->set_price($final_price);
        }
    }

   
}