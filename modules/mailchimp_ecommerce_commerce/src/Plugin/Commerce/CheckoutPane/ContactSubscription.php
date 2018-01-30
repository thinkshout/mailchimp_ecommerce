<?php

namespace Drupal\mailchimp_ecommerce_commerce\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;

/**
 * Provides the subscription information pane.
 *
 * @CommerceCheckoutPane(
 *   id = "subscription_information",
 *   label = @Translation("Subscription information"),
 *   default_step = "order_information",
 *   wrapper_element = "fieldset",
 * )
 */
class ContactSubscription extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'label' => 'Subscribe to our newsletter',
        'review' => 0,
        'review_label' => 'Subscribe to newsletter:',
        'review_label_on' => 'Yes',
        'review_label_off' => 'No',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    $summary = '';

    if (!empty($this->configuration['label'])) {
      $summary .= $this->t('Label: @text', ['@text' => $this->configuration['label']]) . '<br/>';
    }

    if (isset($this->configuration['review'])) {
      $text = ($this->configuration['review'] == 1) ? $this->t('Yes') : $this->t('No');
      $summary .= $this->t('Display in review step: @text', ['@text' => $text]) . '<br/>';
    }

    if (!empty($this->configuration['review_label'])) {
      $summary .= $this->t('Review label: @text', ['@text' => $this->configuration['review_label']]) . '<br/>';
    }

    if (!empty($this->configuration['review_label_on'])) {
      $summary .= $this->t('Review label on: @text', ['@text' => $this->configuration['review_label_on']]) . '<br/>';
    }

    if (!empty($this->configuration['review_label_off'])) {
      $summary .= $this->t('Review label off: @text', ['@text' => $this->configuration['review_label_off']]) . '<br/>';
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form = parent::buildConfigurationForm($form, $form_state);
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->configuration['label'],
    ];
    $form['review'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display in review step'),
      '#default_value' => $this->configuration['review'],
    ];
    $form['review_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Review label'),
      '#default_value' => $this->configuration['review_label'],
    ];
    $form['review_label_on'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Review label on'),
      '#default_value' => $this->configuration['review_label_on'],
    ];
    $form['review_label_off'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Review label off'),
      '#default_value' => $this->configuration['review_label_off'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['label'] = $values['label'];
      $this->configuration['review'] = $values['review'];
      $this->configuration['review_label'] = $values['review_label'];
      $this->configuration['review_label_on'] = $values['review_label_on'];
      $this->configuration['review_label_off'] = $values['review_label_off'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    $steps = $this->checkoutFlow->getSteps();


    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {

    $pane_form = [];

    if ($this->configuration['review'] == 1) {

      // @TODO $form_state isn't available here. How can the value selected be
      // retrieved?
      $value_label = $this->configuration['review_label_on'];
      $pane_form['subscription'] = [
        '#type' => 'markup',
        '#markup' => $this->configuration['review_label'] . ' ' . $value_label
      ];
    }

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['subscription'] = [
      '#type' => 'checkbox',
      '#title' => $this->configuration['label'],
      '#default_value' => '',
      '#required' => FALSE,
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);

    if($values['subscription'] == 1) {
      $customer = [];
      $customer['email_address'] = $this->order->getEmail();

      if (!empty($customer['email_address'])) {
        /* @var \Drupal\mailchimp_ecommerce\CustomerHandler $customer_handler */
        $customer_handler = \Drupal::service('mailchimp_ecommerce.customer_handler');

        $billing_profile = $this->order->getBillingProfile();
        $customer = $customer_handler->buildCustomer($customer, $billing_profile);
        $customer_handler->addOrUpdateCustomer($customer);

        module_load_include('module', 'mailchimp', 'mailchimp');

        $list_id = mailchimp_ecommerce_get_list_id();

        $merge_vars = [
          'EMAIL' => $customer['email_address'],
          'FNAME' => $customer['first_name'],
          'LNAME' => $customer['last_name'],
        ];

        mailchimp_subscribe($list_id, $customer['email_address'], $merge_vars);
      }
    }
  }

}
