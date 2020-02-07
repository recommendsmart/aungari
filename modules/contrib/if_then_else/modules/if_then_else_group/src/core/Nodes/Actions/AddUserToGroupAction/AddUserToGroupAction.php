<?php

namespace Drupal\if_then_else_group\core\Nodes\Actions\AddUserToGroupAction;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;
use Drupal\group\Entity\Group;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\group\GroupMembership;

/**
 * Add user to group class.
 */
class AddUserToGroupAction extends Action {
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
    return 'add_user_to_group_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Add User To Group'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else_group\\core\\Nodes\\Actions\\AddUserToGroupAction\\AddUserToGroupAction',
      'classArg' => ['entity_type.manager'],
      'dependencies' => ['group', 'if_then_else_group'],
      'inputs' => [
        'user' => [
          'label' => $this->t('User Id / User object'),
          'description' => $this->t('User Id or User object.'),
          'sockets' => ['number', 'object.entity.user'],
          'required' => TRUE,
        ],
        'group_id' => [
          'label' => $this->t('Group Id'),
          'description' => $this->t('The group id to add the user.'),
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
          if ($connection->input == 'user' &&  (!property_exists($node->data, 'value') || !is_numeric($node->data->value))) {
            $event->errors[] = $this->t('Enter User Id / User object in "@node_name".', ['@node_name' => $node->name]);

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
    $user = $this->inputs['user'];
    $group_id = $this->inputs['group_id'];
    if (is_numeric($user)) {
      $user = $this->entityTypeManager->getStorage('user')->load($user);
      if (empty($user)) {
        $this->outputs['message'] = $this->t('User id @node could not be added to group @group.', ['@node' => $this->inputs['user'], '@group' => $group_id])->render();
        $this->setSuccess(FALSE);
        return;
      }
    }
    elseif (!$user instanceof UserInterface) {
      $this->outputs['message'] = $this->t('User id @node could not be added to group @group.', ['@node' => $user->id(), '@group' => $group_id])->render();
      $this->setSuccess(FALSE);
      return;
    }
    $group = Group::load($group_id);
    $member = $group->getMember($user);
    if (!$member instanceof GroupMembership) {
      $group->addMember($user);
      $this->outputs['message'] = $this->t('User @node has been added to group @group.', ['@node' => $user->getUsername(), '@group' => $group->label()])->render();
    }
    else {
      $this->outputs['message'] = $this->t('User @node is already on group @group.', ['@node' => $user->getUsername(), '@group' => $group->label()])->render();
      $this->setSuccess(FALSE);
      return;
    }

  }

}
