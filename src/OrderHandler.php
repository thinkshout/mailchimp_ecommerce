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
  public function buildOrder(Order $order, array $customer) {
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
      'processed_at_foreign' => date('c'),
      'lines' => $lines,
    ];

    if(isset($customer['address'])) {
      $order_data['billing_address'] = $customer['address'];
    }


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
    $mc_billing_address = [];

    //TODO: Refactor this into the customer handler.
    $customer_handler = new customerHandler(\Drupal::database());

    $billing_address = $order->getAddress('billing');
    if ($billing_address->getFirstName() && $billing_address->getLastName()) {
      $mc_billing_address = [
        'name' => $billing_address->getFirstName() . ' ' . $billing_address->getLastName(),
        'company' => $billing_address->getCompany(),
        'address1' => $billing_address->getStreet1(),
        'address2' => $billing_address->getStreet2(),
        'city' => $billing_address->getCity(),
        'province_code' => $billing_address->getZone(),
        'postal_code' => $billing_address->getPostalCode(),
        'country_code' => $billing_address->getCountry()
      ];
    }
    foreach ($mc_billing_address as $key => $value) {
      if ($value === null) {
        unset($mc_billing_address[$key]);
      }
    }
    $order_total = $order->getTotal();
    $products = $order->products;

    if (!empty($products)) {
      foreach ($products as $product) {
        $line = [
          'id' => $product->nid->target_id,
          'product_id' => $product->nid->target_id,
          'product_variant_id' => $product->nid->target_id,
          'quantity' => (int) $product->qty->value,
          'price' => $product->price->value,
        ];

        $lines[] = $line;
      }
    }

    $customer_id = $customer_handler->loadCustomerId($order->mail);

    $list_id = mailchimp_ecommerce_get_list_id();
    // Pull member information to get member status.
    $memberinfo = mailchimp_get_memberinfo($list_id, $order->getEmail(), TRUE);

    $opt_in_status = (isset($memberinfo->status) && ($memberinfo->status == 'subscribed')) ? TRUE : FALSE;

    $customer = [
      'id' => $customer_id,
      'email_address' => $order->getEmail(),
      'opt_in_status' => $opt_in_status,
      'first_name' => $billing_address->getFirstName(),
      'last_name' => $billing_address->getlastName(),
      'address' => $mc_billing_address,
    ];

    foreach ($customer as $key => $value) {
      if ($value === null) {
        unset($customer[$key]);
      }
    }
    // TODO: END Refactor

    $order_data = [
      'customer' => $customer,
      'currency_code' => $currency_code,
      'order_total' => $order_total,
      'billing_address' => $mc_billing_address,
      'processed_at_foreign' => date('c'),
      'lines' => $lines,
    ];
    foreach ($order_data as $key => $value) {
      if ($value === null) {
        unset($order_data[$key]);
      }
    }

    return ['customer' => $customer, 'order_data' => $order_data];
  }
}
