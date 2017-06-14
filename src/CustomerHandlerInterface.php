<?php

namespace Drupal\mailchimp_ecommerce;
use Drupal\commerce_order\Entity\Order;

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

  /**
   * Saves the customer locally and returns customer data formatted for use with MailChimp.
   *
   * @param array $customer
   *   The customer.
   *
   * @param object $billing_profile
   *   The Drupal Commerce Billing Profile.
   * @return array
   *
   *   Array of customer data.
   */
  public function buildCustomer($customer, $billing_profile);

  /**
   * Increments the order count and total amount spent by a customer.
   *
   * This information is tracked and sent to MailChimp with every order.
   *
   * @param string $email_address
   *   The email address associated with the customer.
   * @param float $total_spent
   *   The amount to increment total spent for. This is the order total.
   * @param int $orders_count
   *   The number of orders to increment.
   *   Should always be 1, but available here to change if needed.
   *
   * @return bool
   *   TRUE if the customer exists and was updated, FALSE otherwise.
   */
  public function incrementCustomerOrderTotal($email_address, $total_spent, $orders_count = 1);

  /**
   * Returns the total amount spent by a customer.
   *
   * @param string $email_address
   *   The email address associated with the customer.
   *
   * @return float
   *   The total amount spent.
   */
  public function getCustomerTotalSpent($email_address);

  /**
   * Returns the total number of orders made by a customer.
   *
   * @param string $email_address
   *   The email address associated with the customer.
   *
   * @return int
   *   The total number of orders.
   */
  public function getCustomerTotalOrders($email_address);

}
