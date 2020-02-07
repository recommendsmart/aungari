<?php

namespace Drupal\if_then_else_group\core\Nodes\Actions\RemoveNodeFromGroupAction;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\group\Entity\Group;
use Drupal\if_then_else\Event\GraphValidationEvent;

/**
 * Remove node from group class.
 */
class RemoveNodeFromGroupAction extends Action {
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
    return 'remove_node_from_group_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Remove Node From Group'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else_group\\core\\Nodes\\Actions\\RemoveNodeFromGroupAction\\RemoveNodeFromGroupAction',
      'classArg' => ['entity_type.manager'],
      'dependencies' => ['group', 'if_then_else_group'],
      'inputs' => [
        'node' => [
          'label' => $this->t('Nid / Node'),
          'description' => $this->t('Nid or Node object. Can be of any bundle.'),
          'sockets' => ['number', 'object.entity.node'],
          'required' => TRUE,
        ],
        'group_id' => [
          'label' => $this->t('Group Id'),
          'description' => $this->t('The group id to add the node.'),
          'sockets' => ['number'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'message' => [
          'label' => $this->t('Message'),
          'description' => $this->t('Status message after applying this action.'),
          'socket' => 'string',
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
            $event->errors[] = $this->t('Enter Nid / Node in "@node_name".', ['@node_name' => $node->name]);

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
    $node = $this->inputs['node'];
    $group_id = $this->inputs['group_id'];
    if (is_numeric($node)) {
      $node = $this->entityTypeManager->getStorage('node')->load($node);
      if (empty($node)) {
        $this->outputs['message'] = $this->t('Node id @node could not be removed from group @group.', ['@node' => $this->inputs['node'], '@group' => $group_id])->render();
        $this->setSuccess(FALSE);
        return;
      }
    }
    elseif (!$node instanceof NodeInterface) {
      $this->outputs['message'] = $this->t('Node id @node could not be removed from group @group.', ['@node' => $node->id(), '@group' => $group_id])->render();
      $this->setSuccess(FALSE);
      return;
    }
    $group = Group::load($group_id);
    $type = 'group_node:' . $node->getType();
    $current_node = $group->getContent($type, ['entity_id' => $node->id()]);
    if (count($current_node)) {
      $content = array_values($current_node)[0];
      $content->delete();
      $this->outputs['message'] = $this->t('Node @node has been removed from group @group.', ['@node' => $node->label(), '@group' => $group->label()])->render();
    }
    else {
      $this->outputs['message'] = $this->t('Node @node is not on group @group.', ['@node' => $node->label(), '@group' => $group->label()])->render();
      $this->setSuccess(FALSE);
      return;
    }

  }

}
