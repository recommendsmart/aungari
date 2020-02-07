<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SendAccountEmailAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\user\UserInterface;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Send account email action class.
 */
class SendAccountEmailAction extends Action {
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
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'send_account_email_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Send Account Email'),
      'description' => $this->t('Send Account Email'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SendAccountEmailAction\\SendAccountEmailAction',
      'classArg' => ['entity_type.manager'],
      'library' => 'if_then_else/SendAccountEmailAction',
      'control_class_name' => 'SendAccountEmailActionControl',
      'compare_options' => [
        ['code' => 'register_admin_created', 'name' => 'Welcome message for user created by the admin'],
        ['code' => 'register_no_approval_required', 'name' => 'Welcome message when user self-registers'],
        ['code' => 'register_pending_approval', 'name' => 'Welcome message, user pending admin approval'],
        ['code' => 'password_reset', 'name' => 'Password recovery request'],
        ['code' => 'status_activated', 'name' => 'Account activated'],
        ['code' => 'status_blocked', 'name' => 'Account blocked'],
        ['code' => 'cancel_confirm', 'name' => 'Account cancellation request'],
        ['code' => 'status_canceled', 'name' => 'Account canceled'],
      ],
      'inputs' => [
        'user' => [
          'label' => $this->t('User Id / User object'),
          'description' => $this->t('User Id or User Object.'),
          'sockets' => ['number', 'object.entity.user'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->selected_type->code)) {
      $event->errors[] = $this->t('Select type of email to be sent in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $user = $this->inputs['user'];
    $email_type = $this->data->selected_type->code;
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

    _user_mail_notify($email_type, $user);

  }

}
