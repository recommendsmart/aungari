<?php

namespace Drupal\alter_entity_autocomplete\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityType;

/**
 * Defines a form that configures forms module settings.
 */
class EntityAutocompleteFieldConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alter_entity_autocomplete_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alter_entity_autocomplete.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alter_entity_autocomplete.settings');
    $content_entity_types = [];
    $entity_type_definations = \Drupal::entityTypeManager()->getDefinitions();
    /* @var $definition EntityTypeInterface */
    foreach ($entity_type_definations as $definition) {
      if ($definition instanceof ContentEntityType) {
        $content_entity_types[] = $definition;
      }
    }
    $form['alter_autocomplete_entity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Entity machine name'),
      '#default_value' => $config->get('alter_autocomplete_entity'),
    ];
    $form['alter_autocomplete_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Field Name'),
      '#default_value' => $config->get('alter_autocomplete_field_name'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alter_entity_autocomplete.settings')
      ->set('alter_autocomplete_entity', $form_state->getValue('alter_autocomplete_entity'))
      ->set('alter_autocomplete_field_name', $form_state->getValue('alter_autocomplete_field_name'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
