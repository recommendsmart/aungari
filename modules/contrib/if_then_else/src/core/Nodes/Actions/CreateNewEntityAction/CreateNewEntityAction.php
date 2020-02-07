<?php

namespace Drupal\if_then_else\core\Nodes\Actions\CreateNewEntityAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Create New Entity action class.
 */
class CreateNewEntityAction extends Action {
  use StringTranslationTrait;

  /**
   * The ifthenelse utitlities.
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
   *   The ifthenelse utitlities.
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
    return 'create_new_entity_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Calling custom service for if then else utilities. To
    // fetch values of entities and bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();

    $event->nodes[static::getName()] = [
      'label' => $this->t('Create New Entity'),
      'description' => $this->t('Create New Entity'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\CreateNewEntityAction\\CreateNewEntityAction',
      'classArg' => ['ifthenelse.utilities', 'entity_type.manager'],
      'library' => 'if_then_else/CreateNewEntityAction',
      'control_class_name' => 'CreateNewEntityActionControl',
      'component_class_name' => 'CreateNewEntityActionComponent',
      'entity_info' => $form_entity_info,
      'outputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('Entity Object'),
          'socket' => 'object.entity',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;

    if (!property_exists($data, 'selected_entity') || !property_exists($data, 'selected_bundle')) {
      // Make sure that both selected_entity and selected_bundle are set.
      $event->errors[] = $this->t('Select both entity type and bundle in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $type = $this->data->selected_entity->value;
    $bundle = $this->data->selected_bundle->value;
    if (empty($type) || empty($bundle)) {
      $this->setSuccess(FALSE);
      return;
    }
    $entity = $this->entityTypeManager->getStorage($type)->create(['type' => $bundle]);

    $this->outputs['entity'] = $entity;

  }

}
