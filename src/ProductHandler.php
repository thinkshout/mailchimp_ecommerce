<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Product handler.
 */
class ProductHandler implements ProductHandlerInterface {

  /**
   * @inheritdoc
   */
  public function addProduct($product_id, $title, $description, $type, $variants) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot add a product without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');

      $mc_ecommerce->addProduct($store_id, $product_id, $title, $variants, [
        'description' => $description,
        'type' => $type,
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
  public function getProductVariant($product_id, $product_variant_id) {
    $product_variant = NULL;
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot delete a product without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $product_variant = $mc_ecommerce->getProductVariant($store_id, $product_id, $product_variant_id);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to delete product: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }

    return $product_variant;
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
