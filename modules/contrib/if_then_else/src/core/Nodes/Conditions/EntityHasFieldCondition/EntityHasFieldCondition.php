<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\EntityHasFieldCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Entity has field condition class.
 */
class EntityHasFieldCondition extends Condition {
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
    return 'entity_has_field_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();
    $entity_fields = $this->ifthenelseUtilities->getFieldsByEntityBundleId($form_entity_info);

    $event->nodes[static::getName()] = [
      'label' => $this->t('Entity Has Field'),
      'description' => $this->t('Entity Has Field'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\EntityHasFieldCondition\\EntityHasFieldCondition',
      'classArg' => ['ifthenelse.utilities', 'logger.factory'],
      'library' => 'if_then_else/EntityHasFieldCondition',
      'control_class_name' => 'EntityHasFieldConditionControl',
      'entity_fields' => $entity_fields,
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('The entity to check for the provided field.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('TRUE if the provided entity has the provided field.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'value' && $node->data->name == 'text_value') {
        // To check empty input.
        foreach ($node->outputs->text->connections as $connection) {
          if ($connection->input == 'field' &&  empty($node->data->value)) {
            $event->errors[] = $this->t('Enter value for field in "@node_name".', ['@node_name' => $node->name]);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;

    if ($data->field_selection == 'list' &&  empty($data->entity_fields)) {
      $event->errors[] = $this->t('Select field that you want to check for in "@node_name".', ['@node_name' => $event->node->name]);
    }
    if ($data->field_selection == 'input' &&  (!property_exists($data, 'valueText') || empty($data->valueText))) {
      $event->errors[] = $this->t('Enter field name that you want to check for in "@node_name".', ['@node_name' => $event->node->name]);
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

    $field_selection_type = $this->data->field_selection;
    if ($field_selection_type == 'list') {
      $field = $this->data->entity_fields[0]->code;
    }
    elseif ($field_selection_type == 'input') {
      $field = Html::escape(trim($this->data->valueText));
    }

    $this->outputs['success'] = $entity->hasField($field);
  }

}
