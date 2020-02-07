<?php

namespace Drupal\if_then_else\core\Nodes\Actions\GetUserRolesAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class GetUserRolesAction.
 *
 * @package Drupal\if_then_else\core\Nodes\Actions\GetUserRolesAction
 */
class GetUserRolesAction extends Action {
  use StringTranslationTrait;

  /**
   * Get node name.
   */
  public static function getName() {
    return 'set_cookie_action';
  }

  /**
   * Register node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Get User Roles'),
      'description' => $this->t('Get User Roles'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\GetUserRolesAction\\GetUserRolesAction',
      'inputs' => [
        'user' => [
          'label' => $this->t('User'),
          'description' => $this->t('Account object.'),
          'sockets' => ['object.entity.user'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'roles' => [
          'label' => $this->t('Roles'),
          'description' => $this->t('Array of roles that the user has.'),
          'socket' => 'array',
        ],
      ],
    ];
  }

  /**
   * Process node.
   */
  public function process() {
    /** @var \Drupal\user\Entity\User $user */
    $user = $this->inputs['user'];
    $this->outputs['roles'] = $user->getRoles();
  }

}
