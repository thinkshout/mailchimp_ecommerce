<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Interface for the Order handler.
 */
interface OrderHandlerInterface {

  /**
   * Gets an order from the current MailChimp store.
   *
   * @param string $order_id
   *   The order ID.
   *
   * @return object
   *   The order.
   */
  public function getOrder($order_id);

  /**
   * Adds a new order to the current MailChimp store.
   *
   * @param string $order_id
   *   The order ID.
   * @param array $customer
   *   Associative array of customer information.
   *   - id (string): A unique identifier for the customer.
   * @param array $order
   *   Associative array of order information.
   *   - currency_code (string): The three-letter ISO 4217 currency code.
   *   - order_total (float): The total for the order.
   *   - lines (array): An array of the order's line items.
   *
   * @see http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#create-post_ecommerce_stores_store_id_orders
   */
  public function addOrder($order_id, array $customer, array $order);

  /**
   * Updates an existing order in the current MailChimp store.
   *
   * @param string $order_id
   *   The order ID.
   * @param array $order
   *   Associative array of order information.
   *   - currency_code (string): The three-letter ISO 4217 currency code.
   *   - order_total (float): The total for the order.
   *   - lines (array): An array of the order's line items.
   *
   * @see http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#edit-patch_ecommerce_stores_store_id_orders_order_id
   */
  public function updateOrder($order_id, array $order);

}
