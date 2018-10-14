<?php

namespace Drupal\mailchimp_ecommerce_ubercart\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\mailchimp_ecommerce\Form\MailchimpEcommerceAdminSettings;

class MailchimpEcommerceUbercartAdminSettings extends MailchimpEcommerceAdminSettings {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // TODO Get default currency
    if (!empty($store)) {
      // Set default currency code for the Mailchimp store.
      $default_currency = $store->getDefaultCurrencyCode();
      if (isset($form['mailchimp_ecommerce_currency']['#options'][$default_currency])) {
        $form['mailchimp_ecommerce_currency']['#default_value'] = $default_currency;
      }
    }

    // Identify Ubercart to Mailchimp.
    $form['platform']['#default_value'] = 'Drupal Ubercart';

    return $form;
  }

}
