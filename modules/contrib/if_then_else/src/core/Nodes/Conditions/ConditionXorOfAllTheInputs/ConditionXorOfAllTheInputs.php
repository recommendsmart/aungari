<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\ConditionXorOfAllTheInputs;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class defined to process boolean xor class.
 */
class ConditionXorOfAllTheInputs extends Condition {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'condition_xor_of_all_the_inputs';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => $this->t('Boolean XOR'),
      'description' => $this->t('Boolean XOR'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\ConditionXorOfAllTheInputs\\ConditionXorOfAllTheInputs',
      'inputs' => [
        'input1' => [
          'label' => $this->t('Input 1'),
          'description' => $this->t('Did the condition pass?'),
          'sockets' => ['bool'],
          'required' => TRUE,
        ],
        'input2' => [
          'label' => $this->t('Input 2'),
          'description' => $this->t('Did the condition pass?'),
          'sockets' => ['bool'],
          'required' => TRUE,
        ],
        'input3' => [
          'label' => $this->t('Input 3'),
          'description' => $this->t('Did the condition pass?'),
          'sockets' => ['bool'],
        ],
        'input4' => [
          'label' => $this->t('Input 4'),
          'description' => $this->t('Did the condition pass?'),
          'sockets' => ['bool'],
        ],
        'input5' => [
          'label' => $this->t('Input 5'),
          'description' => $this->t('Did the condition pass?'),
          'sockets' => ['bool'],
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('Did the condition pass?'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * Process inputs and set output.
   */
  public function process() {
    $condition_status = 0;
    $conditions_all_inputs = $this->inputs;

    // Get all the inputs and check condition for all the inputs.
    if (!empty($conditions_all_inputs)) {
      foreach ($conditions_all_inputs as $conditions_all_input) {
        $condition_status ^= $conditions_all_input;
      }
    }

    $this->outputs['success'] = boolval($condition_status);
  }

}
