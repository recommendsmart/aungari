<?php

namespace Drupal\if_then_else\core\Nodes\Actions\CalculateValueAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Calculate value action class.
 */
class CalculateValueAction extends Action {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'calculate_value_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Calculate Value'),
      'description' =>  $this->t('Calculate Value'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\CalculateValueAction\\CalculateValueAction',
      'library' => 'if_then_else/CalculateValueAction',
      'control_class_name' => 'CalculateValueActionControl',
      'operator_options' => [
        ['code' => 'addition', 'name' => '+'],
        ['code' => 'subtraction', 'name' => '-'],
        ['code' => 'multiplication', 'name' => '*'],
        ['code' => 'division', 'name' => '/'],
        ['code' => 'modulo', 'name' => '%'],
      ],
      'inputs' => [
        'input1' => [
          'label' => $this->t('Input Value1'),
          'description' => $this->t('Input Value1.'),
          'sockets' => ['number'],
          'required' => TRUE,
        ],
        'input2' => [
          'label' => $this->t('Input Value2'),
          'description' => $this->t('Input Value2.'),
          'sockets' => ['number'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'output' => [
          'label' => $this->t('Output'),
          'description' => $this->t('Output'),
          'socket' => 'number',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    // Make sure that data type option is not empty.
    if (empty($event->node->data->operator)) {
      $event->errors[] = $this->t('Select at least one operator in "@node_name".', ['@node_name' => $event->node->name]);
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
   * {@inheritdoc}
   */
  public function process() {
    $input1 = $this->inputs['input1'];
    $input2 = $this->inputs['input2'];

    $operator = $this->data->operator[0]->code;

    $output = 0;

    switch ($operator) {
      case 'addition':
        $output = $input1 + $input2;
        break;

      case 'subtraction':
        $output = $input1 - $input2;
        break;

      case 'multiplication':
        $output = $input1 * $input2;
        break;

      case 'division':
        $output = $input1 / $input2;
        break;

      case 'modulo':
        if (!empty($input2)) {
          $output = $input1 % $input2;
        }
        break;
    }

    $this->outputs['output'] = $output;
  }

}
