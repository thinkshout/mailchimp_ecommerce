<?php

namespace Drupal\mailchimp_ecommerce\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

abstract class MailchimpEcommerceSync extends FormBase {

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
    $form['sync_products'] = [
      '#type' => 'checkbox',
      '#title' => t('Sync Products'),
      '#description' => t('Sync all existing products to MailChimp.'),
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

    parent::submitForm($form, $form_state);
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
