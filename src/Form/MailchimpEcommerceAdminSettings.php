<?php

/**
 * @file
 * Contains \Drupal\mailchimp_ecommerce\Form\MailchimpEcommerceAdminSettings.
 */

namespace Drupal\mailchimp_ecommerce\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class MailchimpEcommerceAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_ecommerce_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('mailchimp_ecommerce.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailchimp_ecommerce.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['mailchimp_ecommerce_notice'] = [
      '#markup' => t('This page will allow you to create a store. Once created, you cannot change the list associated with the store.')
      ];
    $form['mailchimp_ecommerce_store_name'] = [
      '#type' => 'textfield',
      '#title' => t('Store Name'),
      '#required' => TRUE,
      '#default_value' => \Drupal::config('mailchimp_ecommerce.settings')->get('mailchimp_ecommerce_store_name'),
      '#description' => t('The name of your store as it should appear in your MailChimp account.'),
    ];

    $mailchimp_lists = mailchimp_get_lists();
    $list_options = ['' => '-- Select --'];

    foreach ($mailchimp_lists as $list_id => $list) {
      $list_options[$list_id] = $list->name;
    }

    if (!empty(\Drupal::config('mailchimp_ecommerce.settings')->get('mailchimp_ecommerce_list_id'))) {
      // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/mailchimp_ecommerce.settings.yml and config/schema/mailchimp_ecommerce.schema.yml.
      $existing_store_id = \Drupal::config('mailchimp_ecommerce.settings')->get('mailchimp_ecommerce_list_id');
      $form['mailchimp_ecommerce_list_id_existing'] = [
        '#markup' => t('Once created, the list cannot be changed for a given store. This store is connected to the list named') . ' ' . $list_options[$existing_store_id]
        ];
    }
    else {
      $form['mailchimp_ecommerce_list_id'] = [
        '#type' => 'select',
        '#title' => t('Store List'),
        '#required' => TRUE,
        '#options' => $list_options,
        '#default_value' => \Drupal::config('mailchimp_ecommerce.settings')->get('mailchimp_ecommerce_list_id'),
      ];
    }

    $list_options_currency = ['' => '-- Select --'] + mailchimp_ecommerce_get_currency_codes();
    $form['mailchimp_ecommerce_currency'] = [
      '#type' => 'select',
      '#options' => $list_options_currency,
      '#title' => t('Store Currency Code'),
      '#required' => TRUE,
      '#description' => t('This is overridden if you have selected to use the default currency from Commerce.'),
    ];

    $list_options = [
      '' => '-- Select --',
      1 => 'Normal Subscriber',
      0 => 'Transactional Subscriber',
    ];

    $form['mailchimp_ecommerce_opt_in'] = [
      '#type' => 'fieldset',
      '#title' => t('New User Opt-In Status'),
      '#collapsible' => FALSE,
    ];
    $form['mailchimp_ecommerce_opt_in']['mailchimp_ecommerce_opt_in_status_markup'] = [
      '#markup' => t('You must decide on the status of customers that entered into
     the eCommerce API. By choosing "<b>Normal Subscriber</b>" in the option
     below, users will added as normal subscribers.  If you choose
     "Normal Subscriber", be certain your customers know they are subscribing
     to an email list. If you choose "<b>Transactional Subscriber</b>" below,
     the users will be added as "transactional" users. Transactional users
     cannot be changed via the MailChimp UI. Changing the status of a 
     "transactional" user call only be accomplished via the API. For additional
     information, please read the') . ' ' . \Drupal::l(t('MailChimp Documentation.'), \Drupal\Core\Url::fromUri('http://developer.mailchimp.com/documentation/mailchimp/guides/getting-started-with-ecommerce/#about-subscribers-and-customers'))
      ];

    $form['mailchimp_ecommerce_opt_in']['mailchimp_ecommerce_opt_in_status'] = [
      '#type' => 'select',
      '#title' => t('Opt-In Status For Customers'),
      '#required' => TRUE,
      '#options' => $list_options,
      '#default_value' => \Drupal::config('mailchimp_ecommerce.settings')->get('mailchimp_ecommerce_opt_in_status'),
      '#description' => t('Choose your opt-in status before using this module.'),
    ];

    $settings_form = parent::buildForm($form, $form_state);
    $settings_form['#submit'][] = 'mailchimp_ecommerce_admin_settings_submit';

    return $settings_form;
  }

  public function _submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $store_id = \Drupal::config('mailchimp_ecommerce.settings')->get('mailchimp_ecommerce_store_id');
    if (\Drupal::config('mailchimp_ecommerce.settings')->get('mailchimp_ecommerce_store_id') == NULL) {
      $store_id = mailchimp_ecommerce_generate_store_id();
      \Drupal::configFactory()->getEditable('mailchimp_ecommerce.settings')->set('mailchimp_ecommerce_store_id', $store_id)->save();
    }

    if ($store_id != NULL) {
      $currency = $form_state->getValue(['mailchimp_ecommerce_currency']);

      // Save value as boolean.
      if ($form_state->getValue(['mailchimp_ecommerce_opt_in_status']) == 1) {
        \Drupal::configFactory()->getEditable('mailchimp_ecommerce.settings')->set('mailchimp_ecommerce_opt_in_status', TRUE)->save();
      }
      else {
        \Drupal::configFactory()->getEditable('mailchimp_ecommerce.settings')->set('mailchimp_ecommerce_opt_in_status', FALSE)->save();
      }

      // Determine if a store is being created or updated.
      $existing_store = mailchimp_ecommerce_get_store($store_id);

      if (empty($existing_store)) {
        // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/mailchimp_ecommerce.settings.yml and config/schema/mailchimp_ecommerce.schema.yml.
        $store = [
          'list_id' => !$form_state->getValue(['mailchimp_ecommerce_list_id']) ? $form_state->getValue(['mailchimp_ecommerce_list_id']) : \Drupal::config('mailchimp_ecommerce.settings')->get('mailchimp_ecommerce_list_id'),
          'name' => $form_state->getValue(['mailchimp_ecommerce_store_name']),
          'currency_code' => $currency,
        ];

        mailchimp_ecommerce_add_store($store_id, $store);
      }
      else {
        mailchimp_ecommerce_update_store($store_id, $form_state->getValue(['mailchimp_ecommerce_store_name']), $currency);
      }
    }

  }

}
?>
