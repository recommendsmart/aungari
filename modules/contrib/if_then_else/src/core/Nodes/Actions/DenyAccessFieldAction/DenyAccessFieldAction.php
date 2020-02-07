<?php

namespace Drupal\if_then_else\core\Nodes\Actions\DenyAccessFieldAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;

/**
 * Class defined to deny access form field action node.
 */
class DenyAccessFieldAction extends Action {
  use StringTranslationTrait;

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
   * Return name of node.
   */
  public static function getName() {
    return 'deny_access_field_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();
    $form_fields = $this->ifthenelseUtilities->getFieldsByEntityBundleId($form_entity_info);
    $event->nodes[static::getName()] = [
      'label' => $this->t('Deny Field Access'),
      'description' => $this->t('Deny Field Access'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\DenyAccessFieldAction\\DenyAccessFieldAction',
      'classArg' => ['ifthenelse.utilities'],
      'library' => 'if_then_else/DenyAccessFieldAction',
      'control_class_name' => 'DenyAccessFieldActionControl',
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
      $event->errors[] = $this->t('Select a field to deny access in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritDoc}.
   */
  public function process() {

    $form = &$this->inputs['form'];
    $field = $this->data->form_fields[0]->code;

    // Checking field exist then disable access.
    if (isset($form[$field])) {
      if ($form[$field]['#type'] == 'container') {
        if (isset($form[$field]['widget'])) {
          if (isset($form[$field]['widget']['#type'])) {
            if (isset($form[$field]['widget']['#type']) == 'select') {
              $form[$field]['widget']['#access'] = FALSE;
            }
          }
          else {
            foreach ($form[$field]['widget'] as $k => $value) {
              if (strpos($k, '#') !== FALSE) {
                // Skip all keys which have #.
                continue;
              }

              $form[$field]['widget'][$k]['#access'] = FALSE;
            }
          }
        }
      }
      else {
        $form[$field]['#access'] = FALSE;
      }
    }
    else {
      $this->setSuccess(FALSE);
      return;
    }

  }

}
