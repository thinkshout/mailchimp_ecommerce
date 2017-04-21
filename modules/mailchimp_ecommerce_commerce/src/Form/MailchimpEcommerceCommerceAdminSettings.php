<?php

/**
 * @file
 * Contains \Drupal\mailchimp_ecommerce_commerce\Form\MailchimpEcommerceCommerceAdminSettings.
 */

namespace Drupal\mailchimp_ecommerce_commerce\Form;

use Drupal\commerce_store\StoreContextInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mailchimp_ecommerce\Form\MailchimpEcommerceAdminSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MailchimpEcommerceCommerceAdminSettings extends MailchimpEcommerceAdminSettings {

  /**
   * The Store Context Interface.
   *
   * @var \Drupal\commerce_store\StoreContextInterface $store_context
   */
  private $store_context;

  /**
   * MailchimpEcommerceCommerceAdminSettings constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Config Factory Interface.
   * @param \Drupal\commerce_store\StoreContextInterface $store_context
   *   The Store Context Interface.
   */
  public function __construct(ConfigFactoryInterface $config_factory, StoreContextInterface $store_context) {
    parent::__construct($config_factory);

    $this->store_context = $store_context;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('commerce_store.store_context')
    );
  }

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
