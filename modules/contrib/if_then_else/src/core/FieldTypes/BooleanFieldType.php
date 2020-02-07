<?php

namespace Drupal\if_then_else\core\FieldTypes;

use Drupal\if_then_else\Event\FieldValueProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Boolean Field type class.
 */
class BooleanFieldType implements EventSubscriberInterface {

  /**
   * Registering event subscribers.
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      'if_then_else_boolean_field_type_process_event' => 'process',
    ];
  }

  /**
   * Process input to produce output.
   */
  public function process(FieldValueProcessEvent $event) {
    if (isset($event->field_value[0]['value']) && !empty($event->field_value[0]['value'])) {
      if ($event->field_cardinality == 1) {
        $event->output = $event->field_value[0]['value'];
      }
      elseif ($event->field_cardinality > 1 || $event->field_cardinality == -1) {
        $event->output = [];
        for ($i = 0; $i < count($event->field_value); $i++) {
          if (isset($event->field_value[$i]['value']) && !empty($event->field_value[$i]['value'])) {
            $event->output[] = $event->field_value[$i]['value'];
          }
        }
      }
    }
    elseif (isset($event->field_value['value']) && !empty($event->field_value['value'])) {
      $event->output = $event->field_value['value'];
    }
    else {
      $event->output = '';
    }
  }

}
