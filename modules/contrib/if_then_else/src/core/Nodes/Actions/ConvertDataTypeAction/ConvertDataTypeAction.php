<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ConvertDataTypeAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Convert data type action class.
 */
class ConvertDataTypeAction extends Action {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'convert_data_type_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Convert Data Type'),
      'description' => $this->t('Convert Data Type'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ConvertDataTypeAction\\ConvertDataTypeAction',
      'library' => 'if_then_else/ConvertDataTypeAction',
      'control_class_name' => 'ConvertDataTypeActionControl',
      'component_class_name' => 'ConvertDataTypeActionComponent',
      'compare_options' => [
        ['code' => 'str', 'name' => 'String'],
        ['code' => 'int', 'name' => 'Integer'],
      ],
      'inputs' => [
        'input' => [
          'label' => $this->t('Input'),
          'description' => $this->t('Input'),
          'sockets' => ['string', 'number'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'output' => [
          'label' => $this->t('Output'),
          'description' => $this->t('Output'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    // Make sure that data type option is not empty.
    if (empty($event->node->data->data_type)) {
      $event->errors[] = $this->t('Select at least one data type in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $input = $this->inputs['input'];
    $data_type = $this->data->data_type[0]->code;

    $output = $input;

    switch ($data_type) {
      case 'str':
        $output = (string) $input;
        break;

      case 'int':
        $output = (int) $input;
        break;
    }

    $this->outputs['output'] = $output;
  }

}
