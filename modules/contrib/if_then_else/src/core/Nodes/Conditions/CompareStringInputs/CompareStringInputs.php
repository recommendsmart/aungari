<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\CompareStringInputs;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Condition class defined to compare 2 string inputs and return boolean.
 */
class CompareStringInputs extends Condition {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'compare_string_inputs';
  }

  /**
   * Register Node function.
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => $this->t('Compare Two String Inputs'),
      'description' => $this->t('Compare Two String Inputs'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\CompareStringInputs\\CompareStringInputs',
      'library' => 'if_then_else/CompareStringInputs',
      'control_class_name' => 'CompareStringInputsControl',
      'compare_options' => [
        ['code' => 'equal', 'name' => 'Equal'],
        ['code' => 'notequal', 'name' => 'Not Equal'],
        ['code' => 'contains', 'name' => 'Input 2 Contains Input 1'],
        ['code' => 'startswith', 'name' => 'Input 2 Starts With Input 1'],
        ['code' => 'endswith', 'name' => 'Input 2 Ends With Input 1'],
        ['code' => 'regular_expression', 'name' => 'Input 1 Regular Expression and Input 2 string'],
      ],
      'inputs' => [
        'input1' => [
          'label' => $this->t('Input 1'),
          'description' => $this->t('Input 1'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'input2' => [
          'label' => $this->t('Input 2'),
          'description' => $this->t('Input 2'),
          'sockets' => ['string'],
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
   * Process inputs and set output.
   */
  public function process() {
    $input1 = $this->inputs['input1'];
    $input2 = $this->inputs['input2'];
    $condition_type = $this->data->compare_type[0]->code;
    $case_sensitive = FALSE;
    if (isset($this->data->selection)) {
      $case_sensitive = $this->data->selection;
    }
    $output = FALSE;

    switch ($condition_type) {
      case 'equal':
        if ($case_sensitive) {
          if ($input1 == $input2) {
            $output = TRUE;
          }
        }
        else {
          if (strcasecmp($input1, $input2) === 0) {
            $output = TRUE;
          }
        }
        break;

      case 'notequal':
        if ($case_sensitive) {
          if ($input1 != $input2) {
            $output = TRUE;
          }
        }
        else {
          if (strcasecmp($input1, $input2) !== 0) {
            $output = TRUE;
          }
        }
        break;

      case 'contains':
        if ($case_sensitive) {
          if (strpos($input2, $input1) !== FALSE) {
            $output = TRUE;
          }
        }
        else {
          if (stripos($input2, $input1) !== FALSE) {
            $output = TRUE;
          }
        }

        break;

      case 'startswith':
        if ($case_sensitive) {
          if (strpos($input2, $input1) === 0) {
            $output = TRUE;
          }
        }
        else {
          if (stripos($input2, $input1) === 0) {
            $output = TRUE;
          }
        }
        break;

      case 'endswith':
        if ($case_sensitive) {
          if (substr_compare($input2, $input1, -strlen($input1)) === 0) {
            $output = TRUE;
          }
        }
        else {
          if (substr_compare($input2, $input1, -strlen($input1), strlen($input1), TRUE) === 0) {
            $output = TRUE;
          }
        }

        break;
      
      case 'regular_expression':
        if (preg_match($input1, $input2)) {
          $output = TRUE;
        }
    }

    $this->outputs['success'] = $output;
  }

}
