<?php

namespace Drupal\mailchimp_ecommerce_ubercart\Form;

use Drupal\mailchimp_ecommerce\Form\MailchimpEcommerceSyncOrders;

class MailchimpEcommerceUbercartSyncOrders extends MailchimpEcommerceSyncOrders {

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

      // TODO Replace this with a get all uber orders
      //$query = \Drupal::entityQuery('commerce_order');
      //$result = $query->execute();

      if (!empty($result)) {
        $order_ids = array_keys($result);

        $batch['operations'][] = [
          '\Drupal\mailchimp_ecommerce_ubercart\BatchSyncOrders::syncOrders',
          [$order_ids],
        ];
      }

      batch_set($batch);
    }
  }
}
