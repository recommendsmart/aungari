<?php

namespace Drupal\commerce_vendor\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Branch entities.
 *
 * @ingroup commerce_vendor
 */
interface BranchInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Branch name.
   *
   * @return string
   *   Name of the Branch.
   */
  public function getName();

  /**
   * Sets the Branch name.
   *
   * @param string $name
   *   The Branch name.
   *
   * @return \Drupal\commerce_vendor\Entity\BranchInterface
   *   The called Branch entity.
   */
  public function setName($name);

  /**
   * Gets the Branch creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Branch.
   */
  public function getCreatedTime();

  /**
   * Sets the Branch creation timestamp.
   *
   * @param int $timestamp
   *   The Branch creation timestamp.
   *
   * @return \Drupal\commerce_vendor\Entity\BranchInterface
   *   The called Branch entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Branch revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Branch revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\commerce_vendor\Entity\BranchInterface
   *   The called Branch entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Branch revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Branch revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\commerce_vendor\Entity\BranchInterface
   *   The called Branch entity.
   */
  public function setRevisionUserId($uid);

}
