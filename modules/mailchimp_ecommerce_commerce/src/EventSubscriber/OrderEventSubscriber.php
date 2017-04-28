<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

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
   */
  public function __construct(OrderHandler $order_handler, CartHandler $cart_handler, CustomerHandler $customer_handler) {
    $this->order_handler = $order_handler;
    $this->cart_handler = $cart_handler;
    $this->customer_handler = $customer_handler;
  }

  /**
   * Respond to event fired after saving a new order.
   */
  public function orderInsert(OrderEvent $event) {
    // TODO: Process order creation.
  }

  /**
   * Respond to event fired after updating an existing order.
   */
  public function orderUpdate(OrderEvent $event) {
    // TODO: Process order update.
  }

  /**
   * Respond to event fired after assigning an anonymous order to a user.
   */
  public function orderAssign(OrderAssignEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $this->order_handler->buildOrder($event->getOrder());

    // An anonymous user has logged in or created an account after populating
    // a cart with items. This is the first point we can send this cart to
    // MailChimp as we are now able to get the user's email address.
    $account = $event->getAccount();

    $customer = [
      'id' => $account->id(),
      'email_address' => $account->getEmail(),
      // TODO: Get opt_in_status from settings.
      'opt_in_status' => '',
    ];

    $this->customer_handler->addCustomer($customer);

    // MailChimp considers any order to be a cart until the order is complete.
    // This order is created as a cart in MailChimp when assigned to the user.
    $this->cart_handler->addCart($order->id(), $customer, $order);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[OrderEvents::ORDER_INSERT][] = ['orderInsert'];
    $events[OrderEvents::ORDER_UPDATE][] = ['orderUpdate'];
    $events[OrderEvents::ORDER_ASSIGN][] = ['orderAssign'];

    return $events;
  }

}
