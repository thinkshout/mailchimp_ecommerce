<?php

/**
 * @file
 * Administration forms for MailChimp eCommerce.
 */

/**
 * The MailChimp eCommerce settings form.
 */
function mailchimp_ecommerce_admin_settings() {
  $form['mailchimp_ecommerce_integration'] = array(
    '#type' => 'radios',
    '#title' => 'Enabled Integration',
    '#options' => array(
      '' => t('None'),
    ),
    '#default_value' => variable_get('mailchimp_ecommerce_integration', ''),
  );

  $form['mailchimp_ecommerce_store_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Store Name'),
    '#required' => TRUE,
    '#default_value' => variable_get('mailchimp_ecommerce_store_name', ''),
    '#description' => t('The name of your store as it should appear in your MailChimp account.'),
  );

  $mailchimp_lists = mailchimp_get_lists();
  $list_options = array(
    '' => '-- Select --',
  );

  foreach ($mailchimp_lists as $list_id => $list) {
    $list_options[$list_id] = $list->name;
  }

  $form['mailchimp_ecommerce_list_id'] = array(
    '#type' => 'select',
    '#title' => t('Store List'),
    '#required' => TRUE,
    '#options' => $list_options,
    '#default_value' => variable_get('mailchimp_ecommerce_list_id', ''),
  );

  $settings_form = system_settings_form($form);
  $settings_form['#submit'][] = 'mailchimp_ecommerce_admin_settings_submit';

  return $settings_form;
}

/**
 * Submit handler for the MailChimp eCommerce form.
 */
function mailchimp_ecommerce_admin_settings_submit($form, &$form_state) {
  $store_id = variable_get('mailchimp_ecommerce_store_id', NULL);
  if ($store_id != NULL) {
    // TODO: Get currency from active shopping cart.
    mailchimp_ecommerce_add_store($store_id,
      $form_state['values']['mailchimp_ecommerce_list_id'],
      $form_state['values']['mailchimp_ecommerce_store_name'],
      'USD');
  }
}
