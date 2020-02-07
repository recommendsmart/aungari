<?php

namespace Drupal\group_behavior_field_sync;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\GroupContentInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group_behavior\GroupBehaviorHelpers;
use Drupal\user\UserInterface;

class FieldSyncEntityHooks {

  const BATCH_SIZE = 30;

  protected static $doingDomainSync = FALSE;

  /**
   * Entity postSave = insert + update.
   *
   * If we were using the OG module, we could do this in presave as we there
   * have a group reference field. But we are in group.
   *
   * For entities which are updated without changing their GroupContent
   * relations, everything is fine.
   *
   * The computed field in https://www.drupal.org/project/group/issues/2813405
   * updates GroupContent relations in FieldItemList::postSave, which runs
   * before this entity postsave. All fine.
   *
   * For entities created via the GroupContent form, this is more complicated.
   * I currently could not find the source, but the entity must first be saved
   * so it has an ID and only then the GroupContent is saved.
   * After that, in GroupContent::postSave, the entity is saved again to update
   * some caches.
   *
   * Fortunately (erh hopefully if i got things right) this means, we can safely
   * rely on the GroupContent relation when this is called. (And ignore the
   * missing relation on the first save.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public static function postSave(EntityInterface $entity) {
    if (static::$doingDomainSync) {
      return;
    }
    static::$doingDomainSync = TRUE;
    self::doPostSave($entity);
    static::$doingDomainSync = FALSE;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  protected static function doPostSave(EntityInterface $entity) {
    if (
      $entity instanceof ContentEntityInterface
      && !$entity instanceof UserInterface
      && ($groupContents = GroupContent::loadByEntity($entity))
    ) {
      $syncedToThis = FALSE;
      /** @var \Drupal\group\Entity\GroupContentInterface[] $groupContents */
      foreach ($groupContents as $groupContent) {
        $groupContentType = $groupContent->getGroupContentType();
        $groupEnabler = $groupContentType->getContentPlugin();
        $groupType = $groupEnabler->getGroupType();
        $hasAnyGroupBehavior = GroupBehaviorHelpers::getGroupBehaviorContentPluginId($groupType);
        if ($hasAnyGroupBehavior) {
          $fieldNamesToSync = FieldSyncHelpers::getFieldsToSync($groupType);
          $hasFieldsToSync = array_intersect(array_keys($entity->getFieldDefinitions()), $fieldNamesToSync);
          if ($hasFieldsToSync) {
            $hasGroupBehavior = GroupBehaviorHelpers::checkGroupContentTypeHasGroupBehavior($groupContentType);
            if ($hasGroupBehavior) {
              self::syncFieldsToGroupContent($groupContent);
            }
            else {
              if ($syncedToThis) {
                throw new \LogicException("Trying to sync twice to {$entity->getEntityTypeId()} {$entity->id()}");
              }
              self::syncFieldsFromGroupBehaviorContent($groupContent);
              $syncedToThis = TRUE;
            }
          }
        }
      }
    }
  }

  protected static function syncFieldsToGroupContent(GroupContentInterface $thisGroupContent) {
    // Sync domain fields to all other group content with domain sync enabled type.
    /** @var FieldableEntityInterface $thisEntity */
    $thisEntity = $thisGroupContent->getEntity();
    $group = $thisGroupContent->getGroup();
    $groupType = $group->getGroupType();

    // Filtering out content plugins for users would be tedious here, so we do
    // it in ::copyFields.
    $groupContentTypeIds = \Drupal::entityQuery('group_content_type')
      ->condition('group_type', $groupType->id())
      ->execute();
    if ($groupContentTypeIds) {
      // Content entities seem to need explicit 'IN'.
      // @see \Drupal\Core\Database\Query\Condition::condition
      $groupContentIds = \Drupal::entityQuery('group_content')
        ->condition('gid', $group->id())
        ->condition('type', $groupContentTypeIds, 'IN')
        ->condition('id', $thisGroupContent->id(), '<>')
        ->execute();
      if (count($groupContentIds) <= static::BATCH_SIZE) {
        self::copyFieldsToGroupContent($thisEntity, $groupType, $groupContentIds);
      }
      else {
        $batchBuilder = (new BatchBuilder())
          ->setTitle(t('Syncing fields to group content'))
          ->setProgressMessage('')
          ->addOperation([
            static::class,
            'batchCopyFieldsToGroupContent',
          ], [$thisEntity, $groupType, $groupContentIds]);
        batch_set($batchBuilder->toArray());
      }
    }
  }

  /**
   * @param \Drupal\Core\Entity\FieldableEntityInterface $thisEntity
   * @param \Drupal\group\Entity\GroupTypeInterface $groupType
   * @param array $groupContentIds
   */
  public static function copyFieldsToGroupContent(FieldableEntityInterface $thisEntity, GroupTypeInterface $groupType, array $groupContentIds) {
    $groupContents = GroupContent::loadMultiple($groupContentIds);
    $fieldNames = FieldSyncHelpers::getFieldsToSync($groupType);
    // @todo Move this to a batch for huge entity count.
    foreach ($groupContents as $groupContent) {
      /** @var ContentEntityInterface $otherEntity */
      $otherEntity = $groupContent->getEntity();
      self::copyFields($thisEntity, $otherEntity, $fieldNames);
    }
  }

  protected static function syncFieldsFromGroupBehaviorContent(GroupContentInterface $thisGroupContent) {
    /** @var FieldableEntityInterface $thisEntity */
    $thisEntity = $thisGroupContent->getEntity();
    $group = $thisGroupContent->getGroup();
    $groupType = $group->getGroupType();
    // We ensured that this is not null in ::doPostSave.
    $groupContentPlugin = GroupBehaviorHelpers::getGroupBehaviorContentPlugin($groupType);

    $groupContentTypeIds = \Drupal::entityQuery('group_content_type')
      ->condition('group_type', $groupType->id())
      ->condition('content_plugin', $groupContentPlugin->getPluginId())
      ->execute();
    assert(count($groupContentTypeIds) === 1);
    if ($groupContentTypeIds) {
      // Content entities seem to need explicit 'IN'.
      // @see \Drupal\Core\Database\Query\Condition::condition
      $groupContentIds = \Drupal::entityQuery('group_content')
        ->condition('gid', $group->id())
        ->condition('type', $groupContentTypeIds, 'IN')
        ->execute();
      $groupContents = GroupContent::loadMultiple($groupContentIds);

      if (count($groupContents) > 1) {
        $groupContentIdsImploded = implode('|', array_keys($groupContents));
        throw new \LogicException("Group behavior entity not unique for {$thisEntity->getEntityTypeId()} {$thisEntity->id()}: $groupContentIdsImploded");
      }
      elseif (!$groupContents) {
        throw new \LogicException("No group behavior entity for {$thisEntity->getEntityTypeId()} {$thisEntity->id()}");
      }
      else {
        $fieldNames = FieldSyncHelpers::getFieldsToSync($groupType);
        $groupContent = reset($groupContents);
        /** @var ContentEntityInterface $otherEntity */
        $otherEntity = $groupContent->getEntity();
        self::copyFields($otherEntity, $thisEntity, $fieldNames);
      }
    }
  }

  /**
   * Copy fields. But not to users.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $sourceEntity
   * @param \Drupal\Core\Entity\FieldableEntityInterface $destinationEntity
   * @param array $fieldNames
   */
  protected static function copyFields(FieldableEntityInterface $sourceEntity, FieldableEntityInterface $destinationEntity, array $fieldNames) {
    if ($destinationEntity instanceof UserInterface) {
      return;
    }
    $needsSave = FALSE;
    foreach ($fieldNames as $fieldName) {
      try {
        $sourceField = $sourceEntity->get($fieldName);
        $destinationField = $destinationEntity->get($fieldName);
        if (!$sourceField->equals($destinationField)) {
          $needsSave = TRUE;
          $destinationField->setValue($sourceField->getValue());
        }
        // Ignore if field does not exist.
      } catch (\InvalidArgumentException $e) {}
    }
    // @todo Add debug setting.
    // \Drupal::messenger()->addStatus("Copying to {$destinationEntity->getEntityTypeId()} {$destinationEntity->id()}", 1);
    if ($needsSave) {
      $destinationEntity->save();
    }
  }

  public static function batchCopyFieldsToGroupContent($thisEntity, $groupType, array $groupContentIds, array &$context) {
    static::$doingDomainSync = TRUE;
    if (empty($context['sandbox'])) {
      $context['sandbox']['index'] = 0;
    }
    $groupContentIds = array_values($groupContentIds);
    $index =& $context['sandbox']['index'];
    $chunk = array_slice($groupContentIds, $index, $groupContentIds);
    self::copyFieldsToGroupContent($thisEntity, $groupType, $chunk);
    $index += count($chunk);
    $context['finished'] = $index / count($groupContentIds);
    $context['results']['index'] = $index;
    $context['results']['total'] = count($groupContentIds);
    static::$doingDomainSync = FALSE;
  }

}
