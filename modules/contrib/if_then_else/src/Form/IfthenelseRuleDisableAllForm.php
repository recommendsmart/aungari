<?php

namespace Drupal\if_then_else\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityConfirmFormBase;

/**
 * Defines a class to build a ifthenelseRule disable all entity form.
 *
 * @see \Drupal\if_then_else
 */
class IfthenelseRuleDisableAllForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "confirm_disable_all_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.ifthenelserule.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to disable all If Then Else?');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Fetch all rules if active then disable it.
    $query = \Drupal::entityQuery('ifthenelserule')
      ->condition('active', TRUE);
    $enabled_rule_ids = $query->execute();
    $entitys = \Drupal::entityTypeManager()->getStorage('ifthenelserule')->loadMultiple($enabled_rule_ids);
    foreach ($entitys as $entity) {
      $entity->setActive(FALSE);
      $entity->save();
    }
    \Drupal::messenger()->addMessage($this->t('All Ifthenelse rules are disabled.'));
    $path = $this->getCancelUrl();
    $form_state->setRedirectUrl($path);
  }

}
