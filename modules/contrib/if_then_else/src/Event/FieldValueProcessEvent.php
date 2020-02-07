<?php

namespace Drupal\if_then_else\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event for processing field value outputs.
 */
class FieldValueProcessEvent extends Event {

  const EVENT_NAME = 'field_value_process_event';

  /**
   * Field value processed output.
   *
   * @var array
   */
  public $field_value;

  /**
   * Field value processed output.
   *
   * @var int
   */
  public $field_cardinality;

  /**
   * Field value processed output.
   *
   * @var array
   */
  public $output;

  /**
   * Constructs the object.
   *
   * @param string $field_type
   *   Field type.
   * 
   * @param array $field_value
   *   Field value retured by getvalue.
   *
   * @param string $field_cardinality
   *   Field cardinality.
   */
  public function __construct($field_value, $field_cardinality, $output="") {
    $this->field_value = $field_value;
    $this->field_cardinality = $field_cardinality;
    $this->output = $output;
  }

}
