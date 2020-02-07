<?php

namespace Drupal\if_then_else\core\Nodes\Actions\AddToLogAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class defined to execute add to log action.
 */
class AddToLogAction extends Action {
  use StringTranslationTrait;
  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory) {
    $this->loggerFactory = $loggerFactory->get('if_then_else');
  }

  /**
   * Return node name.
   */
  public static function getName() {
    return 'add_to_log_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Log'),
      'description' => $this->t('Log'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\AddToLogAction\\AddToLogAction',
      'library' => 'if_then_else/AddToLogAction',
      'control_class_name' => 'AddToLogActionControl',
      'classArg' => ['logger.factory'],
      'compare_options' => [
        ['code' => 'emergency', 'name' => 'Emergency'],
        ['code' => 'alert', 'name' => 'Alert'],
        ['code' => 'critical', 'name' => 'Critical'],
        ['code' => 'error', 'name' => 'Error'],
        ['code' => 'warning', 'name' => 'Warning'],
        ['code' => 'notice', 'name' => 'Notice'],
        ['code' => 'info', 'name' => 'Info'],
        ['code' => 'debug', 'name' => 'Debug'],
      ],
      'inputs' => [
        'message' => [
          'label' => $this->t('Message'),
          'description' => $this->t('Message string'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->selected_severity->code)) {
      $event->errors[] = $this->t('Select at least one severity in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * Process add to log action node.
   */
  public function process() {
    $severity = $this->data->selected_severity->code;
    $set_message_text = $this->inputs['message'];
    if (!empty($severity) && !empty($set_message_text)) {
      $this->loggerFactory->{$severity}($set_message_text);
    }
    else {
      $this->setSuccess(FALSE);
      return;
    }
  }

}
