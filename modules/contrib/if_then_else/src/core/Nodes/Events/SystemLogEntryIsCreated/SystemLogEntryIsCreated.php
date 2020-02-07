<?php

namespace Drupal\if_then_else\core\Nodes\Events\SystemLogEntryIsCreated;

use Drupal\if_then_else\core\Nodes\Events\Event;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * System Log Entry Is Created event node class.
 */
class SystemLogEntryIsCreated extends Event {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'system_log_entry_is_created_event';
  }

  /**
   * Event subscriber for System Log Entry Is Created event node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('System Log Entry Is Created'),
      'description' => $this->t('System Log Entry Is Created'),
      'type' => 'event',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Events\\SystemLogEntryIsCreated\\SystemLogEntryIsCreated',
    ];
  }

}
