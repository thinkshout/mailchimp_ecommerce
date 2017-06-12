<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Event\OrderAssignEvent;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\mailchimp_ecommerce\CartHandler;
use Drupal\mailchimp_ecommerce\CustomerHandler;
use Drupal\mailchimp_ecommerce\OrderHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber for Commerce Orders.
 */
class OrderEventSubscriber implements EventSubscriberInterface {

  /**
   * The Order Handler.
   *
   * @var \Drupal\mailchimp_ecommerce\OrderHandler
   */
  private $order_handler;

  /**
   * The Cart Handler.
   *
   * @var \Drupal\mailchimp_ecommerce\CartHandler
   */
  private $cart_handler;

  /**
   * The Customer Handler.
   *
   * @var \Drupal\mailchimp_ecommerce\CustomerHandler
   */
  private $customer_handler;

  /**
   * OrderEventSubscriber constructor.
   *
   * @param \Drupal\mailchimp_ecommerce\OrderHandler $order_handler
   *   The Order Handler.
   * @param \Drupal\mailchimp_ecommerce\CartHandler $cart_handler
   *   The Cart Handler.
   * @param \Drupal\mailchimp_ecommerce\CustomerHandler $customer_handler
   *   The Customer Handler.
   */
  public function __construct(OrderHandler $order_handler, CartHandler $cart_handler, CustomerHandler $customer_handler) {
    $this->order_handler = $order_handler;
    $this->cart_handler = $cart_handler;
    $this->customer_handler = $customer_handler;
  }

  /**
   * Respond to event fired after updating an existing order.
   */
  public function orderUpdate(OrderEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getOrder();
    $customer = [];

    $order_state = $order->get('state')->value;

    // Handle guest orders at the checkout review step - first time the user's
    // email address is available.
    if (empty($order->getCustomer()->id()) && ($order->get('checkout_step')->value == 'review')) {
      $customer['email_address'] = $event->getOrder()->getEmail();
      if (!empty($customer['email_address'])) {
        $billing_profile = $order->getBillingProfile();
        $customer = $this->customer_handler->buildCustomer($customer, $billing_profile);
        $this->customer_handler->addOrUpdateCustomer($customer);
      }

      $order_data = $this->order_handler->buildOrder($order);

      // Add cart item price to order data.
      if (!isset($order_data['currency_code'])) {
        /** @var Price $price */
        $price = $event->getEntity()->getPrice();

        $order_data['currency_code'] = $price->getCurrencyCode();
        $order_data['order_total'] = $price->getNumber();
      }

      $this->cart_handler->addOrUpdateCart($order->id(), $customer, $order_data);
    }

    // On order completion, replace cart in MailChimp with order.
    // TODO: Only perform action the first time an order has 'completed' status.
    if ($order_state == 'completed') {
      $this->cart_handler->deleteCart($order->id());

      // Email address should always be available on checkout completion.
      $customer['email_address'] = $order->getEmail();
      $billing_profile = $order->getBillingProfile();

      $customer = $this->customer_handler->buildCustomer($customer, $billing_profile);
      $order_data = $this->order_handler->buildOrder($order);

      $this->order_handler->addOrder($order->id(), $customer, $order_data);
    }
  }

  /**
   * Respond to event fired after assigning an anonymous order to a user.
   */
  public function orderAssign(OrderAssignEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getOrder();

    // An anonymous user has logged in or created an account after populating
    // a cart with items. This is the first point we can send this cart to
    // MailChimp as we are now able to get the user's email address.
    $account = $event->getAccount();
    $customer['email_address'] = $account->getEmail();
    $billing_profile = $order->getBillingProfile();

    $customer = $this->customer_handler->buildCustomer($customer, $billing_profile);

    $this->customer_handler->addOrUpdateCustomer($customer);

    // MailChimp considers any order to be a cart until the order is complete.
    // This order is created as a cart in MailChimp when assigned to the user.
    $order_data = $this->order_handler->buildOrder($order);

    // Add cart item price to order data.
    if (!isset($order_data['currency_code'])) {
      /** @var \Drupal\commerce_price\Price $price */
      $price = $event->getEntity()->getPrice();

      $order_data['currency_code'] = $price->getCurrencyCode();
      $order_data['order_total'] = $price->getNumber();
    }

    $this->cart_handler->addOrUpdateCart($order->id(), $customer, $order_data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[OrderEvents::ORDER_UPDATE][] = ['orderUpdate'];
    $events[OrderEvents::ORDER_ASSIGN][] = ['orderAssign'];

    return $events;
  }

}
