<?php

namespace Drupal\mailchimp_ecommerce;

use Drupal\Core\Url;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Product handler.
 */
class ProductHandler implements ProductHandlerInterface {

  /**
   * @inheritdoc
   */
  public function addProduct($product_id, $title, $url, $description, $type, $variants) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot add a product without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');

      $mc_ecommerce->addProduct($store_id, (string) $product_id, $title, $url, $variants, [
        'description' => $description,
        'type' => $type,
        'url' => $url,
      ]);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add product: ' . $e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
    }
  }

  /**
   * @inheritdoc
   */
  public function updateProduct($product, $title, $url, $description, $type, $variants) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot update a product without a store ID.');
      }

      $product_id = $product->get('product_id')->value;

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');

      // Update the base product with no variant.
      $mc_ecommerce->updateProduct($store_id, $product_id, $variants, [
          'title' => $title,
          'description' => $description,
          'type' => $type,
          'url' => $url,
      ]);

      // Update the variant.
      $product_variations = $product->get('variations')->getValue();
      if (!empty($product_variations)) {
        foreach ($product_variations as $variation_data) {

          /** @var ProductVariation $product_variation */
          $product_variation = ProductVariation::load($variation_data['target_id']);

          $url = $this->buildProductUrl($product);

          $existing_variant = $this->getProductVariant($product_id, $product_variation->id());
          if ($existing_variant) {

            // Update the existing product variant.
            $mc_ecommerce->updateProductVariant($store_id, $product_id, $product_variation->id(), [
              'title' => $product->getTitle(),
              'url' => $url,
              'sku' => $product_variation->getSku(),
              'price' => $product_variation->getPrice()->getNumber(),
            ]);
          }
          else {

            // Create a new product variant.
            $this->addProductVariant($product_id,
              $product_variation->id(),
              $product->getTitle(),
              $url,
              $product_variation->getSku(),
              $product_variation->getPrice()->getNumber());
          }
        }
      }
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        drupal_set_message('This product doesn\'t exist in MailChimp. Please sync all your products.');
      }
      else {
        // An actual error occurred; pass on the exception.
        mailchimp_ecommerce_log_error_message('Unable to update product: ' . $e->getMessage());
        drupal_set_message($e->getMessage(), 'error');
      }
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
  public function addProductVariant($product_id, $product_variant_id, $title, $url, $sku, $price) {
    try {
      $store_id = mailchimp_ecommerce_get_store_id();
      if (empty($store_id)) {
        throw new \Exception('Cannot add a product variant without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $mc_ecommerce->addProductVariant($store_id, $product_id, [
        'id' => $product_variant_id,
        'title' => $title,
        'url' => $url,
        'sku' => $sku,
        'price' => $price,
      ]);
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to add product variant: ' . $e->getMessage());
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
        throw new \Exception('Cannot get a product variant without a store ID.');
      }

      /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
      $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
      $product_variant = $mc_ecommerce->getProductVariant($store_id, $product_id, $product_variant_id);

      // MailChimp will return a product variant object even if the variant
      // doesn't exist. Checking for an empty SKU is a reliable way to
      // determine if a product variant doesn't exist in MailChimp.
      if (empty($product_variant->sku)) {
        return NULL;
      }
    }
    catch (\Exception $e) {
      mailchimp_ecommerce_log_error_message('Unable to get product variant: ' . $e->getMessage());
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

  /**
   * Returns product variant data formatted for use with MailChimp.
   *
   * @param \Drupal\commerce_product\Entity\Product $product
   *   The Commerce Product.
   *
   * @return array
   *   Array of product variant data.
   */
  public function buildProductVariants(Product $product) {
    $variants = [];

    $product_variations = $product->get('variations')->getValue();
    if (!empty($product_variations)) {
      foreach ($product_variations as $variation_data) {
        /** @var ProductVariation $product_variation */
        $product_variation = ProductVariation::load($variation_data['target_id']);
        $url = $this->buildProductUrl($product);

        $variant = [
          'id' => $product_variation->id(),
          'title' => $product->getTitle(),
          'url' => $url,
          'sku' => $product_variation->getSku(),
        ];

        $price = $product_variation->getPrice();
        if (!empty($price)) {
          $variant['price'] = $price->getNumber();
        }
        else {
          $variant['price'] = 0;
        }

        // Product variations contain a currency code, but MailChimp requires
        // store currency to be set at the point when the store is created, so
        // the variation currency is ignored here.
        // TODO: Make sure the user knows this through a form hint.

        $variants[] = $variant;
      }
    }

    return $variants;
  }

  /**
   * Build MailChimp product values from an Ubercart product node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Ubercart 'product' type node.
   *
   * @return array
   *   Array of product values for use with MailChimp.
   */
  function buildProductFromNode(\Drupal\node\NodeInterface $node) {
    $variant = [
      'id' => $node->id(),
      'title' => $node->getTitle(),
      'sku' => $node->model->value,
      'price' => $node->price->value,
    ];

    $product = array(
      'id' => $node->id(),
      'variant_id' => $node->id(),
      'sku' => $node->model->value,
      'title' => $node->getTitle(),
      'description' => $node->body->value,
      'price' => $node->price->value,
      'type' => $node->getType(),
      'variants' => [$variant],
    );

    return $product;
  }

  /**
   * Creates a URL from a product.
   *
   * @param Product $product
   *   The Commerce product object.
   *
   * @return string
   *   The URL of the product.
   */
  public function buildProductUrl($product) {
    global $base_url;

    // MailChimp will accept an empty string if no URL is available.
    $full_url = '';

    // TODO: Add Ubercart product URL
    $url = $product->toURL();
    if (!empty($url)) {
      $full_url = $base_url . $url->toString();
    }

    return $full_url;
  }

}
