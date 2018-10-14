<?php

/**
 * @file
 * Administration forms for Mailchimp eCommerce.
 */

/**
 * The Mailchimp eCommerce settings form.
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
    '#description' => t('The name of your store as it should appear in your Mailchimp account.'),
  ];
  $form['mailchimp_ecommerce_store_domain'] = [
    '#type' => 'textfield',
    '#title' => t('Store Domain'),
    '#required' => TRUE,
    '#default_value' => variable_get('mailchimp_ecommerce_store_domain', ''),
    '#description' => t('The domain of your store as it should appear in your Mailchimp account. Use absolute URL without protocol or trailing slash.'),
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
      '#weight' => 99,
    ];
    $platform = module_exists('mailchimp_ecommerce_ubercart') ? 'Ubercart' : 'Commerce';
    $form['sync']['products'] = [
      '#markup' => l(t('Sync existing @platform products to Mailchimp', ['@platform' => $platform]), 'admin/config/services/mailchimp/ecommerce/sync'),
    ];
  }

  // Identify the eCommerce platform to Mailchimp. This value is set in this
  // module's submodules for both Drupal Commerce and Ubercart.
  $form['platform'] = [
    '#type' => 'hidden',
    '#default_value' => '',
  ];

  $form['mailchimp_ecommerce_send_carts'] = [
    '#type' => 'checkbox',
    '#options' => [
      1 => 'Send all active Carts',
      0 => 'Send completed orders only',
    ],
    '#title' => t('Send Carts to Mailchimp'),
    '#default_value' => variable_get('mailchimp_ecommerce_send_carts', FALSE),
    '#description' => t('When enabled, shopping carts are sent to Mailchimp during each step of their lifecycle. When disabled, only completed orders are sent during Checkout completion.'),
  ];

  $form['mailchimp_ecommerce_use_queue'] = [
    '#type' => 'radios',
    '#options' => [1 => 'Queue requests', 0 => 'Do not use queue'],
    '#title' => t('Use Queue'),
    '#required' => TRUE,
    '#default_value' => variable_get('mailchimp_ecommerce_use_queue', 0),
    '#description' => t('Enable this to use the Queue API to process requests as background tasks. Requires Cron.'),
  ];

  $settings_form = system_settings_form($form);
  $settings_form['#submit'][] = 'mailchimp_ecommerce_admin_settings_submit';

  return $settings_form;
}

/**
 * Submit handler for the Mailchimp eCommerce form.
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

    $platform = !empty($form_state['values']['platform']) ? $form_state['values']['platform'] : '';

    if (empty($existing_store)) {
      $store = [
        'list_id' => isset($form_state['values']['mailchimp_ecommerce_list_id']) ? $form_state['values']['mailchimp_ecommerce_list_id'] : variable_get('mailchimp_ecommerce_list_id'),
        'name' => $form_state['values']['mailchimp_ecommerce_store_name'],
        'currency_code' => $currency,
        'domain' => check_plain($form_state['values']['mailchimp_ecommerce_store_domain']),
      ];

      mailchimp_ecommerce_add_store($store_id, $store, $platform);
    }
    else {
      mailchimp_ecommerce_update_store($store_id,
        $form_state['values']['mailchimp_ecommerce_store_name'],
        $currency,
        $platform);
    }
  }

}

/**
 * The Mailchimp eCommerce data sync form.
 */
function mailchimp_ecommerce_admin_sync() {
  $form['sync_products'] = [
    '#type' => 'checkbox',
    '#title' => t('Sync Products'),
    '#description' => t('Sync all existing products to Mailchimp.'),
  ];

  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Sync with Mailchimp'),
  ];

  $form['#submit'][] = 'mailchimp_ecommerce_admin_sync_submit';

  return $form;
}

/**
 * Empty submit submit handler for the Mailchimp eCommerce sync form.
 *
 * Syncing data is specific to each shopping cart implementation.
 * Use a form_alter hook to add a custom submit handler to the
 * 'mailchimp_ecommerce_admin_sync' form.
 */
function mailchimp_ecommerce_admin_sync_submit($form, &$form_state) {
}


/**
 * The Mailchimp eCommerce historical order data sync form.
 */
function mailchimp_ecommerce_admin_sync_orders() {
  $form['sync_orders'] = [
    '#type' => 'checkbox',
    '#title' => t('Sync Orders'),
    '#description' => t('Sync historical orders to Mailchimp.'),
  ];

  $form['timespan'] = [
    '#type' => 'textfield',
    '#title' => t('Time span'),
    '#default_value' => 6,
    '#field_suffix' => 'months',
    '#description' => 'Mailchimp recommends syncing the past 6 months of order data. Leave blank to sync all orders.',
    '#size' => 3,
    '#maxlength' => 3,
  ];

  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Sync with Mailchimp'),
  ];

  $form['#submit'][] = 'mailchimp_ecommerce_admin_sync_submit';

  return $form;
}

