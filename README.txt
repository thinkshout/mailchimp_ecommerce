# MailChimp eCommerce

## Description

The MailChimp eCommerce module connects Drupal-based shopping carts to
Mailchimp's eCommerce 360 API.

For more information about eCommerce 360, please see:
http://kb.mailchimp.com/integrations/other-integrations/about-ecommerce360

## Dependencies
  * mailchimp: http://drupal.org/project/mailchimp
  * commerce: https://www.drupal.org/project/commerce

## Installation Notes
  * You need to have a MailChimp API Key.
  * You need to have the mailchimp module installed, and mailchimp_lists and
    mailchimp_campaign submodules enabled.
  * MailChimp E-Commerce's submodule, MailChimp eCommerce Commerce will need to
    be enabled.
  * The library will need to be renamed from mailchimp-api-php to mailchimp

## Configuration
  1. Follow the README.txt 7.x-4.x branch instructions for installation and
    configuration of the mailchimp module.
  2. Integrate your store with MailChimp by visiting this path:
    admin/config/services/mailchimp/ecommerce

## Related Modules
### Mailchimp
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
