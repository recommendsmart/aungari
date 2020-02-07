<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\DataValueEmptyCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Data value is empty condition class.
 */
class DataValueEmptyCondition extends Condition {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'data_value_empty_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Data Value Empty'),
      'description' => $this->t('Data Value Empty'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\DataValueEmptyCondition\\DataValueEmptyCondition',
      'inputs' => [
        'input' => [
          'label' => $this->t('Data to check'),
          'description' => $this->t('Data to check.'),
          'sockets' => [
            'string',
            'number',
            'array',
            'object.entity.user',
            'object.entity.node',
          ],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('Data value is empty?'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $input = $this->inputs['input'];
    $output = FALSE;

    if (empty($input)) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
