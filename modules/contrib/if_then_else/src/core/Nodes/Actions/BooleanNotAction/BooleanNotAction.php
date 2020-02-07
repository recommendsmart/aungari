<?php

namespace Drupal\if_then_else\core\Nodes\Actions\BooleanNotAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class defined to execute subtract numbers action node.
 */
class BooleanNotAction extends Action {
  use StringTranslationTrait;

  /**
   * Return node name.
   */
  public static function getName() {
    return 'boolean_not_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => $this->t('Boolean NOT'),
      'description' => $this->t('Boolean NOT'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\BooleanNotAction\\BooleanNotAction',
      'inputs' => [
        'input' => [
          'label' => $this->t('Input'),
          'description' => $this->t('Input'),
          'sockets' => ['bool'],
        ],
      ],
      'outputs' => [
        'output' => [
          'label' => $this->t('Output'),
          'description' => $this->t('NOT of the input'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * Process function.
   */
  public function process() {
    $this->outputs['output'] = !$this->inputs['input'];
  }

}
