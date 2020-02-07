<?php

namespace Drupal\commerce_vendor;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_vendor\Entity\BranchInterface;

/**
 * Defines the storage handler class for Branch entities.
 *
 * This extends the base storage class, adding required special handling for
 * Branch entities.
 *
 * @ingroup commerce_vendor
 */
class BranchStorage extends SqlContentEntityStorage implements BranchStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BranchInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {branch_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {branch_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BranchInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {branch_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('branch_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
