<?php

namespace Drupal\commerce_vendor;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Branch entity.
 *
 * @see \Drupal\commerce_vendor\Entity\Branch.
 */
class BranchAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_vendor\Entity\BranchInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished branch entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published branch entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit branch entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete branch entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add branch entities');
  }


}
