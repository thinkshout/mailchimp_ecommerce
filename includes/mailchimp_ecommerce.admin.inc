<?php

/**
 * @file
 * Administration forms for MailChimp eCommerce.
 */

/**
 * The MailChimp eCommerce settings form.
 */
function mailchimp_ecommerce_admin_settings() {
  $form['mailchimp_ecommerce_notice'] = [
    '#markup' => t('This page will allow you to create a store. Once created, you cannot change the list associated with the store.'),
  ];
  $form['mailchimp_ecommerce_store_name'] = [
    '#type' => 'textfield',
    '#title' => t('Store Name'),
    '#required' => TRUE,
    '#default_value' => variable_get('mailchimp_ecommerce_store_name', ''),
    '#description' => t('The name of your store as it should appear in your MailChimp account.'),
  ];

  $mailchimp_lists = mailchimp_get_lists();
  $list_options = [
    '' => '-- Select --',
  ];

  foreach ($mailchimp_lists as $list_id => $list) {
    $list_options[$list_id] = $list->name;
  }

  if (!empty(variable_get('mailchimp_ecommerce_list_id', ''))) {
    $existing_store_id = variable_get('mailchimp_ecommerce_list_id');
    $form['mailchimp_ecommerce_list_id_existing'] = [
      '#markup' => t('Once created, the list cannot be changed for a given store. This store is connected to the list named') . ' ' . $list_options[$existing_store_id],
    ];
  }
  else {
    $form['mailchimp_ecommerce_list_id'] = [
      '#type' => 'select',
      '#title' => t('Store List'),
      '#required' => TRUE,
      '#options' => $list_options,
      '#default_value' => variable_get('mailchimp_ecommerce_list_id', ''),
    ];
  }

  $list_options_currency = [
    '' => '-- Select --',
  ] + mailchimp_ecommerce_get_currency_codes();
  $form['mailchimp_ecommerce_currency'] = [
    '#type' => 'select',
    '#options' => $list_options_currency,
    '#title' => t('Store Currency Code'),
    '#required' => TRUE,
    '#description' => t('This is overridden if you have selected to use the default currency from Commerce.'),
  ];

  if (!empty(variable_get('mailchimp_ecommerce_list_id', ''))) {
    $form['sync'] = [
      '#type' => 'fieldset',
      '#title' => t('Data sync'),
      '#collapsible' => FALSE,
    ];
    $form['sync']['products'] = [
      '#markup' => l(t('Sync existing Commerce products to MailChimp'), 'admin/config/services/mailchimp/ecommerce/sync'),
    ];
  }

  $settings_form = system_settings_form($form);
  $settings_form['#submit'][] = 'mailchimp_ecommerce_admin_settings_submit';

  return $settings_form;
}

/**
 * Submit handler for the MailChimp eCommerce form.
 */
function mailchimp_ecommerce_admin_settings_submit($form, &$form_state) {
  $store_id = variable_get('mailchimp_ecommerce_store_id', NULL);
  if (variable_get('mailchimp_ecommerce_store_id', NULL) == NULL) {
    $store_id = mailchimp_ecommerce_generate_store_id();
    variable_set('mailchimp_ecommerce_store_id', $store_id);
  }

  if ($store_id != NULL) {
    $currency = $form_state['values']['mailchimp_ecommerce_currency'];

    // Determine if a store is being created or updated.
    $existing_store = mailchimp_ecommerce_get_store($store_id);

    if (empty($existing_store)) {
      $store = [
        'list_id' => isset($form_state['values']['mailchimp_ecommerce_list_id']) ? $form_state['values']['mailchimp_ecommerce_list_id'] : variable_get('mailchimp_ecommerce_list_id'),
        'name' => $form_state['values']['mailchimp_ecommerce_store_name'],
        'currency_code' => $currency,
      ];

      mailchimp_ecommerce_add_store($store_id, $store);
    }
    else {
      mailchimp_ecommerce_update_store($store_id,
        $form_state['values']['mailchimp_ecommerce_store_name'],
        $currency);
    }
  }

}

/**
 * The MailChimp eCommerce data sync form.
 */
function mailchimp_ecommerce_admin_sync() {
  $form['sync_products'] = [
    '#type' => 'checkbox',
    '#title' => t('Sync Products'),
    '#description' => t('Sync all existing products to MailChimp.'),
  ];

  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Sync with MailChimp'),
  ];

  $form['#submit'][] = 'mailchimp_ecommerce_admin_sync_submit';

  return $form;
}

/**
 * Empty submit submit handler for the MailChimp eCommerce sync form.
 *
 * Syncing data is specific to each shopping cart implementation.
 * Use a form_alter hook to add a custom submit handler to the
 * 'mailchimp_ecommerce_admin_sync' form.
 */
function mailchimp_ecommerce_admin_sync_submit($form, &$form_state) {
}
