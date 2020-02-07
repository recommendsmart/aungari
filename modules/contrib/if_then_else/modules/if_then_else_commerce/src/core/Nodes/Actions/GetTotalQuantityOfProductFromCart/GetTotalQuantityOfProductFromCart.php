<?php

namespace Drupal\if_then_else_commerce\core\Nodes\Actions\GetTotalQuantityOfProductFromCart;

use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\if_then_else_commerce\core\IfthenelseCommerceUtilitiesInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\commerce_cart\CartProviderInterface;

/**
 * Get total quantity of product from cart class.
 */
class GetTotalQuantityOfProductFromCart extends Action {
  use StringTranslationTrait;

  /**
   * The ifthenelse utilities.
   *
   * @var \Drupal\if_then_else_commerce\core\IfthenelseCommerceUtilitiesInterface
   */
  protected $ifthenelseCommerceUtilities;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseCommerceUtilitiesInterface $ifthenelseCommerceUtilities
   *   The ifthenelse utilities.
   * @param \Drupal\commerce_cart\CartProviderInterface $cartProvider
   *   Creates and loads carts for anonymous and authenticated users.
   */
  public function __construct(IfthenelseCommerceUtilitiesInterface $ifthenelseCommerceUtilities, CartProviderInterface $cartProvider) {
    $this->ifthenelseCommerceUtilities = $ifthenelseCommerceUtilities;
    $this->cartProvider = $cartProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'get_total_quantity_of_product_from_cart_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Calling custom service for if then else utilities. To
    // fetch values of product type and product sku.
    $product_types = $this->ifthenelseCommerceUtilities->getAllProductTypes();
    $product_skus = $this->ifthenelseCommerceUtilities->getAllProductSku();

    $event->nodes[static::getName()] = [
      'label' => $this->t('Get Total Quantity From Cart'),
      'description' => $this->t('Get total quantity of product from cart.'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else_commerce\\core\\Nodes\\Actions\\GetTotalQuantityOfProductFromCart\\GetTotalQuantityOfProductFromCart',
      'library' => 'if_then_else_commerce/GetTotalQuantityOfProductFromCart',
      'control_class_name' => 'GetTotalQuantityOfProductFromCartControl',
      'product_types' => $product_types,
      'product_skus' => $product_skus,
      'classArg' => ['if_then_else_commerce.utilities', 'commerce_cart.cart_provider'],
      'dependencies' => ['commerce_cart'],
      'outputs' => [
        'quantity' => [
          'label' => $this->t('Quantity'),
          'description' => $this->t('Total quantity of product from cart.'),
          'socket' => 'number',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;

    if (!property_exists($data, 'form_selection')) {
      $event->errors[] = $this->t('Select the Match Condition in "@node_name".', ['@node_name' => $event->node->name]);
      return;
    }

    if ($data->form_selection == 'list' && empty($data->selected_product_type)) {
      // Make sure that both selected_entity and selected_bundle are set.
      $event->errors[] = $this->t('Select product type in "@node_name".', ['@node_name' => $event->node->name]);
    }
    elseif ($data->form_selection == 'other' && empty($data->selected_product_sku)) {
      $event->errors[] = $this->t('Select product SKU in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    /* @var CartProviderInterface $cart_provider */
    $cart_provider = $this->cartProvider;
    $carts = $cart_provider->getCarts();
    $carts = array_filter($carts, function ($cart) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $cart */
      // There is a chance the cart may have converted from a draft order, but
      // is still in session. Such as just completing check out. So we verify
      // that the cart is still a cart.
      return $cart->hasItems() && $cart->cart->value;
    });
    $no_items_in_cart = 0;
    $selected_product_type = $this->data->selected_product_type;
    $selected_product_sku = $this->data->selected_product_sku;
    $form_selection = $this->data->form_selection;
    $selected_sku = [];
    if (!empty($selected_product_sku) && ($form_selection == 'other')) {
      foreach ($selected_product_sku as $value) {
        $selected_sku[] = $value->label;
      }
    }
    $selected_type = [];
    if (!empty($selected_product_type) && ($form_selection == 'list')) {
      foreach ($selected_product_type as $value) {
        $selected_type[] = $value->id;
      }
    }
    if (!empty($carts)) {
      foreach ($carts as $cart) {
        foreach ($cart->getItems() as $order_item) {
          $purchasedEntity = $order_item->getPurchasedEntity();
          if ($form_selection == 'all') {
            $no_items_in_cart += (int) $order_item->getQuantity();
          }
          elseif ($form_selection == 'other') {
            $sku = $purchasedEntity->getSku();
            if (in_array($sku, $selected_sku)) {
              $no_items_in_cart += (int) $order_item->getQuantity();
            }
          }
          elseif ($form_selection == 'list') {
            $product = $purchasedEntity->getProduct();
            $product_type = $product->type->entity->id();
            if (in_array($product_type, $selected_type)) {
              $no_items_in_cart += (int) $order_item->getQuantity();
            }
          }
        }
      }
    }

    $this->outputs['quantity'] = $no_items_in_cart;

  }

}
