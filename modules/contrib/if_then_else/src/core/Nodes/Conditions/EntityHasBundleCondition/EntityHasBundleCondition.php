<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\EntityHasBundleCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Entity Has Bundle condition class.
 */
class EntityHasBundleCondition extends Condition {
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
  public static function getName() {
    return 'entity_has_bundle_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Calling custom service for if then else utilities. To
    // fetch values of entities and bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();

    $event->nodes[static::getName()] = [
      'label' => $this->t('Entity Bundle'),
      'description' => $this->t('Entity Bundle'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\EntityHasBundleCondition\\EntityHasBundleCondition',
      'classArg' => ['ifthenelse.utilities', 'logger.factory'],
      'library' => 'if_then_else/EntityHasBundleCondition',
      'control_class_name' => 'EntityHasBundleConditionControl',
      'entity_info' => $form_entity_info,
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('The entity to check the bundle and type of.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('TRUE if the provided entity is of the provided type and bundle.'),
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

    if (!property_exists($data, 'selected_entity') || !property_exists($data, 'selected_bundle')) {
      // Make sure that both selected_entity and selected_bundle are set.
      $event->errors[] = $this->t('Select both entity type and bundle in "@node_name".', ['@node_name' => $event->node->name]);
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
    $bundle = $this->data->selected_bundle->value;

    $entity_type = $entity->getEntityTypeId();
    $entity_bundle = $entity->bundle();

    $output = FALSE;
    // To match the entity's bundle and type with the specified values.
    if (($entity_bundle == $bundle) && ($entity_type == $type)) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
