<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Cart handler.
 */
class CartHandler implements CartHandlerInterface {

  /**
   * @inheritdoc
   */
  public function cartExists($cart_id) {
    return (!empty($this->getCart($cart_id)));
  }

  /**
   * @inheritdoc
   */
  public function getCart($cart_id) {
    $cart = NULL;

    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot get the requested cart without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');

      try {
        $cart = $mc_ecommerce->getCart($store_id, $cart_id);
      }
      catch (\Exception $e) {
        if ($e->getCode() == 404) {
          // Cart doesn't exist.
        }
        else {
          // An actual error occurred; pass on the exception.
          throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
      }
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to get the requested cart: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }

    return $cart;
  }

  /**
   * @inheritdoc
   */
  public function addOrUpdateCart($cart_id, array $customer, array $cart) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot add a cart without a store ID.');
      }
      if (!mailchimp_ecommerce_validate_customer($customer)) {
        // A user not existing in the store's MailChimp list is not an error, so
        // don't throw an exception.
        return;
      }

      // Get the MailChimp campaign ID, if available.
      $campaign_id = mailchimp_ecommerce_get_campaign_id();
      if (!empty($campaign_id)) {
        $cart['campaign_id'] = $campaign_id;
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');

      try {
        if (!empty($mc_ecommerce->getCart($store_id, $cart_id))) {
          $mc_ecommerce->updateCart($store_id, $cart_id, $customer, $cart);
        }
      }
      catch (\Exception $e) {
        if ($e->getCode() == 404) {
          // Cart doesn't exist; add a new cart.
          $mc_ecommerce->addCart($store_id, $cart_id, $customer, $cart);
        }
        else {
          // An actual error occurred; pass on the exception.
          throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
      }
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add a cart: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function deleteCart($cart_id) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot delete a cart without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->deleteCart($store_id, $cart_id);
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        // Cart doesn't exist; no need to log an error.
      }
      else {
        mailchimp_ecommerce_log_error_message('Unable to delete a cart: ' . $e->getMessage());
        drupal_set_message($e->getMessage(), 'error');
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function addCartLine($cart_id, $line_id, $product) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot add a cart line without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->addCartLine($store_id, $cart_id, $line_id, $product);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add a cart line: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function updateCartLine($cart_id, $line_id, $product) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot update a cart line without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->updateCartLine($store_id, $cart_id, $line_id, $product);
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        $mc_ecommerce->addCartLine($store_id, $cart_id, $line_id, $product);
      }
      else {
        mailchimp_ecommerce_log_error_message('Unable to update a cart line: ' . $e->getMessage());
        drupal_set_message($e->getMessage(), 'error');
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function deleteCartLine($cart_id, $line_id) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot delete a cart line without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->deleteCartLine($store_id, $cart_id, $line_id);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to delete a cart line: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

}
