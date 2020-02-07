<?php

namespace Drupal\if_then_else\core\FieldTypes;

use Drupal\if_then_else\Event\FieldValueProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use stdClass;

/**
 * Text Field type class.
 */
class TextFieldType implements EventSubscriberInterface {

  /**
   * Registering event subscribers.
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      'if_then_else_text_field_type_process_event' => 'process',
    ];
  }

  /**
   * Process input to produce output.
   */
  public function process(FieldValueProcessEvent $event) {
    if (isset($event->field_value[0]['value']) && !empty($event->field_value[0]['value'])) {
      $output_value = new stdClass();
      if ($event->field_cardinality == 1) {
        $output_value->value = $event->field_value[0]['value'];
        $output_value->format = $event->field_value[0]['format'];
        $event->output = $output_value;
      }
      elseif ($event->field_cardinality > 1 || $event->field_cardinality == -1) {
        for ($i = 0; $i < count($event->field_value); $i++) {
          if (isset($event->field_value[$i]['value']) && !empty($event->field_value[$i]['value'])) {
            $output_value = new stdClass();
            $output_value->value = $event->field_value[$i]['value'];
            $output_value->format = $event->field_value[$i]['format'];
            $event->output[$i] = $output_value;
          }
        }
      }
    }
    else {
      $event->output = '';
    }
  }

}
