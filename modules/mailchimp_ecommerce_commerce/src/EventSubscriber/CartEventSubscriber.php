<?php

namespace Drupal\mailchimp_ecommerce_commerce\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\mailchimp_ecommerce\CartHandler;
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
   * CartEventSubscriber constructor.
   *
   * @param \Drupal\mailchimp_ecommerce\CartHandler $cart_handler
   *   The Cart Handler.
   */
  public function __construct(CartHandler $cart_handler) {
    $this->cart_handler = $cart_handler;
  }

  /**
   * Respond to event fired after adding a cart item.
   */
  public function cartAdd(CartEntityAddEvent $event) {
    // TODO: Process item addition to cart.
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
