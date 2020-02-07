<?php

namespace Drupal\if_then_else\core\Nodes\Events\ViewIsLoadedEvent;

use Drupal\if_then_else\core\Nodes\Events\Event;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\if_then_else\Event\EventConditionEvent;
use Drupal\if_then_else\Event\EventFilterEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;

/**
 * A view is loaded event class.
 */
class ViewIsLoadedEvent extends Event {
  use StringTranslationTrait;

  /**
   * The ifthenelse Utilities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse Utilities.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
  }

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'view_is_loaded_event';
  }

  /**
   * Event subscriber for View Is Loaded event node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch values of views name and display ID.
    $views_lists = $this->ifthenelseUtilities->getViewsNameAndDisplay();

    $event->nodes[static::getName()] = [
      'label' => $this->t('View Load'),
      'description' => $this->t('View Load'),
      'type' => 'event',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Events\\ViewIsLoadedEvent\\ViewIsLoadedEvent',
      'library' => 'if_then_else/ViewIsLoadedEvent',
      'control_class_name' => 'ViewIsLoadedEventControl',
      'entity_info' => $views_lists,
      'classArg' => ['ifthenelse.utilities'],
      'outputs' => [
        'view' => [
          'label' => $this->t('View'),
          'description' => $this->t('View executable object.'),
          'socket' => 'object.view',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;

    if (empty($data->selected_display_id) || empty($data->selected_view_name)) {
      // Make sure that both selected_entity and selected_bundle are set.
      $event->errors[] = $this->t('Select both view name and display ID in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions(EventConditionEvent $event) {
    $data = $event->data;
    if (!empty($data->selected_display_id) && !empty($data->selected_view_name)) {
      $event->conditions[] = self::getName() . '_' . $data->selected_view_name->value . '_' . $data->selected_display_id->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function filterEvents(EventFilterEvent $event) {
    $view = $event->args['view'];
    $view_name = $view->id();
    $view_display_id = $view->current_display;
    if (empty($view_name) && empty($view_display_id)) {
      $event->query->condition('event', '');
      return;
    }
    $event->query->condition('condition', self::getName() . '_' . $view_name . '_' . $view_display_id, 'CONTAINS');
  }

}
