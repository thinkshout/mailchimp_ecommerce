<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Interface for the Cart handler.
 */
interface CartHandlerInterface {

  /**
   * Determines if a cart exists in MailChimp.
   *
   * @param string $cart_id
   *   The cart ID.
   *
   * @return bool
   *   TRUE if cart exists, FALSE otherwise.
   */
  public function cartExists($cart_id);

  /**
   * Gets an existing cart from the current MailChimp store.
   *
   * @param string $cart_id
   *   The cart ID.
   *
   * @see http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/#read-get_ecommerce_stores_store_id_carts_cart_id
   */
  public function getCart($cart_id);

  /**
   * Adds or updates a cart in the current MailChimp store.
   *
   * @param string $cart_id
   *   The cart ID.
   * @param array $customer
   *   Associative array of customer information.
   *   - id (string): A unique identifier for the customer.
   * @param array $cart
   *   Associative array of cart information.
   *   - currency_code (string): The three-letter ISO 4217 currency code.
   *   - order_total (float): The total for the order.
   *   - lines (array): An array of the order's line items.
   *
   * @see http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/#create-post_ecommerce_stores_store_id_carts
   */
  public function addOrUpdateCart($cart_id, array $customer, array $cart);

  /**
   * Deletes a cart in the current MailChimp store.
   *
   * @param string $cart_id
   *   The cart ID.
   */
  public function deleteCart($cart_id);

  /**
   * Adds a line to a cart in the current MailChimp store.
   *
   * @param string $cart_id
   *   The cart ID.
   * @param string $line_id
   *   A unique identifier for the order line item.
   * @param array $product
   *   Associative array of product information.
   *   - product_id (string) The unique identifier for the product.
   *   - product_variant_id (string) The unique identifier for the variant.
   *   - quantity (int) The quantity of a cart line item.
   *   - price (float) The price of a cart line item.
   */
  public function addCartLine($cart_id, $line_id, $product);

  /**
   * Updates an existing line in a cart in the current MailChimp store.
   *
   * @param string $cart_id
   *   The cart ID.
   * @param string $line_id
   *   A unique identifier for the order line item.
   * @param array $product
   *   Associative array of product information.
   *   - product_id (string) The unique identifier for the product.
   *   - product_variant_id (string) The unique identifier for the variant.
   *   - quantity (int) The quantity of a cart line item.
   *   - price (float) The price of a cart line item.
   */
  public function updateCartLine($cart_id, $line_id, $product);

  /**
   * Deletes a line in a cart in the current MailChimp store.
   *
   * @param string $cart_id
   *   The cart ID.
   * @param string $line_id
   *   A unique identifier for the order line item.
   */
  public function deleteCartLine($cart_id, $line_id);

}
