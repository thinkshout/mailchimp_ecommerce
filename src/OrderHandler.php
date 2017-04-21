<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Order handler.
 */
class OrderHandler implements OrderHandlerInterface {

  /**
   * @inheritdoc
   */
  public function getOrder($order_id) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot get an order without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $order = $mc_ecommerce->getOrder($store_id, $order_id);
      return $order;
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to get order: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }

    return NULL;
  }

  /**
   * @inheritdoc
   */
  public function addOrder($order_id, array $customer, array $order) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot add an order without a store ID.');
      }
      if (!mailchimp_ecommerce_validate_customer($customer)) {
        // A user not existing in the store's MailChimp list is not an error, so
        // don't throw an exception.
        return;
      }
      $campaign_id = mailchimp_ecommerce_get_campaign_id();
      if (!empty($campaign_id)) {
        $order['campaign_id'] = $campaign_id;
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->addOrder($store_id, $order_id, $customer, $order);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add an order: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function updateOrder($order_id, array $order) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot update an order without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->updateOrder($store_id, $order_id, $order);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to update an order: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

}
