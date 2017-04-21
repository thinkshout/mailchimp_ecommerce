<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Interface for the Store handler.
 */
interface StoreHandlerInterface {

  /**
   * Return information about the store from the supplied id.
   *
   * @param string $store_id
   *   The ID of the store.
   *
   * @return object
   *   MailChimp store object.
   */
  public function getStore($store_id);

  /**
   * Add a new store to Mailchimp.
   *
   * @param string $store_id
   *   The ID of the store.
   * @param array $store
   *   Associative array of store information.
   *   - list_id (string) The id for the list associated with the store.
   *   - name (string) The name of the store.
   *   - currency_code (string) The three-letter ISO 4217 code for the currency
   *     that the store accepts.
   */
  public function addStore($store_id, $store);

  /**
   * Update a store name or currency code.
   *
   * @param string $store_id
   *   The ID of the store.
   * @param string $name
   *   The name of the store.
   * @param string $currency_code
   *   The three-letter ISO 4217 code.
   */
  public function updateStore($store_id, $name, $currency_code);

}
