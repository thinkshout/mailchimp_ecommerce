<?php

namespace Drupal\mailchimp_ecommerce_commerce\Form;

use Drupal\mailchimp_ecommerce\Form\MailchimpEcommerceSync;

class MailchimpEcommerceCommerceSync extends MailchimpEcommerceSync {

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
    // TODO: Start batch sync process.
  }

}
