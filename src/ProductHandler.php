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
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot add a product without a store ID.');
      }

      // TRUE when a new product is created, false if a variant is added.
      $new_product = FALSE;

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');

      // Create MailChimp product from product type.
      try {
        $mc_ecommerce->getProduct($store_id, $product_id);
      }
      catch (\Exception $e) {
        if ($e->getCode() == 404) {
          // No existing product; create new product with default variant.
          $variant = (object) [
            'id' => $product_variant_id,
            'title' => $title,
            'sku' => $sku,
            'price' => $price,
          ];

          $mc_ecommerce->addProduct($store_id, $product_id, $title, [$variant], [
            'description' => $description,
            'type' => $type,
          ]);

          $new_product = TRUE;
        }
        else {
          // An actual error occurred; pass on the exception.
          throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
      }

      if (!$new_product) {
        // Add a variant to an existing product.
        $mc_ecommerce->addProductVariant($store_id, $product_id, [
          'id' => $product_variant_id,
          'title' => $title,
          'sku' => $sku,
          'price' => $price,
        ]);
      }
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add a product: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function updateProduct($product_id, $product_variant_id, $title, $sku, $price) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot update a product without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->updateProductVariant($store_id, $product_id, $product_variant_id, [
        'title' => $title,
        'sku' => $sku,
        'price' => $price,
      ]);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to update product: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function deleteProduct($product_id) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot delete a product without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->deleteProduct($store_id, $product_id);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to delete product: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function deleteProductVariant($product_id, $product_variant_id) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot delete a product variant without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');

      try {
        $variants = $mc_ecommerce->getProductVariants($store_id, $product_id);

        // Delete the variant if the product contains multiple variants.
        if ($variants->total_items > 1) {
          $mc_ecommerce->deleteProductVariant($store_id, $product_id, $product_variant_id);
        }
        else {
          // Delete the product if the product has only one variant.
          $mc_ecommerce->deleteProduct($store_id, $product_id);
        }
      }
      catch (\Exception $e) {
        if ($e->getCode() == 404) {
          // This product isn't in MailChimp.
          return;
        }
        else {
          // An actual error occurred; pass on the exception.
          throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
      }
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to delete product variant: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

}
