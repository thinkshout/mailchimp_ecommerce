<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\commerce_price\Price;
use Drupal\mailchimp_ecommerce\CartHandler;
use Drupal\mailchimp_ecommerce\CustomerHandler;
use Drupal\mailchimp_ecommerce\OrderHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber for Commerce Carts.
 */
class CartEventSubscriber implements EventSubscriberInterface {

  /**
   * The Cart Handler.
   *
   * @var \Drupal\mailchimp_ecommerce\CartHandler
   */
  private $cart_handler;

  /**
   * The Order Handler.
   *
   * @var \Drupal\mailchimp_ecommerce\OrderHandler
   */
  private $order_handler;

  /**
   * The Customer Handler.
   *
   * @var \Drupal\mailchimp_ecommerce\CustomerHandler
   */
  private $customer_handler;

  /**
   * CartEventSubscriber constructor.
   *
   * @param \Drupal\mailchimp_ecommerce\CartHandler $cart_handler
   *   The Cart Handler.
   * @param \Drupal\mailchimp_ecommerce\OrderHandler $order_handler
   *   The Order Handler.
   * @param \Drupal\mailchimp_ecommerce\CustomerHandler $customer_handler
   *   The Customer Handler.
   */
  public function __construct(CartHandler $cart_handler, OrderHandler $order_handler, CustomerHandler $customer_handler) {
    $this->cart_handler = $cart_handler;
    $this->order_handler = $order_handler;
    $this->customer_handler = $customer_handler;
  }

  /**
   * Respond to event fired after adding a cart item.
   *
   * Initial cart creation in MailChimp needs to happen when the first cart
   * item is added. This is because we can't rely on the total price being
   * available when the Commerce Order itself is first created.
   */
  public function cartAdd(CartEntityAddEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getCart();

    // Process order for existing users.
    $account = $order->getCustomer();

    if (!empty($account)) {
      $customer = $this->customer_handler->buildCustomer($account);

      $this->customer_handler->addOrUpdateCustomer($customer);

      $order_data = $this->order_handler->buildOrder($order);

      // Add cart item price to order data.
      if (!isset($order_data['currency_code'])) {
        /** @var Price $price */
        $price = $event->getEntity()->getPrice();

        $order_data['currency_code'] = $price->getCurrencyCode();
        $order_data['order_total'] = $price->getNumber();
      }

      $this->cart_handler->addOrUpdateCart($order->id(), $customer, $order_data);
    }

    // TODO: Process order for guests with no user.
  }

  /**
   * Respond to event fired after updating a cart item.
   */
  public function cartItemUpdate(CartOrderItemRemoveEvent $event) {
    // TODO: Process item update in cart.
  }

  /**
   * Respond to event fired after removing a cart item.
   */
  public function cartItemRemove(CartOrderItemRemoveEvent $event) {
    // TODO: Process item removal from cart.
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CartEvents::CART_ENTITY_ADD][] = ['cartAdd'];
    $events[CartEvents::CART_ORDER_ITEM_UPDATE][] = ['cartItemUpdate'];
    $events[CartEvents::CART_ORDER_ITEM_REMOVE][] = ['cartItemRemove'];

    return $events;
  }

}
