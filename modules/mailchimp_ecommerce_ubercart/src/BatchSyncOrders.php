<?php

namespace Drupal\mailchimp_ecommerce_ubercart;

/**
 * Batch process handler for syncing order data to MailChimp.
 */
class BatchSyncOrders {

  /**
   * @param $order_ids
   * @param $context
   */
  public static function syncOrders($order_ids, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['total'] = count($order_ids);
      $context['results']['order_ids'] = $order_ids;
    }

    $config = \Drupal::config('mailchimp.settings');
    $batch_limit = $config->get('batch_limit');

    $batch = array_slice($context['results']['product_ids'], $context['sandbox']['progress'], $batch_limit);

    /** @var \Drupal\mailchimp_ecommerce\CustomerHandler $customer_handler */
    $customer_handler = \Drupal::service('mailchimp_ecommerce.customer_handler');

    /** @var \Drupal\mailchimp_ecommerce\CartHandler $cart_handler */
    $cart_handler = \Drupal::service('mailchimp_ecommerce.cart_handler');

    /** @var \Drupal\mailchimp_ecommerce\OrderHandler $order_handler */
    $order_handler = \Drupal::service('mailchimp_ecommerce.order_handler');

    foreach ($batch as $order_id) {

      // TODO Replace this
      //$order = \Drupal\commerce_Order\Entity\Order::load($order_id);
      $order = null;

      $customer = [];
      $order_data = [];
      $order_state = $order->get('state')->value;

      // Handle guest orders at the checkout review step - first time the user's
      // email address is available.
      if (empty($order->getCustomer()->id()) && ($order->get('checkout_step')->value == 'review')) {
        $customer['email_address'] = $order->getEmail();
        if (!empty($customer['email_address'])) {
          $billing_profile = $order->getBillingProfile();
          $customer = $customer_handler->buildCustomer($customer, $billing_profile);
          $customer_handler->addOrUpdateCustomer($customer);
        }

        $order_data = $order_handler->buildOrder($order, $customer);

        // Add cart item price to order data.
        if (!isset($order_data['currency_code'])) {
          $price = $order->getTotalPrice();

          $order_data['currency_code'] = $price->getCurrencyCode();
          $order_data['order_total'] = $price->getNumber();
        }

        $cart_handler->addOrUpdateCart($order->id(), $customer, $order_data);
      }

      // On order completion, replace cart in MailChimp with order.
      // TODO: Only perform action the first time an order has 'completed' status.
      if ($order_state == 'completed') {
        $cart_handler->deleteCart($order->id());

        // Update the customer's total order count and total amount spent.
        $customer_handler->incrementCustomerOrderTotal($customer['email_address'], $order_data['order_total']);

        // Email address should always be available on checkout completion.
        $customer['email_address'] = $order->getEmail();
        $billing_profile = $order->getBillingProfile();

        $customer = $customer_handler->buildCustomer($customer, $billing_profile);
        $order_data = $order_handler->buildOrder($order, $customer);

        $order_handler->addOrder($order->id(), $customer, $order_data);
      }

      $context['sandbox']['progress']++;

      $context['message'] = t('Sent @count of @total products to MailChimp', [
        '@count' => $context['sandbox']['progress'],
        '@total' => $context['sandbox']['total'],
      ]);

      $context['finished'] = ($context['sandbox']['progress'] / $context['sandbox']['total']);
    }
  }

}
