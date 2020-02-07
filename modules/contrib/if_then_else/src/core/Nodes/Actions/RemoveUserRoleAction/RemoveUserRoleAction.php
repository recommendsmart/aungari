<?php

namespace Drupal\if_then_else\core\Nodes\Actions\RemoveUserRoleAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\user\UserInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Remove user role action class.
 */
class RemoveUserRoleAction extends Action {
  use StringTranslationTrait;
  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager,
                              LoggerChannelFactoryInterface $loggerFactory) {
    $this->entityTypeManager = $entity_manager;
    $this->loggerFactory = $loggerFactory->get('if_then_else');
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'remove_user_role_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    $role_array = [];
    foreach ($roles as $rid => $role) {
      $role_array[$rid] = $role->label();
    }
    $event->nodes[static::getName()] = [
      'label' => $this->t('Remove User Roles'),
      'description' => $this->t('Remove User Roles'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\RemoveUserRoleAction\\RemoveUserRoleAction',
      'library' => 'if_then_else/RemoveUserRoleAction',
      'control_class_name' => 'RemoveUserRoleActionControl',
      'classArg' => ['entity_type.manager', 'logger.factory'],
      'roles' => $role_array,
      'inputs' => [
        'user' => [
          'label' => $this->t('Remove User Roles'),
          'label' => $this->t('User Id / User object'),
          'description' => $this->t('User Id or User object.'),
          'sockets' => ['number', 'object.entity.user'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    // Make sure that role option is not empty.
    if (empty($event->node->data->selected_options)) {
      $event->errors[] = $this->t('Select at least one role in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $roles = $this->data->selected_options;
    $user = $this->inputs['user'];
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
    foreach ($roles as $role) {
      if ($user->hasRole($role->name)) {
        $user->removeRole($role->name);
      }
      else {
        $this->loggerFactory->notice($this->t("Rule @node_name did not run as the user doesn't have the role @role", ['@node_name' => $this->data->name, '@role' => $role->name]));
      }
    }
    $user->save();
  }

}
