<?php

namespace Drupal\if_then_else\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config form for Ifthenelse settings.
 */
class IfthenelseSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'if_then_else.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ifthenelse_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('if_then_else.adminsettings');

    $form['enable_debugging'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Debugging'),
      '#description' => $this->t('This will enable debugging for If Then Else.'),
      '#default_value' => $config->get('enable_debugging'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('if_then_else.adminsettings')
      ->set('enable_debugging', $form_state->getValue('enable_debugging'))
      ->save();
  }

}
