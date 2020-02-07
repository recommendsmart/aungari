<?php

namespace Drupal\inline_entity_revision_form\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\inline_entity_revision_form\TranslationHelper;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormSimple;

/**
 * Simple inline widget.
 *
 * @FieldWidget(
 *   id = "inline_entity_revision_form_simple",
 *   label = @Translation("Inline entity revision form"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   },
 *   multiple = false
 * )
 */
class InlineEntityRevisionFormSimple extends InlineEntityFormSimple {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Trick inline_entity_form_form_alter() into attaching the handlers,
    // WidgetSubmit will be needed once extractFormValues fills the $form_state.
    $parents = array_merge($element['#field_parents'], [$items->getName()]);
    $ief_id = sha1(implode('-', $parents));
    $form_state->set(['inline_entity_revision_form', $ief_id], []);

    $element = [
      '#type' => $this->getSetting('collapsible') ? 'details' : 'fieldset',
      '#field_title' => $this->fieldDefinition->getLabel(),
      '#after_build' => [
        [get_class($this), 'removeTranslatabilityClue'],
      ],
    ] + $element;
    if ($element['#type'] == 'details') {
      $element['#open'] = !$this->getSetting('collapsed');
    }

    $item = $items->get($delta);
    if ($item->target_id && !$item->entity) {
      $element['warning']['#markup'] = $this->t('Unable to load the referenced entity.');
      return $element;
    }
    $entity = $item->entity;
    $op = $entity ? 'edit' : 'add';
    $langcode = $items->getEntity()->language()->getId();
    $parents = array_merge($element['#field_parents'], [
      $items->getName(),
      $delta,
      'inline_entity_revision_form'
    ]);

    $bundle = $this->getBundle();

    $element['inline_entity_revision_form'] = $this->getInlineEntityRevisionForm($op, $bundle, $langcode, $delta, $parents, $entity);

