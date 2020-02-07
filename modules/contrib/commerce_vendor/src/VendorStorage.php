<?php

namespace Drupal\commerce_vendor;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_vendor\Entity\VendorInterface;

/**
 * Defines the storage handler class for Vendor entities.
 *
 * This extends the base storage class, adding required special handling for
 * Vendor entities.
 *
 * @ingroup commerce_vendor
 */
class VendorStorage extends SqlContentEntityStorage implements VendorStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(VendorInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {vendor_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {vendor_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(VendorInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {vendor_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('vendor_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
