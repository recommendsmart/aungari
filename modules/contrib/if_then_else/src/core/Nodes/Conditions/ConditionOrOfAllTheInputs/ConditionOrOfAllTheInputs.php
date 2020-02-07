<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\ConditionOrOfAllTheInputs;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class defined to process make all fields required action.
 */
class ConditionOrOfAllTheInputs extends Condition {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'condition_or_of_all_the_inputs';
  }

  /**
   *
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => $this->t('Boolean OR'),
      'description' => $this->t('Boolean OR'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\ConditionOrOfAllTheInputs\\ConditionOrOfAllTheInputs',
      'library' => 'if_then_else/ConditionOrOfAllTheInputs',
      'control_class_name' => 'FormIdConditionOrOfAllTheInputs',
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
    $condition_status = FALSE;
    $conditions_all_inputs = $this->inputs;

    // Get all the inputs and check condition for all the inputs.
    if ($conditions_all_inputs) {
      if (in_array(FALSE, $conditions_all_inputs, TRUE) === FALSE) {
        $condition_status = TRUE;
      }
      elseif (in_array(TRUE, $conditions_all_inputs, TRUE) === FALSE) {
        $condition_status = FALSE;
      }
      else {
        $condition_status = TRUE;
      }
    }

    $this->outputs['success'] = $condition_status;
  }

}
