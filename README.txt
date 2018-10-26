# Mailchimp eCommerce

## Description

The Mailchimp eCommerce module connects Drupal-based shopping carts to
Mailchimp's eCommerce API.

For more information about Mailchimp eCommerce integration, please see:

https://mailchimp.com/resources/mailchimp-e-commerce/

## Dependencies

  * Mailchimp: https://www.drupal.org/project/mailchimp
  * Mailchimp Lists

## Optional Dependencies

  This module supports two eCommerce platforms. Only one should be enabled
  at any time.

  * Commerce: https://www.drupal.org/project/commerce
  * Ubercart: https://www.drupal.org/project/ubercart

## Installation

  * Ensure you have the Mailchimp module configured with your API key.

## Installation Notes

  ### For Drupal Commerce

    * Enable the Mailchimp eCommerce Commerce module.

  ### For Ubercart

    * Enable the Mailchimp eCommerce Ubercart module.

## Configuration

  * Connect your Drupal Commerce or Ubercart store to MailChimp at this path:
      /admin/config/services/mailchimp/ecommerce

  * Enter a name for your store and select a MailChimp list.
    Stores are mapped one-to-one with lists.

## For Existing Stores

  If you already have an active Drupal Commerce or Ubercart store, you must
  sync your existing products to MailChimp. You may optionally sync existing
  orders.

  You will find options to sync both products and orders at this path:
    /admin/config/services/mailchimp/ecommerce

  If syncing existing orders, you will need to sync existing products first.

## Testing

  ### Mailchimp API Playground

    Mailchimp provides a way to interact with their API via your web browser.

    See: https://us1.api.mailchimp.com/playground/

    You are able to execute API commands that will show your eCommerce stores,
    products, orders, and customers here.

  ### Drush Commands

    This module provides these drush commands:

      * drush mcstores
        Retrieve a list of stores attached to your API key.

      * drush mcorders [store ID]
        Retrieve a list of orders for a given store ID.
        Store IDs are available via the mcstores drush command.

## Related Modules

  ### Mailchimp - http://drupal.org/project/mailchimp

    * This module provides integration with Mailchimp, a popular email delivery
      service. The module makes it easy for website users or visitors to control
      which of your email lists they want to be on (or off), lets you generate
      and send Mailchimp email campaigns from your site, and lets you and your
      users view a history of emails they have been sent from Mailchimp.
      More generally, it aspires to makes your email delivery world efficient
      and to make your user's email receipt and control simple and precise.

  ### Mandrill - http://drupal.org/project/mandrill

    * Mandrill is Mailchimp's transactional email service. The module provides
      the ability to send all site emails through Mandrill with reporting
      available from within Drupal. Please refer to the project page for more
      details.
