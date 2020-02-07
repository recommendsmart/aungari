<?php

namespace Drupal\advance_link_attributes\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ModuleConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'advance_link_attributes_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'advance_link_attributes.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('advance_link_attributes.settings');

    $form['ala_default_classes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Define possibles classes'),
      '#default_value' => $config->get('ala_default_classes'),
      '#description' => $this->selectClassDescription(),
      '#attributes' => [
        'placeholder' => 'btn btn-default|Default button' . PHP_EOL . 'btn btn-primary|Primary button',
      ],
      '#size' => '30',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Return the description for the class select mode.
   */
  protected function selectClassDescription() {
    $description = '<p>' . $this->t('The possible classes this link can have. Enter one value per line, in the format key|label.');
    $description .= '<br/>' . $this->t('The key is the string which will be used as a class on a link. The label will be used on edit forms.');
    $description .= '<br/>' . $this->t('If the key contains several classes, each class must be separated by a <strong>space</strong>.');
    $description .= '<br/>' . $this->t('The label is optional: if a line contains a single string, it will be used as key and label.');
    $description .= '</p>';
    return $description;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('advance_link_attributes.settings')
      ->set('ala_default_classes', $form_state->getValue('ala_default_classes'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
