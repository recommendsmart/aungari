<?php

namespace Drupal\if_then_else\core\FieldTypes;

use Drupal\if_then_else\Event\FieldValueProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use stdClass;

/**
 * Link Field type class.
 */
class LinkFieldType implements EventSubscriberInterface {

  /**
   * Registering event subscribers.
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      'if_then_else_link_field_type_process_event' => 'process',
    ];
  }

  /**
   * Process input to produce output.
   */
  public function process(FieldValueProcessEvent $event) {
    if (isset($event->field_value[0]['uri']) && !empty($event->field_value[0]['uri'])) {
      $output_value = new stdClass();
      if ($event->field_cardinality == 1) {
        $output_value->uri = $event->field_value[0]['uri'];
        $output_value->title = $event->field_value[0]['title'];
        $event->output = $output_value;
      }
      elseif ($event->field_cardinality > 1 || $event->field_cardinality == -1) {
        $event->output = [];
        for ($i = 0; $i < count($event->field_value); $i++) {
          if (isset($event->field_value[$i]['uri']) && !empty($event->field_value[$i]['uri'])) {
            $output_value = new stdClass();
            $output_value->uri = $event->field_value[0]['uri'];
            $output_value->title = $event->field_value[0]['title'];
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
