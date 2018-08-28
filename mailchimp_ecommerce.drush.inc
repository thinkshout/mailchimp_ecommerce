<?php

/**
 * Implements hook_drush_command().
 */
function mailchimp_ecommerce_drush_command() {
  $commands = [];

  $commands['mc-ecommerce-get-stores'] = [
    'description' => 'Gets stores attached to the currently set MailChimp API key.',
    'aliases' => ['mcstores'],
  ];

  $commands['mc-ecommerce-get-orders'] = [
    'description' => 'Gets orders attached to a given store ID.',
    'aliases' => ['mcorders'],
    'options' => [
      'store_id' => 'ID of the store as defined by MailChimp.',
    ],
  ];

  return $commands;
}

/**
 * Drush command to retrieve a list of MailChimp eCommerce stores.
 */
function drush_mailchimp_ecommerce_mc_ecommerce_get_stores() {
  $api_key = \Drupal::config('mailchimp.settings')->get('api_key');

  if (!empty($api_key)) {
    /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
    $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
    $stores = $mc_ecommerce->getStores();

    drush_print("");

    foreach ($stores->stores as $store) {
      drush_print("ID:        " . $store->id);
      drush_print("Name:      " . $store->name);
      drush_print("List ID:   " . $store->list_id);
      drush_print("Platform:  " . $store->platform);
      drush_print("");
    }
  }
}

/**
 * Drush command to retrieve a list of orders for a MailChimp eCommerce store.
 */
function drush_mailchimp_ecommerce_mc_ecommerce_get_orders($store_id) {
  $api_key = \Drupal::config('mailchimp.settings')->get('api_key');

  if (!empty($api_key)) {
    /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
    $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
    $orders = $mc_ecommerce->getOrders($store_id);

    drush_print("");

    foreach ($orders->orders as $order) {
      drush_print("ID:              " . $order->id);
      drush_print("Customer ID:     " . $order->customer->id);
      drush_print("Customer email:  " . $order->customer->email_address);
      drush_print("Total:  " . $order->order_total . " " . $order->currency_code);

      drush_print("");
      drush_print("- Line items -");
      drush_print("");

      foreach ($order->lines as $line) {
        drush_print("  ID:            " . $line->id);
        drush_print("  Product ID:    " . $line->product_id);
        drush_print("  Product Name:  " . $line->product_title);
        drush_print("  Quantity:      " . $line->quantity);
        drush_print("  Price:         " . $line->price . " " . $order->currency_code);

        drush_print("");
      }

      drush_print("");
    }
  }
}
