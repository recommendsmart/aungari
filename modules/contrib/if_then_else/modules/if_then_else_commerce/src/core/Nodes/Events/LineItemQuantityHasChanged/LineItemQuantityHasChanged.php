<?php

namespace Drupal\if_then_else_commerce\core\Nodes\Events\LineItemQuantityHasChanged;

use Drupal\if_then_else\core\Nodes\Events\Event;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Line item quantity has changed class.
 */
class LineItemQuantityHasChanged extends Event {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'line_item_quantity_has_changed_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Line item quantity changed'),
      'type' => 'event',
      'class' => 'Drupal\\if_then_else_commerce\\core\\Nodes\\Events\\LineItemQuantityHasChanged\\LineItemQuantityHasChanged',
      'dependencies' => ['commerce_cart'],
      'outputs' => [
        'order' => [
          'label' => $this->t('Order'),
          'description' => $this->t('Order object.'),
          'socket' => 'object.entity',
        ],
        'order_item' => [
          'label' => $this->t('Order Item'),
          'description' => $this->t('Order item object.'),
          'socket' => 'object.entity',
        ],
        'quantity' => [
          'label' => $this->t('Quantity'),
          'description' => $this->t('Quantity.'),
          'socket' => 'number',
        ],
      ],
    ];
  }

}
