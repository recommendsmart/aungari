<?php

namespace Drupal\commerce_vendor\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Vendor entities.
 *
 * @ingroup commerce_vendor
 */
interface VendorInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Vendor name.
   *
   * @return string
   *   Name of the Vendor.
   */
  public function getName();

  /**
   * Sets the Vendor name.
   *
   * @param string $name
   *   The Vendor name.
   *
   * @return \Drupal\commerce_vendor\Entity\VendorInterface
   *   The called Vendor entity.
   */
  public function setName($name);

  /**
   * Gets the Vendor creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Vendor.
   */
  public function getCreatedTime();

  /**
   * Sets the Vendor creation timestamp.
   *
   * @param int $timestamp
   *   The Vendor creation timestamp.
   *
   * @return \Drupal\commerce_vendor\Entity\VendorInterface
   *   The called Vendor entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Vendor revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Vendor revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\commerce_vendor\Entity\VendorInterface
   *   The called Vendor entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Vendor revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Vendor revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\commerce_vendor\Entity\VendorInterface
   *   The called Vendor entity.
   */
  public function setRevisionUserId($uid);

}
