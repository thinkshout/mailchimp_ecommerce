<?php

namespace Drupal\mailchimp_ecommerce_commerce\Form;

use Drupal\mailchimp_ecommerce\Form\MailchimpEcommerceSyncOrders;

class MailchimpEcommerceCommerceSyncOrders extends MailchimpEcommerceSyncOrders {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function _submitForm($form, $form_state) {
    if (!empty($form_state->getValue('sync_orders'))) {
      $batch = [
        'title' => t('Adding orders to MailChimp'),
        'operations' => [],
      ];

      // Set timestamp of earliest order to sync.
      $min_timestamp = 0;

      $timespan = $form_state->getValue('timespan');

      if (!empty($timespan)) {
        // Calculate timestamp as current time minus given timespan (months).
        $months = (abs(intval($timespan)) * -1);
        $min_timestamp = strtotime($months . ' months');
      }

      // Retrieve orders created at or after the timestamp calculated above.
      $query = \Drupal::entityQuery('commerce_order')
        ->condition('created', $min_timestamp, '>=');

      $result = $query->execute();

      // Add orders to a batch operation for processing.
      if (!empty($result)) {
        $order_ids = array_keys($result);

        $batch['operations'][] = [
          '\Drupal\mailchimp_ecommerce_commerce\BatchSyncOrders::syncOrders',
          [$order_ids],
        ];
      }

      batch_set($batch);
    }
  }

}
