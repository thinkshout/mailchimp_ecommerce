<?php
namespace Drupal\mailchimp_ecommerce_ubercart;

/**
 * Tests for MailChimp eCommerce integration with Drupal Commerce.
 */
class MailchimpEcommerceUbercartTestCase extends DrupalWebTestCase {

  /**
   * Returns info displayed in the test interface.
   *
   * @return array
   *   Formatted as specified by simpletest.
   */
  public static function getInfo() {
    return array(
      'name' => 'MailChimp eCommerce Ubercart',
      'description' => 'Test MailChimp eCommerce integration with Ubercart.',
      'group' => 'MailChimp',
    );
  }

  /**
   * Pre-test setup function.
   *
   * Enables dependencies.
   * Sets the mailchimp_api_key to the test-mode key.
   * Sets test mode to TRUE.
   */
  protected function setUp() {
    $this->profile = drupal_get_profile();

    // Enable modules required for the test.
    $enabled_modules = array(
      'commerce',
      'entity',
      'libraries',
      'mailchimp',
      'mailchimp_activity',
      'mailchimp_campaign',
      'mailchimp_ecommerce',
      'mailchimp_ecommerce_commerce',
      'mailchimp_signup',
    );

    parent::setUp($enabled_modules);

    // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// variable_set('mailchimp_api_key', 'MAILCHIMP_TEST_API_KEY');

    // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// variable_set('mailchimp_test_mode', TRUE);

  }

  /**
   * Post-test function.
   *
   * Sets test mode to FALSE.
   */
  protected function tearDown() {
    parent::tearDown();

    // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// variable_del('mailchimp_test_mode');

  }

  /**
   * Tests adding a store to MailChimp.
   */
  public function testAddStore() {
    $admin = $this->drupalCreateUser(array('administer site configuration', 'administer mailchimp'));
    $this->drupalLogin($admin);

    $edit = array();

    $edit['mailchimp_ecommerce_integration'] = 'commerce';
    $edit['mailchimp_ecommerce_store_name'] = 'Freddie\'s Merchandise';
    $edit['mailchimp_ecommerce_list_id'] = '57afe96172';
    $edit['mailchimp_ecommerce_currency'] = 'USD';

    $this->drupalPost('admin/config/services/mailchimp/ecommerce', $edit, t('Save configuration'));

    $this->assertText(t('The configuration options have been saved.'), 'The text "The configuration options have been saved." appears on the page that adds a store.');
  }

  /**
   * Tests adding a store to MailChimp with no list.
   */
  public function testAddStoreNoList() {
    $admin = $this->drupalCreateUser(array('administer site configuration', 'administer mailchimp'));
    $this->drupalLogin($admin);

    $edit = array();

    $edit['mailchimp_ecommerce_integration'] = 'commerce';
    $edit['mailchimp_ecommerce_store_name'] = 'Freddie\'s Merchandise';
    $edit['mailchimp_ecommerce_currency'] = 'USD';

    $this->drupalPost('admin/config/services/mailchimp/ecommerce', $edit, t('Save configuration'));

    $this->assertNoText(t('The configuration options have been saved.'), 'The text "The configuration options have been saved." should not appear on the page that adds a store.');
    $this->assertText(t('Store List field is required.'), 'The text "Store List field is required." should appear on the page, indicating a form validation error.');
  }

  /**
   * Tests adding a product to MailChimp.
   */
  public function testAddProduct() {
    // Should pass.
    $admin = $this->drupalCreateUser(array(
      'administer site configuration',
      'configure store',
    ));
    $this->drupalLogin($admin);

    $edit = array();

    $edit['sku'] = 'Jokes002';
    $edit['title'] = 'Freddie\'s Jokes Volume 2';
    $edit['commerce_price[und][0][amount]'] = 5;
    $edit['status'] = 1;

    // TODO Ubercart path
    $this->drupalPost('admin/commerce/products/add/product', $edit, t('Save product'));

    $this->assertText(t('Product saved.'), 'The text "Product saved." should appear on the page, indicating a product was added successfully.');
  }

  /**
   * Tests adding a product to MailChimp with no product variants.
   */
  public function testAddProductNoVariants() {
    // Action should fail.
  }

  /**
   * Tests adding a customer to MailChimp.
   */
  public function testAddCustomer() {
    // Should pass.
//    $customer = [
//      'id' => '001',
//      'email_address' => 'sarah@example.com',
//      'first_name' => 'Sarah',
//      'last_name' => 'Connor'
//    ];
//    mailchimp_ecommerce_add_customer($customer);
//    $new_customer = mailchimp_ecommerce_get_customer($customer['id']);
//    var_dump($new_customer);
//
//    $this->assertEqual($new_customer['id'], $customer['id']);
//    $this->assertEqual($new_customer['email_address'], $customer['email_address']);
//    $this->assertEqual($new_customer['first_name'], $customer['first_name']);
//    $this->assertEqual($new_customer['last_name'], $customer['last_name']);
  }

  /**
   * Tests adding a customer to MailChimp with no email address.
   */
  public function testAddCustomerNoEmail() {
    // Action should fail.
  }

}
