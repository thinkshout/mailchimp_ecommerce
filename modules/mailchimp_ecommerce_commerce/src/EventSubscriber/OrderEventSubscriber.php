<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\Event\OrderEvents;
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
   * OrderEventSubscriber constructor.
   *
   * @param \Drupal\mailchimp_ecommerce\OrderHandler $order_handler
   *   The Order Handler.
   */
  public function __construct(OrderHandler $order_handler) {
    $this->order_handler = $order_handler;
  }

  /**
   * Respond to event fired after saving a new order.
   */
  public function orderInsert(OrderEvent $event) {

  }

  /**
   * Respond to event fired after updating an existing order.
   */
  public function orderUpdate(OrderEvent $event) {

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[OrderEvents::ORDER_INSERT][] = ['orderInsert'];
    $events[OrderEvents::ORDER_UPDATE][] = ['orderUpdate'];

    return $events;
  }

}
