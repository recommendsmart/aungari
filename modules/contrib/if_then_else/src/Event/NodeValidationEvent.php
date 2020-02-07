<?php

namespace Drupal\if_then_else\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Node validation event class.
 */
class NodeValidationEvent extends Event {

  /**
   * Rete node id.
   *
   * @var int
   *   Node id.
   */
  public $nid;

  /**
   * Rete Node.
   *
   * @var node
   *   Node to be validated.
   */
  public $node;

  /**
   * Errors variable.
   *
   * @var errors
   *   An associative array of errors.
   */
  public $errors;

  /**
   * GraphValidationEvent constructor.
   *
   * @param int $nid
   *   Node id.
   * @param object $node
   *   Node data.
   */
  public function __construct($nid, $node) {
    $this->nid = $nid;
    $this->node = $node;
    $this->errors = [];
  }

}
