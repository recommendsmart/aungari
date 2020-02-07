<?php

namespace Drupal\if_then_else\core\Nodes\Actions\AddItemListAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Add Item To A List action class.
 */
class AddItemListAction extends Action {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'add_item_list_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Add Item To List'),
      'description' => $this->t('Add Item To List'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\AddItemListAction\\AddItemListAction',
      'library' => 'if_then_else/AddItemListAction',
      'control_class_name' => 'AddItemListActionControl',
      'compare_options' => [
        ['code' => 'start', 'name' => 'Add to beginning of the list'],
        ['code' => 'end', 'name' => 'Add to end of the list'],
      ],
      'inputs' => [
        'list' => [
          'label' => $this->t('List'),
          'description' => $this->t('A list to which an item is added.'),
          'sockets' => ['array'],
          'required' => TRUE,
        ],
        'item' => [
          'label' => $this->t('Item'),
          'description' => $this->t('An item being added to the list.'),
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
    $unique = FALSE;
    $position = 'end';
    if (isset($this->data->selection)) {
      $unique = $this->data->selection;
    }
    if (!empty($this->data->postion)) {
      $position = $this->data->postion[0]->code;
    }
    // Optionally, only add the list item if it is not yet contained.
    if (!((bool) $unique && in_array($item, $list))) {
      if ($position === 'start') {
        array_unshift($list, $item);
      }
      else {
        $list[] = $item;
      }
    }
    $this->outputs['list'] = $list;

  }

}
