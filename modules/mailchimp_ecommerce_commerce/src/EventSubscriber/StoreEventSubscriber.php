<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_store\Event\StoreEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber for Commerce Stores.
 */
class StoreEventSubscriber implements EventSubscriberInterface {

  /**
   * The Store Handler.
   *
   * @var \Drupal\mailchimp_ecommerce\StoreHandler
   */
  private $store_handler;

  /**
   * StoreEventSubscriber constructor.
   *
   * @param \Drupal\mailchimp_ecommerce\StoreHandler $store_handler
   *   The Store Handler.
   */
  public function __construct(StoreHandler $store_handler) {
    $this->store_handler = $store_handler;
  }

  /**
   * Respond to event fired after saving a new store.
   */
  public function storeInsert(StoreEvent $event) {

  }

  /**
   * Respond to event fired after updating an existing store.
   */
  public function storeUpdate(StoreEvent $event) {

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[StoreEvents::STORE_INSERT][] = ['storeInsert'];
    $events[StoreEvents::STORE_UPDATE][] = ['storeUpdate'];

    return $events;
  }

}
