<?php

namespace Drupal\if_then_else\core\Nodes\Actions\GrantAccessFieldAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;

/**
 * Class defined to grant access field action node.
 */
class GrantAccessFieldAction extends Action {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'grant_access_field_action';
  }

  /**
   * The ifthenelse utitlities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse utitlities.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();
    $form_fields = $this->ifthenelseUtilities->getFieldsByEntityBundleId($form_entity_info);
    $event->nodes[static::getName()] = [
      'label' => $this->t('Grant Field Access'),
      'description' => $this->t('Grant Field Access'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\GrantAccessFieldAction\\GrantAccessFieldAction',
      'classArg' => ['ifthenelse.utilities'],
      'library' => 'if_then_else/GrantAccessFieldAction',
      'control_class_name' => 'GrantAccessFieldActionControl',
      'form_fields' => $form_fields,
      'inputs' => [
        'form' => [
          'label' => $this->t('Form'),
          'description' => $this->t('Form object.'),
          'sockets' => ['form'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->form_fields)) {
      $event->errors[] = $this->t('Select a field to grant access to field in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritDoc}.
   */
  public function process() {

    $form = &$this->inputs['form'];
    $field = $this->data->form_fields[0]->code;

    // Checking field exist then grant access.
    if (isset($form[$field])) {
      if ($form[$field]['#type'] == 'container') {
        if (isset($form[$field]['widget'])) {
          if (isset($form[$field]['widget']['#type'])) {
            if (isset($form[$field]['widget']['#type']) == 'select') {
              $form[$field]['widget']['#access'] = TRUE;
            }
          }
          else {
            foreach ($form[$field]['widget'] as $k => $value) {
              if (strpos($k, '#') !== FALSE) {
                // Skip all keys which have #.
                continue;
              }

              $form[$field]['widget'][$k]['#access'] = TRUE;
            }
          }
        }
      }
      else {
        $form[$field]['#access'] = TRUE;
      }
    }
    else {
      $this->setSuccess(FALSE);
      return;
    }
  }

}
