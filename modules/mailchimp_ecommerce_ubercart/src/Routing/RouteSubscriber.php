<?php

/**
 * @file
 * Contains \Drupal\mailchimp_ecommerce_ubercart\Routing\RouteSubscriber.
 */

namespace Drupal\mailchimp_ecommerce_ubercart\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('mailchimp_ecommerce.admin_settings')) {
      $route->setDefault('_form', '\Drupal\mailchimp_ecommerce_ubercart\Form\MailchimpEcommerceUbercartAdminSettings');
    }

    if ($route = $collection->get('mailchimp_ecommerce.sync')) {
      $route->setDefault('_form', '\Drupal\mailchimp_ecommerce_ubercart\Form\MailchimpEcommerceUbercartSync');
    }

  }

}
