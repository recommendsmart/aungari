<?php

namespace Drupal\if_then_else\core\Nodes\Actions\StickyNodeAction;

use Drupal\node\NodeInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Make a node sticky action class.
 */
class StickyNodeAction extends Action {
  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'sticky_node_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Make Node Sticky'),
      'description' => $this->t('Make Node Sticky'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\StickyNodeAction\\StickyNodeAction',
      'classArg' => ['entity_type.manager'],
      'inputs' => [
        'node' => [
          'label' => $this->t('Nid / Node'),
          'description' => $this->t('Nid or Node object. Can be of any bundle.'),
          'sockets' => ['number', 'object.entity.node'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritDoc}.
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;

    foreach ($nodes as $node) {

      if ($node->data->type == 'event' && $node->data->name == 'entity_load_event') {
        if ((property_exists($node->data, 'selected_entity') && $node->data->selected_entity->value == 'node') ||
          (property_exists($node->data, 'selection') && $node->data->selection == 'all')) {
          $event->errors[] = $this->t('Event trigger is an entity load. This may call the If Then Else flow to go into an infinity loop.');
        }
      }
      if ($node->data->type == 'value' && $node->data->name == 'entity_value') {
        if (!property_exists($node->data, 'selected_entity')) {
          $event->errors[] = $this->t('There are an error to process this rule. Please select content entity to process "@node_name" rule', ['@node_name' => $event->node->name]);
        }
        elseif ($node->data->selected_entity->value != 'node') {
          $event->errors[] = $this->t('There are an error to process this rule. Please select content entity to process "@node_name" rule', ['@node_name' => $event->node->name]);
        }
      }
    }
  }

  /**
   * {@inheritDoc}.
   */
  public function process() {

    $node = $this->inputs['node'];

    if (is_numeric($node)) {
      $node = $this->entityTypeManager->getStorage('node')->load($node);
      if (empty($node)) {
        $this->setSuccess(FALSE);
        return;
      }
    }
    elseif (!$node instanceof NodeInterface) {
      $this->setSuccess(FALSE);
      return;
    }

    $node->setSticky(TRUE);
    $node->save();
  }

}
