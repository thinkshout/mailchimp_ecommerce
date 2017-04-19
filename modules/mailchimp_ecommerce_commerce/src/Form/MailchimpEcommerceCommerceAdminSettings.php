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
   * @var \Drupal\commerce_store\StoreContextInterface $storeContext
   */
  private $storeContext;

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

    $this->storeContext = $store_context;
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

    // TODO: Get Commerce stores from store context.
    // TODO: Get default currency from selected Commerce store.
    $form['mailchimp_ecommerce_store_name']['#default_value'] = 'USD';

    return $form;
  }

}
