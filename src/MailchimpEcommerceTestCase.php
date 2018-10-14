<?php
namespace Drupal\mailchimp_ecommerce;

/**
 * Tests for Mailchimp eCommerce core integration.
 */
class MailchimpEcommerceTestCase extends DrupalWebTestCase {

  /**
   * Returns info displayed in the test interface.
   *
   * @return array
   *   Formatted as specified by simpletest.
   */
  public static function getInfo() {
    return array(
      'name' => 'Mailchimp eCommerce',
      'description' => 'Test Mailchimp eCommerce core integration.',
      'group' => 'Mailchimp',
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
      'mailchimp_ecommerce',
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


    \Drupal::configFactory()->getEditable('mailchimp_ecommerce.settings')->set('mailchimp_ecommerce_store_id', 1)->save();
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


    \Drupal::config('mailchimp_ecommerce.settings')->clear('mailchimp_ecommerce_store_id')->save();
  }

  /**
   * Tests adding a store to Mailchimp.
   */
  public function testAddStore() {
    $store_id = 1;
    $store = array(
      'list_id' => '57afe96172',
      'name' => 'Freddie\'s Merchandise',
      'currency_code' => 'USD',
    );

    mailchimp_ecommerce_add_store($store_id, $store);

    $saved_store = mailchimp_ecommerce_get_store($store_id);

    $this->assertEqual($saved_store->id, $store_id);
    $this->assertEqual($saved_store->name, $store['name']);
    $this->assertEqual($saved_store->currency_code, $store['currency_code']);
  }

  /**
   * Tests adding an order to Mailchimp.
   */
  public function testAddOrder() {
    $store_id = 1;
    $order_id = 1;
    $customer = [
      'id' => 1,
      'email_address' => 'testuser@example.com',
      'first_name' => 'Test',
      'last_name' => 'User',
    ];
    $order = [
      'currency_code' => 'USD',
      'order_total' => 29.98,
      'lines' => [
        (object) [
          'id' => 1,
          'product_id' => 11,
          'product_variant_id' => 11,
          'quantity' => 1,
          'price' => 9.99,
        ],
        'lines' => [
          (object) [
            'id' => 2,
            'product_id' => 12,
            'product_variant_id' => 12,
            'quantity' => 1,
            'price' => 19.99,
          ],
        ],
      ],
    ];

    mailchimp_ecommerce_add_order($order_id, $customer, $order);

    /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
    $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
    $saved_order = $mc_ecommerce->getOrder($store_id, $order_id);

    $this->assertEqual($saved_order->id, $order_id);
    $this->assertEqual($saved_order->currency_code, $order['currency_code']);
    $this->assertEqual($saved_order->order_total, $order['order_total']);
  }

  /**
   * Tests adding an order to Mailchimp with no line items.
   */
  public function testAddOrderNoLineItems() {
    $store_id = 1;
    $order_id = 1;
    $customer = [
      'id' => 1,
      'email_address' => 'testuser@example.com',
      'first_name' => 'Test',
      'last_name' => 'User',
    ];
    $order = [
      'currency_code' => 'USD',
      'order_total' => 19.99,
    ];

    mailchimp_ecommerce_add_order($order_id, $customer, $order);

    /* @var \Mailchimp\MailchimpEcommerce $mc_ecommerce */
    $mc_ecommerce = mailchimp_get_api_object('MailchimpEcommerce');
    $saved_order = $mc_ecommerce->getOrder($store_id, $order_id);

    $this->assertNull($saved_order);
  }

}
