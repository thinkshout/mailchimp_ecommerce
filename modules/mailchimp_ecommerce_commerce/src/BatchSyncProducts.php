<?php

namespace Drupal\mailchimp_ecommerce_commerce;

/**
 * Batch process handler for syncing product data to MailChimp.
 */
class BatchSyncProducts {

  public static function syncProducts($product_ids, &$context) {
    $total_products = count($product_ids);
    $sync_count = 0;

    foreach ($product_ids as $product_id) {
      /** @var \Drupal\commerce_product\Entity\Product $product */
      $product = \Drupal\commerce_product\Entity\Product::load($product_id);

      $title = (!empty($product->get('title')->value)) ? $product->get('title')->value : '';
      $description = (!empty($product->get('body')->value)) ? $product->get('body')->value : '';
      $type = (!empty($product->get('type')->value)) ? $product->get('type')->value : '';

      /** @var \Drupal\mailchimp_ecommerce\ProductHandler $product_handler */
      $product_handler = \Drupal::service('mailchimp_ecommerce.product_handler');

      $variants = $product_handler->buildProductVariants($product);

      $product_handler->addProduct($product_id, $title, $description, $type, $variants);

      $sync_count++;

      $context['message'] = t('Sent product @count of @total to MailChimp', [
        '@count' => $sync_count,
        '@total' => $total_products,
      ]);
    }
  }

}
