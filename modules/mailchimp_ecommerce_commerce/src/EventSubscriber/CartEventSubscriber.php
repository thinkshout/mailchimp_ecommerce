<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\commerce_cart\Event\CartOrderItemUpdateEvent;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\Core\Url;
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

    $customer['email_address'] = $order->getEmail();

    if (empty($customer['email_address'])) {
      // Cannot create or add an item to a cart with no customer email address.
      return;
    }

    if ($this->cart_handler->cartExists($order->id())) {
      // Add item to the existing cart.
      /** @var \Drupal\commerce_order\Entity\OrderItem $order_item */
      $order_item = $event->getOrderItem();

      $product = $this->order_handler->buildProduct($order_item);

      $this->cart_handler->addCartLine($order->id(), $order_item->id(), $product);
    }
    else {
      // Create a new cart.
      $billing_profile = $order->getBillingProfile();
      $customer = $this->customer_handler->buildCustomer($customer, $billing_profile);

      // Update or add customer in case this is a new cart.
      $this->customer_handler->addOrUpdateCustomer($customer);

      $order_data = $this->order_handler->buildOrder($order, $customer);

      // Add cart total price to order data.
      if (!isset($order_data['currency_code'])) {
        /** @var Price $price */
        $price = $event->getEntity()->getPrice();

        $order_data['currency_code'] = $price->getCurrencyCode();
        $order_data['order_total'] = $price->getNumber();
      }

      $order_data['checkout_url'] = Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order->id()])->toString();
      $this->cart_handler->addOrUpdateCart($order->id(), $customer, $order_data);
    }
  }

  /**
   * Respond to event fired after updating a cart item.
   */
  public function cartItemUpdate(CartOrderItemUpdateEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getCart();
    /** @var \Drupal\commerce_order\Entity\OrderItem $order_item */
    $order_item = $event->getOrderItem();

    $product = $this->order_handler->buildProduct($order_item);

    $this->cart_handler->updateCartLine($order->id(), $order_item->id(), $product);
  }

  /**
   * Respond to event fired after removing a cart item.
   */
  public function cartItemRemove(CartOrderItemRemoveEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getCart();

    $this->cart_handler->deleteCartLine($order->id(), $event->getOrderItem()->id());
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
