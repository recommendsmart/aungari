<?php

namespace Drupal\commerce_vendor;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface BranchStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Branch revision IDs for a specific Branch.
   *
   * @param \Drupal\commerce_vendor\Entity\BranchInterface $entity
   *   The Branch entity.
   *
   * @return int[]
   *   Branch revision IDs (in ascending order).
   */
  public function revisionIds(BranchInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Branch author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Branch revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\commerce_vendor\Entity\BranchInterface $entity
   *   The Branch entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BranchInterface $entity);

  /**
   * Unsets the language for all Branch with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
