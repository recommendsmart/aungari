<?php

namespace Drupal\if_then_else_group\core\Nodes\Conditions\NodeInGroupCondition;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\group\Entity\Group;
use Drupal\if_then_else\Event\GraphValidationEvent;

/**
 * Condition node present in group.
 */
class NodeInGroupCondition extends Condition {
  use StringTranslationTrait;
  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'node_in_group_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Node In Group'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else_group\\core\\Nodes\\Conditions\\NodeInGroupCondition\\NodeInGroupCondition',
      'classArg' => ['entity_type.manager'],
      'dependencies' => ['group', 'if_then_else_group'],
      'inputs' => [
        'node' => [
          'label' => $this->t('Node Id / Node object'),
          'description' => $this->t('Node Id or Node object.'),
          'sockets' => ['number', 'object.entity.node'],
          'required' => TRUE,
        ],
        'group_id' => [
          'label' => $this->t('Group Id'),
          'description' => $this->t('The group id to check node.'),
          'sockets' => ['number'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('Does the node in group(s)?'),
          'socket' => 'bool',
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
      if ($node->data->type == 'value' && $node->data->name == 'number_value') {
        // To check empty input.
        foreach ($node->outputs->number->connections as $connection) {
          if ($connection->input == 'node' &&  (!property_exists($node->data, 'value') || !is_numeric($node->data->value))) {
            $event->errors[] = $this->t('Enter Node Id / Node object in "@node_name".', ['@node_name' => $node->name]);

          }
          if ($connection->input == 'group_id' &&  (!property_exists($node->data, 'value') || !is_numeric($node->data->value))) {
            $event->errors[] = $this->t('Enter Group Id in "@node_name".', ['@node_name' => $node->name]);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $group_id = $this->inputs['group_id'];
    $node = $this->inputs['node'];
    if (is_numeric($node)) {
      $node_load = $this->entityTypeManager->getStorage('node')->load($node);
      if (empty($node_load)) {
        $this->setSuccess(FALSE);
        return;
      }
    }
    elseif (!$node instanceof NodeInterface) {
      $this->setSuccess(FALSE);
      return;
    }
    $group = Group::load($group_id);
    $type = 'group_node:' . $node_load->getType();
    $current_node = $group->getContent($type, ['entity_id' => $node_load->id()]);
    if (!count($current_node)) {
      $this->setSuccess(TRUE);
    }
    else {
      $this->setSuccess(FALSE);
      return;
    }
  }

}
