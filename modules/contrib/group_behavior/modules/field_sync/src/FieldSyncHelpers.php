<?php

namespace Drupal\group_behavior_field_sync;

use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group_behavior\GroupBehaviorHelpers;

class FieldSyncHelpers {

  /**
   * @param \Drupal\group\Entity\GroupTypeInterface $groupType
   *
   * @return mixed
   */
  public static function getFieldsToSync(GroupTypeInterface $groupType) {
    return $groupType->getThirdPartySetting('group_behavior', 'field_sync');
  }


  public static function groupBehaviorBundleFieldOptions(GroupTypeInterface $groupType) {
    $fieldOptions = [];
    $fieldDefinitions = self::groupBehaviorBundleFieldDefinitions($groupType);
    foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
      if (!$fieldDefinition->isComputed() && !$fieldDefinition->isReadOnly()) {
        $fieldOptions[$fieldName] = $fieldDefinition->getLabel();
      }
    }
    asort($fieldOptions);
    return $fieldOptions;
  }

  /**
   * @param \Drupal\group\Entity\GroupTypeInterface $groupType
   *
   * @return array|\Drupal\Core\Field\FieldDefinitionInterface[]
   */
  public static function groupBehaviorBundleFieldDefinitions(GroupTypeInterface $groupType) {
    $contentPlugin = GroupBehaviorHelpers::getGroupBehaviorContentPlugin($groupType);
    if ($contentPlugin) {
      $entityTypeId = $contentPlugin->getEntityTypeId();
      $bundle = $contentPlugin->getEntityBundle();
      /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
      $entityFieldManager = \Drupal::service('entity_field.manager');
      $fieldDefinitions = $entityFieldManager->getFieldDefinitions($entityTypeId, $bundle);
    }
    else {
      $fieldDefinitions = [];
    }
    return $fieldDefinitions;
  }

}
