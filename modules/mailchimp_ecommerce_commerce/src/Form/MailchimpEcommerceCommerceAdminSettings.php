<?php

namespace Drupal\mailchimp_ecommerce_commerce\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\mailchimp_ecommerce\Form\MailchimpEcommerceAdminSettings;

class MailchimpEcommerceCommerceAdminSettings extends MailchimpEcommerceAdminSettings {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // This is the currently active store according to Drupal Commerce.
    // Commerce allows multiple stores in D8 - may need to consider that here.
    $store = $this->store_context->getStore();

    if (!empty($store)) {
      // Set default currency code for the MailChimp store.
      $default_currency = $store->getDefaultCurrencyCode();
      if (isset($form['mailchimp_ecommerce_currency']['#options'][$default_currency])) {
        $form['mailchimp_ecommerce_currency']['#default_value'] = $default_currency;
      }
    }

    return $form;
  }

}
