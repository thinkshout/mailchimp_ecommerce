# MailChimp eCommerce

## Description

The MailChimp eCommerce module connects Drupal-based shopping carts to
Mailchimp's eCommerce API.

For more information about Mailchimp eCommerce integration, please see:
https://kb.mailchimp.com/integrations/e-commerce/sell-more-stuff-with-mailchimp

## Dependencies
  * MailChimp: https://www.drupal.org/project/mailchimp
  * Commerce: https://www.drupal.org/project/commerce

## Installation Notes
  * Make sure you have a MailChimp API Key.
  * Make sure the MailChimp and MailChimp Lists modules are enabled.
  * Presently the only shopping cart supported is Drupal Commerce. Enable the
    MailChimp eCommerce Commerce module to integrate with Drupal Commerce.

## Configuration
  1. Follow the MailChimp module installation instructions in README.txt on the
     MailChimp 7.x-4.x branch.
  2. Integrate your store with MailChimp by visiting this path:
     admin/config/services/mailchimp/ecommerce

## Related Modules

### MailChimp
  * This module provides integration with MailChimp, a popular email delivery
    service. The module makes it easy for website users or visitors to control
    which of your email lists they want to be on (or off), lets you generate and
    send MailChimp email campaigns from your site, and lets you and your users
    view a history of emails they have been sent from MailChimp. More generally,
    it aspires to makes your email delivery world efficient and to make your
    user's email receipt and control simple and precise.
  * http://drupal.org/project/mailchimp

### Mandrill
  * Mandrill is MailChimp's transactional email service. The module provides the
    ability to send all site emails through Mandrill with reporting available
    from within Drupal. Please refer to the project page for more details.
  * http://drupal.org/project/mandrill
