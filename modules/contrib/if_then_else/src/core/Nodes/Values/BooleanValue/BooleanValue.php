<?php

namespace Drupal\if_then_else\core\Nodes\Values\BooleanValue;

use Drupal\if_then_else\core\Nodes\Values\Value;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Boolean value node class.
 */
class BooleanValue extends Value {
  use StringTranslationTrait;

  /**
   * Return name of boolean value node.
   */
  public static function getName() {
    return 'boolean_value';
  }

  /**
   * Event subscriber for registering number value node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Bool'),
      'description' => $this->t('Bool'),
      'type' => 'value',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Values\\BooleanValue\\BooleanValue',
      'library' => 'if_then_else/BooleanValue',
      'control_class_name' => 'BooleanValueControl',
      'outputs' => [
        'number' => [
          'label' => $this->t('Output'),
          'description' => $this->t('Output Boolean.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * Process function for boolean value node.
   */
  public function process() {
    if (property_exists($this->data, 'selection')) {
      $this->outputs['number'] = $this->data->selection;
    }
    else {
      $this->outputs['number'] = TRUE;
    }
  }

}
