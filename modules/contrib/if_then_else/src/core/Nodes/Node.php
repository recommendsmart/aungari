<?php

namespace Drupal\if_then_else\core\Nodes;

use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Node abstract class.
 */
abstract class Node implements EventSubscriberInterface {

  protected $inputs;
  protected $outputs;

  /**
   * Current condition data object.
   *
   * @var object
   */
  protected $data;

  /**
   * Returns node name.
   *
   * @return string
   *   Node name.
   */
  abstract public static function getName();

  /**
   * Registering event subscribers.
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      NodeSubscriptionEvent::EVENT_NAME => 'registerNode',
      'if_then_else_' . static::getName() . '_graph_validation_event' => 'validateGraph',
      'if_then_else_' . static::getName() . '_node_validation_event' => 'validateNode',
    ];
  }

  /**
   * Register the node so that If Then Else controller recognizes it.
   *
   * @param \Drupal\if_then_else\Event\NodeSubscriptionEvent $event
   *   Event node.
   */
  abstract public function registerNode(NodeSubscriptionEvent $event);

  /**
   * Validates the node.
   *
   * @param \Drupal\if_then_else\Event\NodeValidationEvent $event
   *   Event node.
   */
  public function validateNode(NodeValidationEvent $event) {}

  /**
   * Validates the graph.
   *
   * @param \Drupal\if_then_else\Event\GraphValidationEvent $event
   *   Event node.
   */
  public function validateGraph(GraphValidationEvent $event) {}

  /**
   * Set input values for a node.
   *
   * @param array $inputs
   *   Sets the input array.
   */
  public function setInputs(array $inputs) {
    $this->inputs = $inputs;
  }

  /**
   * Return output of node.
   *
   * @return array
   *   Returns an array of outputs.
   */
  public function getOutputs() {
    return $this->outputs;
  }

  /**
   * Process input to produce output.
   */
  abstract public function process();

  /**
   * Set user-selected data.
   *
   * @param object $data
   *   Node data.
   */
  public function setData($data) {
    $this->data = $data;
  }

}
