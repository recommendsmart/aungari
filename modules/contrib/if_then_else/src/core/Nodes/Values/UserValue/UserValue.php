<?php

namespace Drupal\if_then_else\core\Nodes\Values\UserValue;

use Drupal\if_then_else\core\Nodes\Values\Value;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Textvalue node class.
 */
class UserValue extends Value {
  use StringTranslationTrait;

  /**
   * Current account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The account.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(AccountProxyInterface $account, EntityTypeManagerInterface $entity_manager) {
    $this->account = $account;
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'user_value';
  }

  /**
   * Register Node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Logged-In User'),
      'description' => $this->t('Logged-In User'),
      'type' => 'value',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Values\\UserValue\\UserValue',
      'classArg' => ['current_user', 'entity_type.manager'],
      'outputs' => [
        'user' => [
          'label' => $this->t('User'),
          'description' => $this->t('User object.'),
          'socket' => 'object.entity.user',
        ],
      ],
    ];
  }

  /**
   * Process node.
   */
  public function process() {
    $this->outputs['user'] = $this->entityTypeManager->getStorage('user')->load($this->account->id());
  }

}
