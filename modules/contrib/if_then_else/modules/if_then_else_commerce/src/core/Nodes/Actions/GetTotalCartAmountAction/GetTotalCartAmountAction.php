<?php

namespace Drupal\if_then_else_commerce\core\Nodes\Actions\GetTotalCartAmountAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Get total amount from cart class.
 */
class GetTotalCartAmountAction extends Action {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'get_total_amount_from_cart_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Get Total Cart Amount'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else_commerce\\core\\Nodes\\Actions\\GetTotalCartAmountAction\\GetTotalCartAmountAction',
      'dependencies' => ['commerce_cart'],
      'outputs' => [
        'amount' => [
          'label' => $this->t('Amount'),
          'description' => $this->t('Total cart amount.'),
          'socket' => 'number',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    /* @var CartProviderInterface $cart_provider */
    $cart_provider = \Drupal::service('commerce_cart.cart_provider');

    $carts = $cart_provider->getCarts();
    $carts = array_filter($carts, function ($cart) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $cart */
      // There is a chance the cart may have converted from a draft order, but
      // is still in session. Such as just completing check out. So we verify
      // that the cart is still a cart.
      return $cart->hasItems() && $cart->cart->value;
    });
    $no_items_in_cart = 0;
    if (!empty($carts)) {
      foreach ($carts as $cart) {
        $no_items_in_cart += $cart->getTotalPrice()->getNumber();
      }
    }

    $this->outputs['amount'] = $no_items_in_cart;

  }

}
