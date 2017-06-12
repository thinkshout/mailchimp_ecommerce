<?php

namespace Drupal\mailchimp_ecommerce;

use Drupal\Core\Database\Connection;

/**
 * Customer handler.
 */
class CustomerHandler implements CustomerHandlerInterface {

  /**
   * The database connection.
   *
   * @var Connection
   */
  private $database;

  /**
   * CustomerHandler constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The Order Handler.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * @inheritdoc
   */
  public function getCustomer($customer_id) {
    $customer = NULL;
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot get a customer without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $customer = $mc_ecommerce->getCustomer($store_id, $customer_id);
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        // Customer doesn't exist in the store; no need to log an error.
      }
      else {
        mailchimp_ecommerce_log_error_message('Unable to delete a customer: ' . $e->getMessage());
        drupal_set_message($e->getMessage(), 'error');
      }
    }

    return $customer;
  }

  /**
   * @inheritdoc
   */
  public function addOrUpdateCustomer($customer) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      $list_id = mailchimp_ecommerce_get_list_id();

      if (empty($store_id)) {
        throw new \Exception('Cannot add or update a customer without a store ID.');
      }

      // Pull member information to get member status.
      $memberinfo = mailchimp_get_memberinfo($list_id, $customer['email_address'], TRUE);

      $opt_in_status = (isset($memberinfo->status) && ($memberinfo->status == 'subscribed')) ? TRUE : FALSE;
      $customer['opt_in_status'] = $opt_in_status;

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');

      try {
        if (!empty($mc_ecommerce->getCustomer($store_id, $customer['id']))) {
          $mc_ecommerce->updateCustomer($store_id, $customer);
        }
      }
      catch (\Exception $e) {
        if ($e->getCode() == 404) {
          // Customer doesn't exist; add a new customer.
          $mc_ecommerce->addCustomer($store_id, $customer);
        }
        else {
          // An actual error occurred; pass on the exception.
          throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
      }
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add a customer: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function deleteCustomer($customer_id) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot delete a customer without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->deleteCustomer($store_id, $customer_id);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to delete a customer: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function buildCustomer($customer, $billing_profile) {
    $customer_id = 0;

    // Load an existing customer using the order ID.
    $query = $this->database->select('mailchimp_ecommerce_customer', 'c')
      ->fields('c', ['mailchimp_customer_id'])
      ->condition('mail', $customer['email_address']);

    $result = $query->execute()->fetch();

    if (!empty($result)) {
      $customer_id = $result->mailchimp_customer_id;
    }

    // Create a new customer if no customer is attached to the order.
    if (empty($customer_id)) {
      $customer_id = $result = $this->database->insert('mailchimp_ecommerce_customer')
        ->fields(['mail' => $customer['email_address']])
        ->execute();
    }

    if (!empty($customer_id)) {
      $customer['id'] = $customer_id;
    }
    $address = $billing_profile->address->first();

    $customer['company'] = $address->getOrganization();
    $customer['first_name'] = $address->getGivenName();
    $customer['last_name'] = $address->getFamilyName();

    $customer['address'] = [
      'address1' => $address->getAddressLine1(),
      'address2' => $address->getAddressLine2(),
      'city' => $address->getLocality(),
      'province_code' => $address->getAdministrativeArea(),
      'postal_code' => $address->getPostalCode(),
      'country_code' => $address->getcountryCode(),
    ];

    return $customer;
  }

}
