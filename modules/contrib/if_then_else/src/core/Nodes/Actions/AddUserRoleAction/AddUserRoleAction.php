<?php

namespace Drupal\if_then_else\core\Nodes\Actions\AddUserRoleAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\user\UserInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Add user role action class.
 */
class AddUserRoleAction extends Action {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'add_user_role_action';
  }

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;
  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $loggerFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerFactory = $loggerFactory->get('if_then_else');
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
      'label' => $this->t('Add User Roles'),
      'description' => $this->t('Add User Roles'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\AddUserRoleAction\\AddUserRoleAction',
      'library' => 'if_then_else/AddUserRoleAction',
      'control_class_name' => 'AddUserRoleActionControl',
      'classArg' => ['entity_type.manager', 'logger.factory'],
      'roles' => $role_array,
      'inputs' => [
        'user' => [
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
      if (!$user->hasRole($role->name)) {
        $user->addRole($role->name);
      }
      else {
        $this->loggerFactory->notice($this->t("Rule @node_name did not run as the user already have the role @role", ['@node_name' => $this->data->name, '@role' => $role->name]));
      }
    }
    $user->save();

  }

}
