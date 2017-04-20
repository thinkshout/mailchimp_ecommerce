<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Product handler.
 */
class ProductHandler implements ProductHandlerInterface {

  /**
   * @inheritdoc
   */
  public function addProduct($product_id, $product_variant_id, $title, $description, $type, $sku, $price) {

  }

  /**
   * @inheritdoc
   */
  public function updateProduct($product_id, $product_variant_id, $title, $sku, $price) {

  }

  /**
   * @inheritdoc
   */
  public function deleteProduct($product_id) {

  }

  /**
   * @inheritdoc
   */
  public function deleteProductVariant($product_id, $product_variant_id) {

  }

}
