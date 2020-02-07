<?php

namespace Drupal\if_then_else\core\Nodes\Actions\AddFormFieldAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Add form field action class.
 */
class AddFormFieldAction extends Action {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'add_form_field_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Add Form Field'),
      'description' => $this->t('Add Form Field'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\AddFormFieldAction\\AddFormFieldAction',
      'inputs' => [
        'form' => [
          'label' => $this->t('Form'),
          'description' => $this->t('Form object.'),
          'sockets' => ['form'],
          'required' => TRUE,
        ],
        'name' => [
          'label' => $this->t('Name'),
          'description' => $this->t('Set name of form element.'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'value' => [
          'label' => $this->t('Value'),
          'description' => $this->t('Add value to form element.'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'value' && $node->data->name == 'text_value') {
        // To check empty input.
        if (!property_exists($node->data, 'value') || empty($node->data->value)) {
          $event->errors[] = $this->t('Enter the name of field in  "@node_name".', ['@node_name' => $node->name]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $form = &$this->inputs['form'];
    $name = &$this->inputs['name'];
    $value = &$this->inputs['value'];

    $machine_readable = strtolower($name);
    $machine_readable = preg_replace('@[^a-z0-9_]+@', '_', $machine_readable);

    $form[$machine_readable] = [
      '#type' => 'hidden',
      '#title' => $this->t('@name', ['@name' => $name]),
      '#value' => $value,
    ];
  }

}
