<?php

namespace Drupal\group_behavior_field_sync;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormStateInterface;

class FieldSyncFormHooks {

  /**
   * Alter the group content type edit form.
   *
   * @see \Drupal\group_behavior\GroupBehaviorFormHooks::alterGroupTypeEditForm
   */
  public static function alterGroupTypeEditForm(&$form, FormStateInterface $form_state, $form_id) {
    /** @var \Drupal\Core\Entity\EntityFormInterface $formObject */
    $formObject = $form_state->getFormObject();
    /** @var \Drupal\group\Entity\GroupTypeInterface $groupType */
    $groupType = $formObject->getEntity();

    $tpsForm =&$form['third_party_settings']['group_behavior'];
    $tpsForm['field_sync'] = [
      '#type' => 'checkboxes',
      '#title' => t('Fields to sync'),
      '#description' => t('The field machine names to sync from group behavior to all group content (not users).'),
      '#default_value' => FieldSyncHelpers::getFieldsToSync($groupType),
      '#options' => FieldSyncHelpers::groupBehaviorBundleFieldOptions($groupType),
    ];

    // Care that our settings go the right way.
    $form['#entity_builders'][] = [static::class, 'groupTypeBuilder'];
  }

  /**
   * TPS group content type entity builder.
   */
  public static function groupTypeBuilder($entity_type, ConfigEntityInterface $type, &$form, FormStateInterface $form_state) {
    $fieldsToSync = $form_state->getValue(['third_party_settings', 'group_behavior', 'field_sync']);
    $fieldsToSync = array_keys(array_filter($fieldsToSync));
    sort($fieldsToSync);
    $type->setThirdPartySetting('group_behavior', 'field_sync', $fieldsToSync);
  }

}
