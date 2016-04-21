<?php

/**
 * @file
 * Administration forms for MailChimp eCommerce.
 */

/**
 * The MailChimp eCommerce settings form.
 */
function mailchimp_ecommerce_admin_settings() {
  $form['mailchimp_ecommerce_store_name'] = array(
    '#type' => 'textfield',
    '#title' => t('MailChimp eCommerce Store Name'),
    '#required' => TRUE,
    '#default_value' => variable_get('mailchimp_ecommerce_store_name', ''),
    '#description' => t('The name of your store as it should appear in your MailChimp account.'),
  );

  return system_settings_form($form);
}
