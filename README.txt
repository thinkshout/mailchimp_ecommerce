# Mailchimp eCommerce

## Description

The Mailchimp eCommerce module connects Drupal-based shopping carts to
Mailchimp's eCommerce API.

For more information about Mailchimp eCommerce integration, please see:
https://kb.mailchimp.com/integrations/e-commerce/sell-more-stuff-with-mailchimp

## Dependencies
  * Mailchimp: https://www.drupal.org/project/mailchimp
  * Commerce: https://www.drupal.org/project/commerce

## Installation Notes
  * Make sure you have a Mailchimp API Key.
  * Make sure the Mailchimp and Mailchimp Lists modules are enabled.
  * Presently the only shopping cart supported is Drupal Commerce. Enable the
    Mailchimp eCommerce Commerce module to integrate with Drupal Commerce.

## Configuration
  1. Follow the Mailchimp module installation instructions in README.txt on the
     Mailchimp 7.x-4.x branch.
  2. Integrate your store with Mailchimp by visiting this path:
     admin/config/services/mailchimp/ecommerce

## Related Modules

### Mailchimp
  * This module provides integration with Mailchimp, a popular email delivery
    service. The module makes it easy for website users or visitors to control
    which of your email lists they want to be on (or off), lets you generate and
    send Mailchimp email campaigns from your site, and lets you and your users
    view a history of emails they have been sent from Mailchimp. More generally,
    it aspires to makes your email delivery world efficient and to make your
    user's email receipt and control simple and precise.
  * http://drupal.org/project/mailchimp

### Mandrill
  * Mandrill is Mailchimp's transactional email service. The module provides the
    ability to send all site emails through Mandrill with reporting available
    from within Drupal. Please refer to the project page for more details.
  * http://drupal.org/project/mandrill
