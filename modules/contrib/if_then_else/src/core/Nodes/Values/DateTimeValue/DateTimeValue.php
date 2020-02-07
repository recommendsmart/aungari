<?php

namespace Drupal\if_then_else\core\Nodes\Values\DateTimeValue;

use Drupal\if_then_else\core\Nodes\Values\Value;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Textvalue node class.
 */
class DateTimeValue extends Value {
  use StringTranslationTrait;

  /**
   * The Date Formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The Date Formatter.
   */
  public function __construct(DateFormatterInterface $date_formatter) {
    $this->dateFormatter = $date_formatter;
  }

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'date_time_value';
  }

  /**
   * Event subscriber of registering node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Date Time'),
      'description' => $this->t('Date Time'),
      'type' => 'value',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Values\\DateTimeValue\\DateTimeValue',
      'library' => 'if_then_else/DateTimeValue',
      'control_class_name' => 'DateTimeValueControl',
      'classArg' => ['date.formatter'],
      'outputs' => [
        'datetime' => [
          'label' => $this->t('Date Time'),
          'description' => $this->t('Date Time String'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    // $data = $event->node->data;.
  }

  /**
   * Process function for Textvalue node.
   */
  public function process() {

    $date = strtotime($this->data->value);

    $date = $this->dateFormatter->format($date, 'custom', 'Y-m-d H:i:s', drupal_get_user_timezone());
    $date = str_replace(' ', 'T', trim($date));

    // Using the storage controller.
    $this->outputs['datetime'] = $date;
  }

}
