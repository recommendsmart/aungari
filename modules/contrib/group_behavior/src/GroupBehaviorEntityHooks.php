<?php

namespace Drupal\group_behavior;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\GroupContentInterface;
use Drupal\group\Entity\GroupContentType;
use Drupal\group\Entity\GroupContentTypeInterface;

class GroupBehaviorEntityHooks {

  /**
   * Post insert.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function insert(EntityInterface $entity) {
    self::createGroupsIfNecessary($entity);
  }

  /**
   * Post update.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function update(EntityInterface $entity) {
    self::createGroupsIfNecessary($entity);
    self::updateGroupsAndContentConnectorIfNecessary($entity);
  }

  /**
   * Post insert translation.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function insertTranslation(EntityInterface $entity) {
    self::createGroupsIfNecessary($entity);
    self::updateGroupsAndContentConnectorIfNecessary($entity);
  }

  /**
   * Post delete translation.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function deleteTranslation(EntityInterface $entity) {
    self::updateGroupsAndContentConnectorIfNecessary($entity);
  }

  /**
   * Post delete.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public static function delete(EntityInterface $entity) {
    // Note that deleting content will delete the GroupContent relation via
    // group_entity_delete(), and deleting the group will delete the
    // GroupContent relation via \Drupal\group\Entity\Group::preDelete.
    $groups = GroupBehaviorHelpers::fetchGroupBehaviorGroupsOfEntity($entity);

    // We knocked that out in group_behavior_module_implements_alter().
    \group_entity_delete($entity);

    foreach ($groups as $group) {
      // This will delete more GroupContent relations, but not the group content
      // itself.
      $group->delete();
    }
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected static function createGroupsIfNecessary(EntityInterface $entity) {
    if ($entity instanceof ContentEntityInterface) {
      $groupContentTypes = GroupBehaviorHelpers::fetchGroupContentTypesWithGroupBehaviorByEntity($entity);
      foreach ($groupContentTypes as $groupContentType) {
        if (!GroupBehaviorHelpers::fetchEntityGroupContentOfType($entity, $groupContentType)) {
          $group = Group::create([
            'type' => $groupContentType->getGroupTypeId(),
            'label' => $entity->label(),
            'langcode' => $entity->language()->getId(),
          ]);
          $group->save();
          $groupContent = GroupContent::create([
            'type' => $groupContentType->id(),
            'gid' => $group->id(),
            'entity_id' => $entity->id(),
            'label' => $entity->label(),
            'langcode' => $entity->language()->getId(),
          ]);
          $groupContent->save();
        }
      }
    }
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected static function updateGroupsAndContentConnectorIfNecessary(EntityInterface $entity) {
    if ($entity instanceof ContentEntityInterface) {
      $entityLangcode = $entity->language()->getId();
      if ($groupContents = GroupContent::loadByEntity($entity)) {
        /** @var \Drupal\group\Entity\GroupContentInterface[] $groupContents */
        foreach ($groupContents as $groupContent) {
          $groupContentType = $groupContent->getGroupContentType();
          if (GroupBehaviorHelpers::checkGroupContentTypeHasGroupBehavior($groupContentType)) {
            $groupContent = $groupContent->hasTranslation($entityLangcode) ?
              $groupContent->getTranslation($entityLangcode) :
              $groupContent->addTranslation($entityLangcode);
            $groupContent->set('label', $entity->label());
            $groupContent->save();
            $group = $groupContent->getGroup();
            $group = $group->hasTranslation($entityLangcode) ?
              $group->getTranslation($entityLangcode) :
              $group->addTranslation($entityLangcode);
            $group->set('label', $entity->label());
            $group->save();
          }
        }
      }
    }
  }

}
