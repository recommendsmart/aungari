<?php

namespace Drupal\if_then_else\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event before attaching all libraries for rete nodes.
 */
class SocketSubscriptionEvent extends Event {

  const EVENT_NAME = 'socket_registration_event';

  /**
   * Array of socket types.
   *
   * @var array
   */
  public $sockets;

}
