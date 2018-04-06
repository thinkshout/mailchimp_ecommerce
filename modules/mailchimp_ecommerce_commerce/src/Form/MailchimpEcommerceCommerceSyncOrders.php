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

      $query = \Drupal::entityQuery('commerce_order');
      $result = $query->execute();

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
