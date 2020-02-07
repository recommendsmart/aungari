<?php

namespace Drupal\group_behavior;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupTypeInterface;

class GroupBehaviorFormHooks {

  public static function alterGroupTypeEditForm(&$form, FormStateInterface $form_state, $form_id) {
    /** @var \Drupal\Core\Entity\EntityFormInterface $formObject */
    $formObject = $form_state->getFormObject();
    /** @var \Drupal\group\Entity\GroupTypeInterface $groupType */
    $groupType = $formObject->getEntity();

    $form['third_party_settings']['#tree'] = TRUE;
    $tpsForm =&$form['third_party_settings']['group_behavior'];
    $tpsForm['#type'] = 'fieldset';
    $tpsForm['#title'] = t('Group Behavior');
    $tpsForm['#description'] = t('Cares about creating, deleting and enriching entities with a shadow group.');

    $tpsForm['content_plugin'] = [
      '#type' => 'select',
      '#title' => t('Content type'),
      '#description' => t('If a content of this type is created, updated, deleted, a corresponding group is created, updated, deleted.'),
      '#default_value' => $groupType->getThirdPartySetting('group_behavior', 'content_plugin'),
      '#options' => self::contentPluginOptions($groupType),
      '#empty_value' => '',
    ];

    // Care that our settings go the right way.
    $form['#entity_builders'][] = [static::class, 'groupTypeBuilder'];
  }

  /**
   * TPS group content type entity builder.
   */
  public static function groupTypeBuilder($entity_type, ConfigEntityInterface $type, &$form, FormStateInterface $form_state) {
    $settings = $form_state->getValue(['third_party_settings', 'group_behavior']);
    $type->setThirdPartySetting('group_behavior', 'content_plugin', $settings['content_plugin']);
  }

  private static function contentPluginOptions(GroupTypeInterface $groupType) {
    /** @var \Drupal\group\Plugin\GroupContentEnablerManagerInterface $pluginManager */
    $pluginManager = \Drupal::service('plugin.manager.group_content_enabler');
    $options = [];
    foreach ($pluginManager->getInstalledIds($groupType) as $id) {
      $plugin = $pluginManager->getDefinition($id);
      $options[$id] = $plugin ? $plugin['label'] : t('Broken plugin');
    }
    return $options;
  }

}
