<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Customer handler.
 */
class CustomerHandler implements CustomerHandlerInterface {

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
  public function addCustomer($customer) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      $list_id = mailchimp_ecommerce_get_list_id();

      if (empty($store_id)) {
        throw new \Exception('Cannot add a customer without a store ID.');
      }

      // Pull member information to get member status.
      $memberinfo = mailchimp_get_memberinfo($list_id, $customer['email_address'], TRUE);

      if (empty($memberinfo) || !isset($memberinfo->status)) {
        // Cannot create a customer with no list member.
        return;
      }

      $opt_in_status = (isset($memberinfo->status) && ($memberinfo->status == 'subscribed')) ? TRUE : FALSE;
      $customer['opt_in_status'] = $opt_in_status;

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->addCustomer($store_id, $customer);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add a customer: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function updateCustomer($customer) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      $list_id = mailchimp_ecommerce_get_list_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot update a customer without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      // Pull member information to get member status.
      $memberinfo = mailchimp_get_memberinfo($list_id, $customer['email_address'], TRUE);
      if (empty($memberinfo) || !isset($memberinfo->status)) {
        // Cannot update a customer with no list member.
        return;
      }
      $customer['opt_in_status'] = (isset($memberinfo->status) && ($memberinfo->status == 'subscribed')) ? TRUE : FALSE;
      $mc_ecommerce->updateCustomer($store_id, $customer);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to update a customer: ' . $e->getMessage());
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

}
