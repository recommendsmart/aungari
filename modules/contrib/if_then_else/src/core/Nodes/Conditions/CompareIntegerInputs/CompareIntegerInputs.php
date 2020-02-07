<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\CompareIntegerInputs;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Condition class defined to compare 2 number inputs and return boolean.
 */
class CompareIntegerInputs extends Condition {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'compare_integer_inputs';
  }

  /**
   * Function for registring Node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => $this->t('Compare Two Number Inputs'),
      'description' => $this->t('Compare Two Number Inputs'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\CompareIntegerInputs\\CompareIntegerInputs',
      'library' => 'if_then_else/CompareIntegerInputs',
      'control_class_name' => 'CompareIntegerInputsControl',
      'compare_options' => [
        ['code' => 'equal', 'name' => 'Equal'],
        ['code' => 'notequal', 'name' => 'Not Equal'],
        ['code' => 'greaterthan', 'name' => 'Greater Than'],
        ['code' => 'greaterthanequal', 'name' => 'Greater Than Or Equal'],
        ['code' => 'lessthan', 'name' => 'Less Than'],
        ['code' => 'lessthanequal', 'name' => 'Less Than Or Equal'],
      ],
      'inputs' => [
        'input1' => [
          'label' => $this->t('Input 1'),
          'description' => $this->t('Input 1'),
          'sockets' => ['number'],
          'required' => TRUE,
        ],
        'input2' => [
          'label' => $this->t('Input 2'),
          'description' => $this->t('Input 2'),
          'sockets' => ['number'],
          'required' => TRUE,
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
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!isset($data->compare_type[0]->code) || empty($data->compare_type[0]->code)) {
      $event->errors[] = $this->t('Select a compare type for "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'value' && $node->data->name == 'number_value') {
        // To check empty input.
        foreach ($node->outputs->number->connections as $connection) {
          if ($connection->input == 'input1' &&  (!property_exists($node->data, 'value') || !is_numeric($node->data->value))) {
            $event->errors[] = $this->t('Enter input value1 in "@node_name".', ['@node_name' => $node->name]);

          }
          if ($connection->input == 'input2' &&  (!property_exists($node->data, 'value') || !is_numeric($node->data->value))) {
            $event->errors[] = $this->t('Enter input value2 in "@node_name".', ['@node_name' => $node->name]);
          }
        }
      }
    }
  }

  /**
   * Process inputs and set output.
   */
  public function process() {
    $input1 = $this->inputs['input1'];
    $input2 = $this->inputs['input2'];
    $condition_type = $this->data->compare_type[0]->code;
    $output = FALSE;

    switch ($condition_type) {
      case 'equal':
        if ($input1 == $input2) {
          $output = TRUE;
        }

        break;

      case 'notequal':
        if ($input1 != $input2) {
          $output = TRUE;
        }

        break;

      case 'greaterthan':
        if ($input1 > $input2) {
          $output = TRUE;
        }

        break;

      case 'greaterthanequal':
        if ($input1 >= $input2) {
          $output = TRUE;
        }

        break;

      case 'lessthan':
        if ($input1 < $input2) {
          $output = TRUE;
        }

        break;

      case 'lessthanequal':
        if ($input1 <= $input2) {
          $output = TRUE;
        }

        break;
    }

    $this->outputs['success'] = $output;
  }

}
