<?php

namespace Drupal\if_then_else\core\Nodes\Actions\GetArrayIndexValueAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * GetArrayIndexValueAction node class.
 */
class GetArrayIndexValueAction extends Action {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'get_array_index_value_action';
  }

  /**
   * Event subscriber of registering node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Get Array Index Value'),
      'description' => $this->t('Get value of a specific index of an array.'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\GetArrayIndexValueAction\\GetArrayIndexValueAction',
      'library' => 'if_then_else/GetArrayIndexValueAction',
      'control_class_name' => 'GetArrayIndexValueActionControl',
      'inputs' => [
        'input_array' => [
          'label' => $this->t('Input Array'),
          'description' => $this->t('Input array whose index value need to be returned.'),
          'sockets' => ['array'],
        ],
      ],
      'outputs' => [
        'index_value' => [
          'label' => $this->t('Array index value'),
          'description' => $this->t('Array index value.'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * Process function for Textvalue node.
   */
  public function process() {
    $index = (int) $this->data->value;

    if (isset($this->inputs['input_array'][$index])) {
      return $this->outputs['index_value'] = $this->inputs['input_array'][$index];
    }
    else {
      $this->outputs['index_value'] = '';
    }
  }

}
