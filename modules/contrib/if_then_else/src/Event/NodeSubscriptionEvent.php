<?php

namespace Drupal\if_then_else\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event before attaching all libraries for rete nodes.
 */
class NodeSubscriptionEvent extends Event {

  const EVENT_NAME = 'node_registration_event';

  /**
   * Array of node types.
   *
   * @var array
   */
  public $nodes;

  /**
   * NodeSubscriptionEvent constructor.
   */
  public function __construct() {}

}
