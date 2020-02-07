<?php

namespace Drupal\if_then_else\core\Nodes\Actions\RemoveItemFromListAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Remove Item From List action class.
 */
class RemoveItemFromListAction extends Action {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'remove_item_from_list_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Remove Item From List'),
      'description' => $this->t('Remove Item From List'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\RemoveItemFromListAction\\RemoveItemFromListAction',
      'inputs' => [
        'list' => [
          'label' => $this->t('List'),
          'description' => $this->t('An array to remove an item from.'),
          'sockets' => ['array'],
          'required' => TRUE,
        ],
        'item' => [
          'label' => $this->t('Item'),
          'description' => $this->t('An item to remove from the array.'),
          'sockets' => ['string', 'number', 'array'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'list' => [
          'label' => $this->t('List'),
          'description' => $this->t('List'),
          'socket' => 'array',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $list = $this->inputs['list'];
    $item = $this->inputs['item'];
    foreach (array_keys($list, $item) as $key) {
      unset($list[$key]);
    }
    $this->outputs['list'] = $list;

  }

}
