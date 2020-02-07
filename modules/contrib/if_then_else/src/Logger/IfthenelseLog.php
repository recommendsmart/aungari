<?php

namespace Drupal\if_then_else\Logger;

use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;
use Drupal\if_then_else\Controller\IfThenElseController;

/**
 * IfthenelseLog subscriber class.
 */
class IfthenelseLog implements LoggerInterface {

  use RfcLoggerTrait;

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    IfThenElseController::process('system_log_entry_is_created_event', []);
  }

}
