<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\UserHasEntityFieldAccessCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * User has entity field access condition class.
 */
class UserHasEntityFieldAccessCondition extends Condition {
  use StringTranslationTrait;

  /**
   * The ifthenelse utilities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse utilities.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities, EntityTypeManagerInterface $entity_manager) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'user_has_entity_field_access_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();
    $form_fields = $this->ifthenelseUtilities->getFieldsByEntityBundleId($form_entity_info);
    $event->nodes[static::getName()] = [
      'label' => $this->t('User Has Entity Field Access'),
      'description' => $this->t('User Has Entity Field Access'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\UserHasEntityFieldAccessCondition\\UserHasEntityFieldAccessCondition',
      'library' => 'if_then_else/UserHasEntityFieldAccessCondition',
      'control_class_name' => 'UserHasEntityFieldAccessConditionControl',
      'form_fields' => $form_fields,
      'classArg' => ['ifthenelse.utilities', 'entity_type.manager'],
      'opt_options' => [
        ['code' => 'view', 'name' => 'View'],
        ['code' => 'edit', 'name' => 'Edit'],
      ],
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('The entity to check access on.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
        'user' => [
          'label' => $this->t('User'),
          'description' => $this->t('The user account to check access against.'),
          'sockets' => ['object.entity.user'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('TRUE if the user has access to the field on the entity, FALSE otherwise.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->form_fields)|| empty($data->opt_form_fields)) {
      $event->errors[] = $this->t('Select both field and operation in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $entity = $this->inputs['entity'];
    $user = $this->inputs['user'];

    $selected_field = $this->data->form_fields[0]->code;
    $selected_operation = $this->data->opt_form_fields[0]->code;
    if (!$entity->hasField($selected_field)) {
      $this->outputs['success'] = FALSE;
      return;
    }

    $access = $this->entityTypeManager->getAccessControlHandler($entity->getEntityTypeId());
    if (!$access->access($entity, $selected_operation, $user)) {
      $this->outputs['success'] = FALSE;
      return;
    }
    $definition = $entity->getFieldDefinition($selected_field);
    $items = $entity->get($selected_field);

    if ($access->fieldAccess($selected_operation, $definition, $user, $items)) {
      $this->outputs['success'] = TRUE;
      return;
    }

  }

}
