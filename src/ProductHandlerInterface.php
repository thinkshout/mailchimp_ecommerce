<?php

namespace Drupal\mailchimp_ecommerce;

/**
 * Interface for the Product handler.
 */
interface ProductHandlerInterface {

  /**
   * Adds a product to MailChimp.
   *
   * Adds a product variant if a product with the given ID exists.
   *
   * In MailChimp, each product requires at least one product variant. This
   * function will create a single product variant when creating new products.
   *
   * A product variant is contained within a product and can be used to
   * represent shirt size, color, etc.
   *
   * @param string $product_id
   *   Unique ID of the product.
   * @param string $product_variant_id
   *   ID of the product variant.
   *   May be identical to $product_id for single products.
   * @param string $title
   *   The product title.
   * @param string $description
   *   The product description.
   * @param string $type
   *   The product type.
   * @param string $sku
   *   The product SKU.
   * @param float $price
   *   The product price.
   */
  public function addProduct($product_id, $product_variant_id, $title, $description, $type, $sku, $price);

  /**
   * Updates an existing product in MailChimp.
   *
   * MailChimp only allows for product variants to be updated. The parent
   * product cannot be changed once created. This function will update the
   * variant associated with the given product ID and SKU.
   *
   * @param string $product_id
   *   Unique ID of the product.
   * @param string $product_variant_id
   *   ID of the product variant.
   *   May be identical to $product_id for single products.
   * @param string $title
   *   The product title.
   * @param string $sku
   *   The product SKU.
   * @param float $price
   *   The product price.
   */
  public function updateProduct($product_id, $product_variant_id, $title, $sku, $price);

  /**
   * Deletes a product in MailChimp.
   *
   * @param string $product_id
   *   Unique ID of the product.
   */
  public function deleteProduct($product_id);

  /**
   * Deletes a product variant in MailChimp.
   *
   * Automatically deletes the product if the only product variant is removed.
   *
   * @param string $product_id
   *   Unique ID of the product.
   * @param string $product_variant_id
   *   ID of the product variant.
   *   Can be identical to $product_id for single products.
   */
  public function deleteProductVariant($product_id, $product_variant_id);

}
