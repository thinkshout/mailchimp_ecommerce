<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Interface for the Customer handler.
 */
interface CustomerHandlerInterface {

  /**
   * Read a customer from Mailchimp.
   *
   * @param string $customer_id
   *   Unique id of customer.
   *
   * @return object
   *   MailChimp customer object.
   */
  public function getCustomer($customer_id);

  /**
   * Add a new customer to Mailchimp.
   *
   * @param array $customer
   *   Array of customer fields.
   *
   * @see http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/customers/#create-post_ecommerce_stores_store_id_customers
   */
  public function addOrUpdateCustomer($customer);

  /**
   * Delete a customer from Mailchimp.
   *
   * @param string $customer_id
   *   Unique id of customer.
   */
  public function deleteCustomer($customer_id);

}
