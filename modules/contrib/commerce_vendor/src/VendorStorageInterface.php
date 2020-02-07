<?php

namespace Drupal\commerce_vendor;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface VendorStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Vendor revision IDs for a specific Vendor.
   *
   * @param \Drupal\commerce_vendor\Entity\VendorInterface $entity
   *   The Vendor entity.
   *
   * @return int[]
   *   Vendor revision IDs (in ascending order).
   */
  public function revisionIds(VendorInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Vendor author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Vendor revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\commerce_vendor\Entity\VendorInterface $entity
   *   The Vendor entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(VendorInterface $entity);

  /**
   * Unsets the language for all Vendor with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
