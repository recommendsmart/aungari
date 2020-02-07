<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\EntityIsOfTypeCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Entity is of type condition class.
 */
class EntityIsOfTypeCondition extends Condition {
  use StringTranslationTrait;

  /**
   * The ifthenelse utilities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'entity_is_of_type_condition';
  }

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse utilities.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities, LoggerChannelFactoryInterface $loggerFactory) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
    $this->loggerFactory = $loggerFactory->get('if_then_else');
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Calling custom service for if then else utilities. To
    // fetch values of entities and bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();

    $event->nodes[static::getName()] = [
      'label' => $this->t('Entity Type'),
      'description' => $this->t('Entity Type'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\EntityIsOfTypeCondition\\EntityIsOfTypeCondition',
      'classArg' => ['ifthenelse.utilities', 'logger.factory'],
      'library' => 'if_then_else/EntityIsOfTypeCondition',
      'control_class_name' => 'EntityIsOfTypeConditionControl',
      'entity_info' => $form_entity_info,
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('The entity to check for a type.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('TRUE if the entity is of the provided type.'),
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

    if (!property_exists($data, 'selected_entity')) {
      // Make sure that both selected_entity and selected_bundle are set.
      $event->errors[] = $this->t('Select entity type in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    /** @var \Drupal\Core\Entity\EntityBase $entity */
    $entity = $this->inputs['entity'];

    if (!$entity) {
      $this->loggerFactory->notice($this->t("Rule @node_name did not run as the instance of the entity could not be found", ['@node_name' => $this->data->name]));
      $this->setSuccess(FALSE);
      return;
    }
    $type = $this->data->selected_entity->value;

    $entity_type = $entity->getEntityTypeId();

    $output = FALSE;
    // To match the entity's type with the specified values.
    if ($entity_type == $type) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
