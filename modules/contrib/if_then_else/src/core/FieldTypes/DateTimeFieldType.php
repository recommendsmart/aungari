<?php

namespace Drupal\if_then_else\core\FieldTypes;

use Drupal\if_then_else\Event\FieldValueProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Date time Field type class.
 */
class DateTimeFieldType implements EventSubscriberInterface {

  /**
   * Registering event subscribers.
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      'if_then_else_datetime_field_type_process_event' => 'process',
    ];
  }

  /**
   * Process input to produce output.
   */
  public function process(FieldValueProcessEvent $event) {
    $date_original = new DrupalDateTime($event->field_value[0]['value'], 'UTC');
    $event->output = $this->dateFormatter->format($date_original->getTimestamp(), 'custom', 'Y-m-d H:i:s');
  }

}
