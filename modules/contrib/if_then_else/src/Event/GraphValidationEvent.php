<?php

namespace Drupal\if_then_else\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Graph validation class.
 */
class GraphValidationEvent extends Event {

  const EVENT_NAME = 'graph_validation_event';

  /**
   * Graph data.
   *
   * @var data
   *   Graph data
   */
  public $data;

  /**
   * Errors.
   *
   * @var array
   *   An associative array of errors.
   */
  public $errors;

  /**
   * GraphValidationEvent constructor.
   *
   * @param object $data
   *   Graph data.
   */
  public function __construct($data) {
    $this->data = $data;
    $this->errors = [];
  }

}
