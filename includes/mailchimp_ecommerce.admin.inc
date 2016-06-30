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
  $form['mailchimp_ecommerce_notice'] = array(
    '#markup' => t('This page will allow you to create a store. Once created, you cannot change the list associated with the store.'),
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

  if (!empty(variable_get('mailchimp_ecommerce_list_id', ''))) {
    $form['mailchimp_ecommerce_list_id_existing'] = array(
      '#markup' => t('Once created, the list cannot be changed for a given store. This store is connected to the list named') . ' ' . variable_get('mailchimp_ecommerce_list_id'),
    );
  }
  else {
    $form['mailchimp_ecommerce_list_id'] = array(
      '#type' => 'select',
      '#title' => t('Store List'),
      '#required' => TRUE,
      '#options' => $list_options,
      '#default_value' => variable_get('mailchimp_ecommerce_list_id', ''),
    );
  }

  if (module_exists('commerce')) {
    $form['mailchimp_ecommere_commerce_default_currency'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Commerce Default Currency'),
      '#description' => t('Drupal Commerce was detected as being installed. Check this box to use the default currency of Commerce'),
    );
  }

  // @TODO Make this item go away with AJAX if the Drupal commerce box is checked.
  $list_options_currency = array(
    '' => '-- Select --',
  ) + mailchimp_ecommerce_get_currency_codes();
  $form['mailchimp_ecommerce_currency'] = array(
    '#type' => 'select',
    '#options' => $list_options_currency,
    '#title' => t('Store Currence Code'),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save changes'),
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

  // Add / update store only if an eCommerce integration is selected.
  if (!empty($form_state['values']['mailchimp_ecommerce_integration'])) {
    if ($store_id != NULL) {
      $existing_store = mailchimp_ecommerce_get_store($store_id);

      // Detect if Commerce is installed and use the default currency.

      if (!empty($form_state['values']['mailchimp_ecommere_commerce_default_currency'])) {
        $currency = commerce_default_currency();
      }
      else {
        $currency = $form_state['values']['mailchimp_ecommerce_currency'];
      }

      if (empty($existing_store)) {
        mailchimp_ecommerce_add_store($store_id,
          $form_state['values']['mailchimp_ecommerce_list_id'],
          $form_state['values']['mailchimp_ecommerce_store_name'],
          $currency);
      }
      else {
        mailchimp_ecommerce_update_store($store_id,
          $form_state['values']['mailchimp_ecommerce_store_name'],
          $currency);
      }
    }
  }
  else {
    variable_del('mailchimp_ecommerce_integration');
  }
}
