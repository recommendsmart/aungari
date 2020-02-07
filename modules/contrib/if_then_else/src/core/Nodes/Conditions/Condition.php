<?php

namespace Drupal\if_then_else\core\Nodes\Conditions;

use Drupal\if_then_else\core\Nodes\Node;

/**
 * Condition node class Inheriting from Node class.
 */
abstract class Condition extends Node {

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
