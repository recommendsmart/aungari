<?php

namespace Drupal\if_then_else\core\FieldTypes;

use Drupal\if_then_else\Event\FieldValueProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use stdClass;

/**
 * Image Field type class.
 */
class ImageFieldType implements EventSubscriberInterface {

  /**
   * Registering event subscribers.
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      'if_then_else_image_field_type_process_event' => 'process',
    ];
  }

  /**
   * Process input to produce output.
   */
  public function process(FieldValueProcessEvent $event) {
    if (isset($event->field_value[0]['target_id']) && !empty($event->field_value[0]['target_id'])) {
      $output_value = new stdClass();
      if ($event->field_cardinality == 1) {
        $output_value->alt = $event->field_value[0]['alt'];
        $output_value->fids = $event->field_value[0]['target_id'];
        $output_value->width = $event->field_value[0]['width'];
        $output_value->height = $event->field_value[0]['height'];
        $output_value->description = "";
        $output_value->title = $event->field_value[0]['title'];
        $event->output = $output_value;
      }
      elseif ($event->field_cardinality > 1 || $event->field_cardinality == -1) {
        $event->output = [];
        for ($i = 0; $i < count($event->field_value); $i++) {
          if (isset($event->field_value[$i]['target_id']) && !empty($event->field_value[$i]['target_id'])) {
            $output_value = new stdClass();
            $output_value->alt = $event->field_value[$i]['alt'];
            $output_value->fids = $event->field_value[$i]['target_id'];
            $output_value->width = $event->field_value[$i]['width'];
            $output_value->height = $event->field_value[$i]['height'];
            $output_value->description = "";
            $output_value->title = $event->field_value[$i]['title'];
            $event->output[$i] = $output_value;
          }
        }
      }
    }
    elseif (isset($event->field_value[0]['fids']) && !empty($event->field_value[0]['fids'])) {
      $output_value = new stdClass();
      if ($event->field_cardinality == 1) {
        $output_value->alt = $event->field_value[0]['alt'];
        $output_value->fids = $event->field_value[0]['fids'];
        $output_value->width = $event->field_value[0]['width'];
        $output_value->height = $event->field_value[0]['height'];
        $output_value->description = "";
        $output_value->title = $event->field_value[0]['title'];
        $event->output = $output_value;
      }
      elseif ($event->field_cardinality > 1 || $event->field_cardinality == -1) {
        $event->output = [];
        for ($i = 0; $i < count($event->field_value); $i++) {
          if (isset($event->field_value[$i]['fids']) && !empty($event->field_value[$i]['fids'])) {
            $output_value = new stdClass();
            $output_value->alt = $event->field_value[$i]['alt'];
            $output_value->fids = $event->field_value[$i]['fids'];
            $output_value->width = $event->field_value[$i]['width'];
            $output_value->height = $event->field_value[$i]['height'];
            $output_value->description = "";
            $output_value->title = $event->field_value[$i]['title'];
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
