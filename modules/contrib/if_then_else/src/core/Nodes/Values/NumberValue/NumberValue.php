<?php

namespace Drupal\if_then_else\core\Nodes\Values\NumberValue;

use Drupal\if_then_else\core\Nodes\Values\Value;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Number value node class.
 */
class NumberValue extends Value {
  use StringTranslationTrait;

  /**
   * Return name of number value node.
   */
  public static function getName() {
    return 'number_value';
  }

  /**
   * Event subscriber for registering number value node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Number'),
      'description' => $this->t('Number'),
      'type' => 'value',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Values\\NumberValue\\NumberValue',
      'library' => 'if_then_else/NumberValue',
      'control_class_name' => 'NumberValueControl',
      'outputs' => [
        'number' => [
          'label' => $this->t('Output'),
          'description' => $this->t('Output number.'),
          'socket' => 'number',
        ],
      ],
    ];
  }

  /**
   * Validate node.
   */
  public function validateNode(NodeValidationEvent $event) {
  }

  /**
   * Process function for number value node.
   */
  public function process() {
    $this->outputs['number'] = $this->data->value;
  }

}
