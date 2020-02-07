<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SubtractNumbersAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class defined to execute subtract numbers action node.
 */
class SubtractNumbersAction extends Action {
  use StringTranslationTrait;

  /**
   * Return node name.
   */
  public static function getName() {
    return 'subtract_numbers_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => $this->t('Subtract Numbers'),
      'description' => $this->t('Subtract Numbers'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SubtractNumbersAction\\SubtractNumbersAction',
      'inputs' => [
        'input1' => [
          'label' => $this->t('Input 1'),
          'description' => $this->t('Input 1'),
          'sockets' => ['number'],
        ],
        'input2' => [
          'label' => $this->t('Input 2'),
          'description' => $this->t('Input 2'),
          'sockets' => ['number'],
        ],
      ],
      'outputs' => [
        'output' => [
          'label' => $this->t('Output'),
          'description' => $this->t('Subtraction of input numbers'),
          'socket' => 'number',
        ],
      ],
    ];
  }

  /**
   * Process subtract number action node.
   */
  public function process() {
    $input_numbers = 0;
    $first_input = $this->inputs['input1'];
    $second_input = $this->inputs['input2'];

    if (isset($first_input) && isset($second_input)) {
      $input_numbers = $first_input - $second_input;
    }

    $this->outputs['output'] = $input_numbers;
  }

}
