<?php

namespace Drupal\if_then_else\core\Nodes\Events;

use Drupal\if_then_else\core\Nodes\Node;
use Drupal\if_then_else\Event\EventConditionEvent;
use Drupal\if_then_else\Event\EventFilterEvent;

/**
 * Event node class inheriting node class.
 */
abstract class Event extends Node {

  /**
   * Get subscribed events.
   */
  public static function getSubscribedEvents() {
    $subscribed_events = parent::getSubscribedEvents();
    $subscribed_events['if_then_else_' . static::getName() . '_event_condition_event'] = 'getConditions';
    $subscribed_events['if_then_else_' . static::getName() . '_event_filter_event'] = 'filterEvents';
    return $subscribed_events;
  }

  /**
   * Returns an empty array.
   */
  public static function getInputs() {
    return [];
  }

  /**
   * Get all condition.
   *
   * Adds a condition string to $event->conditions array so that relevant rules
   * can be filtered efficiently later.
   *
   * @param \Drupal\if_then_else\Event\EventConditionEvent $event
   */
  public function getConditions(EventConditionEvent $event) {}

  /**
   * Adds conditions to $event->query so that only relevant rules are returns.
   *
   * @param \Drupal\if_then_else\Event\EventFilterEvent $event
   */
  public function filterEvents(EventFilterEvent $event) {}

  /**
   * Process function for event class.
   */
  public function process() {
    $this->outputs = $this->inputs;
  }

  /**
   * Set the success socket of the node.
   *
   * @param bool $success
   *   Value to which the socket needs to be set to.
   */
  public function setSuccess(bool $success) {
    $this->outputs['success'] = $success;
  }

}
