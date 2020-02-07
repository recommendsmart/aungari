<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SetMessageAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Class defined to execute set message action node.
 */
class SetMessageAction extends Action {
  use StringTranslationTrait;

  /**
   * The module manager.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * Return node name.
   */
  public static function getName() {
    return 'set_message_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Set Message'),
      'description' => $this->t('Set Message'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SetMessageAction\\SetMessageAction',
      'classArg' => ['messenger'],
      'library' => 'if_then_else/SetMessageAction',
      'control_class_name' => 'SetMessageActionControl',
      'severity_options' => ['status', 'warning', 'error'],
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
   * Validation for make fields required action node.
   */
  public function validateNode(NodeValidationEvent $event) {
    // Make sure that severity option is selected.
    if (!property_exists($event->node->data, "selected_options")) {
      $event->errors[] = $this->t('Select at least one severity in "@node_name".', ['@node_name' => $evclearent->node->name]);
    }
  }

  /**
   * Process set message action node.
   */
  public function process() {
    if (isset($this->data->selected_options->label) && !empty($this->inputs['message'])) {
      $set_message_text = $this->inputs['message'];
      $severity = $this->data->selected_options->label;
      $this->messenger->addMessage($this->t($set_message_text), $severity);
    }
    else {
      $this->setSuccess(FALSE);
      return;
    }
  }

}
