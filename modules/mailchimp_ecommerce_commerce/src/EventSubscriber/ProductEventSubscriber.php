<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_product\Event\ProductEvent;
use Drupal\commerce_product\Event\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber for Commerce Products.
 */
class ProductEventSubscriber implements EventSubscriberInterface {

  /**
   * Respond to event fired after saving a new product.
   */
  public function productInsert(ProductEvent $event) {
    // TODO: Send product data to MailChimp.
    $product = $event->getProduct();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // TODO: Add product update / delete events.
    $events[ProductEvents::PRODUCT_INSERT][] = ['productInsert'];

    return $events;
  }

}
