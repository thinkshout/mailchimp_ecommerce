<?php

/**
 * @file
 * Hooks provided by the MailChimp eCommerce module.
 */

/**
 * Allows modules to react to the addition of a MailChimp store.
 *
 * @param object $store
 *   The new store.
 *
 * @see http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#read-get_ecommerce_stores_store_id
 */
function hook_mailchimp_ecommerce_add_store($store) {

}

/**
 * Allow other modules to alter the description of a product.
 *
 * @param string $description
 *  Current value for the product description text.
 *
 * @param $product
 *  The product being added or updated in Drupal.
 */
function hook_mailchimp_ecommerce_product_description_alter(&$description, $product) {
  // In Commerce, the description for the product might reside in
  // the display node, rather than the product entity.

  // Query the database to find the display node.
  $query = new EntityFieldQuery;
  $query->entityCondition('entity_type', 'node')
    ->fieldCondition('field_product', 'product_id', $product_id, '=')
    ->range(0, 1);

  $result = $query->execute();

  if ($result && !empty($result['node'])) {
    $nids[$product_id] = reset($result['node']);
    $node = node_load($nids[$product_id]);

    if (!isset($node->field_product_desription)) {
      // The description lives in a custom field called product_description.
      $description = check_plain($node->product_description[LANGUAGE_NONE][0]['value']);
    }
  }
}