    if ($op == 'edit') {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      if (!$entity->access('update')) {
        // The user isn't allowed to edit the entity, but still needs to see
        // it, to be able to reorder values.
        $element['entity_label'] = [
          '#type' => 'markup',
          '#markup' => $entity->label(),
        ];
        // Hide the inline form. getInlineEntityForm() still needed to be
        // called because otherwise the field re-ordering doesn't work.
        $element['inline_entity_revision_form']['#access'] = FALSE;
      }
    }
    return $element;
  }

    /**
   * Gets inline entity form element.
   *
   * @param string $operation
   *   The operation (i.e. 'add' or 'edit').
   * @param string $bundle
   *   Entity bundle.
   * @param string $langcode
   *   Entity langcode.
   * @param array $parents
   *   Array of parent element names.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Optional entity object.
   *
   * @return array
   *   IEF form element structure.
   */
  protected function getInlineEntityRevisionForm($operation, $bundle, $langcode, $delta, array $parents, EntityInterface $entity = NULL) {

    if( empty( $bundle ) ) {
      $bundle = $this->getSetting('entity_bundle');
    }

    $element = [
      '#type' => 'inline_entity_revision_form',
      '#entity_type' => $this->getFieldSetting('target_type'),
      '#bundle' => $bundle,
      '#langcode' => $langcode,
      '#default_value' => $entity,
      '#op' => $operation,
      '#form_mode' => $this->getSetting('form_mode'),
      '#save_entity' => TRUE,
      '#ief_row_delta' => $delta,
      // Used by Field API and controller methods to find the relevant
      // values in $form_state.
      '#parents' => $parents,
      // Labels could be overridden in field widget settings. We won't have
      // access to those in static callbacks (#process, ...) so let's add
      // them here.
      '#ief_labels' => $this->getEntityTypeLabels(),
      // Identifies the IEF widget to which the form belongs.
      '#ief_id' => $this->getIefId(),
//       '#target_type' => $bundle,
//       '#autocreate' => TRUE,
// '#validate_reference' => false,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $element = parent::formMultipleElements($items, $form, $form_state);

    // If we're using ulimited cardinality we don't display one empty item. Form
    // validation will kick in if left empty which esentially means people won't
    // be able to submit w/o creating another entity.
    if (!$form_state->isSubmitted() && $element['#cardinality'] == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && $element['#max_delta'] > 0) {
      $max = $element['#max_delta'];
      unset($element[$max]);
      $element['#max_delta'] = $max - 1;
      $items->removeItem($max);
      // Decrement the items count.
      $field_name = $element['#field_name'];
      $parents = $element[0]['#field_parents'];
      $field_state = static::getWidgetState($parents, $field_name, $form_state);
      $field_state['items_count']--;
      static::setWidgetState($parents, $field_name, $form_state, $field_state);
    }

    // Remove add options if the user cannot add new entities.
    if (!$this->canAddNew()) {
      if (isset($element['add_more'])) {
        unset($element['add_more']);
      }
      foreach (Element::children($element) as $delta) {
        if (isset($element[$delta]['inline_entity_revision_form'])) {
          if ($element[$delta]['inline_entity_revision_form']['#op'] == 'add') {
            unset($element[$delta]);
          }
        }
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    if ($this->isDefaultValueWidget($form_state)) {
      $items->filterEmptyItems();
      return;
    }

    $field_name = $this->fieldDefinition->getName();
    $parents = array_merge($form['#parents'], [$field_name]);
    $submitted_values = $form_state->getValue($parents);
    $values = [];
    foreach ($items as $delta => $value) {
      $element = NestedArray::getValue($form, [$field_name, 'widget', $delta]);
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      if( isset( $element['inline_entity_revision_form']['#entity'] ) ) {
        $entity = $element['inline_entity_revision_form']['#entity'];
        $weight = isset($submitted_values[$delta]['_weight']) ? $submitted_values[$delta]['_weight'] : 0;
        $values[$weight] = ['entity' => $entity];
      }
    }

    // Sort items base on weights.
    ksort($values);
    $values = array_values($values);

    // Let the widget massage the submitted values.
    $values = $this->massageFormValues($values, $form, $form_state);

    // Assign the values and remove the empty ones.
    $items->setValue($values);
    $items->filterEmptyItems();

    // Populate the IEF form state with $items so that WidgetSubmit can
    // perform the necessary saves.
    $ief_id = sha1(implode('-', $parents));
    $widget_state = [
      'instance' => $this->fieldDefinition,
      'delete' => [],
      'entities' => [],
    ];
    foreach ($items as $delta => $value) {
      TranslationHelper::updateEntityLangcode($value->entity, $form_state);
      $widget_state['entities'][$delta] = [
        'entity' => $value->entity,
        'needs_save' => TRUE,
      ];

    }
    $form_state->set(['inline_entity_revision_form', $ief_id], $widget_state);


    // Put delta mapping in $form_state, so that flagErrors() can use it.
    $field_name = $this->fieldDefinition->getName();
    $field_state = WidgetBase::getWidgetState($form['#parents'], $field_name, $form_state);
    foreach ($items as $delta => $item) {
      $field_state['original_deltas'][$delta] = isset($item->_original_delta) ? $item->_original_delta : $delta;
      unset($item->_original_delta, $item->weight);
    }
    WidgetBase::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);


  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $entity_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    foreach ($values as $key => $value) {
      if($value['entity'] && is_object( $value['entity'] )) {
        // Add the current revision ID.
        $values[$key]['target_id'] = $value['entity']->id();
        $values[$key]['target_revision_id'] = _inline_get_latest_revision($value['entity'], $entity_type);
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
//     $handler_settings = $field_definition->getSettings()['handler_settings'];
//     $target_entity_type_id = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
//     $target_entity_type = \Drupal::entityTypeManager()->getDefinition($target_entity_type_id);
// //     The target entity type doesn't use bundles, no need to validate them.
//     if (!$target_entity_type->getKey('bundle')) {
//       return TRUE;
//     }

//     if (empty($handler_settings['target_bundles'])) {
//       return FALSE;
//     }

//     if (count($handler_settings['target_bundles']) != 1) {
//       return FALSE;
//     }

    return TRUE;
  }

  /**
   * Gets the bundle for the inline entity.
   *
   * @return string|null
   *   The bundle, or NULL if not known.
   */
  protected function getBundle() {
    if (!empty($this->getFieldSetting('handler_settings')['target_bundles'])) {
      return reset($this->getFieldSetting('handler_settings')['target_bundles']);
    }
  }

    /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'form_mode' => 'default',
      'override_labels' => FALSE,
      'label_singular' => '',
      'label_plural' => '',
      'collapsible' => FALSE,
      'collapsed' => FALSE,
      'entity_bundle' => '',
    ];
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $entity_type_id = $this->getFieldSetting('target_type');
    $states_prefix = 'fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings]';
    $element = [];
    $element['form_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Form mode'),
      '#default_value' => $this->getSetting('form_mode'),
      '#options' => $this->entityDisplayRepository->getFormModeOptions($entity_type_id),
      '#required' => TRUE,
    ];
    $element['override_labels'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override labels'),
      '#default_value' => $this->getSetting('override_labels'),
    ];
    $element['label_singular'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Singular label'),
      '#default_value' => $this->getSetting('label_singular'),
      '#states' => [
        'visible' => [
          ':input[name="' . $states_prefix . '[override_labels]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $element['label_plural'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Plural label'),
      '#default_value' => $this->getSetting('label_plural'),
      '#states' => [
        'visible' => [
          ':input[name="' . $states_prefix . '[override_labels]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $element['collapsible'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Collapsible'),
      '#default_value' => $this->getSetting('collapsible'),
    ];
    $element['collapsed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Collapsed by default'),
      '#default_value' => $this->getSetting('collapsed'),
      '#states' => [
        'visible' => [
          ':input[name="' . $states_prefix . '[collapsible]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['entity_bundle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Entity Bundle'),
      '#default_value' => $this->getSetting('entity_bundle'),
    ];

    return $element;
  }

}
