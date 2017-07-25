<?php

namespace Drupal\mailchimp_ecommerce;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;

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

      // Get the MailChimp campaign ID, if available.
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

  /**
   * @inheritdoc
   */
  public function buildOrder(Drupal\commerce_order\Entity\Order $order, array $customer) {
    $order_items = $order->getItems();
    $lines = [];

    /** @var OrderItem $order_item */
    foreach ($order_items as $order_item) {
      $line = [
        'id' => $order_item->id(),
        'product_id' => $order_item->getPurchasedEntityId(),
        // TODO: Figure out how to differentiate between product and variant ID here.
        'product_variant_id' => $order_item->getPurchasedEntityId(),
        'quantity' => (int) $order_item->getQuantity(),
        'price' => $order_item->getUnitPrice()->getNumber(),
      ];

      $lines[] = $line;
    }

    $order_data = [
      'customer' => $customer,
      'billing_address' => $customer['address'],
      'processed_at_foreign' => date('c'),
      'lines' => $lines,
    ];

    if (!empty($order->getTotalPrice())) {
      $order_data['currency_code'] = $order->getTotalPrice()->getCurrencyCode();
      $order_data['order_total'] = $order->getTotalPrice()->getNumber();
    }

    return $order_data;
  }

  /**
   * @inheritdoc
   */
  public function buildProduct(OrderItem $order_item) {
    $product = [
      'id' => $order_item->id(),
      'product_id' => $order_item->getPurchasedEntityId(),
      // TODO: Figure out how to differentiate between product and variant ID here.
      'product_variant_id' => $order_item->getPurchasedEntityId(),
      'quantity' => (int) $order_item->getQuantity(),
      'price' => $order_item->getUnitPrice()->getNumber(),
    ];

    return $product;
  }

  /**
   * @inheritdoc
   */
  function buildUberOrder(\Drupal\uc_order\Entity\Order $order) {
    $currency_code = $order->getCurrency();
    $order_total = '';
    $lines = [];

    $billing_address = $order->getAddress('billing');
    //(isset($province_code)) ? $province_code : ''
    $mc_billing_address = [

      'name' => $billing_address->getFirstName() . ' ' . $billing_address->getLastName(),
      'company' => null !== $billing_address->getCompany() ? $billing_address->getCompany() : '',
      'address1' => $billing_address->getStreet1(),
      'address2' => $billing_address->getStreet2(),
      'city' => $billing_address->getCity(),
      'province_code' => $billing_address->getZone(),
      'postal_code' => $billing_address->getPostalCode(),
      'country_code' => $billing_address->getCountry()
    ];
    $order_total = $order->getTotal();
    $products = $order->getLineItems();

    if (!empty($products)) {
      foreach ($products as $product) {
        $line = [
          'id' => $product->cart_item_id,
          'product_id' => $product->nid,
          'product_variant_id' => $product->model,
          'quantity' => (int) $product->qty,
          'price' => $product->price,
        ];

        $lines[] = $line;
      }
    }

    $customer_id = _mailchimp_ecommerce_get_local_customer($order->mail);

    $customer = [
      'id' => $customer_id,
      'email_address' => $order->primary_email,
      'first_name' => $order->billing_first_name,
      'last_name' => $order->billing_last_name,
      'address' => mailchimp_ecommerce_ubercart_parse_billing_address($order),
    ];

    $order_data = [
      'currency_code' => $currency_code,
      'order_total' => $order_total,
      'billing_address' => $billing_address,
      'processed_at_foreign' => date('c'),
      'lines' => $lines,
    ];

    return ['customer' => $customer, 'order_data' => $order_data];
  }

}
