<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ParseLinkFieldValueAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Parse Link field value node class.
 */
class ParseLinkFieldValueAction extends Action {
  use StringTranslationTrait;

  /**
   * Return name of Link field value node.
   */
  public static function getName() {
    return 'link_field_value_action';
  }

  /**
   * Event subscriber for registering Link field value action node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Parse Link Field'),
      'description' => $this->t('Parse Link Field'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ParseLinkFieldValueAction\\ParseLinkFieldValueAction',
      'inputs' => [
        'field_value' => [
          'label' => $this->t('Link field object'),
          'description' => $this->t('Link field object'),
          'sockets' => ['object.field.link'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'uri' => [
          'label' => $this->t('Link Uri'),
          'description' => $this->t('Link Uri'),
          'socket' => 'string',
        ],
        'title' => [
          'label' => $this->t('Link title'),
          'description' => $this->t('Link Title'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * Process function for Link field value action node.
   */
  public function process() {
    $this->outputs['uri'] = $this->inputs['field_value']->uri;
    $this->outputs['title'] = $this->inputs['field_value']->title;
  }

}
