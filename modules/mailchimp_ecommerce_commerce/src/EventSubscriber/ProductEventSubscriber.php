<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_product\Entity\Product;
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
    /** @var Product $product */
    $product = $event->getProduct();

    $product_id = $product->get('product_id')->value;
    $title = (!empty($product->get('title')->value)) ? $product->get('title')->value : '';
    $description = (!empty($product->get('body')->value)) ? $product->get('body')->value : '';
    $type = (!empty($product->get('type')->value)) ? $product->get('type')->value : '';

    $variants = $this->product_handler->buildProductVariants($product);

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

        $existing_variant = $this->product_handler->getProductVariant($product_id, $product_variation->id());
        if ($existing_variant == NULL) {
          // Create a new product variant.
          $this->product_handler->addProductVariant($product_id,
            $product_variation->id(),
            $product_variation->getTitle(),
            $product_variation->getSku(),
            $product_variation->getPrice()->getNumber());
        }
        else {
          // Update the existing product variant.
          $this->product_handler->updateProduct($product_id,
            $product_variation->id(),
            $product_variation->getTitle(),
            $product_variation->getSku(),
            $product_variation->getPrice()->getNumber());
        }
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
