<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_product\Event\ProductEvent;
use Drupal\commerce_product\Event\ProductEvents;
use Drupal\mailchimp_ecommerce\ProductHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber for Commerce Products.
 */
class ProductEventSubscriber implements EventSubscriberInterface {

  /**
   * The Product Handler.
   *
   * @var \Drupal\mailchimp_ecommerce\ProductHandler
   */
  private $product_handler;

  /**
   * ProductEventSubscriber constructor.
   *
   * @param \Drupal\mailchimp_ecommerce\ProductHandler $product_handler
   *   The Product Handler.
   */
  public function __construct(ProductHandler $product_handler) {
    $this->product_handler = $product_handler;
  }

  /**
   * Respond to event fired after saving a new product.
   */
  public function productInsert(ProductEvent $event) {
    $product = $event->getProduct();

    $product_id = $product->get('product_id')->value;
    $product_variant_id = '';
    $title = $product->get('title')->value;
    $description = $product->get('body')->value;
    // TODO: Get product type.
    $type = '';
    // TODO: Get product SKU.
    $sku = '';
    // TODO: Get product price.
    $price = 0;

    $this->product_handler->addProduct($product_id, $product_variant_id, $title, $description, $type, $sku, $price);
  }

  /**
   * Respond to event fired after updating an existing product.
   */
  public function productUpdate(ProductEvent $event) {

  }

  /**
   * Respond to event fired after deleting a product.
   */
  public function productDelete(ProductEvent $event) {

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ProductEvents::PRODUCT_INSERT][] = ['productInsert'];
    $events[ProductEvents::PRODUCT_UPDATE][] = ['productUpdate'];
    $events[ProductEvents::PRODUCT_DELETE][] = ['productDelete'];

    return $events;
  }

}
