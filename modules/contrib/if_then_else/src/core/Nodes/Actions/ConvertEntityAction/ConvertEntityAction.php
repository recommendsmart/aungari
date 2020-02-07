<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ConvertEntityAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Convert Entity action class.
 */
class ConvertEntityAction extends Action {
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
    return 'convert_entity_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Calling custom service for if then else utilities. To
    // fetch values of entities and bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();

    $event->nodes[static::getName()] = [
      'label' => $this->t('Cast To Entity Type'),
      'description' => $this->t('Cast to a Entity type and bundle type with correct socket.'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ConvertEntityAction\\ConvertEntityAction',
      'classArg' => ['ifthenelse.utilities', 'entity_type.manager'],
      'library' => 'if_then_else/ConvertEntityAction',
      'control_class_name' => 'ConvertEntityActionControl',
      'component_class_name' => 'ConvertEntityActionComponent',
      'entity_info' => $form_entity_info,
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
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
    $entity = $this->inputs['entity'];
    $entityType = $entity->getEntityTypeId();
    $bundleType = $entity->bundle();
    $inputEntityType = $this->data->selected_entity->value;
    $inputBundleType = $this->data->selected_bundle->value;
    if ($entityType == $inputEntityType &&  $bundleType == $inputBundleType) {
      $this->outputs['entity'] = $entity;
    }
    else {
      $this->outputs['entity'] = '';
    }
  }
}
