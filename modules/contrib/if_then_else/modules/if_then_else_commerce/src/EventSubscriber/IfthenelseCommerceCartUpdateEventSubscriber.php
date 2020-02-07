<?php

namespace Drupal\if_then_else_commerce\EventSubscriber;

use Drupal\commerce_cart\Event\CartOrderItemUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\if_then_else\Controller\IfThenElseController;

/**
 * Ifthenelse commerce cart update event subscriber class.
 */
class IfthenelseCommerceCartUpdateEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_cart.order_item.update' => 'onCartItemUpdate',
    ];
    return $events;
  }

  /**
   * On cart item update.
   *
   * @param \Drupal\commerce_cart\Event\CartOrderItemUpdateEvent $cart_item_event
   *   The cart order item update event.
   */
  public function onCartItemUpdate(CartOrderItemUpdateEvent $cart_item_event) {
    $order_item = $cart_item_event->getOrderItem();
    $order = $order_item->getOrder();
    $quantity = $order_item->getQuantity();
    IfThenElseController::process('line_item_quantity_has_changed_action', [
      'order' => $order,
      'order_item' => $order_item,
      'quantity' => $quantity,
    ]);

  }

}
