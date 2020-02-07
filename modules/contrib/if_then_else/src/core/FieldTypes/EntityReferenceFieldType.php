<?php

namespace Drupal\if_then_else\core\FieldTypes;

use Drupal\if_then_else\Event\FieldValueProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Entity Reference Field type class.
 */
class EntityReferenceFieldType implements EventSubscriberInterface {

  /**
   * Registering event subscribers.
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      'if_then_else_entity_reference_field_type_process_event' => 'process',
    ];
  }

  /**
   * Process input to produce output.
   */
  public function process(FieldValueProcessEvent $event) {
    if (isset($event->field_value['target_id'][0]) && !empty($event->field_value['target_id'][0])) {
      if ($event->field_cardinality == 1) {
        $event->output = $event->field_value['target_id'][0]['target_id'];
      }
      elseif ($event->field_cardinality > 1 || $event->field_cardinality == -1) {
        $event->output = [];
        for ($i = 0; $i < count($event->field_value['target_id']); $i++) {
          if (isset($event->field_value['target_id'][$i]['target_id']) && !empty($event->field_value['target_id'][$i]['target_id'])) {
            $event->output[] = $event->field_value['target_id'][$i]['target_id'];
          }
        }
      }
    }
    elseif (isset($event->field_value[0]['target_id']) && !empty($event->field_value[0]['target_id'])) {
      if ($event->field_cardinality == 1) {
        $event->output = $event->field_value[0]['target_id'];
      }
      elseif ($event->field_cardinality > 1 || $event->field_cardinality == -1) {
        $event->output = [];
        for ($i = 0; $i < count($event->field_value); $i++) {
          if (isset($event->field_value[$i]['target_id']) && !empty($event->field_value[$i]['target_id'])) {
            $event->output[] = $event->field_value[$i]['target_id'];
          }
        }
      }
    }
    else {
      $event->output = "";
    }
  }

}
