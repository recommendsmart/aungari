<?php

namespace Drupal\if_then_else_group\core\Nodes\Conditions\UserInGroupCondition;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;
use Drupal\group\Entity\Group;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\group\GroupMembership;

/**
 * Condition user present in group.
 */
class UserInGroupCondition extends Condition {
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
    return 'user_in_group_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('User In Group'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else_group\\core\\Nodes\\Conditions\\UserInGroupCondition\\UserInGroupCondition',
      'classArg' => ['entity_type.manager'],
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
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('Does the user in group(s)?'),
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
        $this->setSuccess(FALSE);
        return;
      }
    }
    elseif (!$user instanceof UserInterface) {
      $this->setSuccess(FALSE);
      return;
    }

    $group = Group::load($group_id);
    $member = $group->getMember($user);
    if ($member instanceof GroupMembership) {
      $this->setSuccess(TRUE);
    }
    else {
      $this->setSuccess(FALSE);
      return;
    }
  }

}
