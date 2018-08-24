<?php

namespace Drupal\mailchimp_ecommerce_commerce\Form;

use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mailchimp_ecommerce\Form\MailchimpEcommerceAdminSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MailchimpEcommerceCommerceAdminSettings extends MailchimpEcommerceAdminSettings {

  /**
   * The Store Context Interface.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface $store_context
   */
  protected $store_context;

  /**
   * The Store Handler Interface.
   *
   * @var \Drupal\mailchimp_ecommerce\StoreHandler $store_handler
   */
  protected $store_handler;

  /**
   * MailchimpEcommerceAdminSettings constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Config Factory Interface.
   * @param mixed $store_context
   *   The Store Context Interface.
   * @param \Drupal\mailchimp_ecommerce\StoreHandlerInterface $store_handler
   *   The Store Handler Interface.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CurrentStoreInterface $store_context, StoreHandlerInterface $store_handler) {
    parent::__construct($config_factory);

    $this->store_context = $store_context;
    $this->store_handler = $store_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    if (\Drupal::moduleHandler()->moduleExists('commerce')) {
      $static = new static(
        $container->get('config.factory'),
        $container->get('commerce_store.current_store'),
        $container->get('mailchimp_ecommerce.store_handler')
      );
    }
    else {
      $static = new static(
        $container->get('config.factory'),
        '',
        $container->get('mailchimp_ecommerce.store_handler')
      );
    }

    return $static;
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

    // Identify Drupal Commerce to MailChimp.
    $form['platform']['#default_value'] = 'Drupal Commerce';

    return $form;
  }

}
