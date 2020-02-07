<?php

namespace Drupal\if_then_else\core\Nodes\Actions;

use Drupal\if_then_else\core\Nodes\Node;

/**
 * Action class inheriting Node class.
 */
abstract class Action extends Node {

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
