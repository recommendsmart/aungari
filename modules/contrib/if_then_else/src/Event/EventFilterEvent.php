<?php

namespace Drupal\if_then_else\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 *
 */
class EventFilterEvent extends Event {

  /**
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  public $query;

  /**
   * Arguments using which a node should decide which rules to filter.
   *
   * @var array
   */
  public $args;

  /**
   * GraphValidationEvent constructor.
   *
   * @param $data
   *   Graph data.
   */
  public function __construct($query, $args) {
    $this->query = $query;
    $this->args = $args;
  }

}
