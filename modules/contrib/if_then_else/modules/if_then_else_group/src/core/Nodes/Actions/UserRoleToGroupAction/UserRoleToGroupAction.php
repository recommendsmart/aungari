<?php

namespace Drupal\if_then_else_group\core\Nodes\Actions\UserRoleToGroupAction;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;
use Drupal\group\Entity\Group;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Add group member role to user of a group.
 */
class UserRoleToGroupAction extends Action {
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
    return 'user_role_to_group_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $groups = $this->entityTypeManager->getStorage('group_type')->loadMultiple();
    $group_list = [];
    foreach ($groups as $group_id => $group) {
      if (!empty($group->getRoleIds(FALSE))) {
        $group_list[$group_id]['name'] = $group->label();
        $group_list[$group_id]['code'] = $group_id;
        $group_roles = $group->getRoles(FALSE);

        foreach ($group_roles as $group_role) {
          $group_list[$group_id]['roles'][] = [
            'code' => $group_role->id(),
            'name' => $group_role->label(),
          ];
        }
      }
    }

    $group_array = [];
    $i = 0;
    $group_roles = [];
    foreach ($group_list as $group_type) {
      $group_array[$i]['name'] = $group_type['name'];
      $group_array[$i]['code'] = $group_type['code'];

      $j = 0;
      foreach ($group_type['roles'] as $roles) {
        $group_roles[$group_type['code']][$j]['code'] = $roles['code'];
        $group_roles[$group_type['code']][$j]['name'] = $roles['name'];
        $j++;
      }
      $i++;
    }

    $event->nodes[static::getName()] = [
      'label' => $this->t('User Role To Group'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else_group\\core\\Nodes\\Actions\\UserRoleToGroupAction\\UserRoleToGroupAction',
      'classArg' => ['entity_type.manager'],
      'dependencies' => ['group', 'if_then_else_group'],
      'library' => 'if_then_else_group/UserRoleToGroupAction',
      'control_class_name' => 'UserRoleToGroupActionControl',
      'group_types' => $group_array,
      'group_roles' => $group_roles,
      'inputs' => [
        'user' => [
          'label' => $this->t('User Id / User object'),
          'description' => $this->t('User Id or User object.'),
          'sockets' => ['number', 'object.entity.user'],
          'required' => TRUE,
        ],
        'group_id' => [
          'label' => $this->t('Group Id'),
          'description' => $this->t('The group id to add the user role.'),
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
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->selected_options)) {
      $event->errors[] = $this->t('Selected a options name to fetch it\'s value in "@node_name".', ['@node_name' => $event->node->name]);
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
        $this->outputs['message'] = $this->t('User member role could not be added to group @group.', ['@node' => $this->inputs['user'], '@group' => $group_id])->render();
        $this->setSuccess(FALSE);
        return;
      }
    }
    elseif (!$user instanceof UserInterface) {
      $this->outputs['message'] = $this->t('User member role could not be added to group @group.', ['@node' => $user->id(), '@group' => $group_id])->render();
      $this->setSuccess(FALSE);
      return;
    }
    $group = Group::load($group_id);
    $member = $group->getMember($user);
    if ($member != NULL) {
      foreach ($this->data->selected_roles as $role) {
        $list_role = $member->getRoles();
        $group_content = $member->getGroupContent();
        if (!isset($list_role[$role->code])) {
          $group_content->group_roles[] = ['target_id' => $role->code];
        }
        $group_content->save();
      }
    }
  }

}
