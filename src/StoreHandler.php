<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Store handler.
 */
class StoreHandler implements StoreHandlerInterface {

  /**
   * @inheritdoc
   */
  public function getStore($store_id) {
    $store = NULL;
    try {
      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $store = $mc_ecommerce->getStore($store_id);
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        // Store doesn't exist; no need to log an error.
      }
      else {
        mailchimp_ecommerce_log_error_message('Unable to get store: ' . $e->getMessage());
        drupal_set_message($e->getMessage(), 'error');
      }
    }

    return $store;
  }

  /**
   * @inheritdoc
   */
  public function addStore($store_id, $store) {
    try {
      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_store = $mc_ecommerce->addStore($store_id, $store);

      \Drupal::moduleHandler()->invokeAll('mailchimp_ecommerce_add_store', [$mc_store]);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add a new store: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function updateStore($store_id, $name, $currency_code) {
    try {
      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->updateStore($store_id, $name, $currency_code);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to update a store: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

}
