<?php

/**
 * @file
 * Hooks provided by the Mailchimp eCommerce module.
 */

/**
 * Allows modules to react to the addition of a Mailchimp store.
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
  // Query the database to find our custom display node.
  $query = new EntityFieldQuery;
  $query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'custom_node_type')
    // TODO: $product_id is not defined in this function.
    ->fieldCondition('field_custom_product', 'product_id', $product_id, '=')
    ->range(0, 1);

  $result = $query->execute();

  if ($result && !empty($result['node'])) {
    $nids[$product_id] = reset($result['node']);
    $node = node_load($nids[$product_id]);

    // The description lives in a custom field called product_description.
    if (!isset($node->field_product_desription)) {
      $description = check_plain($node->product_description[LANGUAGE_NONE][0]['value']);
    }
  }
}
