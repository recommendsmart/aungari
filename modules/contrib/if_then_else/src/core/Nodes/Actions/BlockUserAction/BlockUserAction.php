<?php

namespace Drupal\if_then_else\core\Nodes\Actions\BlockUserAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\user\UserInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Block a user action node class.
 */
class BlockUserAction extends Action {
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
   *   The Mail Manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'block_user_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Block User'),
      'description' => $this->t('Block User'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\BlockUserAction\\BlockUserAction',
      'classArg' => ['entity_type.manager'],
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
   * {@inheritDoc}.
   */
  public function process() {

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

    $user->block();
    $user->save();

  }

}
