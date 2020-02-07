<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ParseTextLongFieldValueAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Parse Text Long field value action node class.
 */
class ParseTextLongFieldValueAction extends Action {
  use StringTranslationTrait;

  /**
   * Return name of Text long field value action node.
   */
  public static function getName() {
    return 'text_long_field_value_action';
  }

  /**
   * Event subscriber for registering Text long field value action node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Parse Text Long Field'),
      'description' => $this->t('Parse Text Long Field'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ParseTextLongFieldValueAction\\ParseTextLongFieldValueAction',
      'inputs' => [
        'field_value' => [
          'label' => $this->t('Text Long field object'),
          'description' => $this->t('Text Long field object'),
          'sockets' => ['object.field.text_long'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'value' => [
          'label' => $this->t('Text Field Value'),
          'description' => $this->t('Text Field Value'),
          'socket' => 'string',
        ],
        'format' => [
          'label' => $this->t('Text Field Format'),
          'description' => $this->t('Text Field Format'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * Process function for Text long field value action node.
   */
  public function process() {
    $this->outputs['value'] = $this->inputs['field_value']->value;
    $this->outputs['format'] = $this->inputs['field_value']->format;
  }

}
