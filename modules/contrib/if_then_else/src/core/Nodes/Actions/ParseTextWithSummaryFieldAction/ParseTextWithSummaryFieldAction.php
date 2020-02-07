<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ParseTextWithSummaryFieldAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Parse Text with Summary node action class.
 */
class ParseTextWithSummaryFieldAction extends Action {
  use StringTranslationTrait;

  /**
   * Return name of Text with summary node action.
   */
  public static function getName() {
    return 'text_with_summary_field_value_action';
  }

  /**
   * Event subscriber for registering Text with summary action node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Parse Text With Summary Field'),
      'description' => $this->t('Parse Text With Summary Field'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ParseTextWithSummaryFieldAction\\ParseTextWithSummaryFieldAction',
      'inputs' => [
        'field_value' => [
          'label' => $this->t('Text with summary field object'),
          'description' => $this->t('Text with summary field object'),
          'sockets' => ['object.field.text_with_summary'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'summary' => [
          'label' => $this->t('Summary'),
          'description' => $this->t('Summary'),
          'socket' => 'string',
        ],
        'value' => [
          'label' => $this->t('Field Value'),
          'description' => $this->t('Field value'),
          'socket' => 'string',
        ],
        'format' => [
          'label' => $this->t('Field Text Format'),
          'description' => $this->t('Field Text Format'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * Process function for Text with summary action node.
   */
  public function process() {
    $this->outputs['summary'] = $this->inputs['field_value']->summary;
    $this->outputs['value'] = $this->inputs['field_value']->value;
    $this->outputs['format'] = $this->inputs['field_value']->format;
  }

}
