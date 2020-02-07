<?php

namespace Drupal\if_then_else\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 *
 */
class EventConditionEvent extends Event {

  /**
   * @var object
   *   Graph data
   */
  public $data;

  /**
   * @var array
   */
  public $conditions;

  /**
   * GraphValidationEvent constructor.
   *
   * @param $data
   *   Graph data.
   */
  public function __construct($data) {
    $this->data = $data;
    $this->conditions = [];
  }

}
