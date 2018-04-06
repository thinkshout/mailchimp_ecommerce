<?php

namespace Drupal\mailchimp_ecommerce\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

abstract class MailchimpEcommerceSyncOrders extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_ecommerce_sync';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['sync_orders'] = [
      '#type' => 'checkbox',
      '#title' => t('Sync Orders'),
      '#description' => t('Sync all existing Orders to MailChimp.'),
    ];

    $form['timespan'] = [
      '#type' => 'textfield',
      '#title' => t('Time span'),
      '#default_value' => 6,
      '#field_suffix' => 'months',
      '#description' => 'MailChimp recommends syncing the past 6 months of order data. Leave blank to sync all orders.',
      '#size' => 3,
      '#maxlength' => 3,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sync with MailChimp'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->_submitForm($form, $form_state);
  }

  /**
   * Processes data sync to MailChimp.
   *
   * Syncing data to MailChimp is specific to the shopping cart integration.
   * You should implement this function in your integration to process the
   * data sync.
   */
  abstract public function _submitForm($form, $form_state);

}
