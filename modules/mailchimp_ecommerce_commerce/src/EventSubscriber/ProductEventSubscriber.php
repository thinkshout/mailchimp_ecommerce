<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_product\Entity\ProductVariation;
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
    $title = $product->get('title')->value;
    $description = $product->get('body')->value;
    $type = $product->get('type')->value;

    $variants = [];

    $product_variations = $product->get('variations')->getValue();
    if (!empty($product_variations)) {
      foreach ($product_variations as $variation_data) {
        /** @var ProductVariation $product_variation */
        $product_variation = ProductVariation::load($variation_data['target_id']);

        $variant = [
          'id' => $product_variation->id(),
          'title' => $product_variation->getTitle(),
          'sku' => $product_variation->getSku(),
          // Product variations contain a currency code, but MailChimp requires
          // store currency to be set at the point when the store is created, so
          // the variation currency is ignored here.
          // TODO: Make sure the user knows this through a form hint.
          'price' => $product_variation->getPrice()->getNumber(),
        ];

        $variants[] = $variant;
      }
    }

    $this->product_handler->addProduct($product_id, $title, $description, $type, $variants);
  }

  /**
   * Respond to event fired after updating an existing product.
   */
  public function productUpdate(ProductEvent $event) {
    $product = $event->getProduct();

    $product_id = $product->get('product_id')->value;

    $product_variations = $product->get('variations')->getValue();
    if (!empty($product_variations)) {
      foreach ($product_variations as $variation_data) {
        /** @var ProductVariation $product_variation */
        $product_variation = ProductVariation::load($variation_data['target_id']);

        // TODO: Check MailChimp for existing variant. If not found, create.
        $this->product_handler->updateProduct($product_id,
          $product_variation->id(),
          $product_variation->getTitle(),
          $product_variation->getSku(),
          $product_variation->getPrice()->getNumber());
      }
    }
  }

  /**
   * Respond to event fired after deleting a product.
   */
  public function productDelete(ProductEvent $event) {
    // TODO: Process deleted product.
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
